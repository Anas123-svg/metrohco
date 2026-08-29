<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UploadFile;
use App\Jobs\PushNotificationJob;
use App\Models\Language;
use App\Models\MobileSection;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class MobileInterfaceController extends Controller
{
  //Mobile App Settings main page
  public function index(Request $request)
  {
    return view('admin.mobile-interface.index');
  }

  //home page content view and update function
  public function content(Request $request)
  {
    $Language = Language::where('code', $request->language)->firstOrFail();
    $data['langs'] = Language::get();

    $data['data'] = MobileSection::where('language_id', $Language->id)->first();
    return view('admin.mobile-interface.content', $data);
  }

  public function update(Request $request)
  {
    $rules = [
      'category_section_title' => 'max:255',
      'featured_property_section_title' => 'max:255',
      'latest_property_section_title' => 'max:255',
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
      return response()->json([
        'errors' => $validator->getMessageBag()->toArray()
      ], 400);
    }

    $language = Language::where('code', $request->language)->firstOrFail();
    $content = MobileSection::where('Language_id', $language->id)->first();



    if (!empty($content)) {
      $content->Language_id = $language->id;
    } else {
      $content = new MobileSection();
      $content->Language_id = $language->id;
    }
    $content->category_section_title = $request->category_section_title;
    $content->featured_property_section_title = $request->featured_property_section_title;
    $content->latest_property_section_title = $request->latest_property_section_title;

    $content->save();

    session()->flash('success', __('Updated successfully'));
    return response()->json(['status' => 'success']);
  }

  //general setting view and update function
  public function setting(Request $request)
  {
    $data['data'] = DB::table('basic_settings')->select('mobile_favicon', 'mobile_app_logo', 'has_mobile_app')
      ->first();
    // $data['config'] = include(public_path('config.php'));
    return view('admin.mobile-interface.general-settings', $data);
  }

  public function settingUpdate(Request $request)
  {
    $bs = DB::table('basic_settings')->select('mobile_favicon', 'mobile_app_logo')->first();

    $rules = [
      'has_mobile_app' => 'required',
    ];

    if (is_null($bs->mobile_favicon)) {
      $rules['mobile_favicon'] = 'required|mimes:png,jpg,jpeg,svg';
    }
    if (is_null($bs->mobile_favicon)) {
      $rules['mobile_app_logo'] = 'required|mimes:png,jpg,jpeg,svg';
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors())->withInput();
    }


    if ($request->hasFile('mobile_favicon')) {
      if (isset($bs->mobile_favicon)) {
        $favicon = UploadFile::update(public_path('assets/img/mobile-interface/'), $request->file('mobile_favicon'), $bs->mobile_favicon);
      } else {
        $favicon = UploadFile::store(public_path('assets/img/mobile-interface/'), $request->file('mobile_favicon'));
      }
    }
    if ($request->hasFile('mobile_app_logo')) {
      if (isset($bs->mobile_app_logo)) {
        $logo = UploadFile::update(public_path('assets/img/mobile-interface/'), $request->file('mobile_app_logo'), $bs->mobile_app_logo);
      } else {
        $logo = UploadFile::store(public_path('assets/img/mobile-interface/'), $request->file('mobile_app_logo'));
      }
    }

    DB::table('basic_settings')->updateOrInsert(
      ['uniqid' => 12345],
      [
        'mobile_favicon' => $favicon ?? $bs->mobile_favicon,
        'mobile_app_logo' => $logo ?? $bs->mobile_app_logo,
        'has_mobile_app' =>  $request->has_mobile_app,
      ]
    );

    return redirect()->back()->with('success', __('Updated Successfully'));
  }

  //plugins view function
  public function plugins()
  {
    $data = DB::table('basic_settings')->select('firebase_admin_json')
      ->first();
    return view('admin.mobile-interface.plugins', compact('data'));
  }

  public function updateFirebase(Request $request)
  {
    $request->validate([
      'firebase_admin_json' => 'required|mimes:json',
    ], [
      'firebase_admin_json.required' => __('The admin sdk json file is required.'),
      'firebase_admin_json.mimes' => __('Only json files are supported.'),
    ]);

    $bs = DB::table('basic_settings')
      ->select('firebase_admin_json')
      ->where('uniqid', 12345)
      ->first();

    // if json file already exists and user wants to update it
    if ($request->hasFile('firebase_admin_json') && !is_null($bs->firebase_admin_json)) {
      $file = UploadFile::update(public_path('assets/file/'), $request->file('firebase_admin_json'), $bs->firebase_admin_json);
    }

    //if json file doesn't exist and user wants to upload it
    if ($request->hasFile('firebase_admin_json') && is_null($bs->firebase_admin_json)) {
      $file = UploadFile::store(public_path('assets/file/'), $request->file('firebase_admin_json'));
    }

    DB::table('basic_settings')->updateOrInsert(
      ['uniqid' => 12345],
      [
        'firebase_admin_json' => $request->hasFile('firebase_admin_json') ? $file : $bs->firebase_admin_json,
      ]
    );
    session()->flash('success', __('Updated successfully!'));
    return redirect()->back();
  }

  //Mobile App Settings main page
  public function notification(Request $request)
  {
    $information['notifications'] = Notification::orderBy('id', 'asc')->get();

    return view('admin.mobile-interface.notification.index', $information);
  }

  public function notificationStore(Request $request)
  {
    $rules = [
      'title' => 'required|string|max:255',
      'message' => 'nullable|string|max:1000',
      'button_name' => 'required|string|max:100',
      'button_url' => 'required|url|starts_with:https',
    ];

    $messages = [
      'title.required' => 'The title field is required.',
      'button_name.required' => 'The button name field is required.',
      'button_url.required' => 'The button URL field is required.',
      'button_url.url' => 'Please enter a valid URL.',
      'button_url.starts_with' => 'Button URL must start with https.',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      return Response::json([
        'success' => false,
        'errors' => $validator->getMessageBag()
      ], 400);
    }

    Notification::create($request->only([
      'title',
      'message',
      'button_name',
      'button_url'
    ]));

    Session::flash('success', 'New Notification added successfully!');
    return Response::json(['status' => 'success'], 200);
  }

  public function notificationUpdate(Request $request)
  {
    $rules = [
      'title' => 'required|string|max:255',
      'message' => 'nullable|string|max:1000',
      'button_name' => 'required|string|max:100',
      'button_url' => 'required|url|starts_with:https',
    ];

    $messages = [
      'title.required' => 'The title field is required.',
      'button_name.required' => 'The button name field is required.',
      'button_url.required' => 'The button URL field is required.',
      'button_url.url' => 'Please enter a valid URL.',
      'button_url.starts_with' => 'Button URL must start with https.',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      return Response::json([
        'success' => false,
        'errors' => $validator->getMessageBag()
      ], 400);
    }

    $notification = Notification::query()->find($request['id']);


    $notification->update([
      'title'       => $request->title,
      'message'     => $request->message,
      'button_name' => $request->button_name,
      'button_url'  => $request->button_url,
    ]);
    Session::flash('success', 'Notification updated successfully!');

    return Response::json(['status' => 'success'], 200);
  }

  public function destroyNotification($id)
  {
    $notification = Notification::query()->find($id);

    $notification->delete();

    return redirect()->back()->with('success', 'Notification deleted successfully!');
  }
  public function sendNotification($id)
  {
    $notification = Notification::query()->find($id);

    $title = $notification->title;
    $message =  $notification->message;
    $buttonName =  $notification->button_name;
    $buttonURL =  $notification->button_url;



    $firebase_admin_json = DB::table('basic_settings')
      ->where('uniqid', 12345)
      ->value('firebase_admin_json');

    if (!is_null($firebase_admin_json)) {
      PushNotificationJob::dispatch($title, $message, $buttonName, $buttonURL)->delay(now()->addSeconds(1));
    }

    Session::flash('success', 'Notification has been sent.');

    return redirect()->back();
  }
}
