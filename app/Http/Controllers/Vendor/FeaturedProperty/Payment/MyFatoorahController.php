<?php

namespace App\Http\Controllers\Vendor\FeaturedProperty\Payment;

use Carbon\Carbon;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Helpers\MegaMailer;
use Basel\MyFatoorah\MyFatoorah;
use App\Models\BasicSettings\Basic;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\FeaturedProperty\PaymentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\PaymentGateway\OnlineGateway;
use App\Http\Controllers\Vendor\VendorCheckoutController;

class MyFatoorahController extends Controller
{
  private $myfatoorah;

  public function __construct()
  {
    $info = OnlineGateway::where('keyword', 'myfatoorah')->first();
    $information = json_decode($info->information, true);
    $this->myfatoorah = MyFatoorah::getInstance($information['sandbox_status'] == 1 ? true : false);
  }

  public function paymentProcess(Request $request, $_amount, $_cancel_url)
  {
    $cancel_url = $_cancel_url;
    /********************************************************
     * send payment request to yoco for create a payment url
     ********************************************************/

    $info = OnlineGateway::where('keyword', 'myfatoorah')->first();
    $information = json_decode($info->information, true);

    $random_1 = rand(999, 9999);
    $random_2 = rand(9999, 99999);
    $result = $this->myfatoorah->sendPayment(Auth::guard('vendor')->user()->username, intval($_amount), [
      'CustomerMobile' => $information['sandbox_status'] == 1 ? '56562123544' : Auth::guard('vendor')->user()->phone,
      'CustomerReference' => "$random_1", //orderID
      'UserDefinedField' => "$random_2", //clientID
      'InvoiceItems' => [
        [
          'ItemName' => 'Package Purchase or Extends',
          'Quantity' => 1,
          'UnitPrice' => intval($_amount),
        ],
      ],
    ]);

    if ($result && $result['IsSuccess'] == true) {
      Session::put('request', $request->all());
      return redirect($result['Data']['InvoiceURL']);
    } else {
      return redirect($cancel_url);
    }
  }

  public function successPayment(Request $request)
  {
    $requestData = Session::get('request');

    $bs = Basic::first();
    /** Get the payment ID before session clear **/
    if (!empty($request->paymentId)) {
      $result = $this->myfatoorah->getPaymentStatus('paymentId', $request->paymentId);
      if ($result && $result['IsSuccess'] == true && $result['Data']['InvoiceStatus'] == 'Paid') {


        $transaction_id = VendorPermissionHelper::uniqidReal(8);
        $transaction_details = json_encode($request['payment_request_id']);


        $checkout = new PaymentController();

        $checkout->store($requestData, $transaction_id, $transaction_details);

        $checkout->mailToAdminForFeaturedRequest($requestData);
        session()->flash('success', 'Your payment has been completed.');
        Session::forget('request');

        return [
          'url' => route('vendor.property_management.featured_payment_success'),
        ];
      }
    }
  }

  public function cancelPayment()
  {
    return redirect()->route('vendor.featured.cancel');
  }
}
