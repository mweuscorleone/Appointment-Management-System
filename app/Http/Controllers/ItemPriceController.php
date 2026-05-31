<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemPrice;
use Illuminate\Support\Facades\DB;

class ItemPriceController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'item_id' => 'required|numeric|exists:items,id',
            'sponsor_id' => 'required|numeric|exists:sponsors,id',
            'price' => 'required|numeric|min:1'
        ],
        [
            'item_id.exists' => "Provided Item with an ID {$request->item_id} not exist please try again",
            'sponsor_id.exists' => "Provided Sponsor with an ID {$request->sponsor_id} no exist please try again"
        ]);

        $itemPrice = ItemPrice::updateOrCreate(
            [
                'item_id' => $request->item_id,
                'sponsor_id' => $request->sponsor_id
            ],
            [
                'price' => $request->price
            ]
            );
            $data = [];

             $itemData = DB::table('item_prices')->join('items', 'item_prices.item_id', '=', 'items.id')
                            ->join('sponsors', 'item_prices.sponsor_id', '=', 'sponsors.id')
                            ->where('item_prices.id', $itemPrice->id)
                            ->select(
                                'item_prices.id as item_price_id',
                                'items.id as item_id',
                                'items.name as item_name',
                                'sponsors.name as sponsor_name',
                                'item_prices.price as item_price',
                                'items.status as status'

                            )
                            ->first();
                        
            $data[] = [
                'Item price ID' => $itemData->item_price_id,
                'Item ID' => $itemData->item_id,
                'Item Name' => $itemData->item_name,
                'Sponsor' => $itemData->sponsor_name,
                'Item Price' => $itemData->item_price,
                'Status' => $itemData->status,
                'created by' => $request->user()->name

            ];

            


            return response()->json([
                'status' => 'success',
                'message' => 'Item price saved successully!',
                'Item' => $data


            ], 200);


        }
}
