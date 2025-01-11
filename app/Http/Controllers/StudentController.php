<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('parent')->active()->get();
        
        $parents = User::where('role', 'parent')->get();

        return view('pages.teacher', compact('students', 'parents'));
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
        $parents = User::where('role', 'parent')->get();
        return view('students.edit', compact('student', 'parents'));
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
            'status' => 'boolean'
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'student' => $student->load('parent')
        ]);
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