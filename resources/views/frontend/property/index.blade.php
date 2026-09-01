@php
     $version = $basicInfo->theme_version;
@endphp
@extends("frontend.layouts.layout-v$version")

@section('pageHeading')
    {{ !empty($pageHeading) ? $pageHeading->property_page_title : __('Property') }}
@endsection

@section('metaKeywords')
    @if (!empty($seoInfo))
        {{ $seoInfo->meta_keyword_properties }}
    @endif
@endsection

@section('metaDescription')
    @if (!empty($seoInfo))
        {{ $seoInfo->meta_description_properties }}
    @endif
@endsection
@section('style')
    <meta http-equiv="Cache-Control" content="no-store" />
    <style>
        .metro-results-map-wrap {
            position: relative;
            overflow: hidden;
            background: #eef1f1;
        }
        .metro-results-map-stage {
            /* The parent uses a ratio::before spacer. Absolute positioning is
             * required here; percentage heights on an auto-height ratio box can
             * otherwise collapse Google Maps to 0px on some browsers. */
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            min-height: 260px;
        }
        .metro-results-map-stage #main-map {
            width: 100%;
            height: 100%;
            min-height: 260px;
            background: #eef1f1;
        }
        /* Google Maps controls can inherit global button/image rules from the theme.
         * Normalize their box + icon alignment so zoom/fullscreen controls stay centered. */
        .metro-results-map-stage .gm-style button.gm-control-active,
        .metro-results-map-stage .gm-style .gm-fullscreen-control {
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            min-height: 40px !important;
            padding: 0 !important;
            border-radius: 9px !important;
            overflow: hidden !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 5px 16px rgba(31, 31, 31, .14) !important;
        }
        .metro-results-map-stage .gm-style button.gm-control-active > img,
        .metro-results-map-stage .gm-style .gm-fullscreen-control > img {
            position: absolute !important;
            inset: 0 !important;
            margin: auto !important;
            max-width: none !important;
            max-height: none !important;
            transform: none !important;
        }
        .metro-results-map-stage .gm-style .gm-bundled-control {
            margin: 12px !important;
        }
        .metro-results-map-stage .gm-style .gm-fullscreen-control {
            margin: 12px !important;
        }

        @media (max-width: 575.98px) {
            /* The old ratio made the map too shallow on phones. Give the results map
             * a real viewport-based canvas while preserving the ratio layout on desktop. */
            .map-area .metro-results-map-wrap::before {
                padding-bottom: 0 !important;
                height: min(68vh, 560px);
                min-height: 430px;
            }
            .metro-results-map-stage,
            .metro-results-map-stage #main-map {
                min-height: 430px;
            }
            .map-area .container {
                padding-left: 10px;
                padding-right: 10px;
            }
            .metro-results-map-stage .gm-style button.gm-control-active,
            .metro-results-map-stage .gm-style .gm-fullscreen-control {
                width: 42px !important;
                height: 42px !important;
                min-width: 42px !important;
                min-height: 42px !important;
                border-radius: 10px !important;
            }
            .metro-results-map-stage .gm-style .gm-bundled-control,
            .metro-results-map-stage .gm-style .gm-fullscreen-control {
                margin: 10px !important;
            }
        }

        @media (max-width: 575.98px) and (max-height: 680px) {
            .map-area .metro-results-map-wrap::before {
                height: 62vh;
                min-height: 370px;
            }
            .metro-results-map-stage,
            .metro-results-map-stage #main-map {
                min-height: 370px;
            }
        }
        .metro-map-area-summary {
            position: absolute;
            z-index: 5;
            left: 16px;
            top: 16px;
            max-width: calc(100% - 32px);
            padding: 10px 12px;
            border: 1px solid rgba(31,31,31,.08);
            border-radius: 9px;
            background: rgba(255,255,255,.94);
            box-shadow: 0 10px 28px rgba(31,31,31,.13);
            backdrop-filter: blur(8px);
        }
        .metro-map-area-label {
            display: block;
            margin-bottom: 7px;
            color: var(--color-medium);
            font-size: 9px;
            line-height: 1;
            font-weight: 700;
            letter-spacing: .08em;
        }
        .metro-map-area-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .metro-map-pill {
            display: inline-flex;
            align-items: center;
            min-height: 25px;
            padding: 4px 9px;
            border-radius: 999px;
            background: var(--color-secondary);
            color: #fff;
            font-size: 10px;
            line-height: 1.1;
            font-weight: 700;
            white-space: nowrap;
        }
        @media (max-width: 1199.98px) {
            #widgetOffcanvas {
                --bs-offcanvas-width: min(92vw, 390px);
            }
            #widgetOffcanvas .offcanvas-body {
                overflow-y: auto;
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
            }
        }
        @media (max-width: 575.98px) {
            .metro-map-area-summary {
                left: 10px;
                top: 10px;
                max-width: calc(100% - 20px);
                padding: 8px 10px;
            }
        }
    </style>
@endsection
@section('content')
    <!-- Map Start-->
    <div class="map-area border-top header-next pt-30">
        <!-- Background Image -->
        <div class="container">
            <div class="lazy-container radius-md ratio border metro-results-map-wrap">
                <div class="metro-results-map-stage">
                    <div id="main-map"></div>
                    <div class="metro-map-area-summary" id="metroMapAreaSummary" hidden>
                        <span class="metro-map-area-label">SEARCH AREA</span>
                        <div class="metro-map-area-pills" id="metroMapAreaPills"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Map End-->

    <!-- Listing Start -->
    <div class="listing-grid pt-40 pb-70">
        <div class="container">
            <div class="row gx-xl-5">
                <div class="col-xl-3">
                    <div class="widget-offcanvas offcanvas-xl offcanvas-start" tabindex="-1" id="widgetOffcanvas"
                        aria-labelledby="widgetOffcanvas">
                        <div class="offcanvas-header px-20">
                            <h4 class="offcanvas-title">{{ __('Filter') }}</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                data-bs-target="#widgetOffcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body p-3 p-xl-0">

                            <aside class="sidebar-widget-area" data-aos="fade-up">


                                @include('frontend.property.partials.metro-sidebar-filters')


                            </aside>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="product-sort-area mb-10" data-aos="fade-up">
                        <div class="row justify-content-sm-end">
                            <div class="col-sm-5 d-xl-none">
                                <button class="btn btn-sm btn-outline icon-end radius-sm mb-15" type="button"
                                    data-bs-toggle="offcanvas" data-bs-target="#widgetOffcanvas"
                                    aria-controls="widgetOffcanvas">
                                    {{ __('Filter') }} <i class="fal fa-filter"></i>
                                </button>
                            </div>
                            <div class="col-sm-7">
                                <ul class="product-sort-list text-sm-end list-unstyled mb-15">
                                    <li class="item">
                                        <div class="sort-item d-flex align-items-center">
                                            <label class="color-dark me-2 font-sm flex-auto">{{ __('Sort By') }} :</label>
                                            <select class="form-select form_control" name="sort"
                                                onchange="updateURL('sort='+$(this).val())">
                                                <option
                                                    {{ request()->filled('sort') && request()->input('sort') == 'new' ? 'selected' : '' }}
                                                    value="new">{{ __('Newest') }}</option>
                                                <option
                                                    {{ request()->filled('sort') && request()->input('sort') == 'old' ? 'selected' : '' }}
                                                    value="old">{{ __('Oldest') }}</option>
                                                <option
                                                    {{ request()->filled('sort') && request()->input('sort') == 'low-to-high' ? 'selected' : '' }}
                                                    value="low-to-high">
                                                    {{ __('Price : Low to High') }}</option>
                                                <option
                                                    {{ request()->filled('sort') && request()->input('sort') == 'high-to-low' ? 'selected' : '' }}
                                                    value="high-to-low">{{ __('Price : High to Low') }}</option>
                                            </select>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row properties">
                        @forelse ($property_contents as $property_content)
                            <x-property :property="$property_content" :animate="false" class="col-lg-4 col-md-6" />
                        @empty
                            <div class="col-lg-12">
                                <h3 class="text-center mt-5">{{ __('NO PROPERTY FOUND') . '!' }}</h3>
                            </div>
                        @endforelse
                        <div class="row">
                            <div class="col-lg-12 pagination justify-content-center customPaginagte">
                                {{ $property_contents->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Listing End -->
@endsection

@section('script')
    @include('frontend.property.partials.metro-results-map')
    <script src="{{ asset('/assets/front/js/properties.js') }}"></script>
@endsection
