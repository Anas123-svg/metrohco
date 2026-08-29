<?php

namespace App\Http\Controllers\Vendor\FeaturedProperty\Payment;

use Midtrans\Snap;
use Illuminate\Http\Request;
use App\Models\BasicSettings\Basic;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\FeaturedProperty\PaymentController;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config as MidtransConfig;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\PaymentGateway\OnlineGateway;

class MidtransController extends Controller
{
  public function paymentProcess(Request $request, $_amount, $_success_url, $_cancel_url, $_title, $bex)
  {
    $info = OnlineGateway::where('keyword', 'midtrans')->first();
    $information = json_decode($info->information, true);

    // will come from database
    $client_key = $information['server_key'];
    MidtransConfig::$serverKey = $information['server_key'];
    if ($information['midtrans_mode'] == 1) {
      MidtransConfig::$isProduction = false;
    } elseif ($information['midtrans_mode'] == 0) {
      MidtransConfig::$isProduction = true;
    }
    MidtransConfig::$isSanitized = true;
    MidtransConfig::$is3ds = true;
    $token = uniqid();

    // this session $token also is used in the MidtransBankNotifyController
    Session::put('token', $token);

    $params = [
      'transaction_details' => [
        'order_id' => $token,
        'gross_amount' => (int) round($_amount),
      ],
      'customer_details' => [
        'first_name' => Auth::guard('vendor')->user()->name,
        'email' => Auth::guard('vendor')->user()->email,
        'phone' => Auth::guard('vendor')->user()->phone,
      ],
    ];

    $snapToken = Snap::getSnapToken($params);
    //if generate payment url then put some data into session
    Session::put('request', $request->all());
    Session::put('cancel_url', $_cancel_url);
    Session::put('midtrans_payment_type', 'package_feature');
    $paymentFor = Session::get('paymentFor');

    $is_production = $information['midtrans_mode'] == 1 ? $information['midtrans_mode'] : 0;
    return view('frontend.payment.feature-midtrans', compact('snapToken', 'is_production', 'client_key', 'paymentFor'));
  }

  public function cardNotify($order_id)
  {
    $requestData = Session::get('request');
    $requestData['gateway_type'] = 'midtrans';
    $bs = Basic::first();

    $cancel_url = Session::get('cancel_url');
    if ($order_id) {

      $transaction_id = VendorPermissionHelper::uniqidReal(8);
      $transaction_details = 'online';

      $transaction_id = VendorPermissionHelper::uniqidReal(8);

      $checkout = new PaymentController();

      $checkout->store($requestData, $transaction_id, $transaction_details);

      $checkout->mailToAdminForFeaturedRequest($requestData);
      session()->flash('success', 'Your payment has been completed.');
      Session::forget('request');
      Session::forget('paymentFor');
      return redirect()->route('vendor.property_management.featured_payment_success');
    } else {
      return redirect($cancel_url);
    }
  }

  public function OnlineBackNotify($order_id) {}

  public function cancelPayment()
  {
    return redirect()->route('vendor.featured.cancel');
  }
}
