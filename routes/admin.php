<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
*/


Route::prefix('/admin')->middleware('auth:admin')->group(function () {
  // admin redirect to dashboard route
  Route::get('/dashboard', 'Admin\AdminController@redirectToDashboard')->name('admin.dashboard');
  Route::get('/membership-request', 'Admin\AdminController@membershipRequest')->name('admin.membership-request');
  Route::post('/membership-request/update/{id}', 'Admin\AdminController@membershipRequestUpdate')->name('admin.payment-log.update');

  // change admin-panel theme (dark/light) route
  Route::get('/change-theme', 'Admin\AdminController@changeTheme')->name('admin.change_theme');

  // admin profile settings route start
  Route::get('/edit-profile', 'Admin\AdminController@editProfile')->name('admin.edit_profile');

  Route::post('/update-profile', 'Admin\AdminController@updateProfile')->name('admin.update_profile');

  Route::get('/change-password', 'Admin\AdminController@changePassword')->name('admin.change_password');

  Route::post('/update-password', 'Admin\AdminController@updatePassword')->name('admin.update_password');
  // admin profile settings route end

  // admin logout attempt route
  Route::get('/logout', 'Admin\AdminController@logout')->name('admin.logout');

  // menu-builder route start
  Route::prefix('/menu-builder')->middleware('permission:Menu Builder')->group(function () {
    Route::get('', 'Admin\MenuBuilderController@index')->name('admin.menu_builder');

    Route::post('/update-menus', 'Admin\MenuBuilderController@update')->name('admin.menu_builder.update_menus');
  });
  // menu-builder route end

  // Payment Log
  Route::get('/payment-log', 'Admin\PaymentLogController@index')->name('admin.payment-log.index');
  Route::post('/payment-log/update', 'Admin\PaymentLogController@update')->name('admin.payment-log.update');

  Route::prefix('package')->group(function () {
    // Package Settings routes
    Route::get('/settings', 'Admin\PackageController@settings')->name('admin.package.settings');
    Route::post('/settings', 'Admin\PackageController@updateSettings')->name('admin.package.settings');
    // Package routes
    Route::get('packages', 'Admin\PackageController@index')->name('admin.package.index');
    Route::post('package/upload', 'Admin\PackageController@upload')->name('admin.package.upload');
    Route::post('package/store', 'Admin\PackageController@store')->name('admin.package.store');
    Route::get('package/{id}/edit', 'Admin\PackageController@edit')->name('admin.package.edit');
    Route::post('package/update', 'Admin\PackageController@update')->name('admin.package.update');
    Route::post('package/featured', 'Admin\PackageController@featured')->name('admin.package.featured');
    Route::post('package/{id}/uploadUpdate', 'Admin\PackageController@uploadUpdate')->name('admin.package.uploadUpdate');
    Route::post('package/delete', 'Admin\PackageController@delete')->name('admin.package.delete');
    Route::post('package/bulk-delete', 'Admin\PackageController@bulkDelete')->name('admin.package.bulk.delete');
  });
  Route::prefix('featured-pricing')->group(function () {
    Route::get('/', 'Admin\FeaturedPricingController@index')->name('admin.featured_pricing.index');
    Route::post('/store', 'Admin\FeaturedPricingController@store')->name('admin.featured_pricing.store');
    Route::get('/{id}/edit', 'Admin\FeaturedPricingController@edit')->name('admin.featured_pricing.edit');
    Route::post('/update', 'Admin\FeaturedPricingController@update')->name('admin.featured_pricing.update');
    Route::post('/delete', 'Admin\FeaturedPricingController@destroy')->name('admin.featured_pricing.delete');
  });


  Route::get('featured-request', 'Admin\FeaturedPricingController@requestedForFeatured')->name('admin.requested_for_featured');
  Route::post('change-featured-status', 'Admin\FeaturedPricingController@changeFeaturedStatus')->name('admin.edit_featured_status');
  Route::post('change-featured-payment-status', 'Admin\FeaturedPricingController@changeFeaturedPaymentStatus')->name('admin.update_featured_payment_status');
  Route::post('delete-request', 'Admin\FeaturedPricingController@deleteFeturedRequest')->name('admin.delete_featured_request');



  //property spacification
  Route::prefix('property-specification')->group(function () {
    // property category route
    Route::get('/settings', 'Admin\Property\PropertyController@settings')->name('admin.property_specification.settings');
    Route::post('/update-settings', 'Admin\Property\PropertyController@update_settings')->name('admin.property_specification.update_settings');
    Route::get('/categories', 'Admin\Property\CategoryController@index')->name('admin.property_specification.categories');
    Route::post('/store-category', 'Admin\Property\CategoryController@store')->name('admin.property_specification.store_category');
    Route::post('/update-category', 'Admin\Property\CategoryController@update')->name('admin.property_specification.update_category');
    Route::post('/update-category-featured', 'Admin\Property\CategoryController@updateFeatured')->name('admin.property_specification.update_category_featured');

    Route::post('/delete-category', 'Admin\Property\CategoryController@destroy')->name('admin.property_specification.delete_category');
    Route::post('/bulk-delete-category', 'Admin\Property\CategoryController@bulkDestroy')->name('admin.property_specification.bulk_delete_category');

    // property Amenities route
    Route::get('/amenity', 'Admin\Property\AmenityController@index')->name('admin.property_specification.amenities');
    Route::post('/store-amenity', 'Admin\Property\AmenityController@store')->name('admin.property_specification.store_amenity');
    Route::post('/update-amenity', 'Admin\Property\AmenityController@update')->name('admin.property_specification.update_amenity');
    Route::post('/delete-amenity', 'Admin\Property\AmenityController@destroy')->name('admin.property_specification.delete_amenity');
    Route::post('/bulk-delete-amenity', 'Admin\Property\AmenityController@bulkDestroy')->name('admin.property_specification.bulk_delete_amenity');

    // property cities route
    Route::get('/cities', 'Admin\Property\CityController@index')->name('admin.property_specification.cities');
    Route::get('/get-cities', 'Admin\Property\CityController@getCities')->name('admin.property_specification.get_cities');
    Route::post('/store-city', 'Admin\Property\CityController@store')->name('admin.property_specification.store_city');
    Route::post('/update-city', 'Admin\Property\CityController@update')->name('admin.property_specification.update_city');
    Route::post('/update-featured', 'Admin\Property\CityController@updateFeatured')->name('admin.property_specification.update_featured');
    Route::post('/delete-city', 'Admin\Property\CityController@destroy')->name('admin.property_specification.delete_city');
    Route::post('/bulk-delete-city', 'Admin\Property\CityController@bulkDestroy')->name('admin.property_specification.bulk_delete_city');

    // property countries route
    Route::get('/country', 'Admin\Property\CountryController@index')->name('admin.property_specification.countries');
    Route::post('/store-country', 'Admin\Property\CountryController@store')->name('admin.property_specification.store_country');
    Route::post('/update-country', 'Admin\Property\CountryController@update')->name('admin.property_specification.update_country');

    Route::post('/delete-country', 'Admin\Property\CountryController@destroy')->name('admin.property_specification.delete_country');
    Route::post('/bulk-delete-country', 'Admin\Property\CountryController@bulkDestroy')->name('admin.property_specification.bulk_delete_country');

    // property countries route
    Route::get('/states', 'Admin\Property\StateController@index')->name('admin.property_specification.states');
    Route::get('/get-state', 'Admin\Property\StateController@getState')->name('admin.property_specification.get_state');
    Route::get('/get-states-cities', 'Admin\Property\StateController@getStateCities')->name('admin.property_specification.get_state_cities');
    Route::post('/store-state', 'Admin\Property\StateController@store')->name('admin.property_specification.store_state');
    Route::post('/update-state', 'Admin\Property\StateController@update')->name('admin.property_specification.update_state');


    Route::post('/delete-state', 'Admin\Property\StateController@destroy')->name('admin.property_specification.delete_state');
    Route::post('/bulk-delete-state', 'Admin\Property\StateController@bulkDestroy')->name('admin.property_specification.bulk_delete_state');
  });


  Route::prefix('property-management')->group(function () {
    Route::get('/settings', 'Admin\Property\PropertyController@propertSettings')->name('admin.property_management.settings');
    Route::get('/properties', 'Admin\Property\PropertyController@index')->name('admin.property_management.properties');
    Route::get('/type', 'Admin\Property\PropertyController@type')->name('admin.property_management.type');
    Route::get('/create', 'Admin\Property\PropertyController@create')->name('admin.property_management.create_property');
    Route::get('/get-agent', 'Admin\Property\PropertyController@getAgent')->name('admin.property_management.get_agent');
    Route::post('/store', 'Admin\Property\PropertyController@store')->name('admin.property_management.store_property')->middleware('AdminCheckVendorPackage:property,store');
    Route::post('/update_featured', 'Admin\Property\PropertyController@updateFeatured')->name('admin.property_management.update_featured');
    Route::post('update_status', 'Admin\Property\PropertyController@updateStatus')->name('admin.property_management.update_status');
    Route::post('approve-status', 'Admin\Property\PropertyController@approveStatus')->name('admin.property_management.approve_status');
    Route::get('edit-property/{id}', 'Admin\Property\PropertyController@edit')->name('admin.property_management.edit');
    Route::post('update/{id}', 'Admin\Property\PropertyController@update')->name('admin.property_management.update_property')->middleware('AdminCheckVendorPackage:property,update');
    Route::post('specification/delete', 'Admin\Property\PropertyController@specificationDelete')->name('admin.property_management.specification_delete');
    Route::post('/featured-payment', 'Admin\Property\PropertyController@featuredPayment')->name('admin.property_management.featured_payment');
    Route::post('update-mobile-image', 'Admin\Property\PropertyController@updateMobileImage')->name('admin.property_management.feature.update_mobile_image');

    //#========== Property slider image
    Route::post('/img-store', 'Admin\Property\PropertyController@imagesstore')->name('admin.property.imagesstore')->middleware('AdminCheckVendorPackage:property,store');
    Route::post('/img-update', 'Admin\Property\PropertyController@imagesstore')->name('admin.property.imagesupdate')->middleware('AdminCheckVendorPackage:property,update');
    Route::post('/img-remove', 'Admin\Property\PropertyController@imagermv')->name('admin.property.imagermv');
    Route::post('/img-db-remove', 'Admin\Property\PropertyController@imagedbrmv')->name('admin.property.imgdbrmv');

    //#==========property slider image end

    Route::post('delete', 'Admin\Property\PropertyController@delete')->name('admin.property_management.delete_property');
    Route::post('bulk-delete', 'Admin\Property\PropertyController@bulkDelete')->name('admin.property_management.bulk_delete_property');
  });

  // Project Management route start
  Route::prefix('project-management')->group(function () {

    Route::get('/settings', 'Admin\Project\ProjectController@settings')->name('admin.project_management.settings');
    Route::post('/update-settings', 'Admin\Project\ProjectController@updateSettings')->name('admin.project_management.update_settings');
    Route::get('/projects', 'Admin\Project\ProjectController@index')->name('admin.project_management.projects');
    Route::get('/create', 'Admin\Project\ProjectController@create')->name('admin.project_management.create_project');

    Route::post('/store', 'Admin\Project\ProjectController@store')->name('admin.project_management.store_project')->middleware('AdminCheckVendorPackage:project,store');
    Route::post('/update_featured', 'Admin\Project\ProjectController@updateFeatured')->name('admin.project_management.update_featured');
    Route::post('update_status', 'Admin\Project\ProjectController@updateStatus')->name('admin.project_management.update_status');
    Route::post('approve-status', 'Admin\Project\ProjectController@approveStatus')->name('admin.project_management.approve_status');
    Route::get('edit-project/{id}', 'Admin\Project\ProjectController@edit')->name('admin.project_management.edit');
    Route::post('update/{id}', 'Admin\Project\ProjectController@update')->name('admin.project_management.update_project')->middleware('AdminCheckVendorPackage:project,update');
    Route::post('specification/delete', 'Admin\Project\ProjectController@specificationDelete')->name('admin.project_management.specification_delete');

    Route::post('/delete', 'Admin\Project\ProjectController@destroy')->name('admin.project_management.delete_project');
    Route::post('/bulk-delete', 'Admin\Project\ProjectController@bulkDestroy')->name('admin.project_management.bulk_delete_project');

    //#========== project gallery image

    Route::post('/gallery-img-store', 'Admin\Project\ProjectController@galleryImagesStore')->name('admin.project.gallery_image_store')->middleware('AdminCheckVendorPackage:project,update');
    Route::post('/img-remove', 'Admin\Project\ProjectController@galleryImageRmv')->name('admin.project.gallery_imagermv');
    Route::post('/img-db-remove', 'Admin\Project\ProjectController@galleryImageDbrmv')->name('admin.project.gallery_imgdbrmv');
    //#========== project slider image end

    //#========== project gallery image
    Route::post('/floor-plan-img-store', 'Admin\Project\ProjectController@floorPlanImagesStore')->name('admin.project.floor_plan_image_store');
    Route::post('/floor-plan-img-remove', 'Admin\Project\ProjectController@floorPlanImageRmv')->name('admin.project.floor_plan_imagermv');
    Route::post('/floor-plan-img-db-remove', 'Admin\Project\ProjectController@floorPlanImageDbrmv')->name('admin.project.floor_plan_imgdbrmv');
    //#========== project slider image end

    // Project type routes
    Route::prefix('type')->group(function () {
      Route::get('/{id}', 'Admin\Project\TypeController@index')->name('admin.project_management.project_types');
      Route::post('/store', 'Admin\Project\TypeController@store')->name('admin.project_management.project_type.store')->middleware('AdminCheckVendorPackage:projectType,store');
      Route::post('/update', 'Admin\Project\TypeController@update')->name('admin.project_management.project_type.update')->middleware('AdminCheckVendorPackage:projectType,update');
      Route::post('/delete', 'Admin\Project\TypeController@delete')->name('admin.project_management.delete_type');
      Route::post('/bulk-delete', 'Admin\Project\TypeController@bulkDelete')->name('admin.project_management.bulk_delete_type');
    });
  });
  // Project Management Route End
  // property messages
  Route::get('/property-messages', 'Admin\Property\PropertyMessageController@index')->name('admin.property_message.index');
  Route::post('/message-delete', 'Admin\Property\PropertyMessageController@destroy')->name('admin.property_message.destroy');
  // agent Management
  Route::prefix('agent-management')->group(function () {
    Route::get('/', 'Admin\AgentController@index')->name('admin.agent_management.index');
    Route::post('/store', 'Admin\AgentController@store')->name('admin.agent_management.register');
    Route::post('/update', 'Admin\AgentController@update')->name('admin.agent_management.update_agent');
    Route::post('/update-status/{id}', 'Admin\AgentController@changeStatus')->name('admin.agent_management.change_status');
    Route::get('/secret-login/{id}', 'Admin\AgentController@secret_login')->name('admin.agent_management.secret_login');
    Route::post('/{id}/delete', 'Admin\AgentController@destroy')->name('admin.agent_management.destroy');
  });

  // user management route start
  Route::prefix('/user-management')->middleware('permission:User Management')->group(function () {
    // registered user route
    Route::get('/registered-users', 'Admin\User\UserController@index')->name('admin.user_management.registered_users');

    Route::get('/create', 'Admin\User\UserController@create')->name('admin.user_management.registered_user.create');
    Route::post('/store', 'Admin\User\UserController@store')->name('admin.user_management.registered_user.store');

    Route::prefix('/user/{id}')->group(function () {

      Route::get('/edit', 'Admin\User\UserController@edit')->name('admin.user_management.registered_user.edit');
      Route::post('/update', 'Admin\User\UserController@update')->name('admin.user_management.registered_user.update');

      Route::post('/update-account-status', 'Admin\User\UserController@updateAccountStatus')->name('admin.user_management.user.update_account_status');

      Route::post('/update-email-status', 'Admin\User\UserController@updateEmailStatus')->name('admin.user_management.user.update_email_status');

      Route::get('/change-password', 'Admin\User\UserController@changePassword')->name('admin.user_management.user.change_password');

      Route::post('/update-password', 'Admin\User\UserController@updatePassword')->name('admin.user_management.user.update_password');

      Route::post('/delete', 'Admin\User\UserController@destroy')->name('admin.user_management.user.delete');
      Route::get('/secret-login', 'Admin\User\UserController@secret_login')->name('admin.user_management.user.secret-login');
    });

    Route::post('/bulk-delete-user', 'Admin\User\UserController@bulkDestroy')->name('admin.user_management.bulk_delete_user');

    // subscriber route
    Route::get('/subscribers', 'Admin\User\SubscriberController@index')->name('admin.user_management.subscribers');

    Route::post('/subscriber/{id}/delete', 'Admin\User\SubscriberController@destroy')->name('admin.user_management.subscriber.delete');

    Route::post('/bulk-delete-subscriber', 'Admin\User\SubscriberController@bulkDestroy')->name('admin.user_management.bulk_delete_subscriber');

    Route::get('/mail-for-subscribers', 'Admin\User\SubscriberController@writeEmail')->name('admin.user_management.mail_for_subscribers');

    Route::post('/subscribers/send-email', 'Admin\User\SubscriberController@prepareEmail')->name('admin.user_management.subscribers.send_email');
  });
  // user management route end 

  // vendor management route start
  Route::prefix('/vendor-management')->middleware('permission:User Management')->group(function () {
    Route::get('/settings', 'Admin\VendorManagementController@settings')->name('admin.vendor_management.settings');
    Route::post('/settings/update', 'Admin\VendorManagementController@update_setting')->name('admin.vendor_management.setting.update');

    Route::get('/add-vendor', 'Admin\VendorManagementController@add')->name('admin.vendor_management.add_vendor');
    Route::post('/save-vendor', 'Admin\VendorManagementController@create')->name('admin.vendor_management.save-vendor');

    Route::get('/registered-vendors', 'Admin\VendorManagementController@index')->name('admin.vendor_management.registered_vendor');

    Route::prefix('/vendor/{id}')->group(function () {

      Route::post(
        '/update-account-status',
        'Admin\VendorManagementController@updateAccountStatus'
      )->name('admin.vendor_management.vendor.update_account_status');

      Route::post(
        '/update-email-status',
        'Admin\VendorManagementController@updateEmailStatus'
      )->name('admin.vendor_management.vendor.update_email_status');

      Route::get('/details', 'Admin\VendorManagementController@show')->name('admin.vendor_management.vendor_details');

      Route::get('/edit', 'Admin\VendorManagementController@edit')->name('admin.edit_management.vendor_edit');

      Route::post('/update', 'Admin\VendorManagementController@update')->name('admin.vendor_management.vendor.update_vendor');

      Route::post(
        '/update/vendor/balance',
        'Admin\VendorManagementController@update_vendor_balance'
      )->name('admin.vendor_management.update_vendor_balance');

      Route::get('/change-password', 'Admin\VendorManagementController@changePassword')->name('admin.vendor_management.vendor.change_password');

      Route::post('/update-password', 'Admin\VendorManagementController@updatePassword')->name('admin.vendor_management.vendor.update_password');

      Route::post('/delete', 'Admin\VendorManagementController@destroy')->name('admin.vendor_management.vendor.delete');
    });

    Route::post('/vendor/current-package/remove', 'Admin\VendorManagementController@removeCurrPackage')->name('vendor.currPackage.remove');

    Route::post('/vendor/current-package/change', 'Admin\VendorManagementController@changeCurrPackage')->name('vendor.currPackage.change');

    Route::post('/vendor/current-package/add', 'Admin\VendorManagementController@addCurrPackage')->name('vendor.currPackage.add');

    Route::post('/vendor/next-package/remove', 'Admin\VendorManagementController@removeNextPackage')->name('vendor.nextPackage.remove');

    Route::post('/vendor/next-package/change', 'Admin\VendorManagementController@changeNextPackage')->name('vendor.nextPackage.change');

    Route::post('/vendor/next-package/add', 'Admin\VendorManagementController@addNextPackage')->name('vendor.nextPackage.add');

    Route::post('/bulk-delete-vendor', 'Admin\VendorManagementController@bulkDestroy')->name('admin.vendor_management.bulk_delete_vendor');

    Route::get('/secret-login/{id}', 'Admin\VendorManagementController@secret_login')->name('admin.vendor_management.vendor.secret_login');
  });
  // vendor management route start

  // home-page route start
  Route::prefix('/home-page')->middleware('permission:Home Page')->group(function () {
    // hero section
    Route::prefix('/hero-section')->group(function () {
      // slider version route
      Route::prefix('/slider-version')->group(function () {
        Route::get('', 'Admin\HomePage\Hero\SliderController@index')->name('admin.home_page.hero_section.slider_version');

        Route::post('/store', 'Admin\HomePage\Hero\SliderController@store')->name('admin.home_page.hero_section.slider_version.store');

        Route::post('/update', 'Admin\HomePage\Hero\SliderController@update')->name('admin.home_page.hero_section.slider_version.update');

        Route::post('/{id}/delete', 'Admin\HomePage\Hero\SliderController@destroy')->name('admin.home_page.hero_section.slider_version.delete');

        Route::post('update-video-url', 'Admin\HomePage\Hero\SliderController@update_video_url')->name('admin.home_page.hero_section.update.video-url');
      });

      // static version route
      Route::prefix('/static-version')->group(function () {
        Route::get('', 'Admin\HomePage\Hero\StaticController@index')->name('admin.home_page.hero_section.static_version');

        Route::post('/update-image', 'Admin\HomePage\Hero\StaticController@updateImage')->name('admin.home_page.hero_section.static_version.update_image');

        Route::post(
          '/update-information',
          'Admin\HomePage\Hero\StaticController@updateInformation'
        )->name('admin.home_page.hero_section.static_version.update_information');
      });
    });

    // category section
    Route::get('/category-section', 'Admin\HomePage\CategorySectionController@index')->name('admin.home_page.category_section');


    Route::post('/update-category-section', 'Admin\HomePage\CategorySectionController@update')->name('admin.home_page.update_category_section');


    // work process section
    Route::get('/work-process-section', 'Admin\HomePage\WorkProcessController@sectionInfo')->name('admin.home_page.work_process_section');

    Route::post('/update-work-process-section', 'Admin\HomePage\WorkProcessController@updateSectionInfo')->name('admin.home_page.update_work_process_section');

    Route::prefix('/work-process')->group(function () {
      Route::post('/store', 'Admin\HomePage\WorkProcessController@storeWorkProcess')->name('admin.home_page.store_work_process');

      Route::post('/update', 'Admin\HomePage\WorkProcessController@updateWorkProcess')->name('admin.home_page.update_work_process');

      Route::post('{id}/delete', 'Admin\HomePage\WorkProcessController@destroyWorkProcess')->name('admin.home_page.delete_work_process');

      Route::post('/bulk-delete', 'Admin\HomePage\WorkProcessController@bulkDestroyWorkProcess')->name('admin.home_page.bulk_delete_work_process');
    });

    // features property section
    Route::get('/feature-section', 'Admin\HomePage\FeatureController@sectionInfo')->name('admin.home_page.feature_section');

    Route::post('/update-feature-section', 'Admin\HomePage\FeatureController@updateSectionInfo')->name('admin.home_page.update_feature_section');

    // proeprty section
    Route::get('/property-section', 'Admin\HomePage\PropertySectionController@sectionInfo')->name('admin.home_page.property_section');

    Route::post('/update-property-section', 'Admin\HomePage\PropertySectionController@updateSectionInfo')->name('admin.home_page.update_property_section');

    // city section
    Route::get('/city-section', 'Admin\HomePage\CitySectionController@sectionInfo')->name('admin.home_page.city_section');
    Route::post('/update-city-section', 'Admin\HomePage\CitySectionController@updateSectionInfo')->name('admin.home_page.update_city_section');

    // Vendor section
    Route::get('/vendor-section', 'Admin\HomePage\VendorSectionController@sectionInfo')->name('admin.home_page.vendor_section');
    Route::post('/update-vendor-section', 'Admin\HomePage\VendorSectionController@updateSectionInfo')->name('admin.home_page.update_vendor_section');

    // Project section
    Route::get('/project-section', 'Admin\HomePage\ProjectSectionController@sectionInfo')->name('admin.home_page.project_section');
    Route::post('/update-project-section', 'Admin\HomePage\ProjectSectionController@updateSectionInfo')->name('admin.home_page.update_project_section');

    // Pricing section
    Route::get('/pricing-section', 'Admin\HomePage\PricingSectionController@sectionInfo')->name('admin.home_page.pricing_section');
    Route::post('/update-pricing-section', 'Admin\HomePage\PricingSectionController@updateSectionInfo')->name('admin.home_page.update_pricing_section');

    // Project section
    Route::get('/project-section', 'Admin\HomePage\ProjectSectionController@sectionInfo')->name('admin.home_page.project_section');
    Route::post('/update-project-section', 'Admin\HomePage\ProjectSectionController@updateSectionInfo')->name('admin.home_page.update_project_section');

    Route::prefix('/feature')->group(function () {
      Route::post('/store', 'Admin\HomePage\FeatureController@storeFeature')->name('admin.home_page.store_feature');

      Route::post('/update', 'Admin\HomePage\FeatureController@updateFeature')->name('admin.home_page.update_feature');

      Route::post('{id}/delete', 'Admin\HomePage\FeatureController@destroyFeature')->name('admin.home_page.delete_feature');

      Route::post('/bulk-delete', 'Admin\HomePage\FeatureController@bulkDestroyFeature')->name('admin.home_page.bulk_delete_feature');
    });

    // counter section
    Route::get('/counter-section', 'Admin\HomePage\CounterController@index')->name('admin.home_page.counter_section');

    Route::post('/update-counter-section-image', 'Admin\HomePage\CounterController@updateImage')->name('admin.home_page.update_counter_section_image');

    Route::post('/update-counter-section-info', 'Admin\HomePage\CounterController@updateInfo')->name('admin.home_page.update_counter_section_info');

    Route::prefix('/counter')->group(function () {
      Route::post('/store', 'Admin\HomePage\CounterController@storeCounter')->name('admin.home_page.store_counter');

      Route::post('/update', 'Admin\HomePage\CounterController@updateCounter')->name('admin.home_page.update_counter');

      Route::post('{id}/delete', 'Admin\HomePage\CounterController@destroyCounter')->name('admin.home_page.delete_counter');

      Route::post('/bulk-delete', 'Admin\HomePage\CounterController@bulkDestroyCounter')->name('admin.home_page.bulk_delete_counter');
    });

    // testimonial section
    Route::get('/testimonial-section', 'Admin\HomePage\TestimonialController@index')->name('admin.home_page.testimonial_section');

    Route::post('/update-testimonial-section', 'Admin\HomePage\TestimonialController@updateSectionInfo')->name('admin.home_page.update_testimonial_section');

    Route::post('/update-testimonial-section-img', 'Admin\HomePage\TestimonialController@updateSectionBackground')->name('admin.home_page.update_testimonial_section_background');

    Route::prefix('/testimonial')->group(function () {
      Route::post('/store', 'Admin\HomePage\TestimonialController@storeTestimonial')->name('admin.home_page.store_testimonial');

      Route::post('/update', 'Admin\HomePage\TestimonialController@updateTestimonial')->name('admin.home_page.update_testimonial');

      Route::post('{id}/delete', 'Admin\HomePage\TestimonialController@destroyTestimonial')->name('admin.home_page.delete_testimonial');

      Route::post('/bulk-delete', 'Admin\HomePage\TestimonialController@bulkDestroyTestimonial')->name('admin.home_page.bulk_delete_testimonial');
    });

    // subscribe section
    Route::get('/subscribe-section', 'Admin\HomePage\SubscribeController@index')->name('admin.home_page.subscribe_section');
    Route::post('/update-subscribe-section', 'Admin\HomePage\SubscribeController@updateSectionInfo')->name('admin.home_page.update_subscribe_section');
    Route::post('/update-subscribe-section-img', 'Admin\HomePage\SubscribeController@updateSectionBackground')->name('admin.home_page.update_subscribe_section_background');

    // call to action section
    Route::get('/call-to-action-section', 'Admin\HomePage\CallToActionController@index')->name('admin.home_page.call_to_action_section');

    Route::post('/update-call-to-action-section-image', 'Admin\HomePage\CallToActionController@updateImage')->name('admin.home_page.update_call_to_action_section_image');

    Route::post('/update-call-to-action-section', 'Admin\HomePage\CallToActionController@update')->name('admin.home_page.update_call_to_action_section');

    // blog section
    Route::get('/blog-section', 'Admin\HomePage\BlogController@index')->name('admin.home_page.blog_section');

    Route::post('/update-blog-section', 'Admin\HomePage\BlogController@update')->name('admin.home_page.update_blog_section');

    // section customization
    Route::get('/section-customization', 'Admin\HomePage\SectionController@index')->name('admin.home_page.section_customization');

    Route::post(
      '/update-section-status',
      'Admin\HomePage\SectionController@update'
    )->name('admin.home_page.update_section_status');



    // about section
    Route::prefix('/about-section')->group(function () {
      Route::get('', 'Admin\HomePage\AboutController@index')->name('admin.home_page.about_section');

      Route::post('/update-image', 'Admin\HomePage\AboutController@updateImage')->name('admin.home_page.update_about_img');

      Route::post('/update-info', 'Admin\HomePage\AboutController@updateInfo')->name('admin.home_page.update_about_info');
    });

    // about section
    Route::prefix('/why-choose-us-section')->group(function () {
      Route::get('', 'Admin\HomePage\WhyChooseUsController@index')->name('admin.home_page.why_choose_us_section');

      Route::post('/update-image', 'Admin\HomePage\WhyChooseUsController@updateImage')->name('admin.home_page.update_why_choose_us_img');

      Route::post('/update-info', 'Admin\HomePage\WhyChooseUsController@updateInfo')->name('admin.home_page.update_why_choose_us_info');
    });

    // brand section
    Route::prefix('/brand-section')->group(function () {
      Route::get('', 'Admin\HomePage\BrandController@index')->name('admin.home_page.brand_section');

      Route::post('/store', 'Admin\HomePage\BrandController@store')->name('admin.home_page.brand_section.store');

      Route::post('/update', 'Admin\HomePage\BrandController@update')->name('admin.home_page.brand_section.update');

      Route::post('/{id}/delete', 'Admin\HomePage\BrandController@destroy')->name('admin.home_page.brand_section.delete');
    });
  });

  // home-page route end


  #====support tickets ============

  Route::prefix('support-ticket')->group(function () {
    Route::get('/setting', 'Admin\SupportTicketController@setting')->name('admin.support_ticket.setting');
    Route::post('/setting/update', 'Admin\SupportTicketController@update_setting')->name('admin.support_ticket.update_setting');
    Route::get('/tickets', 'Admin\SupportTicketController@index')->name('admin.support_tickets');
    Route::get('/message/{id}', 'Admin\SupportTicketController@message')->name('admin.support_tickets.message');
    Route::post('/zip-upload', 'Admin\SupportTicketController@zip_file_upload')->name('admin.support_ticket.zip_file.upload');
    Route::post('/reply/{id}', 'Admin\SupportTicketController@ticketreply')->name('admin.support_ticket.reply');
    Route::post('/closed/{id}', 'Admin\SupportTicketController@ticket_closed')->name('admin.support_ticket.close');
    Route::post('/assign-stuff/{id}', 'Admin\SupportTicketController@assign_stuff')->name('assign_stuff.supoort.ticket');

    Route::get('/unassign-stuff/{id}', 'Admin\SupportTicketController@unassign_stuff')->name('admin.support_tickets.unassign');

    Route::post('/delete/{id}', 'Admin\SupportTicketController@delete')->name('admin.support_tickets.delete');
    Route::post('/bulk-delete', 'Admin\SupportTicketController@bulk_delete')->name('admin.support_tickets.bulk_delete');
  });


  // footer route start
  Route::prefix('/footer')->middleware('permission:Footer')->group(function () {
    // logo & image route
    Route::get('/logo-and-image', 'Admin\Footer\ImageController@index')->name('admin.footer.logo_and_image');

    Route::post('/update-logo', 'Admin\Footer\ImageController@updateLogo')->name('admin.footer.update_logo');

    Route::post(
      '/update-background-image',
      'Admin\Footer\ImageController@updateImage'
    )->name('admin.footer.update_background_image');

    // content route
    Route::get('/content', 'Admin\Footer\ContentController@index')->name('admin.footer.content');

    Route::post('/update-content', 'Admin\Footer\ContentController@update')->name('admin.footer.update_content');

    // quick link route
    Route::get('/quick-links', 'Admin\Footer\QuickLinkController@index')->name('admin.footer.quick_links');

    Route::post('/store-quick-link', 'Admin\Footer\QuickLinkController@store')->name('admin.footer.store_quick_link');

    Route::post('/update-quick-link', 'Admin\Footer\QuickLinkController@update')->name('admin.footer.update_quick_link');

    Route::post(
      '/delete-quick-link/{id}',
      'Admin\Footer\QuickLinkController@destroy'
    )->name('admin.footer.delete_quick_link');
  });
  // footer route end


  // custom-pages route start
  Route::prefix('/custom-pages')->middleware('permission:Custom Pages')->group(function () {
    Route::get('', 'Admin\CustomPageController@index')->name('admin.custom_pages');

    Route::get('/create-page', 'Admin\CustomPageController@create')->name('admin.custom_pages.create_page');

    Route::post('/store-page', 'Admin\CustomPageController@store')->name('admin.custom_pages.store_page');

    Route::get('/edit-page/{id}', 'Admin\CustomPageController@edit')->name('admin.custom_pages.edit_page');

    Route::post('/update-page/{id}', 'Admin\CustomPageController@update')->name('admin.custom_pages.update_page');

    Route::post('/delete-page/{id}', 'Admin\CustomPageController@destroy')->name('admin.custom_pages.delete_page');

    Route::post('/bulk-delete-page', 'Admin\CustomPageController@bulkDestroy')->name('admin.custom_pages.bulk_delete_page');
  });
  // custom-pages route end

  // blog route start
  Route::prefix('/blog-management')->middleware('permission:Blog Management')->group(function () {
    // blog category route
    Route::get('/categories', 'Admin\Journal\CategoryController@index')->name('admin.blog_management.categories');

    Route::post('/store-category', 'Admin\Journal\CategoryController@store')->name('admin.blog_management.store_category');

    Route::post('/update-category', 'Admin\Journal\CategoryController@update')->name('admin.blog_management.update_category');

    Route::post(
      '/delete-category/{id}',
      'Admin\Journal\CategoryController@destroy'
    )->name('admin.blog_management.delete_category');

    Route::post(
      '/bulk-delete-category',
      'Admin\Journal\CategoryController@bulkDestroy'
    )->name('admin.blog_management.bulk_delete_category');

    // blog route
    Route::get(
      '/blogs',
      'Admin\Journal\BlogController@index'
    )->name('admin.blog_management.blogs');

    Route::get('/create-blog', 'Admin\Journal\BlogController@create')->name('admin.blog_management.create_blog');

    Route::post('/store-blog', 'Admin\Journal\BlogController@store')->name('admin.blog_management.store_blog');

    Route::get('/edit-blog/{id}', 'Admin\Journal\BlogController@edit')->name('admin.blog_management.edit_blog');

    Route::post('/update-blog/{id}', 'Admin\Journal\BlogController@update')->name('admin.blog_management.update_blog');

    Route::post('/delete-blog/{id}', 'Admin\Journal\BlogController@destroy')->name('admin.blog_management.delete_blog');

    Route::post('/bulk-delete-blog', 'Admin\Journal\BlogController@bulkDestroy')->name('admin.blog_management.bulk_delete_blog');
  });
  // blog route end

  // faq route start
  Route::prefix('/faq-management')->middleware('permission:FAQ Management')->group(function () {
    Route::get('', 'Admin\FaqController@index')->name('admin.faq_management');

    Route::post('/store-faq', 'Admin\FaqController@store')->name('admin.faq_management.store_faq');

    Route::post('/update-faq', 'Admin\FaqController@update')->name('admin.faq_management.update_faq');

    Route::post('/delete-faq/{id}', 'Admin\FaqController@destroy')->name('admin.faq_management.delete_faq');

    Route::post('/bulk-delete-faq', 'Admin\FaqController@bulkDestroy')->name('admin.faq_management.bulk_delete_faq');
  });
  // faq route end

  // advertise route start
  Route::prefix('/advertise')->middleware('permission:Advertise')->group(function () {
    Route::get('/settings', 'Admin\AdvertisementController@advertiseSettings')->name('admin.advertise.settings');

    Route::post('/update-settings', 'Admin\AdvertisementController@updateAdvertiseSettings')->name('admin.advertise.update_settings');

    Route::get('/all-advertisement', 'Admin\AdvertisementController@index')->name('admin.advertise.all_advertisement');

    Route::post('/store-advertisement', 'Admin\AdvertisementController@store')->name('admin.advertise.store_advertisement');

    Route::post(
      '/update-advertisement',
      'Admin\AdvertisementController@update'
    )->name('admin.advertise.update_advertisement');

    Route::post('/delete-advertisement/{id}', 'Admin\AdvertisementController@destroy')->name('admin.advertise.delete_advertisement');

    Route::post('/bulk-delete-advertisement', 'Admin\AdvertisementController@bulkDestroy')->name('admin.advertise.bulk_delete_advertisement');
  });
  // advertise route end

  // announcement-popup route start
  Route::prefix('/announcement-popups')->middleware('permission:Announcement Popups')->group(function () {
    Route::get('', 'Admin\PopupController@index')->name('admin.announcement_popups');

    Route::get('/select-popup-type', 'Admin\PopupController@popupType')->name('admin.announcement_popups.select_popup_type');

    Route::get('/create-popup/{type}', 'Admin\PopupController@create')->name('admin.announcement_popups.create_popup');

    Route::post('/store-popup', 'Admin\PopupController@store')->name('admin.announcement_popups.store_popup');

    Route::post('/popup/{id}/update-status', 'Admin\PopupController@updateStatus')->name('admin.announcement_popups.update_popup_status');

    Route::get('/edit-popup/{id}', 'Admin\PopupController@edit')->name('admin.announcement_popups.edit_popup');

    Route::post('/update-popup/{id}', 'Admin\PopupController@update')->name('admin.announcement_popups.update_popup');

    Route::post('/delete-popup/{id}', 'Admin\PopupController@destroy')->name('admin.announcement_popups.delete_popup');

    Route::post('/bulk-delete-popup', 'Admin\PopupController@bulkDestroy')->name('admin.announcement_popups.bulk_delete_popup');
  });
  // announcement-popup route end

  // payment-gateway route start
  Route::prefix('/payment-gateways')->middleware('permission:Payment Gateways')->group(function () {
    Route::get('/online-gateways', 'Admin\PaymentGateway\OnlineGatewayController@index')->name('admin.payment_gateways.online_gateways');
    Route::post('/update-paypal-info', 'Admin\PaymentGateway\OnlineGatewayController@updatePayPalInfo')->name('admin.payment_gateways.update_paypal_info');
    Route::post('/update-instamojo-info', 'Admin\PaymentGateway\OnlineGatewayController@updateInstamojoInfo')->name('admin.payment_gateways.update_instamojo_info');
    Route::post('/update-paystack-info', 'Admin\PaymentGateway\OnlineGatewayController@updatePaystackInfo')->name('admin.payment_gateways.update_paystack_info');
    Route::post('/update-flutterwave-info', 'Admin\PaymentGateway\OnlineGatewayController@updateFlutterwaveInfo')->name('admin.payment_gateways.update_flutterwave_info');
    Route::post('/update-razorpay-info', 'Admin\PaymentGateway\OnlineGatewayController@updateRazorpayInfo')->name('admin.payment_gateways.update_razorpay_info');
    Route::post('/update-mercadopago-info', 'Admin\PaymentGateway\OnlineGatewayController@updateMercadoPagoInfo')->name('admin.payment_gateways.update_mercadopago_info');
    Route::post('/update-mollie-info', 'Admin\PaymentGateway\OnlineGatewayController@updateMollieInfo')->name('admin.payment_gateways.update_mollie_info');
    Route::post('/update-stripe-info', 'Admin\PaymentGateway\OnlineGatewayController@updateStripeInfo')->name('admin.payment_gateways.update_stripe_info');
    Route::post('/update-paytm-info', 'Admin\PaymentGateway\OnlineGatewayController@updatePaytmInfo')->name('admin.payment_gateways.update_paytm_info');
    Route::post('/update-anet-info', 'Admin\PaymentGateway\OnlineGatewayController@updateAnetInfo')->name('admin.payment_gateways.update_anet_info');
    // for shohag-2.0
    Route::post('/midtrans', 'Admin\PaymentGateway\OnlineGatewayController@updateMidtransInfo')->name('admin.payment_gateways.update_midtrans_info');
    Route::post('/iyzico', 'Admin\PaymentGateway\OnlineGatewayController@updateIyzicoInfo')->name('admin.payment_gateways.update_iyzico_info');
    Route::post('/paytabs', 'Admin\PaymentGateway\OnlineGatewayController@updatePaytabsInfo')->name('admin.payment_gateways.update_paytabs_info');
    Route::post('/toyyibpay', 'Admin\PaymentGateway\OnlineGatewayController@updateToyyibpayInfo')->name('admin.payment_gateways.update_toyyibpay_info');
    Route::post('/phonepe', 'Admin\PaymentGateway\OnlineGatewayController@updatePhonepeInfo')->name('admin.payment_gateways.update_phonepe_info');
    Route::post('/yoco', 'Admin\PaymentGateway\OnlineGatewayController@updateYocoInfo')->name('admin.payment_gateways.update_yoco_info');
    Route::post('/myfatoorah', 'Admin\PaymentGateway\OnlineGatewayController@updateMyFatoorahInfo')->name('admin.payment_gateways.update_myfatoorah_info');
    Route::post('/xendit', 'Admin\PaymentGateway\OnlineGatewayController@updateXenditInfo')->name('admin.payment_gateways.update_xendit_info');
    Route::post('/perfect-money', 'Admin\PaymentGateway\OnlineGatewayController@updatePerfectMoneyInfo')->name('admin.payment_gateways.update_perfect_money_info');
    Route::post('/nowpayment', 'Admin\PaymentGateway\OnlineGatewayController@updatNowPayment')->name('admin.payment_gateways.update_nowpayment_info');

    Route::get('/offline-gateways', 'Admin\PaymentGateway\OfflineGatewayController@index')->name('admin.payment_gateways.offline_gateways');
    Route::post('/store-offline-gateway', 'Admin\PaymentGateway\OfflineGatewayController@store')->name('admin.payment_gateways.store_offline_gateway');
    Route::post('/update-status/{id}', 'Admin\PaymentGateway\OfflineGatewayController@updateStatus')->name('admin.payment_gateways.update_status');
    Route::post('/update-offline-gateway', 'Admin\PaymentGateway\OfflineGatewayController@update')->name('admin.payment_gateways.update_offline_gateway');
    Route::post('/delete-offline-gateway/{id}', 'Admin\PaymentGateway\OfflineGatewayController@destroy')->name('admin.payment_gateways.delete_offline_gateway');
  });
  // payment-gateway route end

  Route::prefix('/basic-settings')->middleware('permission:Basic Settings')->group(function () {
    // basic settings favicon route

    Route::get('/favicon', 'Admin\BasicSettings\BasicController@favicon')->name('admin.basic_settings.favicon');

    Route::post('/update-favicon', 'Admin\BasicSettings\BasicController@updateFavicon')->name('admin.basic_settings.update_favicon');

    // basic settings logo route
    Route::get('/logo', 'Admin\BasicSettings\BasicController@logo')->name('admin.basic_settings.logo');

    Route::post('/update-logo', 'Admin\BasicSettings\BasicController@updateLogo')->name('admin.basic_settings.update_logo');

    // basic settings information route
    Route::get('/information', 'Admin\BasicSettings\BasicController@information')->name('admin.basic_settings.information');

    Route::post('/update-info', 'Admin\BasicSettings\BasicController@updateInfo')->name('admin.basic_settings.update_info');

    Route::get('/general-settings', 'Admin\BasicSettings\BasicController@general_settings')->name('admin.basic_settings.general_settings');

    Route::post('/update-general-settings', 'Admin\BasicSettings\BasicController@update_general_setting')->name('admin.basic_settings.general_settings.update');

    Route::get('/contact-page', 'Admin\BasicSettings\BasicController@contact_page')->name('admin.basic_settings.contact_page');

    Route::post('/update-contact-page', 'Admin\BasicSettings\BasicController@update_contact_page')->name('admin.basic_settings.contact_page.update');

    // basic settings (theme & home) route
    Route::get('/theme-and-home', 'Admin\BasicSettings\BasicController@themeAndHome')->name('admin.basic_settings.theme_and_home');

    Route::post(
      '/update-theme-and-home',
      'Admin\BasicSettings\BasicController@updateThemeAndHome'
    )->name('admin.basic_settings.update_theme_and_home');

    // basic settings currency route
    Route::get('/currency', 'Admin\BasicSettings\BasicController@currency')->name('admin.basic_settings.currency');

    Route::post('/update-currency', 'Admin\BasicSettings\BasicController@updateCurrency')->name('admin.basic_settings.update_currency');

    // basic settings appearance route
    Route::get('/appearance', 'Admin\BasicSettings\BasicController@appearance')->name('admin.basic_settings.appearance');

    Route::post('/update-appearance', 'Admin\BasicSettings\BasicController@updateAppearance')->name('admin.basic_settings.update_appearance');

    // basic settings mail route start
    Route::get('/mail-from-admin', 'Admin\BasicSettings\BasicController@mailFromAdmin')->name('admin.basic_settings.mail_from_admin');

    Route::post(
      '/update-mail-from-admin',
      'Admin\BasicSettings\BasicController@updateMailFromAdmin'
    )->name('admin.basic_settings.update_mail_from_admin');

    Route::get('/mail-to-admin', 'Admin\BasicSettings\BasicController@mailToAdmin')->name('admin.basic_settings.mail_to_admin');

    Route::post(
      '/update-mail-to-admin',
      'Admin\BasicSettings\BasicController@updateMailToAdmin'
    )->name('admin.basic_settings.update_mail_to_admin');

    Route::get('/mail-templates', 'Admin\BasicSettings\MailTemplateController@index')->name('admin.basic_settings.mail_templates');

    Route::get('/edit-mail-template/{id}', 'Admin\BasicSettings\MailTemplateController@edit')->name('admin.basic_settings.edit_mail_template');

    Route::post('/update-mail-template/{id}', 'Admin\BasicSettings\MailTemplateController@update')->name('admin.basic_settings.update_mail_template');
    // basic settings mail route end

    // basic settings breadcrumb route
    Route::get('/breadcrumb', 'Admin\BasicSettings\BasicController@breadcrumb')->name('admin.basic_settings.breadcrumb');

    Route::post('/update-breadcrumb', 'Admin\BasicSettings\BasicController@updateBreadcrumb')->name('admin.basic_settings.update_breadcrumb');

    // basic settings page-headings route
    Route::get('/page-headings', 'Admin\BasicSettings\PageHeadingController@pageHeadings')->name('admin.basic_settings.page_headings');

    Route::post(
      '/update-page-headings',
      'Admin\BasicSettings\PageHeadingController@updatePageHeadings'
    )->name('admin.basic_settings.update_page_headings');

    // basic settings plugins route start
    Route::get('/plugins', 'Admin\BasicSettings\BasicController@plugins')->name('admin.basic_settings.plugins');

    Route::post('/update-disqus', 'Admin\BasicSettings\BasicController@updateDisqus')->name('admin.basic_settings.update_disqus');

    Route::post('/update-tawkto', 'Admin\BasicSettings\BasicController@updateTawkTo')->name('admin.basic_settings.update_tawkto');

    Route::post('/update-recaptcha', 'Admin\BasicSettings\BasicController@updateRecaptcha')->name('admin.basic_settings.update_recaptcha');

    Route::post('/update-facebook', 'Admin\BasicSettings\BasicController@updateFacebook')->name('admin.basic_settings.update_facebook');

    Route::post('/update-google', 'Admin\BasicSettings\BasicController@updateGoogle')->name('admin.basic_settings.update_google');

    Route::post('/update-whatsapp', 'Admin\BasicSettings\BasicController@updateWhatsApp')->name('admin.basic_settings.update_whatsapp');
    // basic settings plugins route end

    // basic settings seo route
    Route::get('/seo', 'Admin\BasicSettings\SEOController@index')->name('admin.basic_settings.seo');

    Route::post('/update-seo', 'Admin\BasicSettings\SEOController@update')->name('admin.basic_settings.update_seo');

    // basic settings maintenance-mode route
    Route::get('/maintenance-mode', 'Admin\BasicSettings\BasicController@maintenance')->name('admin.basic_settings.maintenance_mode');

    Route::post('/update-maintenance-mode', 'Admin\BasicSettings\BasicController@updateMaintenance')->name('admin.basic_settings.update_maintenance_mode');

    // basic settings cookie-alert route
    Route::get('/cookie-alert', 'Admin\BasicSettings\CookieAlertController@cookieAlert')->name('admin.basic_settings.cookie_alert');

    Route::post('/update-cookie-alert', 'Admin\BasicSettings\CookieAlertController@updateCookieAlert')->name('admin.basic_settings.update_cookie_alert');

    // basic-settings social-media route
    Route::get('/social-medias', 'Admin\BasicSettings\SocialMediaController@index')->name('admin.basic_settings.social_medias');

    Route::post('/store-social-media', 'Admin\BasicSettings\SocialMediaController@store')->name('admin.basic_settings.store_social_media');

    Route::post('/update-social-media', 'Admin\BasicSettings\SocialMediaController@update')->name('admin.basic_settings.update_social_media');

    Route::post('/delete-social-media/{id}', 'Admin\BasicSettings\SocialMediaController@destroy')->name('admin.basic_settings.delete_social_media');
  });

  // Mobile App Settings route
  Route::prefix('mobile-app-settings')->middleware('permission:Mobile App Settings')->group(function () {
    Route::get('/', 'Admin\MobileInterfaceController@index')->name('admin.mobile_interface');

    Route::get('/home-page-content', 'Admin\MobileInterfaceController@content')->name('admin.mobile_interface_content');
    Route::post('home-page-content/update', 'Admin\MobileInterfaceController@update')->name('admin.mobile_interface_update');
    Route::get('/general-setting', 'Admin\MobileInterfaceController@setting')->name('admin.mobile_interface_general_setting');
    Route::post('/general-setting/update', 'Admin\MobileInterfaceController@settingUpdate')->name('admin.mobile_interface_general_setting.update');

    Route::get('/plugins', 'Admin\MobileInterfaceController@plugins')->name('admin.mobile_interface.plugins');
    Route::post('/update-firebase', 'Admin\MobileInterfaceController@updateFirebase')->name('admin.mobile_interface.updateFirebase');
    Route::prefix('notifications')->group(function () {
      Route::get('/', 'Admin\MobileInterfaceController@notification')->name('admin.mobile_interface.notification');
      Route::post('/store', 'Admin\MobileInterfaceController@notificationStore')->name('admin.mobile_interface.notification.store');
      Route::post('/update', 'Admin\MobileInterfaceController@notificationUpdate')->name('admin.mobile_interface.notification.update');
      Route::post('/delete/{id}', 'Admin\MobileInterfaceController@destroyNotification')->name('admin.mobile_interface.notification.delete');
      Route::post('/send/{id}', 'Admin\MobileInterfaceController@sendNotification')->name('admin.mobile_interface.notification.send');
    });
  });



  // admin management route start
  Route::prefix('/admin-management')->middleware('permission:Admin Management')->group(function () {
    // role-permission route
    Route::get('/role-permissions', 'Admin\Administrator\RolePermissionController@index')->name('admin.admin_management.role_permissions');

    Route::post('/store-role', 'Admin\Administrator\RolePermissionController@store')->name('admin.admin_management.store_role');

    Route::get('/role/{id}/permissions', 'Admin\Administrator\RolePermissionController@permissions')->name('admin.admin_management.role.permissions');

    Route::post('/role/{id}/update-permissions', 'Admin\Administrator\RolePermissionController@updatePermissions')->name('admin.admin_management.role.update_permissions');

    Route::post('/update-role', 'Admin\Administrator\RolePermissionController@update')->name('admin.admin_management.update_role');

    Route::post('/delete-role/{id}', 'Admin\Administrator\RolePermissionController@destroy')->name('admin.admin_management.delete_role');

    // registered admin route
    Route::get('/registered-admins', 'Admin\Administrator\SiteAdminController@index')->name('admin.admin_management.registered_admins');

    Route::post('/store-admin', 'Admin\Administrator\SiteAdminController@store')->name('admin.admin_management.store_admin');

    Route::post('/update-status/{id}', 'Admin\Administrator\SiteAdminController@updateStatus')->name('admin.admin_management.update_status');

    Route::post('/update-admin', 'Admin\Administrator\SiteAdminController@update')->name('admin.admin_management.update_admin');

    Route::post('/delete-admin/{id}', 'Admin\Administrator\SiteAdminController@destroy')->name('admin.admin_management.delete_admin');
  });
  // admin management route end


  // language management route start
  Route::prefix('/language-management')->middleware('permission:Language Management')->group(function () {
    Route::get('', 'Admin\LanguageController@index')->name('admin.language_management');

    Route::post('/store', 'Admin\LanguageController@store')->name('admin.language_management.store');

    Route::post('/{id}/make-default-language', 'Admin\LanguageController@makeDefault')->name('admin.language_management.make_default_language');

    Route::post('/update', 'Admin\LanguageController@update')->name('admin.language_management.update');

    Route::get('/{id}/edit-keyword', 'Admin\LanguageController@editKeyword')->name('admin.language_management.edit_keyword');

    Route::post('add-keyword', 'Admin\LanguageController@addKeyword')->name('admin.language_management.add_keyword');

    Route::post('/{id}/update-keyword', 'Admin\LanguageController@updateKeyword')->name('admin.language_management.update_keyword');

    Route::post('/{id}/delete', 'Admin\LanguageController@destroy')->name('admin.language_management.delete');

    Route::get('/{id}/check-rtl', 'Admin\LanguageController@checkRTL');
    Route::get('/{id}/check-rtl2', 'Admin\LanguageController@checkRTL2');
  });
  // language management route end
});
