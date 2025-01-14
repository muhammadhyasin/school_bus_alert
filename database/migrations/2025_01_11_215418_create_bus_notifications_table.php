<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bus_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('type'); // 'entry', 'exit', 'missed_exit'
            $table->string('message');
            $table->string('bus_id');
            $table->string('recipient_type'); // 'parent', 'teacher', 'driver'
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bus_notifications');
    }
};