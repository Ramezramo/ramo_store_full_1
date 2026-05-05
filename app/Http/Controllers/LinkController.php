<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\Link;
use Illuminate\Http\Request;

class LinkController extends Controller
{
        // Store the link if it's not already stored
        public function store(Request $request)
        {
            // Validate the incoming link
            $rules = [
                

                'link' => 'required|unique:links,link',
                'data' => 'required',
                'post_data' => 'nullable'
            ];
    
            $validator = Validator::make($request->all(), $rules);
        
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 202);
            }
    
            $validatedData = $validator->validated();
            Link::create($validatedData);
    
            return response()->json(['message' => 'Link stored successfully!'], 201);
        }
    
        public function getLinkData(Request $request)
        {
            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'link' => 'required',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 400);
            }
        
            // Check if the link exists in the database
            $link = Link::where('link', $request->input('link'))->first();
        
            if ($link) {
                return response()->json(json_decode($link->data), 200);
            }
        
            return response()->json(['message' => 'This link is available.'], 200);
        }
        // Get all links
        public function getLinks()
        {
            $links = Link::all()->map(function ($link) {
                $link->data = json_decode($link->data);
                // $link->post_data = json_decode($link->post_data);
                return $link;
            });
            return response()->json($links, 200);
        }
}
