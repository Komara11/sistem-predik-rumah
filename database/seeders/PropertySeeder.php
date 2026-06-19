<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PropertySeeder extends Seeder
{
    /**
     * Seed the properties table from the Excel dataset.
     */
    public function run(): void
    {
        $filePath = base_path('Dataset Rumah di Kabupaten Majalengka.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("Dataset file not found: {$filePath}");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);

        // First row is the header
        $header = array_shift($rows);
        $headerMap = array_flip(array_map('trim', $header));

        $count = 0;
        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row[$this->getCol($headerMap, 'harga')])) {
                continue;
            }

            Property::create([
                'tahun'         => (int) $row[$this->getCol($headerMap, 'tahun')],
                'luas_tanah'    => (int) $row[$this->getCol($headerMap, 'luas_tanah')],
                'luas_bangunan' => (int) $row[$this->getCol($headerMap, 'luas_bangunan')],
                'kmr_tidur'     => (int) $row[$this->getCol($headerMap, 'kmr_tidur')],
                'kmr_mandi'     => (int) $row[$this->getCol($headerMap, 'kmr_mandi')],
                'usia'          => (int) $row[$this->getCol($headerMap, 'usia')],
                'lokasi'        => trim($row[$this->getCol($headerMap, 'lokasi')]),
                'tipe_properti' => trim($row[$this->getCol($headerMap, 'tipe_properti')]),
                'kondisi'       => trim($row[$this->getCol($headerMap, 'kondisi')]),
                'ada_garasi'    => (bool) $row[$this->getCol($headerMap, 'ada_garasi')],
                'harga'         => (int) $row[$this->getCol($headerMap, 'harga')],
            ]);
            $count++;
        }

        $this->command->info("✅ Seeded {$count} properties from dataset.");
    }

    /**
     * Get the Excel column letter for a given header name.
     */
    private function getCol(array $headerMap, string $name): string
    {
        return array_search($name, array_flip($headerMap));
    }
}
