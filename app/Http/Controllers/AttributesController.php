<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\AttributesModel;
use Illuminate\Http\Request;
use App\Helpers\ResponseHandlerRam;

// use App\Helpers\ResponseHandlerRam;
class AttributesController extends Controller
{
    // $validated = $request->validate([
    //     'id'=>'required',
    // 'name'=>'required',
    // 'slug'=>'required',
    // 'type'=>'required',
    // 'order_by'=>'required',
    // 'has_archives'=>'required',
    // 'is_visible'=>'required',
    // '_links'=>'required'
    // ]);
    public function store(Request $request)
    {
        // Define validation rules
        
        
        
        
        $rules = [
            'id'=>'required',
            'name'=>'required',
            'slug'=>'required',
            'type'=>'required',
            'order_by'=>'required',
            'has_archives'=>'required',
            'is_visible'=>'required',
            '_links'=>'required'
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);
        
        // Check if validation fails
        if ($validator->fails()) {

           ResponseHandlerRam::validationError(
            errors: $validator->errors(),
            message: 'Validation failed'
        );
        }

        $validatedData = $validator->validated();
        $validatedData['_links'] = json_encode($validatedData['_links']);
        

        AttributesModel::create($validatedData);
        // If validation passes, you can proceed with storing the product
        // Your code to store the product in the database goes here
return ResponseHandlerRam::success(
            data: null,
            message: 'Product created successfully!',
        );
   
    }
    public function index(){
     // $validated = $request->validate([
    //     'id'=>'required',
    // 'name'=>'required',
    // 'slug'=>'required',
    // 'type'=>'required',
    // 'order_by'=>'required',
    // 'has_archives'=>'required',
    // 'is_visible'=>'required',
    // '_links'=>'required'
    // ]);
            $allcat = AttributesModel::get();
            return ResponseHandlerRam::success(
                data: $allcat,
                message: 'Attributes retrieved successfully!',
            );
        }
}
