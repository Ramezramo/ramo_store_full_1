<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\CouponsModel;
use Illuminate\Http\Request;
use App\Http\Resources\CouponsResource; // Ensure this class exists in the specified namespace

class SettingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $copuns = CouponsModel::findOrfail(1);
        // return new CouponsResource($copuns);
    }
}
