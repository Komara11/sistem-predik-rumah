<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'tahun',
        'luas_tanah',
        'luas_bangunan',
        'kmr_tidur',
        'kmr_mandi',
        'usia',
        'lokasi',
        'tipe_properti',
        'kondisi',
        'ada_garasi',
        'harga',
    ];

    protected $casts = [
        'ada_garasi' => 'boolean',
        'harga' => 'integer',
    ];
}
