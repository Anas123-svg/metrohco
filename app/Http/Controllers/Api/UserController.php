<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Http\Helpers\BasicMailer;
use App\Models\BasicSettings\Basic;
use App\Models\BasicSettings\MailTemplate;
use App\Models\Property\Wishlist;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
  public function login(Request $request)
  {
    $language = HelperController::getLanguage($request);

    $misc = new MiscellaneousController();

    $data['pageHeading'] = $misc->getPageHeading($language);
    $data['bgImg'] = asset('assets/img/' . @$misc->getBreadcrumb()->breadcrumb);

    // get the status of digital product (exist or not in the cart)
    if (!empty($request->input('digital_item'))) {
      $data['digitalProductStatus'] = $request->input('digital_item');
    }

    $data['bs'] = Basic::query()->select('google_recaptcha_status', 'facebook_login_status', 'google_login_status')->first();

    return response()->json([
      'success' => true,
      'data' => $data
    ]);
  }

  //login submit
  public function loginSubmit(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'username' => 'required',
      'password' => 'required'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'validation_error',
        'errors' => $validator->errors()
      ], 422);
    }

    $credentials = $request->only('username', 'password');

    if (!Auth::attempt($credentials)) {
      return response()->json([
        'status' => 'error',
        'message' => 'Invalid credentials'
      ], 401);
    }

    $user = Auth::guard('sanctum')->user();
    if (is_null($user->email_verified_at)) {
      return response()->json([
        'status' => 'error',
        'message' => 'Please verify your email address.'
      ], 403);
    }

    if ($user->status == 0) {
      return response()->json([
        'status' => 'error',
        'message' => 'Sorry, your account has been deactivated.'
      ], 403);
    }

    $user->tokens()->where('name', 'customer-login')->delete();
    $token = $user->createToken('customer-login')->plainTextToken;

    $user->image = asset('assets/img/users/' . $user->image);
    if (is_null($user->image)) {
      $user->image = null;
    }

    return response()->json([
      'status' => 'success',
      'user' => $user,
      'token' => $token
    ], 200);
  }

  /**
   * Sign up method for user registration.
   */
  public function signup(Request $request)
  {
    $language = HelperController::getLanguage($request);

    $misc = new MiscellaneousController();

    $data['pageHeading'] = $misc->getPageHeading($language);

    $data['bgImg'] = asset('assets/img/' . @$misc->getBreadcrumb()->breadcrumb);

    $data['bs'] = Basic::query()->select('google_recaptcha_status', 'facebook_login_status', 'google_login_status')->first();

    $data['recaptchaInfo'] = Basic::select('google_recaptcha_status')->first();

    return response()->json([
      'success' => true,
      'data' => $data
    ]);
  }


  /**
   * Sign up method for user registration submission.
   */
  public function signupSubmit(Request $request)
  {
    $info = Basic::select('google_recaptcha_status', 'website_title')->first();

    // validation start
    $rules = [
      'username' => 'required|unique:users|max:255',
      'email' => 'required|email:rfc,dns|unique:users|max:255',
      'password' => 'required|confirmed',
      'password_confirmation' => 'required'
    ];

    if ($info->google_recaptcha_status == 1) {
      $rules['g-recaptcha-response'] = 'required|captcha';
    }

    $messages = [];
    if ($info->google_recaptcha_status == 1) {
      $messages['g-recaptcha-response.required'] = __('Please verify that you are not a robot.');
      $messages['g-recaptcha-response.captcha'] = __('Captcha error! try again later or contact site admin.');
    }

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'errors' => $validator->errors()
      ], 422);
    }
    // validation end

    $user = new User();
    $user->username = $request->username;
    $user->email = $request->email;
    $user->status = 1;
    $user->password = Hash::make($request->password);
    $user->save();

    // get the mail template information from db
    $mailTemplate = MailTemplate::query()->where('mail_type', '=', 'verify_email')->first();
    $mailData['subject'] = $mailTemplate->mail_subject;
    $mailBody = $mailTemplate->mail_body;

    $link = '<a href=' . url("user/signup-verify/" . $user->id) . '>Click Here</a>';

    $mailBody = str_replace('{username}', $user->username, $mailBody);
    $mailBody = str_replace('{verification_link}', $link, $mailBody);
    $mailBody = str_replace('{website_title}', $info->website_title, $mailBody);

    $mailData['body'] = $mailBody;

    $mailData['recipient'] = $user->email;

    $mailData['sessionMessage'] = __('A verification mail has been sent to your email address');

    BasicMailer::sendMail($mailData);

    $queryResult['authUser'] = $user;

    return response()->json([
      'success' => true,
      'message' => __('A verification mail has been sent to your email address'),
      'data' => $queryResult
    ]);
  }

  public function facebookLogin(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'access_token' => 'required|string',
    ]);

    if ($validator->fails()) {
      return response()->json(['status' => 'error', 'message' => 'Access token is required.'], 422);
    }

    try {
      $facebookUser = Socialite::driver('facebook')->userFromToken($request->access_token);

      $user = User::where('provider', 'facebook')->where('provider_id', $facebookUser->id)->first();

      if (!$user) {
        $user = User::where('email', $facebookUser->getEmail())->first();

        if (!$user) {
          $avatarUrl = $facebookUser->getAvatar();
          $avatarName = $facebookUser->getId() . '.jpg';

          $path = public_path('assets/img/users/');
          file_put_contents($path . $avatarName, file_get_contents($avatarUrl));

          $user = User::create([
            'name' => $facebookUser->getName(),
            'username' => $facebookUser->getId(),
            'email' => $facebookUser->getEmail(),
            'email_verified_at' => now(),
            'image' => $avatarName,
            'status' => 1,
            'provider' => 'facebook',
            'provider_id' => $facebookUser->getId(),
            'password' => bcrypt(\Str::random(12)),
          ]);
        } else {
          $user->update([
            'provider' => 'facebook',
            'provider_id' => $facebookUser->getId(),
          ]);
        }
      }

      if ($user->status != 1) {
        return response()->json(['status' => 'error', 'message' => 'Account is deactivated'], 403);
      }

      $token = $user->createToken('facebook-token')->plainTextToken;

      return response()->json([
        'status' => 'success',
        'user' => $user,
        'token' => $token,
      ]);
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => 'Invalid access token'], 401);
    }
  }

  //edit profile
  public function editProfile(Request $request)
  {
    $misc = new MiscellaneousController();
    $data['bgImg'] = asset('assets/img/' . @$misc->getBreadcrumb()->breadcrumb);

    $language = HelperController::getLanguage($request);

    $misc = new MiscellaneousController();

    $data['pageHeading'] = $misc->getPageHeading($language);

    $user = Auth::guard('sanctum')->user();
    if ($user->image) {
      $user->image = asset('assets/img/users/' . $user->image);
    }

    $data['authUser'] = $user;

    return response()->json([
      'status' => 'success',
      'data' => $data
    ]);
  }

  //update profile
  public function updateProfile(Request $request)
  {
    $authUser = Auth::guard('sanctum')->user();
    $rules = ([
      'name' => 'required',
      'username' => [
        'required',
        'alpha_dash',
        Rule::unique('users', 'username')->ignore(Auth::guard('sanctum')->user()),
      ],
      'email' => [
        'required',
        'email',
        Rule::unique('users', 'username')->ignore($authUser->id)
      ],
    ]);

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'errors' => $validator->errors()
      ], 422);
    }

    $in = $request->all();
    $file = $request->file('image');
    if ($file) {
      $extension = $file->getClientOriginalExtension();
      $directory = public_path('assets/img/users/');
      $fileName = uniqid() . '.' . $extension;
      @mkdir($directory, 0775, true);
      $file->move($directory, $fileName);
      $in['image'] = $fileName;
    }

    $authUser->update($in);

    return response()->json([
      'success' => true,
      'message' => 'Your profile has been updated successfully'
    ]);
  }

  //change password
  public function changePassword(Request $request)
  {
    $misc = new MiscellaneousController();

    $breadcrumb = null;
    if (!is_null($misc->getBreadcrumb()->breadcrumb)) {
      $breadcrumb = $misc->getBreadcrumb()->breadcrumb;
    }
    $data['bgImg'] = asset('assets/img/' . $breadcrumb);

    $language = HelperController::getLanguage($request);

    $data['pageHeading'] = $misc->getPageHeading($language);

    return response()->json([
      'success' => true,
      'data' => $data
    ]);
  }

  //update password
  public function updatePassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'current_password' => 'required',
      'new_password' => 'required|confirmed|min:6',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'errors' => $validator->errors()
      ], 422);
    }

    $user = Auth::guard('sanctum')->user();

    if (!Hash::check($request->current_password, $user->password)) {
      return response()->json([
        'errors' => ['current_password' => ['Current password is incorrect.']]
      ], 422);
    }
    $user->update([
      'password' => Hash::make($request->new_password)
    ]);


    return response()->json([
      'status' => true,
      'message' => 'Password updated successfully'
    ]);
  }


  //forget password - send OTP
  public function forgetPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|exists:users,email'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'validation_error',
        'errors' => $validator->errors()
      ], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
      return response()->json([
        'status' => 'error',
        'message' => 'Email not found'
      ], 404);
    }

    // Generate 4-digit OTP
    $otp = rand(1000, 9999);

    // Store OTP in user table (you may want to create a separate password_resets table)
    $user->otp_code = $otp;
    $user->otp_expires_at = Carbon::now()->addMinutes(10);
    $user->save();

    // Send OTP via email
    try {
      $bs = Basic::first();
      $mailTemplate = MailTemplate::where('mail_type', 'password_reset')->first();

      if ($mailTemplate) {
        $mailSubject = $mailTemplate->mail_subject;
        $mailBody = $mailTemplate->mail_body;

        $mailBody = str_replace('{customer_name}', $user->username, $mailBody);
        $mailBody = str_replace('{otp_code}', $otp, $mailBody);
        $mailBody = str_replace('{website_title}', $bs->website_title, $mailBody);

        $mailData = [
          'subject' => $mailSubject,
          'body' => $mailBody,
          'recipient' => $user->email
        ];

        BasicMailer::sendMail($mailData);
      }
    } catch (\Exception $e) {
      Log::error('Failed to send OTP email: ' . $e->getMessage());
      // Continue anyway - OTP is saved in database
    }

    return response()->json([
      'status' => 'success',
      'message' => 'OTP sent to your email address'
    ], 200);
  }

  //verify OTP
  public function verifyOTP(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
      'otp' => 'required|digits:4'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'validation_error',
        'errors' => $validator->errors()
      ], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
      return response()->json([
        'status' => 'error',
        'message' => 'Email not found'
      ], 404);
    }

    if ($user->otp_code != $request->otp) {
      return response()->json([
        'status' => 'error',
        'message' => 'Invalid OTP'
      ], 400);
    }

    if (Carbon::now()->greaterThan($user->otp_expires_at)) {
      return response()->json([
        'status' => 'error',
        'message' => 'OTP has expired'
      ], 400);
    }

    // Mark OTP as verified
    $user->otp_verified = true;
    $user->save();

    return response()->json([
      'status' => 'success',
      'message' => 'OTP verified successfully'
    ], 200);
  }

  //reset password
  public function resetPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
      'password' => 'required|min:6|confirmed',
      'password_confirmation' => 'required'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'validation_error',
        'errors' => $validator->errors()
      ], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
      return response()->json([
        'status' => 'error',
        'message' => 'Email not found'
      ], 404);
    }

    if (!$user->otp_verified) {
      return response()->json([
        'status' => 'error',
        'message' => 'Please verify OTP first'
      ], 400);
    }

    // Update password
    $user->password = Hash::make($request->password);
    $user->otp_code = null;
    $user->otp_expires_at = null;
    $user->otp_verified = false;
    $user->save();

    return response()->json([
      'status' => 'success',
      'message' => 'Password reset successfully'
    ], 200);
  }

  //logout
  public function logoutSubmit(Request $request)
  {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
      'status' => 'success',
      'message' => 'Logout successfully'
    ], 200);
  }

  //dashboard
  public function redirectToDashboard(Request $request)
  {
    $user = Auth::guard('sanctum')->user();

    if (!$user) {
      return response()->json([
        'success' => false,
        'message' => 'Unauthenticated.'
      ], 401);
    }

    $language = HelperController::getLanguage($request);

    $misc = new MiscellaneousController();

    $breadcrumb = null;
    if (!is_null($misc->getBreadcrumb()->breadcrumb)) {
      $breadcrumb = $misc->getBreadcrumb()->breadcrumb;
    }
    if ($user->image) {
      $user->image = asset('assets/img/users/' . $user->image);
    }

    $data = [
      'language' => $language,
      'bgImg' => asset('assets/img/' . $breadcrumb),
      'pageHeading' => $misc->getPageHeading($language),
      'authUser' => $user,
    ];

    return response()->json([
      'success' => true,
      'data' => $data,
    ]);
  }
  //wishlist
  public function wishlist(Request $request)
  {
    $language = HelperController::getLanguage($request);

    $misc = new MiscellaneousController();


    $breadcrumb = null;
    if (!is_null($misc->getBreadcrumb()->breadcrumb)) {
      $breadcrumb = $misc->getBreadcrumb()->breadcrumb;
    }
    $data['bgImg'] = asset('assets/img/' . $breadcrumb);


    $data['pageHeading'] = $misc->getPageHeading($language);

    $user_id = Auth::guard('sanctum')->user()->id;

    $wishlists = Wishlist::join('property_contents', 'wishlists.property_id', 'property_contents.property_id')
      ->join('properties', 'wishlists.property_id', 'properties.id')
      ->when('properties.vendor_id' != "0", function ($query) {
        return $query->leftJoin('memberships', 'properties.vendor_id', '=', 'memberships.vendor_id')
          ->where(function ($query) {
            $query->where([
              ['memberships.status', '=', 1],
              ['memberships.start_date', '<=', now()->format('Y-m-d')],
              ['memberships.expire_date', '>=', now()->format('Y-m-d')],
            ])->orWhere('properties.vendor_id', '=', 0);
          });
      })
      ->when('properties.vendor_id' != "0", function ($query) {
        return $query->leftJoin('vendors', 'properties.vendor_id', '=', 'vendors.id')
          ->where(function ($query) {
            $query->where([
              ['vendors.status', '=', 1],
            ])->orWhere('properties.vendor_id', '=', 0);
          });
      })
      ->where([['vendors.status', 1], ['properties.status', 1]])
      ->where('property_contents.language_id', $language->id)
      ->where('wishlists.user_id', $user_id)
      ->select('wishlists.id as wishlist_id', 'properties.id as property_id',  'property_contents.title', 'property_contents.slug', 'properties.featured_image')
      ->get();

    // Add full image URLs
    $wishlists->transform(function ($wishlist) {
      if (!empty($wishlist->featured_image)) {
        $wishlist->featured_image = asset('assets/img/property/featureds/' . $wishlist->featured_image);
      }
      return $wishlist;
    });

    $data['wishlists'] = $wishlists;
    return response()->json([
      'success' => true,
      'data' => $data
    ]);
  }

  public function addWishlist(Request $request)
  {
    $user =  Auth::guard('sanctum')->user();

    $rules = [
      'property_id' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
      return response()->json([
        'status' => false,
        'errors' => $validator->errors()
      ], 422);
    }

    $user_id = $user->id;
    $property_id = $request->property_id;

    $check = Wishlist::where('property_id', $property_id)
      ->where('user_id', $user_id)
      ->first();
    if (!empty($check)) {
      return response()->json([
        'status' => false,
        'message' => __('This list is already in your wishlist.')
      ]);
    } else {
      $add = new Wishlist;
      $add->property_id = $property_id;
      $add->user_id = $user_id;
      $add->save();

      return response()->json([
        'status' => true,
        'message' => __('Added to your wishlist successfully')
      ], 200);
    }
  }

  public function removeWishlist(Request $request)
  {
    $rules = [
      'property_id' => 'required'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
      return response()->json([
        'status' => false,
        'errors' => $validator->errors()
      ], 422);
    }

    $property_id = $request->property_id;

    if (Auth::guard('sanctum')->user()) {
      $remove = Wishlist::where('property_id', $property_id)->first();
      if ($remove) {
        $remove->delete();
        return response()->json([
          'success' => true,
          'message' => __('Removed From wishlist successfully'),
        ]);
      } else {
        return response()->json([
          'success' => false,
          'message' => __('Item not found!'),
        ]);
      }
    } else {
      return response()->json([
        'success' => false,
        'message' => __('Unauthenticated. please login then remove wishlist'),
      ]);
    }
  }
}
