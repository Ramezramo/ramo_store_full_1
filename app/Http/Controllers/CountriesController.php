<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;

use App\Models\CountriesModel;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    public function store(Request $request)
    {
        // Define validation rules
        
        
        
        
        $rules = [
            'code'=>'nullable',
        'name'=>'required'
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $validatedData = $validator->validated();
        // $validatedData['_links'] = json_encode($validatedData['_links']);
        

        CountriesModel::create($validatedData);
        // If validation passes, you can proceed with storing the product
        // Your code to store the product in the database goes here

        return response()->json([
            'message' => 'Product created successfully!',
            'state' => "done"
        ]);
    }
    public function index(){
        // $validated = $request->validate([
        //     'code'=>'required',
        // 'name'=>'required'
        // ]);
                           $allcat = CountriesModel::get();
                           return response()->json($allcat);
                       }
}
