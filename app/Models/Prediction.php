<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    protected $fillable = [
        'input_data',
        'predicted_price',
        'min_price',
        'max_price',
        'confidence',
        'category',
        'feature_importances',
    ];

    protected $casts = [
        'input_data' => 'array',
        'feature_importances' => 'array',
        'predicted_price' => 'integer',
        'min_price' => 'integer',
        'max_price' => 'integer',
    ];
}
