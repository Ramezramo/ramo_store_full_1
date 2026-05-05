<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseHandlerRam; 

class SimulationController1 extends Controller
{
    // ================================================================
    // Helper Methods (using ResponseHandlerRam)
    // ================================================================
    private function successResponse($data, $message = '', $code = 200)
    {
        return ResponseHandlerRam::success(
            data: $data,
            message: $message,
            statusCode: $code
        );
    }

    private function failureResponse($message, $code = 400, $forceViewMessageDetails = false)
    {
        return ResponseHandlerRam::error(
            forceViewMessageDetails: $forceViewMessageDetails,
            message: $message,
            statusCode: $code
        );
    }

    private function validationErrorResponse($errors, $code = 422)
    {
        return ResponseHandlerRam::validationError(
            errors: $errors,
            message: 'Validation failed',
            statusCode: $code
        );
    }

    // ================================================================
    // Utility
    // ================================================================
    private function getLinkData($theLink)
    {
        $link = Link::where('link', $theLink)->first();

        if ($link && $link->data) {
            return json_decode($link->data, true);
        }

        return null; // or false, depending on prefer
    }

    // ================================================================
    // Endpoints
    // ================================================================

    public function sellerOrdersInfo(Request $request)
    {
        try {
            $filePath = 'D:\xammp\htdocs\ramostore\api-ramo-store-lara\app\Http\Controllers\jsonsimulations\ordersSimulator.json';

            if (!file_exists($filePath)) {
                return $this->failureResponse('Orders simulation file not found', 404);
            }

            $jsonContent = file_get_contents($filePath);
            $ordersData   = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->failureResponse(
                    'Error decoding JSON file: ' . json_last_error_msg(),
                    500
                );
            }

            return $this->successResponse($ordersData);

        } catch (\Exception $e) {
            return $this->failureResponse(
                'An error occurred while processing the request: ' . $e->getMessage(),
                500,
                true
            );
        }
    }

    public function shippingMethod(Request $request)
    {
        try {
            return $this->successResponse(["type" => "order_wise"]);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while processing the request', 500);
        }
    }

    public function languageChange(Request $request)
    {
        try {
            return $this->successResponse(["message" => "Successfully change"]);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while processing the request', 500);
        }
    }

    public function allCategoryCost(Request $request)
    {
        try {
            $data = $this->getLinkData('/api/v3/seller/shipping/all-category-cost');
            if ($data === null) {
                return $this->failureResponse('Link data not found', 404);
            }
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while fetching category costs', 500);
        }
    }

    public function topDeliveryMan(Request $request)
    {
        try {
            $data = $this->getLinkData('/api/v3/seller/top-delivery-man');
            if ($data === null) return $this->failureResponse('Top delivery man data not found', 404);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while fetching top delivery man', 500);
        }
    }

    public function getEarningStatitics(Request $request)
    {
        try {
            $data = $this->getLinkData('/api/v3/seller/get-earning-statitics');
            if ($data === null) return $this->failureResponse('Earning statistics not found', 404);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while fetching earning statistics', 500);
        }
    }

    public function sellerNotification(Request $request)
    {
        try {
            $data = $this->getLinkData('/api/v3/seller/notification');
            if ($data === null) return $this->failureResponse('Notifications not found', 404);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while fetching notifications', 500);
        }
    }

    public function topsellingproduct(Request $request)
    {
        try {
            $data = $this->getLinkData('/api/v3/seller/products/most-popular-product');
            if ($data === null) return $this->failureResponse('Top selling products not found', 404);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while fetching top selling products', 500);
        }
    }

    public function mostpopularproduct(Request $request)
    {
        try {
            $data = $this->getLinkData('/api/v3/seller/products/most-popular-product');
            if ($data === null) return $this->failureResponse('Most popular products not found', 404);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while fetching most popular products', 500);
        }
    }

    public function stockoutlist(Request $request)
    {
        try {
            $data = $this->getLinkData('/api/v3/seller/products/stock-out-list');
            if ($data === null) return $this->failureResponse('Stock out list not found', 404);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while fetching stock out list', 500);
        }
    }

    public function orderStatistics(Request $request)
    {
        try {
            $userId = auth()->user()->id;

            $stateMapping = [
                'pending'     => 'pending',
                'processing'  => 'processing',
                'completed'   => 'delivered',
                'cancelled'   => 'canceled',
                'refunded'    => 'returned',
                'failed'      => 'failed',
                'on-hold'     => 'confirmed',
            ];

            $orderCounts = Order::whereJsonContains('parent_vendors_ids', $userId)
                ->whereIn('status', array_keys($stateMapping))
                ->groupBy('status')
                ->select('status', DB::raw('count(*) as count'))
                ->pluck('count', 'status')
                ->toArray();

            $orderStatistics = [
                'pending'          => 0,
                'confirmed'        => 0,
                'processing'       => 0,
                'out_for_delivery' => 0,
                'delivered'        => 0,
                'canceled'         => 0,
                'returned'         => 20,
                'failed'           => 0,
            ];

            foreach ($orderCounts as $status => $count) {
                if (isset($stateMapping[$status])) {
                    $orderStatistics[$stateMapping[$status]] = $count;
                }
            }

            return $this->successResponse($orderStatistics);

        } catch (\Exception $e) {
            return $this->failureResponse(
                'An error occurred while retrieving order statistics: ' . $e->getMessage(),
                500,
                true
            );
        }
    }

    public function sellerInfo(Request $request)
    {
        try {
            $data = $this->getLinkData('/api/v3/seller/seller-info');
            if ($data === null) return $this->failureResponse('Seller info not found', 404);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while fetching seller info', 500);
        }
    }

    public function refundSimulation(Request $request)
    {
        try {
            $data = $this->getLinkData('/api/v3/seller/refund/list');
            if ($data === null) return $this->failureResponse('Refund list not found', 404);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->failureResponse('An error occurred while fetching refund list', 500);
        }
    }
}