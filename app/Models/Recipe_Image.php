<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe_Image extends Model
{
    use HasFactory;
    protected $fillable = [
        'recipe_id',
        'image_url',
    ];
}
