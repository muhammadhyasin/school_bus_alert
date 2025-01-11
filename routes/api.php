<?php

use App\Http\Controllers\RFIDController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/hardware/rfid-scan', [RFIDController::class, 'handleRFIDScan']);
