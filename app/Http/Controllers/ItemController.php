<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Item;

class ItemController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255|unique:items,name',
            'item_category_id' => 'required|numeric|exists:item_categories,id',
            'consultation_type_id' => 'required|numeric|exists:consulation_types,id'
        ], 
        [
            'name.unique' => "provided item name {$request->name} already exist please try again",
            'item_category_id.exists' => 'provided item category type do not  exist please try again',
            'consultation_type_id.exists' => 'provided consulatation type id do not exist please try again '
        ]);

        $item = Item::create(
            [ 'name' => $request->name,
              'item_category_id' => $request->item_category_id,
              'consultation_type_id' => $request->consultaton_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item created successfully!',
            'Item' => $item
        ], 201);
    }
    
    public function update(Request $request, $id){
        $item = Item::findOrFail($id);

        $fields = $request->validate([
            'name' => 'sometimes|string|unique:items,name',
            'item_category_id' => 'sometimes|numeric|exists:item_categories,id',
            'consultation_type_id' => 'somtimes|numeric|exists:consultation_types,id',
            'status' => 'sometimes|string|in:active,inactive'
        ],
        [   'name.unique' => 'provided item is already exist please try again',
            'item_category_id.exists' => 'provided item category id is not exist please try again',
            'consultation_type_id.exists' => 'provided consultation type id do not exist please try again',
            'status.in' => 'item status must be active or inactive'
        ]);

        $item->update($fields);

        return response()->json([
            'status' => 'success',
            'message' => 'Item updated successfully!',
            'updated fields' => array_keys($fields)
        ], 200);
    }

    public function destroy($id){
        $item = Item::findOrFail($id);

        $item->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Item deleted successfully!'
        ], 200);


    }

    public function index(){
        $items = Item::all();

        return reponse()->json([
            'status' => true,
            'Items' => $items,
            'count' => $items->count()
        ], 200);
    }
}
