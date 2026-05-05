<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHandlerRam;
class CategoriesController extends Controller
{
    public function store(Request $request)
    {
        // Define validation rules
        
        
        
        
        $rules = [
            'name'=>'nullable',
            'slug'=>'nullable',
            'parent'=>'nullable',
            'description'=>'nullable',
            'display'=>'nullable',
            'image'=>'nullable',
            'menu_order'=>'nullable',
            'count'=>'nullable',
            'has_children'=>'nullable',
            '_links'=>'nullable'
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);
        
        // Check if validation fails
        if ($validator->fails()) {
              ResponseHandlerRam::validationError(
            errors: $validator->errors(),
            message: 'Validation failed'
        );
 
            // return response()->json([
            //     'error' => $validator->errors()
            // ], 400);
        }

        $validatedData = $validator->validated();
        $validatedData['_links'] = json_encode($validatedData['_links']);
        $validatedData['image'] = json_encode($validatedData['image']);
        

        Category::create($validatedData);
        // If validation passes, you can proceed with storing the product
        // Your code to store the product in the database goes here

        // return response()->json([
        //     'message' => 'Product created successfully!',
        //     'state' => "done"
        // ]);
        return ResponseHandlerRam::success(
            data: null,
            message: 'Product created successfully!',
        );
    }
    public function index(){
        // $validated = $request->validate([
        //     'id'=>'nullable',
        // 'name'=>'nullable',
        // 'slug'=>'nullable',
        // 'parent'=>'nullable',
        // 'description'=>'nullable',
        // 'display'=>'nullable',
        // 'image'=>'nullable',
        // 'menu_order'=>'nullable',
        // 'count'=>'nullable',
        // 'has_children'=>'nullable',
        // '_links'=>'nullable'
        // ]);
        $allcat = Category::get();
        return ResponseHandlerRam::success(
            data: $allcat,
            message: 'Categories retrieved successfully!',
        );
        // return response()->json($allcat);
    }
}
