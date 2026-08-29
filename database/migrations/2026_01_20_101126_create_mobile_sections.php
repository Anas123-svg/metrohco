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
    Schema::create('mobile_sections', function (Blueprint $table) {
      $table->id();
      $table->bigInteger('language_id')->nullable();
      $table->string('category_section_title')->nullable();
      $table->string('featured_property_section_title')->nullable();
      $table->string('latest_property_section_title')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('mobile_sections');
  }
};
