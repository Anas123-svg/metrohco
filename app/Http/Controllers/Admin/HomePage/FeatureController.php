<?php

namespace App\Http\Controllers\Admin\HomePage;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Prominence\FeatureSection;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
  public function sectionInfo(Request $request)
  {
    $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
    $information['language'] = $language;

    $information['data'] = $language->featureSection()->first();

    $information['langs'] = Language::all();

    return view('admin.home-page.featured-section.index', $information);
  }

  public function updateSectionInfo(Request $request)
  {
    $language = Language::query()->where('code', '=', $request->language)->first();
    $featured_section = FeatureSection::where('language_id', $language->id)->first();

    if ($featured_section) {
      $featured_section->update([
        'language_id' => $language->id,
        'title' => $request->title
      ]);
    } else {
      FeatureSection::create([
        'language_id' => $language->id,
        'title' => $request->title
      ]);
    }

    session()->flash('success', 'Feature section updated successfully!');

    return redirect()->back();
  }
}
