<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\User;
use Illuminate\Http\Request;

class BusController extends Controller
{
    public function index()
    {
        $buses = Bus::with('driver')->get();
        $drivers = User::where('role', 'driver')->get();
        return view('buses.index', compact('buses', 'drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bus_number' => 'required|integer|unique:buses',
            'driver_id' => 'nullable|exists:users,id'
        ]);

        Bus::create($validated);

        return redirect()->back()
            ->with('success', 'Bus added successfully');
    }

    public function update(Request $request, Bus $bus)
    {
        $validated = $request->validate([
            'bus_number' => 'required|integer|unique:buses,bus_number,' . $bus->id,
            'driver_id' => 'nullable|exists:users,id'
        ]);

        $bus->update($validated);

        return redirect()->back()
            ->with('success', 'Bus updated successfully');
    }

    public function destroy(Bus $bus)
    {
        $bus->delete();
        return redirect()->back()
            ->with('success', 'Bus deleted successfully');
    }
}