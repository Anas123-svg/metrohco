<?php

namespace App\Http\Controllers\Vendor\FeaturedProperty\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\FeaturedProperty\PaymentController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\VendorPermissionHelper;
class PaytabsController extends Controller
{
  public function paymentProcess(Request $request, $_amount, $_success_url, $_cancel_url)
  {
    $cancel_url = $_cancel_url;
    $notify_url = $_success_url;

    $paytabInfo = paytabInfo();
    $description = 'Package Purchase via paytabs';
    try {
      $response = Http::withHeaders([
        'Authorization' => $paytabInfo['server_key'], // Server Key
        'Content-Type' => 'application/json',
      ])->post($paytabInfo['url'], [
        'profile_id' => $paytabInfo['profile_id'], // Profile ID
        'tran_type' => 'sale',
        'tran_class' => 'ecom',
        'cart_id' => uniqid(),
        'cart_description' => $description,
        'cart_currency' => $paytabInfo['currency'], // set currency by region
        'cart_amount' => $_amount,
        'return' => $notify_url,
      ]);

      $responseData = $response->json();
      // put some data in session before redirect
      Session::put('request', $request->all());
      Session::put('cancel_url', $cancel_url);
      return redirect()->to($responseData['redirect_url']);
    } catch (\Exception $e) {
      return redirect($cancel_url);
    }
  }

  public function successPayment(Request $request)
  {
    $requestData = Session::get('request');
    $requestData['gateway_type'] = 'paytabs';
    $cancel_url = Session::get('cancel_url');
    /** Get the payment ID before session clear **/

    $resp = $request->all();
    if ($resp['respStatus'] == 'A' && $resp['respMessage'] == 'Authorised') {
      $transaction_id = VendorPermissionHelper::uniqidReal(8);
      $transaction_details = json_encode($request['payment_request_id']);

      $checkout = new PaymentController();

      $checkout->store($requestData, $transaction_id, $transaction_details);

      $checkout->mailToAdminForFeaturedRequest($requestData);
      session()->flash('success', 'Your payment has been completed.');
      Session::forget('request');
      return redirect()->route('vendor.property_management.featured_payment_success');
    } else {
      return redirect($cancel_url);
    }
  }

  public function cancelPayment()
  {
    return redirect()->route('vendor.featured.cancel');
  }
}
