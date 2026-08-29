<?php

namespace App\Http\Controllers\Vendor\FeaturedProperty\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\FeaturedProperty\PaymentController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\PaymentGateway\OnlineGateway;

class YocoController extends Controller
{
  public function paymentProcess(Request $request, $_amount, $_success_url, $_cancel_url, $_title, $bex)
  {
    $info = OnlineGateway::where('keyword', 'yoco')->first();
    $information = json_decode($info->information, true);

    $cancel_url = $_cancel_url;
    $notify_url = $_success_url;

    $response = Http::withHeaders([
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer ' . $information['secret_key'],
    ])->post('https://payments.yoco.com/api/checkouts', [
      'amount' => $_amount * 100,
      'currency' => 'ZAR',
      'successUrl' => $notify_url,
    ]);

    $responseData = $response->json();
    if (array_key_exists('redirectUrl', $responseData)) {
      // put some data in session before redirect
      Session::put('request', $request->all());
      Session::put('cancel_url', $cancel_url);
      Session::put('yoco_id', $responseData['id']);
      Session::put('s_key', $information['secret_key']);
      return redirect($responseData['redirectUrl']);
    } else {
      return redirect($cancel_url);
    }
  }

  public function successPayment(Request $request)
  {
    $requestData = Session::get('request');
    $requestData['gateway_type'] = 'yoco';

    $cancel_url = Session::get('cancel_url');
    $id = Session::get('yoco_id');
    $s_key = Session::get('s_key');
    $info = OnlineGateway::where('keyword', 'yoco')->first();
    $information = json_decode($info->information, true);

    if ($id && $information['secret_key'] == $s_key) {

      $transaction_id = VendorPermissionHelper::uniqidReal(8);
      $transaction_details = json_encode($request['payment_request_id']);

      $checkout = new PaymentController();

      $checkout->store($requestData, $transaction_id, $transaction_details);

      $checkout->mailToAdminForFeaturedRequest($requestData);
      session()->flash('success', 'Your payment has been completed.');
      Session::forget('request');
      return redirect()->route('vendor.property_management.featured_payment_success');
    }
    return redirect($cancel_url);
  }

  public function cancelPayment()
  {
    return redirect()->route('vendor.featured.cancel');
  }
}
