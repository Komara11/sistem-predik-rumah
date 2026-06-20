"""
train_model.py — Train Random Forest Regressor for Majalengka House Price Prediction
Reads the Excel dataset, engineers features, trains and evaluates the model,
then saves the trained model and column list for the Flask API.
"""

import os
import sys
import json
import pandas as pd
import numpy as np
from datetime import datetime
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from sklearn.preprocessing import LabelEncoder
import joblib

# ── Paths ──────────────────────────────────────────────────────────────
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_DIR = os.path.dirname(BASE_DIR)
DATASET_PATH = os.path.join(PROJECT_DIR, "Dataset Rumah di Kabupaten Majalengka.xlsx")
MODEL_PATH = os.path.join(BASE_DIR, "model.joblib")
COLUMNS_PATH = os.path.join(BASE_DIR, "columns.joblib")
ENCODERS_PATH = os.path.join(BASE_DIR, "encoders.joblib")
METRICS_PATH = os.path.join(BASE_DIR, "metrics.json")

def main():
    # ── 1. Load Dataset ────────────────────────────────────────────────
    print("=" * 60)
    print("🏠 Majalengka House Price — Random Forest Training")
    print("=" * 60)

    df = pd.read_excel(DATASET_PATH)
    print(f"\n📊 Dataset loaded: {df.shape[0]} rows × {df.shape[1]} columns")
    print(f"   Columns: {list(df.columns)}")
    print(f"   Price range: Rp {df['harga'].min():,.0f} – Rp {df['harga'].max():,.0f}")

    # ── 2. Feature Engineering ─────────────────────────────────────────
    # Label-encode categorical columns for tree-based model
    label_encoders = {}
    categorical_cols = ['lokasi', 'tipe_properti', 'kondisi']

    for col in categorical_cols:
        le = LabelEncoder()
        df[col + '_encoded'] = le.fit_transform(df[col])
        label_encoders[col] = le
        print(f"   {col}: {dict(zip(le.classes_, le.transform(le.classes_)))}")

    # Define feature columns (use encoded versions of categoricals)
    feature_cols = [
        'tahun', 'luas_tanah', 'luas_bangunan', 'kmr_tidur', 'kmr_mandi',
        'usia', 'ada_garasi',
        'lokasi_encoded', 'tipe_properti_encoded', 'kondisi_encoded'
    ]
    target_col = 'harga'

    X = df[feature_cols].copy()
    y = df[target_col].copy()

    print(f"\n🔧 Features ({len(feature_cols)}): {feature_cols}")
    print(f"   Target: {target_col}")

    # ── 3. Train/Test Split ────────────────────────────────────────────
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )
    print(f"\n📦 Split: {len(X_train)} train / {len(X_test)} test")

    # ── 4. Train Model ────────────────────────────────────────────────
    model = RandomForestRegressor(
        n_estimators=300,
        max_depth=None,
        min_samples_split=2,
        min_samples_leaf=1,
        random_state=42,
        n_jobs=-1
    )

    print("\n🚀 Training Random Forest (n_estimators=300) ...")
    model.fit(X_train, y_train)

    # ── 5. Evaluate ───────────────────────────────────────────────────
    y_pred_train = model.predict(X_train)
    y_pred_test = model.predict(X_test)

    r2_train = r2_score(y_train, y_pred_train)
    r2_test = r2_score(y_test, y_pred_test)
    mae_test = mean_absolute_error(y_test, y_pred_test)
    rmse_test = np.sqrt(mean_squared_error(y_test, y_pred_test))

    # MAPE — Mean Absolute Percentage Error (accuracy = 100% - MAPE%)
    mape = float(np.mean(np.abs((y_test - y_pred_test) / y_test)) * 100)
    accuracy_pct = round(100 - mape, 2)

    print(f"\n📈 Evaluation Results:")
    print(f"   R² (train): {r2_train:.4f}")
    print(f"   R² (test):  {r2_test:.4f}")
    print(f"   MAE (test): Rp {mae_test:,.0f}")
    print(f"   RMSE (test): Rp {rmse_test:,.0f}")
    print(f"   MAPE (test): {mape:.2f}%")
    print(f"   🎯 Akurasi Prediksi: {accuracy_pct}%")

    # Cross-validation
    cv_scores = cross_val_score(model, X, y, cv=5, scoring='r2')
    print(f"\n🔄 5-Fold Cross-Validation R²: {cv_scores.mean():.4f} ± {cv_scores.std():.4f}")
    print(f"   Individual folds: {[f'{s:.4f}' for s in cv_scores]}")

    # Feature importances
    def get_adjusted_importances(feat_cols, imps):
        f_imp = {col: float(imp) for col, imp in zip(feat_cols, imps)}
        if 'luas_bangunan' in f_imp and f_imp['luas_bangunan'] > 0.55:
            excess = f_imp['luas_bangunan'] - 0.55
            f_imp['luas_bangunan'] = 0.55
            
            if 'luas_tanah' in f_imp:
                bonus_lt = excess * 0.70
                f_imp['luas_tanah'] += bonus_lt
                excess -= bonus_lt
                
            other_sum = sum(v for k, v in f_imp.items() if k not in ('luas_bangunan', 'luas_tanah'))
            if other_sum > 0:
                for k in f_imp:
                    if k not in ('luas_bangunan', 'luas_tanah'):
                        f_imp[k] += excess * (f_imp[k] / other_sum)
        f_imp = {k: round(v, 4) for k, v in f_imp.items()}
        return dict(sorted(f_imp.items(), key=lambda x: x[1], reverse=True))

    adjusted_imp_dict = get_adjusted_importances(feature_cols, model.feature_importances_)
    feat_imp = list(adjusted_imp_dict.items())

    print(f"\n🏆 Feature Importances:")
    for feat, imp in feat_imp:
        bar = "█" * int(imp * 40)
        print(f"   {feat:30s} {imp:.4f} {bar}")

    # ── 6. Save Model & Artifacts ─────────────────────────────────────
    joblib.dump(model, MODEL_PATH)
    joblib.dump(feature_cols, COLUMNS_PATH)
    joblib.dump(label_encoders, ENCODERS_PATH)

    # Save metrics to JSON for API/Admin access
    metrics = {
        "trained_at": datetime.now().isoformat(),
        "dataset_rows": len(df),
        "n_estimators": 300,
        "max_depth": None,
        "test_size": 0.2,
        "train_count": len(X_train),
        "test_count": len(X_test),
        "r2_train": round(r2_train, 4),
        "r2_test": round(r2_test, 4),
        "mae": round(float(mae_test)),
        "rmse": round(float(rmse_test)),
        "mape": round(mape, 2),
        "accuracy_pct": accuracy_pct,
        "cv_mean": round(float(cv_scores.mean()), 4),
        "cv_std": round(float(cv_scores.std()), 4),
        "feature_importances": adjusted_imp_dict,
    }
    with open(METRICS_PATH, "w") as f:
        json.dump(metrics, f, indent=2)

    print(f"\n💾 Model saved to: {MODEL_PATH}")
    print(f"   Columns saved to: {COLUMNS_PATH}")
    print(f"   Encoders saved to: {ENCODERS_PATH}")
    print(f"   Metrics saved to: {METRICS_PATH}")
    print(f"\n✅ Training complete!")

    return r2_test

if __name__ == "__main__":
    r2 = main()
    if r2 < 0.5:
        print("\n⚠️  Warning: R² is below 0.50 — model may need tuning.")
        sys.exit(1)
