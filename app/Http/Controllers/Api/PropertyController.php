<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\Amenity;
use App\Models\AmenityContent;
use App\Models\BasicSettings\Basic;
use App\Models\Property\City;
use App\Models\Property\CityContent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use App\Models\Property\Country;
use App\Models\Property\CountryContent;
use Config;
use App\Models\Property\Property;
use App\Models\Property\PropertyCategory;
use App\Models\Property\PropertyCategoryContent;
use App\Models\Property\PropertyContact;
use App\Models\Property\State;
use App\Models\Property\StateContent;
use App\Models\Property\Wishlist;
use App\Models\Vendor;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
  public function index(Request $request)
  {
    $misc = new MiscellaneousController();
    $language = HelperController::getLanguage($request);
    $information['seoInfo'] = $language->seoInfo()->select('meta_keyword_properties', 'meta_description_properties')->first();

    if ($request->has('type') && ($request->type == 'commercial' || $request->type == 'residential')) {
      $information['categories'] = PropertyCategory::with(['categoryContent' => function ($q) use ($language) {
        $q->where('language_id', $language->id);
      }, 'properties'])->where([['status', 1], ['type', $request->type]])->get();
    } else {
      $information['categories'] = PropertyCategory::with(['categoryContent' => function ($q) use ($language) {
        $q->where('language_id', $language->id);
      }, 'properties'])->where('status', 1)->get();
    }

    $information['bgImg'] = $misc->getBreadcrumb();
    $information['pageHeading'] = $misc->getPageHeading($language);
    $information['amenities'] = Amenity::where('status', 1)->with(['amenityContent' => function ($q) use ($language) {
      $q->where('language_id', $language->id);
    }])->orderBy('serial_number')->get();

    $propertyCategory = null;
    $category = null;
    if ($request->filled('category') && $request->category != 'all') {
      $category = $request->category;
      $propertyCategory = PropertyCategoryContent::where([['language_id', $language->id], ['slug', $category]])->first();
    }

    $amenities = [];
    $amenityInContentId = [];
    if ($request->filled('amenities')) {
      $amenities = $request->amenities;
      foreach ($amenities as $amenity) {
        $amenConId = AmenityContent::where('name', $amenity)->where('language_id', $language->id)->pluck('amenity_id')->first();
        array_push($amenityInContentId, $amenConId);
      }
    }

    $amenityInContentId = array_unique($amenityInContentId);
    $type = null;
    if ($request->filled('type') && $request->type != 'all') {
      $type = $request->type;
    }

    $price = null;
    if ($request->filled('price') && $request->price != 'all') {
      $price = $request->price;
    }

    $purpose = null;
    if ($request->filled('purpose') && $request->purpose != 'all') {
      $purpose = $request->purpose;
    }

    $min = $max = null;
    if ($request->filled('min') && $request->filled('max')) {
      $min = intval($request->min);
      $max = intval(($request->max));
    }

    $title = $location = $beds = $baths = $area = $countryId = $stateId = $cityId = null;
    if ($request->filled('country') && $request->filled('country')) {

      $country = CountryContent::where([['name', $request->country], ['language_id', $language->id]])->first();
      if ($country) {
        $countryId = $country->country_id;
      }
    }
    if ($request->filled('state') && $request->filled('state')) {

      $state = StateContent::where([['name', $request->state], ['language_id', $language->id]])->first();
      if ($state) {
        $stateId = $state->state_id;
      }
    }
    if ($request->filled('city') && $request->filled('city')) {

      $city = CityContent::where([['name', $request->city], ['language_id', $language->id]])->first();
      if ($city) {
        $cityId = $city->city_id;
      }
    }
    if ($request->filled('title') && $request->filled('title')) {
      $title =  $request->title;
    }

    if ($request->filled('location') && $request->filled('location')) {
      $location =  $request->location;
    }
    if ($request->filled('beds') && $request->filled('beds')) {
      $beds =  $request->beds;
    }
    if ($request->filled('baths') && $request->filled('baths')) {
      $baths =  $request->baths;
    }
    if ($request->filled('area') && $request->filled('area')) {
      $area =  $request->area;
    }


    if ($request->filled('sort')) {
      if ($request['sort'] == 'new') {
        $order_by_column = 'properties.id';
        $order = 'desc';
      } elseif ($request['sort'] == 'old') {
        $order_by_column = 'properties.id';
        $order = 'asc';
      } elseif ($request['sort'] == 'high-to-low') {
        $order_by_column = 'properties.price';
        $order = 'desc';
      } elseif ($request['sort'] == 'low-to-high') {
        $order_by_column = 'properties.price';
        $order = 'asc';
      } else {
        $order_by_column = 'properties.id';
        $order = 'desc';
      }
    } else {
      $order_by_column = 'properties.id';
      $order = 'desc';
    }

    $property_contents = Property::where([['properties.status', 1], ['properties.approve_status', 1]])
      ->join('property_contents', 'properties.id', 'property_contents.property_id')
      ->join('property_categories', 'property_categories.id', 'properties.category_id')
      ->where('property_contents.language_id', $language->id)
      ->leftJoin('vendors', 'properties.vendor_id', '=', 'vendors.id')
      ->leftJoin('memberships', function ($join) {
        $join->on('properties.vendor_id', '=', 'memberships.vendor_id')
          ->where('memberships.status', '=', 1)
          ->where('memberships.start_date', '<=', Carbon::now()->format('Y-m-d'))
          ->where('memberships.expire_date', '>=', Carbon::now()->format('Y-m-d'));
      })
      ->where(function ($query) {
        $query->where('properties.vendor_id', '=', 0)
          ->orWhere(function ($query) {
            $query->where('vendors.status', '=', 1)->whereNotNull('memberships.id');
          });
      })

      ->when($type, function ($query) use ($type) {
        return $query->where('properties.type', $type);
      })
      ->when($purpose, function ($query) use ($purpose) {
        return $query->where('properties.purpose', $purpose);
      })
      ->when($countryId, function ($query) use ($countryId) {
        return $query->where('properties.country_id', $countryId);
      })
      ->when($stateId, function ($query) use ($stateId) {
        return $query->where('properties.state_id', $stateId);
      })
      ->when($cityId, function ($query) use ($cityId) {
        return $query->where('properties.city_id', $cityId);
      })
      ->when($category && $propertyCategory, function ($query) use ($propertyCategory) {
        return $query->where('properties.category_id', $propertyCategory->category_id);
      })

      ->when(!empty($amenityInContentId), function ($query) use ($amenityInContentId) {
        $query->whereHas(
          'proertyAmenities',
          function ($q) use ($amenityInContentId) {
            $q->whereIn('amenity_id', $amenityInContentId);
          },
          '=',
          count($amenityInContentId)
        );
      })
      ->when($price, function ($query) use ($price) {
        if ($price == 'negotiable') {
          return $query->where('properties.price', null);
        } elseif ($price == 'fixed') {

          return $query->where('properties.price', '!=', null);
        } else {
          return $query;
        }
      })

      ->when($min, function ($query) use ($min, $max, $price) {
        if ($price == 'fixed' || empty($price)) {
          return $query->where('properties.price', '>=', $min)
            ->where('properties.price', '<=', $max);
        } else {
          return $query;
        }
      })
      ->when($beds, function ($query) use ($beds) {
        return $query->where('properties.beds', $beds);
      })
      ->when($baths, function ($query) use ($baths) {
        return $query->where('properties.bath', $baths);
      })
      ->when($area, function ($query) use ($area) {
        return $query->where('properties.area', $area);
      })
      ->when($title, function ($query) use ($title) {
        return $query->where('property_contents.title', 'LIKE', '%' . $title . '%');
      })
      ->when($location, function ($query) use ($location) {
        return $query->where('property_contents.address', 'LIKE', '%' . $location . '%');
      })
      ->with(['categoryContent' => function ($q) use ($language) {
        $q->where('language_id', $language->id);
      }])

      ->select('properties.*', 'property_categories.id as categoryId', 'property_contents.title', 'property_contents.slug', 'property_contents.address', 'property_contents.description', 'property_contents.language_id')
      ->orderBy($order_by_column, $order)
      ->paginate(12);

    // Get user ID if authenticated
    $userId = null;
    if (Auth::guard('sanctum')->check()) {
      $userId = Auth::guard('sanctum')->user()->id;
    }

    // Add full image URLs for properties and check wishlist status
    $property_contents->getCollection()->transform(function ($property) use ($userId) {
      if (!empty($property->featured_image)) {
        $property->featured_image = asset('assets/img/property/featureds/' . $property->featured_image);
      }
      if (!empty($property->floor_planning_image)) {
        $property->floor_planning_image = asset('assets/img/property/plannings/' . $property->floor_planning_image);
      }
      if (!empty($property->video_image)) {
        $property->video_image = asset('assets/img/property/video/' . $property->video_image);
      }

      // Check if property is in user's wishlist
      $property->is_in_wishlist = false;
      if ($userId) {
        $property->is_in_wishlist = Wishlist::where('user_id', $userId)
          ->where('property_id', $property->id)
          ->exists();
      }

      return $property;
    });

    $information['property_contents'] = $property_contents;
    $information['contents'] = $property_contents;

    $information['all_cities'] = City::where('status', 1)->with(['cityContent' => function ($q) use ($language) {
      $q->where('language_id', $language->id);
    }])->get();
    $information['all_states'] = State::with(['stateContent' => function ($q) use ($language) {
      $q->where('language_id', $language->id);
    }])->get();
    $information['all_countries'] = Country::with(['countryContent' => function ($q) use ($language) {
      $q->where('language_id', $language->id);
    }])->get();

    $min = Property::where([['status', 1], ['approve_status', 1]])->min('price');
    $max = Property::where([['status', 1], ['approve_status', 1]])->max('price');
    $information['min'] = intval($min);
    $information['max'] = intval($max);
    $basicData = DB::table('basic_settings')
      ->select('property_country_status', 'property_state_status')
      ->first();
    $information['basic_info'] = $basicData;

    return response()->json([
      'status' => 'success',
      'data' => $information,
    ], 200);
  }

  /**
   * Get single property details
   * GET /api/properties/{id}
   */
  public function show(Request $request, $id)
  {
    $language = HelperController::getLanguage($request);

    $property = Property::where([['properties.status', 1], ['properties.approve_status', 1]])
      ->join('property_contents', 'properties.id', 'property_contents.property_id')
      ->where('property_contents.language_id', $language->id)
      ->where('properties.id', $id)
      ->leftJoin('vendors', 'properties.vendor_id', '=', 'vendors.id')
      ->leftJoin('memberships', function ($join) {
        $join->on('properties.vendor_id', '=', 'memberships.vendor_id')
          ->where('memberships.status', '=', 1)
          ->where('memberships.start_date', '<=', Carbon::now()->format('Y-m-d'))
          ->where('memberships.expire_date', '>=', Carbon::now()->format('Y-m-d'));
      })
      ->where(function ($query) {
        $query->where('properties.vendor_id', '=', 0)
          ->orWhere(function ($query) {
            $query->where('vendors.status', '=', 1)->whereNotNull('memberships.id');
          });
      })
      ->with([
        'propertyContent' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        },
        'categoryContent' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        },
        'vendor',
        'agent',
        'galleryImages',
        'specifications.specificationContents' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        },
        'proertyAmenities.amenity.amenityContent' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        },
        'country.countryContent' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        },
        'state.stateContent' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        },
        'city.cityContent' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        }
      ])
      ->select('properties.*', 'property_contents.title', 'property_contents.slug', 'property_contents.address', 'property_contents.description', 'property_contents.meta_keyword', 'property_contents.meta_description')
      ->first();

    if ((int) $property->vendor_id === 0) {

      $admin = Admin::first();

      if ($admin) {
        $property->admin = $admin;

        $image = $admin->image;

        if ($image && !str_starts_with($image, 'http')) {
          $property->admin->image = asset('assets/img/admins/' . $image);
        } else {
          $property->admin->image = $image ?: null;
        }
      }
    }

    if (!$property) {
      return response()->json([
        'success' => false,
        'message' => 'Property not found'
      ], 404);
    }

    // Add full URLs to images
    if (!empty($property->featured_image)) {
      $property->featured_image = asset('assets/img/property/featureds/' . $property->featured_image);
    }

    if (!empty($property->floor_planning_image)) {
      $property->floor_planning_image = asset('assets/img/property/plannings/' . $property->floor_planning_image);
    }

    if (!empty($property->video_image)) {
      $property->video_image = asset('assets/img/property/video/' . $property->video_image);
    }

    if ($property->galleryImages) {
      $property->galleryImages->transform(function ($image) {
        $image->image = asset('assets/img/property/slider-images/' . $image->image);
        return $image;
      });
    }

    // Add vendor/agent images if available
    if ($property->vendor && !empty($property->vendor->photo)) {
      $property->vendor->photo = asset('assets/admin/img/vendor-photo/' . $property->vendor->photo);
    }

    if ($property->agent && !empty($property->agent->image)) {
      $property->agent->image = asset('assets/img/agents/' . $property->agent->image);
    }

    // Check if property is in user's wishlist
    $property->is_in_wishlist = false;
    if (Auth::guard('sanctum')->check()) {
      $userId = Auth::guard('sanctum')->user()->id;
      $property->is_in_wishlist = Wishlist::where('user_id', $userId)
        ->where('property_id', $property->id)
        ->exists();
    }

    return response()->json([
      'success' => true,
      'data' => $property
    ]);
  }
  public function contact(Request $request)
  {
    $rules = [
      'name'      => 'required|string',
      'email'     => 'required|email:rfc,dns',
      'phone'     => 'required|numeric',
      'message'   => 'required|string',
      'vendor_id' => 'required',
      'property_id' => 'required',
    ];

    $info = Basic::select('google_recaptcha_status')->first();

    if ($info && $info->google_recaptcha_status == 1) {
      $rules['g-recaptcha-response'] = 'required|captcha';
    }

    $messages = [];

    if ($info && $info->google_recaptcha_status == 1) {
      $messages['g-recaptcha-response.required'] = 'Please verify that you are not a robot.';
      $messages['g-recaptcha-response.captcha']  = 'Captcha error! try again later or contact site admin.';
    }

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      return response()->json([
        'status'  => false,
        'message' => 'Validation failed',
        'errors'  => $validator->errors()
      ], 422);
    }

    if ($request->vendor_id != 0) {

      if ($request->vendor_id) {
        $vendor = Vendor::find($request->vendor_id);

        if (empty($vendor)) {

          return back()->with('error', 'Something went wrong!');
        }
        $request['to_mail'] = $vendor->email;
      }
      if ($request->agent_id) {
        $agent = Agent::find($request->agent_id);
        if (empty($agent)) {
          return back()->with('error', 'Something went wrong!');
        }
        $request['to_mail'] = $agent->email;
      }
    } elseif ($request->vendor_id == 0 && !empty($request->agent_id)) {
      $agent = Agent::find($request->agent_id);
      if (empty($agent)) {
        return back()->with('error', 'Something went wrong!');
      }
      $request['to_mail'] = $agent->email;
    } else {

      $admin = Admin::where('role_id', null)->first();
      $request['to_mail'] = $admin->email;
    }

    try {
      PropertyContact::create([
        'vendor_id' => $request->vendor_id,
        'agent_id' => $request->agent_id,
        'property_id' => $request->property_id,
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'message' => $request->message,

      ]);
      $this->sendMail($request);
    } catch (\Exception $e) {
      return response()->json([
        'status'  => false,
        'message' => 'Something went wrong',
        'error'   => $e->getMessage()
      ], 500);
    }

    return response()->json([
      'status'  => true,
      'message' => 'Message sent successfully'
    ], 200);
  }

  public function sendMail($request)
  {

    $info = DB::table('basic_settings')
      ->select('website_title', 'smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name', 'to_mail')
      ->first();
    $name = $request->name;
    $to = $request->to_mail;

    $subject = 'Contact for property';

    $message = '<p>A new message has been sent.<br/><strong>Client Name: </strong>' . $name . '<br/><strong>Client Mail: </strong>' . $request->email . '<br/><strong>Client Phone: </strong>' . $request->phone . '</p><p>Message : ' . $request->message . '</p>';

    if ($info->smtp_status == 1) {
      try {
        $smtp = [
          'transport' => 'smtp',
          'host' => $info->smtp_host,
          'port' => $info->smtp_port,
          'encryption' => $info->encryption,
          'username' => $info->smtp_username,
          'password' => $info->smtp_password,
          'timeout' => null,
          'auth_mode' => null,
        ];
        Config::set('mail.mailers.smtp', $smtp);
      } catch (\Exception $e) {
        Session::flash('error', $e->getMessage());
        return;
      }
    }
    $data = [
      'to' => $to,
      'subject' => $subject,
      'message' => $message,
    ];
    try {
      Mail::send([], [], function (Message $message) use ($data, $info) {
        $fromMail = $info->from_mail;
        $fromName = $info->from_name;
        $message->to($data['to'])
          ->subject($data['subject'])
          ->from($fromMail, $fromName)
          ->html($data['message'], 'text/html');
      });
    } catch (\Exception $e) {
      Session::flash('error', 'Something went wrong.');
      return;
    }
  }
}
