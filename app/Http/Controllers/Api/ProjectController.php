<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Models\Admin;
use App\Models\Project\Project;
use App\Models\Project\ProjectContent;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
  /**
   * Get all projects with basic info
   */
  public function index(Request $request)
  {
    $language = HelperController::getLanguage($request);

    $projects = Project::where('approve_status', 1)
      ->with([
        'projectContent' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        },
        'vendor',
        'agent'
      ])
      ->orderBy('id', 'desc')
      ->paginate(6);

    // Add full image URLs
    $projects->getCollection()->transform(function ($project) {
      if (!empty($project->featured_image) && !str_starts_with($project->featured_image, 'http')) {
        $project->featured_image = asset('assets/img/project/featured/' . $project->featured_image);
      }

      // Add vendor photo URL
      if ($project->vendor && !empty($project->vendor->photo) && !str_starts_with($project->vendor->photo, 'http')) {
        $project->vendor->photo = asset('assets/admin/img/vendor-photo/' . $project->vendor->photo);
      }

      // Add agent image URL
      if ($project->agent && !empty($project->agent->image) && !str_starts_with($project->agent->image, 'http')) {
        $project->agent->image = asset('assets/img/agents/' . $project->agent->image);
      }

      // Handle admin relationship
      if ((!$project->vendor_id || $project->vendor_id == 0) && (!$project->agent_id || $project->agent_id == 0)) {
        // If no vendor or agent, load first active admin as fallback
        if (!$project->admin || !$project->admin_id || $project->admin_id == 0) {
          $project->admin = Admin::where('status', 1)->first();
        }
      }

      // Add admin image URL
      if ((int) $project->vendor_id === 0) {

        $admin = Admin::first();

        if ($admin) {
          $project->admin = $admin;

          $image = $admin->image;

          if ($image && !str_starts_with($image, 'http')) {
            $project->admin->image = asset('assets/img/admins/' . $image);
          } else {
            $project->admin->image = $image ?: null;
          }
        }
      }

      // Add status label
      $project->status_label = match ($project->status) {
        1 => 'Completed',
        0 => 'Under Construction',
        default => 'Under Construction'
      };

      // Add currency symbol to prices
      $currencySymbol = '$'; // You can get this from settings if needed
      $project->formatted_min_price = $currencySymbol . number_format((float)$project->min_price, 0);
      $project->formatted_max_price = $currencySymbol . number_format((float)$project->max_price, 0);

      return $project;
    });

    return response()->json([
      'success' => true,
      'data' => $projects
    ]);
  }

  /**
   * Get single project with full details
   */
  public function show(Request $request, $id)
  {
    $language = HelperController::getLanguage($request);

    $project = Project::where('approve_status', 1)
      ->with([
        'projectContent' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        },
        'vendor',
        'agent',
        'projectTypes.projectTypeContnents' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        },
        'specifications.specificationContents' => function ($q) use ($language) {
          $q->where('language_id', $language->id);
        },
        'galleryImages',
        'floorplanImages'
      ])
      ->find($id);

    if (!$project) {
      return response()->json([
        'success' => false,
        'message' => 'Project not found'
      ], 404);
    }

    // Add admin image URL
    if ((int) $project->vendor_id === 0) {

      $admin = Admin::first();

      if ($admin) {
        $project->admin = $admin;

        $image = $admin->image;

        if ($image && !str_starts_with($image, 'http')) {
          $project->admin->image = asset('assets/img/admins/' . $image);
        } else {
          $project->admin->image = $image ?: null;
        }
      }
    }

    // Add full URL for featured image
    if (!empty($project->featured_image) && !str_starts_with($project->featured_image, 'http')) {
      $project->featured_image = asset('assets/img/project/featured/' . $project->featured_image);
    }

    // Add full URLs for gallery images
    if ($project->galleryImages) {
      foreach ($project->galleryImages as $image) {
        if (!empty($image->image) && !str_starts_with($image->image, 'http')) {
          $image->image = url('assets/img/project/gallery-images/' . $image->image);
        }
      }
    }

    // Add full URLs for floorplan images
    if ($project->floorplanImages) {
      foreach ($project->floorplanImages as $image) {
        if (!empty($image->image) && !str_starts_with($image->image, 'http')) {
          $image->image = url('assets/img/project/floor-paln-images/' . $image->image);
        }
      }
    }

    // Add vendor photo URL
    if ($project->vendor && !empty($project->vendor->photo) && !str_starts_with($project->vendor->photo, 'http')) {
      $project->vendor->photo = asset('assets/admin/img/vendor-photo/' . $project->vendor->photo);
    }

    // Add agent image URL
    if ($project->agent && !empty($project->agent->image) && !str_starts_with($project->agent->image, 'http')) {
      $project->agent->image = asset('assets/img/agents/' . $project->agent->image);
    }

    // Handle admin relationship
    if ((!$project->vendor_id || $project->vendor_id == 0) && (!$project->agent_id || $project->agent_id == 0)) {
      // If no vendor or agent, load first active admin as fallback
      if (!$project->admin || !$project->admin_id || $project->admin_id == 0) {
        $project->admin = Admin::where('status', 1)->first();
      }
    }

    // Add admin image URL
    if ($project->admin) {
      if (!empty($project->admin->image) && !str_starts_with($project->admin->image, 'http')) {
        $project->admin->image = url('assets/img/admins/' . $project->admin->image);
      } else if (empty($project->admin->image)) {
        $project->admin->image = null;
      }
    }

    // Add status label
    $project->status_label = match ($project->status) {
      1 => 'Completed',
      0 => 'Under Construction',
      default => 'Under Construction'
    };

    // Add currency symbol to prices
    $currencySymbol = '$';
    $project->formatted_min_price = $currencySymbol . number_format((float)$project->min_price, 0);
    $project->formatted_max_price = $currencySymbol . number_format((float)$project->max_price, 0);

    return response()->json([
      'success' => true,
      'data' => $project
    ]);
  }
}
