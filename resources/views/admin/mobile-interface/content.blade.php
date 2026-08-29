@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Home Page') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.mobile_interface') }}">{{ __('Mobile App Settings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Home Page') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10">
                            <div class="card-title">{{ __('Update Images & Texts') }}</div>
                        </div>

                        <div class="col-lg-2">
                            @includeIf('admin.partials.languages')
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form id="ajaxForm" action="{{ route('admin.mobile_interface_update') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" value="{{ request()->input('language') }}" name="language">
                        <div class="row px-5">

                            <!-- category section -->
                            <div class="col-lg-12">
                                <fieldset class="form-group border mb-5 border-secondary rounded">
                                    <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                        {{ __('Category Section') }}
                                    </legend>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="">{{ __('Category Section Title') }}</label>
                                                <input type="text" class="form-control" name="category_section_title"
                                                    value="{{ empty($data->category_section_title) ? '' : $data->category_section_title }}"
                                                    placeholder="{{ __('Enter category section title') }}">
                                                @error('category_section_title')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <!-- featured service section -->
                            <div class="col-lg-12">
                                <fieldset class="form-group border mb-5 border-secondary rounded">
                                    <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                        {{ __('Featured Property Section') }}
                                    </legend>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="">{{ __('Featured Property Section Title') }}</label>
                                                <input type="text" class="form-control"
                                                    name="featured_property_section_title"
                                                    value="{{ empty($data->featured_property_section_title) ? '' : $data->featured_property_section_title }}"
                                                    placeholder="{{ __('Enter Car service section title') }}">
                                                @error('featured_property_section_title')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <!-- latest service section -->
                            <div class="col-lg-12">
                                <fieldset class="form-group border mb-5 border-secondary rounded">
                                    <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                        {{ __('Latest Property Section') }}
                                    </legend>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="">{{ __('Latest Property Section Title') }}</label>
                                                <input type="text" class="form-control" name="latest_property_section_title"
                                                    value="{{ empty($data->latest_property_section_title) ? '' : $data->latest_property_section_title }}"
                                                    placeholder="{{ __('Enter latest Car section title') }}">
                                                @error('latest_property_section_title')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="button" id="submitBtn" class="btn btn-success">
                                {{ __('Update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
