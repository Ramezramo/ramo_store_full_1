<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function store(Request $request)
    {
        // Define validation rules
        
        
        
        
        $rules = [
            'name'=>'required',
            'slug'=>'nullable',
            'description'=>'nullable',
            'count'=>'nullable',
            'is_visible'=>'nullable',
            '_links'=>'nullable'
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
        $validatedData['_links'] = json_encode($validatedData['_links']);
        

        Tag::create($validatedData);
        // If validation passes, you can proceed with storing the product
        // Your code to store the product in the database goes here

        return response()->json([
            'message' => 'Product created successfully!',
            'state' => "done"
        ]);
    }
    public function index(){
    // $validated = $request->validate([
    //     'id'=>'required',
    // 'name'=>'required',
    // 'slug'=>'required',
    // 'description'=>'required',
    // 'count'=>'required',
    // 'is_visible'=>'required',
    // '_links'=>'required'
    // ]);
        $allcat = Tag::get();
        return response()->json($allcat);
    }
}
