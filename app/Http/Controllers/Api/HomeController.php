<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\AgentInfo;
use App\Models\BasicSettings\Basic;
use App\Models\HomePage\Section;
use App\Models\Language;
use App\Models\MobileSection;
use App\Models\Project\Project;
use App\Models\Property\Property;
use App\Models\Property\PropertyCategory;
use App\Models\Property\Wishlist;
use App\Models\Vendor;
use App\Models\VendorInfo;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
  public function getBasic()
  {
    $basicData = DB::table('basic_settings')
      ->select('primary_color', 'mobile_app_logo', 'mobile_favicon', 'base_currency_text', 'base_currency_rate')
      ->first();

    $basicData->mobile_app_logo = asset('assets/img/mobile-interface/' . $basicData->mobile_app_logo);
    $basicData->mobile_favicon = asset('assets/img/mobile-interface/' . $basicData->mobile_favicon);

    $data['basic_data'] = $basicData;
    $data['languages'] = Language::all();

    return response()->json([
      'success' => true,
      'data' => $data
    ]);
  }
  public function index(Request $request)
  {
    $language = HelperController::getLanguage($request);

    $information['language'] = $language;

    // Property Categories
    $proeprty_categories = PropertyCategory::where([['status', 1], ['featured', 1]])
      ->with(['categoryContent' => function ($q) use ($language) {
        $q->where('language_id', $language->id);
      }])->orderBy('serial_number', 'asc')->get();

    $all_proeprty_categories = PropertyCategory::where('status', 1)
      ->with(['categoryContent' => function ($q) use ($language) {
        $q->where('language_id', $language->id);
      }])->orderBy('serial_number', 'asc')->get();

    // Add full image URLs for property categories
    $proeprty_categories->transform(function ($category) {
      if (!empty($category->image)) {
        $category->image = asset('assets/img/property-category/' . $category->image);
      }
      return $category;
    });

    $all_proeprty_categories->transform(function ($category) {
      if (!empty($category->image)) {
        $category->image = asset('assets/img/property-category/' . $category->image);
      }
      return $category;
    });

    $sectionContent = MobileSection::where('language_id', $language->id)
      ->first();
    $information['sectionContent'] = $sectionContent;

    $information['property_categories'] = $proeprty_categories;
    $information['all_proeprty_categories'] = $all_proeprty_categories;

    // Properties
    $latest_properties = Property::where([['properties.status', 1], ['properties.approve_status', 1]])
      ->where('property_contents.language_id', $language->id)
      ->join('property_contents', 'property_contents.property_id', 'properties.id')
      ->join('property_categories', 'property_categories.id', 'properties.category_id')
      ->when('properties.vendor_id' != 0, function ($query) {
        $query->leftJoin('memberships', 'properties.vendor_id', '=', 'memberships.vendor_id')
          ->where(function ($query) {
            $query->where([
              ['memberships.status', '=', 1],
              ['memberships.start_date', '<=', now()->format('Y-m-d')],
              ['memberships.expire_date', '>=', now()->format('Y-m-d')],
            ])->orWhere('properties.vendor_id', '=', 0);
          });
      })
      ->when('properties.vendor_id' != 0, function ($query) {
        return $query->leftJoin('vendors', 'properties.vendor_id', '=', 'vendors.id')
          ->where(function ($query) {
            $query->where('vendors.status', '=', 1)->orWhere('properties.vendor_id', '=', 0);
          });
      })
      ->select('properties.*', 'property_contents.language_id', 'property_contents.slug', 'property_contents.title', 'property_contents.address', 'property_contents.language_id')
      ->latest()
      ->take(8)
      ->get();

    // Get user ID if authenticated
    $userId = null;
    if (Auth::guard('sanctum')->check()) {
      $userId = Auth::guard('sanctum')->user()->id;
    }

    // Add full image URLs for properties and check wishlist status
    $latest_properties->transform(function ($property) use ($userId) {
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

    $information['latest_properties'] = $latest_properties;

    // Featured Properties
    $timezone = Basic::pluck('timezone')->first();
    $information['featured_properties'] = Property::where([['properties.status', 1], ['properties.approve_status', 1]])
      ->leftJoin('featured_properties', 'featured_properties.property_id', 'properties.id')
      ->leftJoin('property_contents', 'property_contents.property_id', 'properties.id')
      ->leftJoin('property_categories', 'property_categories.id', '=', 'properties.category_id')
      ->leftJoin('property_category_contents', function ($join) use ($language) {
        $join->on('property_category_contents.category_id', '=', 'property_categories.id')
          ->where('property_category_contents.language_id', '=', $language->id);
      })
      ->when('properties.vendor_id' != 0, function ($query) {
        $query->leftJoin('memberships', 'properties.vendor_id', '=', 'memberships.vendor_id')
          ->where(function ($query) {
            $query->where([
              ['memberships.status', '=', 1],
              ['memberships.start_date', '<=', now()->format('Y-m-d')],
              ['memberships.expire_date', '>=', now()->format('Y-m-d')],
            ])->orWhere('properties.vendor_id', '=', 0);
          });
      })
      ->when('properties.vendor_id' != 0, function ($query) {
        return $query->leftJoin('vendors', 'properties.vendor_id', '=', 'vendors.id')
          ->where(function ($query) {
            $query->where('vendors.status', '=', 1)->orWhere('properties.vendor_id', '=', 0);
          });
      })
      ->where([
        ['featured_properties.status', 1],
        ['featured_properties.start_date', '<=', Carbon::now()->timezone($timezone)->format('Y-m-d H:i:s')],
        ['featured_properties.end_date', '>=', Carbon::now()->timezone($timezone)->format('Y-m-d H:i:s')],
      ])
      ->where('property_contents.language_id', $language->id)
      ->select(
        'properties.*',
        'featured_properties.id as featured_id',
        'property_contents.slug',
        'property_contents.title',
        'property_contents.address',
        'property_contents.language_id',
        'property_category_contents.name as category_name',
      )
      ->inRandomOrder()
      ->take(10)
      ->get();

    // Add full image URLs for featured properties and check wishlist status
    $information['featured_properties']->transform(function ($property) use ($userId) {
      if (!empty($property->featured_image)) {
        $property->featured_image = asset('assets/img/property/featureds/' . $property->featured_image);
      }
      if (!empty($property->floor_planning_image)) {
        $property->floor_planning_image = asset('assets/img/property/plannings/' . $property->floor_planning_image);
      }
      if (!empty($property->video_image)) {
        $property->video_image = asset('assets/img/property/video/' . $property->video_image);
      }
      if (!empty($property->mobile_image)) {
        $property->mobile_image = asset('assets/img/property/mobile-image/' . $property->mobile_image);
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

    return response()->json([
      'status' => 'success',
      'data' => $information,
    ], 200);
  }
  //vendor details 
  public function vendorDetails(Request $request, $username)
  {
    $language = HelperController::getLanguage($request);
    $information['language'] = $language;

    if ($username == 'admin') {
      $vendor = Admin::with(['adminInfo' => function ($q) use ($language) {
        $q->where('language_id', $language->id);
      }])->where('role_id', null)->first();
      $vendor_id = 0;
      $vendor->image = url('assets/img/admins/' . $vendor->image);
      $information['total_properties'] = Property::where('vendor_id', 0)->count();
    } else {
      $vendor = Vendor::join('memberships', 'memberships.vendor_id', 'vendors.id')
        ->where([
          ['memberships.status', 1],
          ['memberships.start_date', '<=', Carbon::now()->format('Y-m-d')],
          ['memberships.expire_date', '>=', Carbon::now()->format('Y-m-d')],
        ])
        ->where('vendors.username', $request->username)
        ->with(['vendor_info' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        }])
        ->select('vendors.*')
        ->first();
      if (!$vendor) {
        return response()->json([
          'success' => false,
          'message' => "No Vendor data Found!"
        ]);
      }
      if ($vendor->photo) {
        $vendor->photo = asset('assets/admin/img/vendor-photo/' . $vendor->photo);
      }
      $vendor_id = $vendor->id;
      $vendorInfo = VendorInfo::where([['vendor_id', $vendor_id], ['language_id', $language->id]])->first();
      $information['vendorInfo'] = $vendorInfo;
    }

    $information['vendor'] = $vendor;

    $all_properties = Property::where([
      ['properties.vendor_id', $vendor_id],
      ['properties.status', 1],
      ['properties.approve_status', 1],
    ])
      ->join('property_contents', 'property_contents.property_id', '=', 'properties.id')
      ->where('property_contents.language_id', $language->id)
      ->select('properties.*',  'property_contents.title', 'property_contents.slug', 'property_contents.address', 'property_contents.description', 'property_contents.language_id')
      ->orderBy('properties.id', 'desc')
      ->paginate(10);

    $all_properties->transform(function ($property) {
      if (!empty($property->featured_image)) {
        $property->featured_image = asset(
          'assets/img/property/featureds/' . $property->featured_image
        );
      }

      return $property; 
    });

    $information['all_properties'] = $all_properties;

    $uniqueCategoryIds = $information['all_properties']->pluck('categoryContent.category_id')->unique();
    $information['categories'] = PropertyCategory::where('status', 1)->with(['categoryContent' => function ($q) use ($language) {
      $q->where('language_id', $language->id);
    }])->whereIn('id', $uniqueCategoryIds)->get();


    $all_projects = Project::where([['projects.vendor_id', $vendor_id], ['projects.approve_status', 1]])
      ->join('project_contents', 'project_contents.project_id', 'projects.id')
      ->where('project_contents.language_id', $language->id)
      ->select('projects.*', 'project_contents.language_id', 'project_contents.title', 'project_contents.slug', 'project_contents.address', 'project_contents.description')
      ->orderBy('id', 'desc')
      ->paginate(10);

    $all_projects->getCollection()->transform(function ($project) {
      if (!empty($project->featured_image) && !str_starts_with($project->featured_image, 'http')) {
        $project->featured_image = asset('assets/img/project/featured/' . $project->featured_image);
      }
      return $project;
    });

    $information['all_projects']  = $all_projects;

    $agents = Agent::where([['vendor_id', $vendor_id], ['status', 1]])->with(['agent_info' => function ($q) use ($language) {
      $q->where('language_id', $language->id);
    }, 'properties', 'projects'])->paginate(10);

    $agents->getCollection()->transform(function ($agent) {
      if (!empty($agent->image) && !str_starts_with($agent->image, 'http')) {
        $agent->image = asset('assets/img/agents/' . $agent->image);
      }
      return $agent;
    });
    $information['agents'] =  $agents;

    $secInfo = Section::query()->select('subscribe_section_status')->first();
    $information['secInfo'] = $secInfo;
    $information['currencyInfo'] = $this->getCurrencyInfo();
    $information['info'] = Basic::select('google_recaptcha_status')->first();

    return response()->json([
      'success' => true,
      'data' => $information
    ]);
  }

  public function agentDetails(Request $request, $username)
  {

    $language = HelperController::getLanguage($request);
    $information['language'] = $language;

    $agent = Agent::query()
      ->leftJoin('vendors', 'agents.vendor_id', '=', 'vendors.id')
      ->leftJoin('memberships', function ($join) {
        $join->on('agents.vendor_id', '=', 'memberships.vendor_id')
          ->where('memberships.status', '=', 1)
          ->where('memberships.start_date', '<=', Carbon::now()->format('Y-m-d'))
          ->where('memberships.expire_date', '>=', Carbon::now()->format('Y-m-d'));
      })

      ->where('agents.username', $username)->select('agents.*')->first();
    if (!$agent) {
      return response()->json([
        'success' => false,
        'message' => "No Agent Data Found!"
      ]);
    }


    $agentInfo = AgentInfo::where([['agent_id', $agent->id], ['language_id', $language->id]])->first();
    $information['agentInfo'] = $agentInfo;
    $agent_id = $agent->id;

    if ($agent->image) {
      $agent->image = asset('assets/img/agents/' . $agent->image);
    }

    $information['agent'] = $agent;

    $all_properties = Property::where([['properties.agent_id', $agent_id], ['properties.status', 1], ['properties.approve_status', 1]])
      ->where('property_contents.language_id', $language->id)
      ->join('property_contents', 'property_contents.property_id', 'properties.id')
      ->select('properties.*',  'property_contents.title', 'property_contents.slug', 'property_contents.address', 'property_contents.description', 'property_contents.language_id')
      ->orderBy('properties.id', 'desc')
      ->paginate(10);
    $all_properties->transform(function ($property) {
      if (!empty($property->featured_image)) {
        $property->featured_image = asset(
          'assets/img/property/featureds/' . $property->featured_image
        );
      }

      return $property;
    });

    $information['all_properties'] = $all_properties;

    $information['all_properties']  = $all_properties;

    $all_projects = Project::where([['projects.agent_id', $agent_id], ['projects.approve_status', 1]])
      ->join('project_contents', 'project_contents.project_id', 'projects.id')
      ->where('project_contents.language_id', $language->id)
      ->select('projects.*', 'project_contents.language_id', 'project_contents.title', 'project_contents.slug', 'project_contents.address', 'project_contents.description')
      ->orderBy('id', 'desc')
      ->paginate(10);
    $all_projects->getCollection()->transform(function ($project) {
      if (!empty($project->featured_image) && !str_starts_with($project->featured_image, 'http')) {
        $project->featured_image = asset('assets/img/project/featured/' . $project->featured_image);
      }
      return $project;
    });
    $information['all_projects']  = $all_projects;

    $uniqueCategoryIds = $information['all_properties']->pluck('categoryContent.category_id')->unique();

    $information['categories'] = PropertyCategory::with(['categoryContent' => function ($q) use ($language) {
      $q->where('language_id', $language->id);
    }])->where('status', 1)->whereIn('id', $uniqueCategoryIds)->get();

    $secInfo = Section::query()->select('subscribe_section_status')->first();
    $information['secInfo'] = $secInfo;
    $information['currencyInfo'] = $this->getCurrencyInfo();
    $information['info'] = Basic::select('google_recaptcha_status')->first();
    return response()->json([
      'success' => true,
      'data' => $information
    ]);
  }
}
