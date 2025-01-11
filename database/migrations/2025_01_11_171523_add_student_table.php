<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('students', 'class')) {
                $table->string('class')->nullable();
            }
            if (!Schema::hasColumn('students', 'section')) {
                $table->string('section')->nullable();
            }
            if (!Schema::hasColumn('students', 'roll_number')) {
                $table->string('roll_number')->nullable();
            }
            if (!Schema::hasColumn('students', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('students', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('students', 'status')) {
                $table->boolean('status')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Remove the columns if they exist
            $table->dropColumn([
                'class',
                'section',
                'roll_number',
                'address',
                'phone',
                'has_exited',
                'status'
            ]);
        });
    }
};