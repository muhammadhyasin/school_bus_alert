<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOutScanTimeToAttendanceLogs extends Migration
{
    public function up()
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->timestamp('out_scan_time')->nullable()->after('scan_time');
        });
    }

    public function down()
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn('out_scan_time');
        });
    }
}