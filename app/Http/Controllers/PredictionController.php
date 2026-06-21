<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Prediction;

class PredictionController extends Controller
{
    /**
     * Flask ML API endpoint.
     */
    private string $apiUrl = 'http://127.0.0.1:5000/predict';

    /**
     * Display the public prediction input form.
     */
    public function index()
    {
        return view('prediction.index');
    }

    /**
     * Process the prediction inputs via Flask API and show results.
     */
    public function result(Request $request)
    {
        // Validate inputs matching the actual dataset columns
        $validated = $request->validate([
            'luas_tanah'     => 'required|numeric|min:1',
            'luas_bangunan'  => 'required|numeric|min:1',
            'kmr_tidur'      => 'required|integer|min:1|max:10',
            'kmr_mandi'      => 'required|integer|min:1|max:5',
            'lokasi'         => 'required|string|in:Ligung,Jatiwangi,Argapura,Majalengka,Sumberjaya,Kertajati,Lainnya',
            'tipe_properti'  => 'required|string|in:Subsidi,Minimalis,Mewah',
            'kondisi'        => 'required|string|in:Baru,Bekas',
            'usia'           => 'required|integer|min:0|max:50',
            'ada_garasi'     => 'nullable',
            'jarak'          => 'nullable|numeric|min:0',
        ]);

        // Build the feature payload for the Flask API
        $payload = [
            'tahun'         => (int) date('Y'),
            'luas_tanah'    => (int) $validated['luas_tanah'],
            'luas_bangunan' => (int) $validated['luas_bangunan'],
            'kmr_tidur'     => (int) $validated['kmr_tidur'],
            'kmr_mandi'     => (int) $validated['kmr_mandi'],
            'usia'          => (int) $validated['usia'],
            'ada_garasi'    => $request->has('ada_garasi') ? 1 : 0,
            'lokasi'        => $validated['lokasi'],
            'tipe_properti' => $validated['tipe_properti'],
            'kondisi'       => $validated['kondisi'],
            'jarak'         => isset($validated['jarak']) ? (float) $validated['jarak'] : 0,
        ];

        try {
            // Call the Flask ML microservice
            $response = Http::timeout(10)->post($this->apiUrl, $payload);

            if ($response->failed()) {
                return back()->withErrors(['api' => 'Gagal menghubungi model prediksi. Silakan coba lagi.'])->withInput();
            }

            $result = $response->json();

            $price       = (int) $result['price'];
            $minPrice    = (int) $result['min_price'];
            $maxPrice    = (int) $result['max_price'];
            $confidence  = (int) $result['confidence'];
            $category    = $result['category'];
            $featureImportances = $result['feature_importances'];
            $accuracyPct = $result['accuracy_pct'] ?? 0;
            $r2Test      = $result['r2_test'] ?? 0;

        } catch (\Exception $e) {
            return back()->withErrors(['api' => 'Model prediksi sedang tidak tersedia: ' . $e->getMessage()])->withInput();
        }

        // Save prediction to database
        Prediction::create([
            'input_data'          => $payload,
            'predicted_price'     => $price,
            'min_price'           => $minPrice,
            'max_price'           => $maxPrice,
            'confidence'          => $confidence,
            'category'            => $category,
            'feature_importances' => $featureImportances,
        ]);

        // Map feature importances to human-readable labels
        $featureLabels = [
            'luas_bangunan'         => 'Luas Bangunan',
            'lokasi_encoded'        => 'Lokasi (Kecamatan)',
            'luas_tanah'            => 'Luas Tanah',
            'usia'                  => 'Usia Bangunan',
            'tipe_properti_encoded' => 'Tipe Properti',
            'kmr_tidur'             => 'Jumlah Kamar Tidur',
            'tahun'                 => 'Tahun Data',
            'ada_garasi'            => 'Ketersediaan Garasi',
            'kmr_mandi'             => 'Jumlah Kamar Mandi',
            'kondisi_encoded'       => 'Kondisi Bangunan',
        ];

        // Build sorted importance list for the view (top 4)
        $importanceDisplay = [];
        $i = 0;
        $colors = ['bg-primary', 'bg-secondary', 'bg-tertiary-container', 'bg-outline-variant'];
        foreach ($featureImportances as $key => $value) {
            if ($i >= 4) break;
            $importanceDisplay[] = [
                'label'   => $featureLabels[$key] ?? $key,
                'value'   => round($value * 100),
                'color'   => $colors[$i] ?? 'bg-outline-variant',
            ];
            $i++;
        }

        // Extract form values for display
        $luasTanah       = $payload['luas_tanah'];
        $luasBangunan    = $payload['luas_bangunan'];
        $kamarTidur      = $payload['kmr_tidur'];
        $kamarMandi      = $payload['kmr_mandi'];
        $lokasi          = $payload['lokasi'];
        $tipeProperti    = $payload['tipe_properti'];
        $kondisi         = $payload['kondisi'];
        $usia            = $payload['usia'];
        $adaGarasi       = $payload['ada_garasi'];

        return view('prediction.result', compact(
            'price',
            'minPrice',
            'maxPrice',
            'confidence',
            'category',
            'importanceDisplay',
            'luasTanah',
            'luasBangunan',
            'kamarTidur',
            'kamarMandi',
            'lokasi',
            'tipeProperti',
            'kondisi',
            'usia',
            'adaGarasi',
            'accuracyPct',
            'r2Test'
        ));
    }
}
