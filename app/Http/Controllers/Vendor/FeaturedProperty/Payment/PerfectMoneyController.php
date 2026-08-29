<?php

namespace App\Http\Controllers\Vendor\FeaturedProperty\Payment;

use Carbon\Carbon;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Helpers\MegaMailer;
use App\Models\BasicSettings\Basic;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\FeaturedProperty\PaymentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\PaymentGateway\OnlineGateway;
use App\Http\Controllers\Vendor\VendorCheckoutController;

class PerfectMoneyController extends Controller
{
  public function paymentProcess(Request $request, $_amount, $_success_url, $_cancel_url, $_title, $bex)
  {
    $info = OnlineGateway::where('keyword', 'perfect_money')->first();
    $information = json_decode($info->information, true);

    $cancel_url = $_cancel_url;
    $notify_url = $_success_url;
    $val = [];
    $randomNo = substr(uniqid(), 0, 8);
    $websiteInfo = Basic::first();
    $perfect_money = OnlineGateway::where('keyword', 'perfect_money')->first();
    $info = json_decode($perfect_money->information, true);
    $val['PAYEE_ACCOUNT'] = $info['perfect_money_wallet_id'];

    $val['PAYEE_NAME'] = $websiteInfo->website_title;
    $val['PAYMENT_ID'] = "$randomNo"; //random id
    $val['PAYMENT_AMOUNT'] = $_amount;
    // $val['PAYMENT_AMOUNT'] = 0.01; //test amount
    $val['PAYMENT_UNITS'] = "$websiteInfo->base_currency_text";

    $val['STATUS_URL'] = $notify_url;
    $val['PAYMENT_URL'] = $notify_url;
    $val['PAYMENT_URL_METHOD'] = 'GET';
    $val['NOPAYMENT_URL'] = $cancel_url;
    $val['NOPAYMENT_URL_METHOD'] = 'GET';
    $val['SUGGESTED_MEMO'] = Auth::guard('vendor')->user()->email;
    $val['BAGGAGE_FIELDS'] = 'IDENT';

    $data['val'] = $val;
    $data['method'] = 'get';
    $data['url'] = 'https://perfectmoney.com/api/step1.asp';

    Session::put('payment_id', $randomNo);
    Session::put('request', $request->all());

    return view('frontend.payment.perfect-money', compact('data'));
  }

  public function successPayment(Request $request)
  {
    $requestData = Session::get('request');
    $requestData['gateway_type'] = 'PERFECT MONEY';
    $cancel_url = Session::get('cancel_url');
    $perfect_money = OnlineGateway::where('keyword', 'perfect_money')->first();
    $perfectMoneyInfo = json_decode($perfect_money->information, true);
    $currencyInfo = Basic::select('base_currency_text')->first();

    $amo = $request['PAYMENT_AMOUNT'];
    $unit = $request['PAYMENT_UNITS'];
    $track = $request['PAYMENT_ID'];
    $id = Session::get('payment_id');
    $final_amount = $requestData['price']; 

    if ($request->PAYEE_ACCOUNT == $perfectMoneyInfo['perfect_money_wallet_id'] && $unit == $currencyInfo->base_currency_text && $track == $id && $amo == round($final_amount, 2)) {
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
