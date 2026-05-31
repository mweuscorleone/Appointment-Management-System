<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemCategory;

class ItemCategoryController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255|unique:item_categories,name'
        ],
        [
            'name.unique' => 'item category name already exists please try again!'
        ]);

        $category = ItemCategory::create([
            'name' => $request->name,
            'status' => $request->status ?? 'active'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item category created successfully!',
            'item category' => $category
        ], 201);
    }
    public function update(Request $request, $id){
        $category  = ItemCategory::findOrFail($id);

        $fields = $request->validate([
            'name' => 'sometimes|string|max:255|unique:item_categories,name',
            'status' => 'sometimes|string|in:active,inactive'
        ],
        [
            'name.unique' => 'item category with the same name already exist please try again!',
            'status.in'  => 'category status must active or inactive'
        ]);

        $category->update($fields);

        return response()->json([
            'status' => 'success',
            'message' => 'category updated successfully!',
            'updated fields' => array_keys($fields)
        ], 200);
    }

    public function destroy($id){
        $category = ItemCategory::findOrFail($id);

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'category deleted successfully!'
        ], 200);
    }

    public function index(){
        $categories = ItemCategory::all();

        return response()->json([
            'status' => true,
            'data' => $categories,
            'count' => $categories->count()
        ], 200);
    }
}
