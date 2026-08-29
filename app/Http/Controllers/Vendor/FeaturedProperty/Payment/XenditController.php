<?php

namespace App\Http\Controllers\Vendor\FeaturedProperty\Payment;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\FeaturedProperty\PaymentController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\VendorPermissionHelper;

class XenditController extends Controller
{
  public function paymentProcess(Request $request, $_amount, $_success_url, $_cancel_url, $_title, $bex)
  {
    $cancel_url = $_cancel_url;
    $notify_url = $_success_url;

    Session::put('request', $request->all());
    Session::put('cancel_url', $cancel_url);

    $external_id = Str::random(10);
    $secret_key = 'Basic ' . config('xendit.key_auth');
    $data_request = Http::withHeaders([
      'Authorization' => $secret_key,
    ])->post('https://api.xendit.co/v2/invoices', [
      'external_id' => $external_id,
      'amount' => (int) round($_amount),
      'currency' => $bex->base_currency_text,
      'success_redirect_url' => $notify_url,
    ]);

    $response = $data_request->object();
    $response = json_decode(json_encode($response), true);

    if (!empty($response['success_redirect_url'])) {
      Session::put('xendit_id', $response['id']);
      Session::put('secret_key', config('xendit.key_auth'));
      return redirect($response['invoice_url']);
    } else {
      return redirect($cancel_url)->with('error', __('Payment Canceled') . '.');
    }
  }

  public function successPayment(Request $request)
  {
    $requestData = Session::get('request');
    $requestData['gateway_type'] = 'xendit';
    $cancel_url = Session::get('cancel_url');
    /** Get the payment ID before session clear **/

    $xendit_id = Session::get('xendit_id');
    $secret_key = Session::get('secret_key');

    if (!is_null($xendit_id) && $secret_key == config('xendit.key_auth')) {

      $transaction_id = VendorPermissionHelper::uniqidReal(8);
      $transaction_details = json_encode($request['payment_request_id']);

      $transaction_id = VendorPermissionHelper::uniqidReal(8);
      $transaction_details = 'online';

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
