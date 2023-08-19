<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe_Ingredient extends Model
{
    use HasFactory;
    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'unit_id',
        'quantity',
    ];
}
