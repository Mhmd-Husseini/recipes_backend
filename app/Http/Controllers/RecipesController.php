<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Comment;
use App\Models\Like;
use App\Models\RecipeImage; 
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Unit;
use App\Models\IngredientRecipe;

class RecipesController extends Controller{

    function getRecipe($recipeId = null){
        $user = Auth::user();
        $user_id = $user->id;
    if ($recipeId){
        $recipe = Recipe::with([
            'comments.user',
            'likes.user',
            'images',
            'cuisine',
            'ingredients.unit'
        ])->findOrFail($recipeId);
    
        $is_liked = $recipe->likes->pluck('user_id')->contains($user_id);
        $getRecipe = [
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
            "is_liked" => $is_liked,
            "ingredients" => $recipe->ingredients->map(function ($ingredient) {
                return [
                    "id" => $ingredient->id,
                    "name" => $ingredient->name,
                    "quantity" => $ingredient->pivot->quantity,
                    "unit" => $ingredient->unit->name,
                    "unit_id" => $ingredient->pivot->unit_id,
                ];
            }),         
        ];
        return response()->json($getRecipe);
    }else{
        $recipes = Recipe::withCount('likes')
        ->with(['images' => function ($query) {
            $query->select('recipe_id', 'image_url'); 
        }, 'cuisine'])
        ->get()
        ->map(function ($recipe) {
            return [
                "id" => $recipe->id,
                "cuisine" => $recipe->cuisine->name,
                "title" => $recipe->title,
                "description" => $recipe->description,
                "likes_count" => $recipe->likes_count,
                "images" => $recipe->images->map(function ($image) {
                    return [
                        "image_url" => $image->image_url,
                    ];
                }),
            ];
        });
    
    return response()->json($recipes);   
}}

    public function likeRecipe(Request $request, $recipeId){
        $user = Auth::user();
        $recipe = Recipe::findOrFail($recipeId);

        $existingLike = Like::where('user_id', $user->id)
            ->where('recipe_id', $recipe->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            return response()->json(['message' => 'Like removed']);
        }

        $like = new Like();
        $like->user_id = $user->id;
        $like->recipe_id = $recipe->id;
        $like->save();

        return response()->json(['message' => 'Recipe liked']);
    }

    public function addComment(Request $request, $recipeId){
        $user = Auth::user();
        $recipe = Recipe::findOrFail($recipeId);

        $this->validate($request, [
            'text' => 'required|string',
        ]);

        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->recipe_id = $recipe->id;
        $comment->text = $request->input('text');
        $comment->save();

        $commentWithUser = Comment::with('user')->find($comment->id);

        return response()->json([
            'message' => 'Comment added',
            'comment' => $commentWithUser, 
        ]);
    }

    public function addRecipe(Request $request)
{
    try {

    $validatedData = $request->validate([
        'cuisine_id' => 'required',
        'title' => 'required',
        'description' => 'required',
        'directions' => 'required',
        'images.*' => 'required|string', 
        'ingredients' => 'required|array|min:1',
        'ingredients.*.id' => 'required|exists:ingredients,id',
        'ingredients.*.quantity' => 'required|numeric',
    ]);

    $recipe = new Recipe();
    $recipe->cuisine_id = $validatedData['cuisine_id'];
    $recipe->title = $validatedData['title'];
    $recipe->description = $validatedData['description'];
    $recipe->directions = $validatedData['directions'];
    $recipe->save();

    if ($request->has('images')) {
        $imageDataArray = $request->input('images'); 
        foreach ($imageDataArray as $index => $imageData) {
            $imageName = time() . "_$index.png"; 
            $path = storage_path('app/public/images/' . $imageName);
            $decodedImageData = base64_decode($imageData);
            file_put_contents($path, $decodedImageData);
            $image = new RecipeImage();
            $image->image_url = $path;
            $image->recipe_id=$recipe->id;
            $image->save();
            $uploadedImagePaths[] = $path;
        }
    }

    foreach ($validatedData['ingredients'] as $ingredient) {
        $recipe->ingredients()->attach($ingredient['id'], ['quantity' => $ingredient['quantity']]);
    }
    $response = [
        'message' => 'Recipe added successfully',
        'recipe' => [
            'id' => $recipe->id,
            'cuisine' => $recipe->cuisine,
            'title' => $recipe->title,
            'description' => $recipe->description,
            'images' => $recipe->images,
            'ingredients' => $recipe->ingredients,
        ],
    ];

    return response()->json(['message' => 'Recipe added successfully'], 200);
} catch (\Exception $e) {
    return response()->json(['error' => $e->getMessage()], 500);
}}

}