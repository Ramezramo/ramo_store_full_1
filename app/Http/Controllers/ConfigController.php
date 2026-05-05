<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    public function uploadConfig(Request $request)
    {
        $response = [
            'success' => false,
            'message' => '',
            'data' => null
        ];

        try {
            // Validate that "config" exists and is an array (JSON object)
            $validator = Validator::make($request->all(), [
                'config' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Store directly (Laravel casts array -> JSON automatically)
            $appConfig = AppConfig::create([
                'config_json' => $request->input('config'),
            ]);

            $response['success'] = true;
            $response['message'] = 'Configuration saved successfully';
            $response['data'] = $appConfig;

            return response()->json($response, 201);

        } catch (\Exception $e) {
            $response['message'] = 'Error saving config: ' . $e->getMessage();
            return response()->json($response, 500);
        }
    }
}
