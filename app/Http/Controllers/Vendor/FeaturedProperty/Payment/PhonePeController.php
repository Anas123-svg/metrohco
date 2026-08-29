<?php

namespace App\Http\Controllers\Vendor\FeaturedProperty\Payment;

use Illuminate\Http\Request;
use App\Models\BasicSettings\Basic;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\FeaturedProperty\PaymentController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\PaymentGateway\OnlineGateway;

class PhonePeController extends Controller
{
  private $sandboxCheck;
  public function paymentProcess(Request $request, $_amount, $_success_url, $_cancel_url)
  {
    $info = OnlineGateway::where('keyword', 'phonepe')->first();
    $paydata = json_decode($info->information, true);

    $cancel_url = $_cancel_url;
    $notify_url = $_success_url;

    $this->sandboxCheck = $paydata['sandbox_status'];

    $clientId = $paydata['merchant_id'];
    $clientSecret = $paydata['salt_key'];

    //* Here i completed 1 step which is generating access token in each request

    $accessToken = $this->getPhonePeAccessToken($clientId, $clientSecret);

    if (!$accessToken) {
      return back()->withError(__('Failed to get PhonePe access token') . '.');
    }
    Session::put('request', $request->all());
    Session::put('amount', $_amount);
    Session::put('cancel_url', $cancel_url);

    return $this->initiatePayment($accessToken, $notify_url, $cancel_url, $_amount);
  }

  private function getPhonePeAccessToken($clientId, $clientSecret)
  {
    return Cache::remember('phonepe_access_token', 3500, function () use ($clientId, $clientSecret) {
      $tokenUrl = $this->sandboxCheck ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token' : 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

      $response = Http::asForm()->post($tokenUrl, [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'client_version' => 1,
        'grant_type' => 'client_credentials',
      ]);

      if ($response->successful()) {
        return $response->json()['access_token'];
      }
      return null;
    });
  }

  public function initiatePayment($accessToken, $successUrl, $cancelUrl, $_amount)
  {
    $baseUrl = $this->sandboxCheck ? 'https://api-preprod.phonepe.com/apis/pg-sandbox' : 'https://api.phonepe.com/apis/pg';

    $endpoint = '/checkout/v2/pay';

    // Generate a unique merchantOrderId and store it in the session
    $merchantOrderId = uniqid();
    Session::put('merchantOrderId', $merchantOrderId);
    Session::put('cancel_url', $cancelUrl);

    //here we preapare the parameter of the request
    $payload = [
      'merchantOrderId' => $merchantOrderId,
      'amount' => intval($_amount * 100),
      'paymentFlow' => [
        'type' => 'PG_CHECKOUT',
        'merchantUrls' => [
          'redirectUrl' => $successUrl,
          'cancelUrl' => $cancelUrl,
        ],
      ],
    ];

    try {
      //after preparing the parameter we send a request to create a payment link
      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => 'O-Bearer ' . $accessToken,
      ])->post($baseUrl . $endpoint, $payload);

      $responseData = $response->json();

      //after successfully created the payment link of we redirect the user to api responsed redirectUrl
      if ($response->successful() && isset($responseData['redirectUrl'])) {
        return redirect()->away($responseData['redirectUrl']);
      } else {
        // Handle API errors
        Session::forget(['merchantOrderId', 'cancel_url']);
        return back()->with('error', 'Failed to initiate payment' . '.');
      }
    } catch (\Exception $e) {
      Session::forget(['merchantOrderId', 'cancel_url']);
      return response()->json(
        [
          'success' => false,
          'code' => 'NETWORK_ERROR',
          'message' => $e->getMessage(),
        ],
        500,
      );
    }
  }

  public function successPayment(Request $request)
  {
    $requestData = Session::get('request');
    $bs = Basic::first();
    $cancel_url = Session::get('cancel_url');
    $requestData['gateway_type'] = 'phonepe';

    /** Get the payment ID before session clear **/
    $info = OnlineGateway::where('keyword', 'phonepe')->first();

    $merchantOrderId = $request->input('merchantOrderId') ?? (Session::get('merchantOrderId') ?? uniqid());

    $verificationResponse = $this->verifyOrderStatus($merchantOrderId);

    // Prepare transaction details with all relevant data
    $transactionDetails = [
      'payment_gateway' => 'PhonePe',
      'merchant_order_id' => $merchantOrderId,
      'gateway_response' => $verificationResponse,
      'request_data' => $requestData,
    ];

    if ($verificationResponse['success']) {
      $transaction_id = VendorPermissionHelper::uniqidReal(8);
      $transaction_details = json_encode($transactionDetails);

      $transaction_id = VendorPermissionHelper::uniqidReal(8);

      $checkout = new PaymentController();

      $checkout->store($requestData, $transaction_id, $transaction_details);

      $checkout->mailToAdminForFeaturedRequest($requestData);
      session()->flash('success', 'Your payment has been completed.');
      Session::forget('request');
      Session::forget('paymentFor');
      return redirect()->route('vendor.property_management.featured_payment_success');
    }
    return redirect($cancel_url);
  }

  private function verifyOrderStatus($merchantOrderId)
  {
    $info = OnlineGateway::where('keyword', 'phonepe')->first();
    $paymentInfo = json_decode($info->information, true);

    $this->sandboxCheck = $paymentInfo['sandbox_status'];

    try {
      $accessToken = $this->getPhonePeAccessToken($paymentInfo['merchant_id'], $paymentInfo['salt_key']);

      if (!$accessToken) {
        throw new \Exception('Failed to get access token');
      }

      $baseUrl = $this->sandboxCheck ? 'https://api-preprod.phonepe.com/apis/pg-sandbox' : 'https://api.phonepe.com/apis/pg';

      $endpoint = "/checkout/v2/order/{$merchantOrderId}/status";

      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => 'O-Bearer ' . $accessToken,
      ])->get($baseUrl . $endpoint);

      if ($response->successful()) {
        $responseData = $response->json();

        return [
          'success' => true,
          'state' => $responseData['state'] ?? null,
          'amount' => $responseData['amount'] ?? null,
          'data' => $responseData,
        ];
      } else {
        return [
          'success' => false,
          'error' => $response->json() ?? 'Unknown error',
        ];
      }
    } catch (\Exception $e) {
      return [
        'success' => false,
        'error' => $e->getMessage(),
      ];
    }
  }
  public function cancelPayment()
  {
    return redirect()->route('vendor.featured.cancel');
  }
}
