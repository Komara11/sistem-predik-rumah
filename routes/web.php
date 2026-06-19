<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PredictionController;

use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    $accuracy = 84.63;
    $datasetRows = 150;

    try {
        $response = Http::timeout(2)->get('http://127.0.0.1:5000/model-info');
        if ($response->successful()) {
            $data = $response->json();
            $accuracy = $data['accuracy_pct'] ?? 84.63;
            $datasetRows = $data['dataset_rows'] ?? 150;
        }
    } catch (\Exception $e) {
        // Fallback to defaults
    }

    return view('home', compact('accuracy', 'datasetRows'));
})->name('home');

Route::get('/prediksi', [PredictionController::class, 'index'])->name('prediction.index');
Route::get('/prediksi/hasil', [PredictionController::class, 'result'])->name('prediction.result');

Route::get('/kalkulator-kpr', function () {
    return view('kpr');
})->name('kpr.index');
