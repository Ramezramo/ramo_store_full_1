<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHandlerRam;

// use App\Helpers\ResponseHandlerRam;
class SippingController extends Controller
{



    public function paymentMethods(Request $request)
    {
        try {
            $lang = $request->query('lang', 'default');
            $paymentMethods = [
                [
                    "id" => "bacs",
                    "title" => "Direct bank transfer",
                    "method_title" => "Direct bank transfer",
                    "description" => "Make your payment directly into our bank account.<br>Please use your Order ID as the payment reference.<br>Your order will not be shipped until the funds have cleared in our account.<br><br>BANK INFO:<br>- Account name: InspireUI<br>- Account Number: 5983838303518<br>- Bank: BANKING NAME<br>"
                ],
                [
                    "id" => "cod",
                    "title" => "Cash on Delivery",
                    "method_title" => "Cash on delivery",
                    "description" => "Pay with cash upon delivery."
                ],
                [
                    "id" => "paypal",
                    "title" => "PayPal Standard",
                    "method_title" => "PayPal Standard",
                    "description" => "Pay via PayPal; you can pay with your credit card if you don't have a PayPal account. SANDBOX ENABLED. You can use sandbox testing accounts only. See the <a href=\"https://developer.paypal.com/docs/classic/lifecycle/ug_sandbox/\">PayPal Sandbox Testing Guide</a> for more details."
                ],
                [
                    "id" => "razorpay",
                    "title" => "Razorpay",
                    "method_title" => "Razorpay",
                    "description" => null
                ],
                [
                    "id" => "stripe",
                    "title" => "Credit Card (Stripe)",
                    "method_title" => "Stripe",
                    "description" => "Pay with your credit card via Stripe."
                ],
                [
                    "id" => "paystack",
                    "title" => "Debit/Credit Cards",
                    "method_title" => "Paystack",
                    "description" => "Make payment using your debit and credit cards"
                ],
                [
                    "id" => "myfatoorah_v2",
                    "title" => "MyFatoorah - Cards",
                    "method_title" => "MyFatoorah - Cards",
                    "description" => "Checkout with MyFatoorah Payment Gateway"
                ],
                [
                    "id" => "midtrans",
                    "title" => "All Supported Payment",
                    "method_title" => "Midtrans",
                    "description" => "Accept all various supported payment methods. Choose your preferred payment on the next page. Secure payment via Midtrans."
                ],
                [
                    "id" => "xendit_cc",
                    "title" => "Credit Card (Xendit)",
                    "method_title" => "Xendit Credit Card",
                    "description" => "Pay with your credit card via Xendit<br/><br/><p style=\"color: red; font-size:80%; margin-top:10px;\"><strong>TEST MODE</strong> - Real payment will not be detected</p><br/><br/>"
                ],
                [
                    "id" => "wallet",
                    "title" => "Wallet payment",
                    "method_title" => "Wallet",
                    "description" => "Pay with wallet."
                ],
                [
                    "id" => "thawani_gw",
                    "title" => "Thawani E-commerce Payments",
                    "method_title" => "Thawani Gateway",
                    "description" => "Pay with thawani"
                ]
            ];
            return ResponseHandlerRam::success(
                data: $paymentMethods,
                message: 'Payment methods retrieved successfully!',
            );
            // return response()->json($paymentMethods, 200);
        } catch (\Exception $e) {
            return ResponseHandlerRam::error(
                message: 'An error occurred',
                statusCode: 500
            );
            // return $this->failureResponse('An error occurred', 500);
        }
    }

    public function shippingMethods(Request $request)
    {
        try {
            $lang = $request->query('lang', 'default');
            $shippingMethods = [
                [
                    'id' => 'free_shipping:1',
                    'method_id' => 'free_shipping',
                    'instance_id' => 1,
                    'label' => 'Free shipping',
                    'cost' => 0.00,
                    'taxes' => [],
                    'shipping_tax' => 0
                ],
                [
                    'id' => 'flat_rate:2',
                    'method_id' => 'flat_rate',
                    'instance_id' => 2,
                    'label' => 'Flat rate',
                    'cost' => 20.00,
                    'taxes' => [],
                    'shipping_tax' => 0
                ],
                [
                    'id' => 'local_pickup:3',
                    'method_id' => 'local_pickup',
                    'instance_id' => 3,
                    'label' => 'Local pickup',
                    'cost' => 10.00,
                    'taxes' => [],
                    'shipping_tax' => 0
                ]
            ];
            return ResponseHandlerRam::success(
                data: $shippingMethods,
                message: 'Shipping Methods retrieved successfully!',
            );
            // return response()->json($shippingMethods, 200);
        } catch (\Exception $e) {
            return ResponseHandlerRam::error(
                message: 'An error occurred'.' '.$e->getMessage(),
                statusCode: 500
            );
            
        }
    }
}
