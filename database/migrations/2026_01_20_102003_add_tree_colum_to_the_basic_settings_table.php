<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('basic_settings', function (Blueprint $table) {
      $table->string('firebase_admin_json')->nullable();
      $table->string('mobile_favicon')->nullable();
      $table->string('mobile_app_logo')->nullable();
      $table->tinyInteger('has_mobile_app')->default(0)->comment('0 = No mobile app, 1 = Has mobile app');
      $table->tinyInteger('mobile_google_map_status')->default(0)->comment('0 = Deactive, 1 = Active');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('basic_settings', function (Blueprint $table) {
      //
    });
  }
};
