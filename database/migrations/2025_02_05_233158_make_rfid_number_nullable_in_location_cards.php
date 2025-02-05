<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('location_cards', function (Blueprint $table) {
            $table->string('rfid_number')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('location_cards', function (Blueprint $table) {
            $table->string('rfid_number')->nullable(false)->change();
        });
    }
};
