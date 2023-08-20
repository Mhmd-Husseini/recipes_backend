<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Comment;
use App\Models\Like;
use App\Models\RecipeImage;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\IngredientRecipe;

class Recipe extends Model
{
    use HasFactory;
    protected $fillable = [
        'cuisine_id',
        'title',
        'description',
        'directions',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(RecipeImage::class);
    }

    public function cuisine(): BelongsTo
    {
        return $this->belongsTo(Cuisine::class);
    }

    public function ingredients()
{
    return $this->belongsToMany(Ingredient::class)
        ->using(IngredientRecipe::class)
        ->withPivot('quantity');
        
}
}


