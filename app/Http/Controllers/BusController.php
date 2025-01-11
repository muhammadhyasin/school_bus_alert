<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use Illuminate\Http\Request;

class BusController extends Controller
{
    public function index()
    {
        $buses = Bus::with('driver')->active()->get();
        return view('buses.index', compact('buses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bus_number' => 'required|string|unique:buses',
            'driver_id' => 'required|exists:users,id'
        ]);

        $bus = Bus::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bus added successfully',
            'bus' => $bus->load('driver')
        ]);
    }

    public function update(Request $request, Bus $bus)
    {
        $validated = $request->validate([
            'bus_number' => 'required|string|unique:buses,bus_number,' . $bus->id,
            'driver_id' => 'required|exists:users,id',
            'status' => 'boolean'
        ]);

        $bus->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bus updated successfully',
            'bus' => $bus->load('driver')
        ]);
    }

    public function destroy(Bus $bus)
    {
        $bus->update(['status' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Bus deactivated successfully'
        ]);
    }

    // Get bus details with current attendance
    public function getBusDetails($busId)
    {
        $bus = Bus::with(['driver', 'attendanceLogs' => function($query) {
            $query->whereDate('scan_time', today())
                  ->with('student');
        }])->findOrFail($busId);

        return response()->json([
            'success' => true,
            'bus' => $bus
        ]);
    }

    // Get active buses for dropdown
    public function getActiveBuses()
    {
        $buses = Bus::active()->get(['id', 'bus_number']);
        return response()->json($buses);
    }
}