<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Bus;
use App\Models\LocationCard;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('parent')->active()->get();
        $parents = User::where('role', 'parent')->get();
        $drivers = User::where('role', 'driver')->get();
        $buses = Bus::with('driver')->get();
        $locationCards = LocationCard::all();
        
        // Get today's attendance logs
        $attendanceLogs = AttendanceLog::with(['student'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate attendance statistics
        $totalStudents = $students->count();
        $presentCount = $attendanceLogs->where('scan_type', 'IN')->unique('student_id')->count();
        $absentCount = $totalStudents - $presentCount;

        return view('pages.teacher', compact(
            'students', 
            'parents', 
            'attendanceLogs',
            'presentCount',
            'absentCount',
            'drivers', 
            'buses',
            'locationCards',
            
        ));
    }

    public function create()
    {
        $parents = User::where('role', 'parent')->get();
        return view('students.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rfid_number' => 'required|string|unique:students',
            'parent_id' => 'required|exists:users,id',
            'class' => 'nullable|string',
            'section' => 'nullable|string',
            'roll_number' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string'
        ]);

        $student = Student::create($validated);

        return redirect()->route('students.index')
        ->with('success', 'Student added successfully');
    }

    public function edit(Student $student)
    {
        return response()->json([
            'id' => $student->id,
            'name' => $student->name,
            'rfid_number' => $student->rfid_number,
            'parent_id' => $student->parent_id,
            'class' => $student->class,
            'section' => $student->section,
            'roll_number' => $student->roll_number,
            'address' => $student->address,
            'phone' => $student->phone,
            'status' => $student->status,
            'exit_location_id' => $student->exit_location_id
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rfid_number' => 'required|string|unique:students,rfid_number,' . $student->id,
            'parent_id' => 'required|exists:users,id',
            'class' => 'nullable|string',
            'section' => 'nullable|string',
            'roll_number' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'status' => 'boolean',
            'exit_location_id' => 'nullable|exists:location_cards,id'
        ]);

        $student->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'student' => $student->load('parent')
            ]);
        }

        return redirect()->route('students.index')
            ->with('success', 'Student updated successfully');
    }

    public function destroy(Student $student)
    {
        $student->update(['status' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Student deactivated successfully'
        ]);
    }
}