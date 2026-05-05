<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandlerRam;
use App\Models\BlogModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function store(Request $request)
    {
        // Define validation rules
        
        
        
        
        $rules = [
        'date'=>'nullable',
        'date_gmt'=>'nullable',
        'guid'=>'nullable',
        'modified'=>'nullable',
        'modified_gmt'=>'nullable',
        'slug'=>'nullable',
        'status'=>'nullable',
        'type'=>'nullable',
        'link'=>'nullable',
        'title'=>'nullable',
        'content'=>'nullable',
        'excerpt'=>'nullable',
        'author'=>'nullable',
        'featured_media'=>'nullable',
        'comment_status'=>'nullable',
        'ping_status'=>'nullable',
        'sticky'=>'nullable',
        'template'=>'nullable',
        'format'=>'nullable',
        'meta'=>'nullable',
        'categories'=>'nullable',
        'tags'=>'nullable',
        'class_list'=>'nullable',
        'better_featured_image'=>'nullable',
        'image_feature'=>'nullable',
        'author_name'=>'nullable',
        '_links'=>'nullable',
        '_embedded'=>'nullable'
      
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);
        
        // Check if validation fails
        if ($validator->fails()) {
            return ResponseHandlerRam::validationError(
            errors: $validator->errors(),
            message: 'Validation failed'
        );

        }

        $validatedData = $validator->validated();
        $validatedData['_embedded'] = json_encode($validatedData['_embedded']);
        $validatedData['_links'] = json_encode($validatedData['_links']);
        $validatedData['better_featured_image'] = json_encode($validatedData['better_featured_image']);
        $validatedData['class_list'] = json_encode($validatedData['class_list']);
        $validatedData['tags'] = json_encode($validatedData['tags']);
        $validatedData['categories'] = json_encode($validatedData['categories']);
        $validatedData['meta'] = json_encode($validatedData['meta']);
        $validatedData['excerpt'] = json_encode($validatedData['excerpt']);
        $validatedData['title'] = json_encode($validatedData['title']);
        $validatedData['content'] = json_encode($validatedData['content']);
        $validatedData['guid'] = json_encode($validatedData['guid']);
        BlogModel::create($validatedData);
        // If validation passes, you can proceed with storing the product
        // Your code to store the product in the database goes here
        
return ResponseHandlerRam::success(
            data: null,
            message: 'Product created successfully!',
        );

    }
    //$validated = $request->validate([
        // 'id'=>'nullable',
        // 'date'=>'nullable',
        // 'date_gmt'=>'nullable',
        // 'guid'=>'nullable',
        // 'modified'=>'nullable',
        // 'modified_gmt'=>'nullable',
        // 'slug'=>'nullable',
        // 'status'=>'nullable',
        // 'type'=>'nullable',
        // 'link'=>'nullable',
        // 'title'=>'nullable',
        // 'content'=>'nullable',
        // 'excerpt'=>'nullable',
        // 'author'=>'nullable',
        // 'featured_media'=>'nullable',
        // 'comment_status'=>'nullable',
        // 'ping_status'=>'nullable',
        // 'sticky'=>'nullable',
        // 'template'=>'nullable',
        // 'format'=>'nullable',
        // 'meta'=>'nullable',
        // 'categories'=>'nullable',
        // 'tags'=>'nullable',
        // 'class_list'=>'nullable',
        // 'better_featured_image'=>'nullable',
        // 'image_feature'=>'nullable',
        // 'author_name'=>'nullable',
        // '_links'=>'nullable',
        // '_embedded'=>'nullable'
        // ]);
        public function index(){
//$validated = $request->validate([
        // 'id'=>'nullable',
        // 'date'=>'nullable',
        // 'date_gmt'=>'nullable',
        // 'guid'=>'nullable',
        // 'modified'=>'nullable',
        // 'modified_gmt'=>'nullable',
        // 'slug'=>'nullable',
        // 'status'=>'nullable',
        // 'type'=>'nullable',
        // 'link'=>'nullable',
        // 'title'=>'nullable',
        // 'content'=>'nullable',
        // 'excerpt'=>'nullable',
        // 'author'=>'nullable',
        // 'featured_media'=>'nullable',
        // 'comment_status'=>'nullable',
        // 'ping_status'=>'nullable',
        // 'sticky'=>'nullable',
        // 'template'=>'nullable',
        // 'format'=>'nullable',
        // 'meta'=>'nullable',
        // 'categories'=>'nullable',
        // 'tags'=>'nullable',
        // 'class_list'=>'nullable',
        // 'better_featured_image'=>'nullable',
        // 'image_feature'=>'nullable',
        // 'author_name'=>'nullable',
        // '_links'=>'nullable',
        // '_embedded'=>'nullable'
        // ]);
                   $allcat = BlogModel::get();
              return ResponseHandlerRam::success(
                data: $allcat,
                message: 'Blog posts retrieved successfully!',
            );
               }
}
