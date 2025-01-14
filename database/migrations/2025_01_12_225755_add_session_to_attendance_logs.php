<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            // Remove 'checkpoint' from scan_type enum
            DB::statement("ALTER TABLE attendance_logs MODIFY COLUMN scan_type ENUM('entry', 'exit')");
            
            // Add session column
            $table->enum('session', ['morning', 'evening'])->after('scan_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            // Revert scan_type to include checkpoint
            DB::statement("ALTER TABLE attendance_logs MODIFY COLUMN scan_type ENUM('entry', 'exit', 'checkpoint')");
            
            // Remove session column
            $table->dropColumn('session');
        });
    }
};