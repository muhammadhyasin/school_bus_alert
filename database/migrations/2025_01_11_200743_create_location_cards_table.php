<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationCardsTable extends Migration
{
    public function up()
    {
        Schema::create('location_cards', function (Blueprint $table) {
            $table->id();
            $table->string('rfid_number')->unique();
            $table->string('location_name');
            $table->timestamps();
        });

        // Add location_id to students table for exit location
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('exit_location_id')
                  ->nullable()
                  ->constrained('location_cards')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['exit_location_id']);
            $table->dropColumn('exit_location_id');
        });

        Schema::dropIfExists('location_cards');
    }
}