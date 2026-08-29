@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Mobile App Settings') }}</h4>
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
                <a href="#">{{ __('Notification') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card-title">{{ __('Notification') }}</div>
                        </div>
                        <div class="col-lg-2">
                        </div>
                        <div class="col-lg-4">
                            <a href="#" data-toggle="modal" data-target="#createModal"
                                class="btn btn-primary btn-sm float-lg-right float-left"><i class="fas fa-plus"></i>
                                {{ __('Add') }}</a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">

                            @if ($notifications->count() == 0)
                                <h3 class="text-center mt-2">
                                    {{ __('NO NOTIFICATION FOUND!') }}
                                </h3>
                            @else
                                <div class="table-responsive mobile-app-table">
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr class="text-center">
                                                <th scope="col">#</th>
                                                <th scope="col">{{ __('Title') }}</th>
                                                <th scope="col">{{ __('Message') }}</th>
                                                <th scope="col">{{ __('Button Name') }}</th>
                                                <th scope="col">{{ __('Button URL') }}</th>
                                                <th scope="col">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($notifications as $key => $notification)
                                                <tr>
                                                    <td class="text-center">{{ $key + 1 }}</td>

                                                    <td>{{ $notification->title }}</td>

                                                    <td>
                                                        <p class="lc-2 message">{{ $notification->message }}</p>
                                                    </td>

                                                    <td class="text-center">
                                                        {{ $notification->button_name }}
                                                    </td>

                                                    <td>
                                                        <a class="link-url" href="{{ $notification->button_url }}" target="_blank">
                                                            {{ $notification->button_url }}
                                                        </a>
                                                    </td>

                                                    <td class="text-center">
                                                        <div class="buttons-group">
                                                            <a href="#" class="btn btn-secondary btn-sm editBtn"
                                                                data-id="{{ $notification->id }}"
                                                                data-title="{{ $notification->title }}"
                                                                data-message="{{ $notification->message }}"
                                                                data-button_name="{{ $notification->button_name }}"
                                                                data-button_url="{{ $notification->button_url }}"
                                                                data-toggle="modal" data-target="#editModal">
                                                                <i class="fas fa-edit"></i>
                                                            </a>

                                                            <form class="deleteForm d-inline-block"
                                                                action="{{ route('admin.mobile_interface.notification.delete', $notification->id) }}"
                                                                method="post">
                                                                @csrf
    
                                                                <button type="submit" class="btn btn-danger btn-sm deleteBtn">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                            <form class="sendForm d-inline-block"
                                                                action="{{ route('admin.mobile_interface.notification.send', $notification->id) }}"
                                                                method="post">
                                                                @csrf
    
                                                                <button type="submit" class="btn btn-success btn-sm sendBtn"
                                                                    title="{{ __('Send Notification') }}">
                                                                    <i class="fas fa-paper-plane"></i>
                                                                </button>
                                                            </form>
                                                        </div>

                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                <div class="card-footer"></div>
            </div>
        </div>
    </div>


    {{-- create modal --}}
    @include('admin.mobile-interface.notification.create')

    {{-- edit modal --}}
    @include('admin.mobile-interface.notification.edit')
@endsection
