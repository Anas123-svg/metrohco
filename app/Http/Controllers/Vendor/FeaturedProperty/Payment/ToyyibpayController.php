<?php

namespace App\Http\Controllers\Vendor\FeaturedProperty\Payment;

use App\Models\PaymentGateway\OnlineGateway;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\FeaturedProperty\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\VendorPermissionHelper;

class ToyyibpayController extends Controller
{
  public function paymentProcess(Request $request, $_amount, $_success_url, $_cancel_url)
  {
    $cancel_url = $_cancel_url;
    $notify_url = $_success_url;

    $info = OnlineGateway::where('keyword', 'toyyibpay')->first();
    $information = json_decode($info->information, true);
    $ref = uniqid();
    session()->put('toyyibpay_ref_id', $ref);
    $bill_title = 'Buy Plan';
    $bill_description = 'Buy Plan via Toyyibpay';

    $vendor = Auth::guard('vendor')->user();

    $username = $vendor->username;
    $email = $vendor->email;
    $phone = $vendor->phone;
    // Validate phone format (Malaysian number: starts with 01, 10–11 digits total)
    $validator = Validator::make(
      ['phone' => $phone],
      [
        'phone' => ['required', 'regex:/^01[0-9]{8,9}$/'],
      ],
    );

    if ($validator->fails()) {
      Session::flash('warning', __('Your profile phone number is invalid. Please update it to a valid phone number in your profile settings') . '.');

      return redirect()->back()->withInput();
    }

    $some_data = [
      'userSecretKey' => $information['secret_key'],
      'categoryCode' => $information['category_code'],
      'billName' => $bill_title,
      'billDescription' => $bill_description,
      'billPriceSetting' => 1,
      'billPayorInfo' => 1,
      'billAmount' => $_amount * 100,
      'billReturnUrl' => $notify_url,
      'billExternalReferenceNo' => $ref,
      'billTo' => $username,
      'billEmail' => $email,
      'billPhone' => $phone,
    ];

    if ($information['sandbox_status'] == 1) {
      $host = 'https://dev.toyyibpay.com/'; // for development environment
    } else {
      $host = 'https://toyyibpay.com/'; // for production environment
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_URL, $host . 'index.php/api/createBill'); // sandbox will be dev.
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);

    $result = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);
    $response = json_decode($result, true);

    Session::put('request', $request->all());
    Session::put('cancel_url', $cancel_url);

    if (!empty($response[0])) {
      // put some data in session before redirect to paytm url
      return redirect($host . $response[0]['BillCode']);
    } else {
      return redirect($cancel_url);
    }
  }

  public function successPayment(Request $request)
  {
    $requestData = Session::get('request');
    $requestData['gateway_type'] = 'toyyibpay';
    $cancel_url = Session::get('cancel_url');
    $ref = session()->get('toyyibpay_ref_id');
    if ($request['status_id'] == 1 && $request['order_id'] == $ref) {
      $transaction_id = VendorPermissionHelper::uniqidReal(8);
      $transaction_details = 'online';

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
