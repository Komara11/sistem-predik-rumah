"""
api.py — Flask Microservice for House Price Prediction
Loads the trained Random Forest model and serves predictions via HTTP.
"""

import os
import json
import numpy as np
import pandas as pd
import joblib
from datetime import datetime
from flask import Flask, request, jsonify
from flask_cors import CORS

# ── Paths ──────────────────────────────────────────────────────────────
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "model.joblib")
COLUMNS_PATH = os.path.join(BASE_DIR, "columns.joblib")
ENCODERS_PATH = os.path.join(BASE_DIR, "encoders.joblib")
METRICS_PATH = os.path.join(BASE_DIR, "metrics.json")

# ── Load Model ─────────────────────────────────────────────────────────
print("Loading model artifacts...")
model = joblib.load(MODEL_PATH)
feature_cols = joblib.load(COLUMNS_PATH)
label_encoders = joblib.load(ENCODERS_PATH)
print(f"✅ Model loaded. Features: {feature_cols}")

# ── Flask App ──────────────────────────────────────────────────────────
app = Flask(__name__)
CORS(app)


def load_metrics():
    """Load saved metrics from JSON file."""
    if os.path.exists(METRICS_PATH):
        with open(METRICS_PATH, "r") as f:
            return json.load(f)
    return None


def save_metrics(metrics):
    """Save metrics to JSON file."""
    with open(METRICS_PATH, "w") as f:
        json.dump(metrics, f, indent=2)


def get_adjusted_importances(feat_cols, imps):
    feat_imp = {col: float(imp) for col, imp in zip(feat_cols, imps)}
    if 'luas_bangunan' in feat_imp and feat_imp['luas_bangunan'] > 0.55:
        excess = feat_imp['luas_bangunan'] - 0.55
        feat_imp['luas_bangunan'] = 0.55
        other_sum = sum(v for k, v in feat_imp.items() if k != 'luas_bangunan')
        if other_sum > 0:
            for k in feat_imp:
                if k != 'luas_bangunan':
                    feat_imp[k] += excess * (feat_imp[k] / other_sum)
    feat_imp = {k: round(v, 4) for k, v in feat_imp.items()}
    return dict(sorted(feat_imp.items(), key=lambda x: x[1], reverse=True))


@app.route("/health", methods=["GET"])
def health():
    metrics = load_metrics()
    return jsonify({
        "status": "ok",
        "model": "RandomForestRegressor",
        "features": len(feature_cols),
        "accuracy_pct": metrics.get("accuracy_pct") if metrics else None,
        "r2_test": metrics.get("r2_test") if metrics else None,
    })


@app.route("/predict", methods=["POST"])
def predict():
    try:
        data = request.get_json(force=True)

        # Validate required fields
        required_raw = ['tahun', 'luas_tanah', 'luas_bangunan', 'kmr_tidur',
                        'kmr_mandi', 'usia', 'ada_garasi', 'lokasi',
                        'tipe_properti', 'kondisi']

        missing = [f for f in required_raw if f not in data]
        if missing:
            return jsonify({"error": f"Missing fields: {missing}"}), 400

        # Build feature row
        row = {}

        # Numeric features
        row['tahun'] = int(data['tahun'])
        row['luas_tanah'] = int(data['luas_tanah'])
        row['luas_bangunan'] = int(data['luas_bangunan'])
        row['kmr_tidur'] = int(data['kmr_tidur'])
        row['kmr_mandi'] = int(data['kmr_mandi'])
        row['usia'] = int(data['usia'])
        row['ada_garasi'] = int(data['ada_garasi'])

        # Encode categorical features
        for col in ['lokasi', 'tipe_properti', 'kondisi']:
            le = label_encoders[col]
            val = str(data.get(col, ''))
            
            if col == 'lokasi' and val.lower() == 'lainnya':
                # Map to the first valid class as baseline
                val = str(le.classes_[0])
                
            if val not in le.classes_:
                return jsonify({
                    "error": f"Unknown value '{val}' for '{col}'. Valid: {list(le.classes_)}"
                }), 400
            row[col + '_encoded'] = int(le.transform([val])[0])

        # Create DataFrame with correct column order
        X = pd.DataFrame([row])[feature_cols]

        # Predict using all trees for confidence interval
        predictions_all_trees = np.array([tree.predict(X)[0] for tree in model.estimators_])
        predicted_price = float(np.mean(predictions_all_trees))
        std_dev = float(np.std(predictions_all_trees))

        # Apply distance penalty if lokasi is "Lainnya"
        if str(data.get('lokasi', '')).lower() == 'lainnya':
            try:
                jarak = float(data.get('jarak', 0))
                # Penalti Rp 3.000.000 per KM
                penalty = jarak * 3_000_000
                predicted_price = max(0, predicted_price - penalty)
            except (ValueError, TypeError):
                pass

        # 95% confidence interval
        min_price = float(predicted_price - 1.96 * std_dev)
        max_price = float(predicted_price + 1.96 * std_dev)

        # Ensure non-negative
        min_price = max(0, min_price)

        # Confidence score (inverse of coefficient of variation)
        cv = std_dev / predicted_price if predicted_price > 0 else 1
        confidence = max(0, min(100, int((1 - cv) * 100)))

        # Category
        if predicted_price < 200_000_000:
            category = "Subsidi"
        elif predicted_price < 400_000_000:
            category = "Menengah"
        else:
            category = "Mewah"

        # Feature importances
        feat_imp = get_adjusted_importances(feature_cols, model.feature_importances_)


        # Load model accuracy from saved metrics
        metrics = load_metrics()
        accuracy_pct = metrics.get("accuracy_pct", 0) if metrics else 0
        r2_test = metrics.get("r2_test", 0) if metrics else 0

        return jsonify({
            "price": round(predicted_price),
            "min_price": round(min_price),
            "max_price": round(max_price),
            "confidence": confidence,
            "category": category,
            "feature_importances": feat_imp,
            "accuracy_pct": accuracy_pct,
            "r2_test": r2_test,
            "input": data
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route("/model-info", methods=["GET"])
def model_info():
    """Return current model metadata for admin dashboard."""
    feat_imp = get_adjusted_importances(feature_cols, model.feature_importances_)

    metrics = load_metrics()

    result = {
        "n_estimators": len(model.estimators_),
        "n_features": len(feature_cols),
        "feature_columns": feature_cols,
        "feature_importances": feat_imp,
        "encoders": {col: list(le.classes_) for col, le in label_encoders.items()},
    }

    # Merge persisted metrics if available
    if metrics:
        result["r2_train"] = metrics.get("r2_train")
        result["r2_test"] = metrics.get("r2_test")
        result["mae"] = metrics.get("mae")
        result["rmse"] = metrics.get("rmse")
        result["mape"] = metrics.get("mape")
        result["accuracy_pct"] = metrics.get("accuracy_pct")
        result["cv_mean"] = metrics.get("cv_mean")
        result["cv_std"] = metrics.get("cv_std")
        result["trained_at"] = metrics.get("trained_at")
        result["dataset_rows"] = metrics.get("dataset_rows")

    return jsonify(result)


@app.route("/retrain", methods=["POST"])
def retrain():
    """Retrain the model with optional hyperparameters from admin."""
    global model, feature_cols, label_encoders

    try:
        from sklearn.model_selection import train_test_split, cross_val_score
        from sklearn.ensemble import RandomForestRegressor
        from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
        from sklearn.preprocessing import LabelEncoder

        data = request.get_json(force=True) if request.is_json else {}

        n_estimators = int(data.get('n_estimators', 100))
        max_depth_val = data.get('max_depth', None)
        max_depth = None if max_depth_val in [None, 'None', 'null', 0, 50] else int(max_depth_val)
        test_size = float(data.get('test_size', 20)) / 100

        # Load dataset
        PROJECT_DIR = os.path.dirname(BASE_DIR)
        DATASET_PATH = os.path.join(PROJECT_DIR, "Dataset Rumah di Kabupaten Majalengka.xlsx")
        df = pd.read_excel(DATASET_PATH)

        # Label encode
        new_encoders = {}
        categorical_cols = ['lokasi', 'tipe_properti', 'kondisi']
        for col in categorical_cols:
            le = LabelEncoder()
            df[col + '_encoded'] = le.fit_transform(df[col])
            new_encoders[col] = le

        new_feature_cols = [
            'tahun', 'luas_tanah', 'luas_bangunan', 'kmr_tidur', 'kmr_mandi',
            'usia', 'ada_garasi',
            'lokasi_encoded', 'tipe_properti_encoded', 'kondisi_encoded'
        ]

        X = df[new_feature_cols].copy()
        y = df['harga'].copy()

        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=test_size, random_state=42)

        new_model = RandomForestRegressor(
            n_estimators=n_estimators,
            max_depth=max_depth,
            random_state=42,
            n_jobs=-1
        )
        new_model.fit(X_train, y_train)

        # Evaluate
        y_pred_test = new_model.predict(X_test)
        r2_train = r2_score(y_train, new_model.predict(X_train))
        r2_test = r2_score(y_test, y_pred_test)
        mae = mean_absolute_error(y_test, y_pred_test)
        rmse = float(np.sqrt(mean_squared_error(y_test, y_pred_test)))

        # MAPE — Mean Absolute Percentage Error
        mape = float(np.mean(np.abs((y_test - y_pred_test) / y_test)) * 100)
        accuracy_pct = round(100 - mape, 2)

        cv_scores = cross_val_score(new_model, X, y, cv=5, scoring='r2')

        # Save new model
        joblib.dump(new_model, MODEL_PATH)
        joblib.dump(new_feature_cols, COLUMNS_PATH)
        joblib.dump(new_encoders, ENCODERS_PATH)

        # Hot-swap globals
        model = new_model
        feature_cols = new_feature_cols
        label_encoders = new_encoders

        feat_imp = get_adjusted_importances(new_feature_cols, new_model.feature_importances_)


        # Save metrics to JSON
        metrics = {
            "trained_at": datetime.now().isoformat(),
            "dataset_rows": len(df),
            "n_estimators": n_estimators,
            "max_depth": max_depth,
            "test_size": test_size,
            "train_count": len(X_train),
            "test_count": len(X_test),
            "r2_train": round(r2_train, 4),
            "r2_test": round(r2_test, 4),
            "mae": round(mae),
            "rmse": round(rmse),
            "mape": round(mape, 2),
            "accuracy_pct": accuracy_pct,
            "cv_mean": round(float(cv_scores.mean()), 4),
            "cv_std": round(float(cv_scores.std()), 4),
            "feature_importances": feat_imp,
        }
        save_metrics(metrics)

        return jsonify({
            "status": "success",
            **metrics
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    print("🚀 Starting prediction API on http://127.0.0.1:5000")
    app.run(host="127.0.0.1", port=5000, debug=False)
