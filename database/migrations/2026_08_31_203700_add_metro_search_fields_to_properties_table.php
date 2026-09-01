<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('borough')->nullable()->after('city_id')->index();
            $table->string('neighborhood')->nullable()->after('borough')->index();
            $table->unsignedSmallInteger('adults')->nullable()->after('bath');
            $table->unsignedSmallInteger('children')->nullable()->after('adults');
            $table->unsignedSmallInteger('infants')->nullable()->after('children');
            $table->boolean('pets_allowed')->default(false)->after('infants');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['borough']);
            $table->dropIndex(['neighborhood']);
            $table->dropColumn(['borough', 'neighborhood', 'adults', 'children', 'infants', 'pets_allowed']);
        });
    }
};
