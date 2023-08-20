<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Recipe_Image;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Unit;
use App\Models\IngredientRecipe;

class RecipesController extends Controller
{

    function getRecipe($recipeId)
    {
        $recipe = Recipe::with([
            'comments.user',
            'likes.user',
            'images',
            'cuisine',
            'ingredients.unit'
        ])->findOrFail($recipeId);
    
        $formattedRecipe = [
            "id" => $recipe->id,
            "cuisine" => $recipe->cuisine->name,
            "title" => $recipe->title,
            "description" => $recipe->description,
            "directions" => $recipe->directions,
            "created_at" => $recipe->created_at,
            "updated_at" => $recipe->updated_at,
            "comments" => $recipe->comments->map(function ($comment) {
                return [
                    "id" => $comment->id,
                    "user_id" => $comment->user_id,
                    "recipe_id" => $comment->recipe_id,
                    "text" => $comment->text,
                    "created_at" => $comment->created_at,
                    "updated_at" => $comment->updated_at,
                    "username" => $comment->user->name, 
                ];
            }),
            "likes" => $recipe->likes->map(function ($like) {
                return [
                    "id" => $like->id,
                    "user_id" => $like->user_id,
                    "created_at" => $like->created_at,
                    "username" => $like->user->name, 
                ];
            }),
            "images" => $recipe->images,
            "ingredients" => $recipe->ingredients->map(function ($ingredient) {
                return [
                    "id" => $ingredient->id,
                    "name" => $ingredient->name,
                    "quantity" => $ingredient->pivot->quantity,
                    "unit" => $ingredient->unit->name,
                    "unit_id" => $ingredient->pivot->unit_id,
                ];
            })
        ];

        return response()->json($formattedRecipe);
    }
    }
    
    