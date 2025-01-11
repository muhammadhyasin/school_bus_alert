@extends('layouts.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div
            class="page-title-box d-sm-flex align-items-center justify-content-between"
        >
            <h4 class="mb-sm-0">Dashboard</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-truncate font-size-14 mb-2">
                            Starting time
                        </p>
                        <h4 class="mb-2">04:30</h4>
                    </div>
                    <div class="avatar-sm">
                        <span
                            class="avatar-title bg-light text-primary rounded-3"
                        >
                            <i class="ri-time-line font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
            <!-- end cardbody -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-truncate font-size-14 mb-2">
                            Call institution
                        </p>
                        <h4 class="mb-2">Office</h4>
                    </div>
                    <div class="avatar-sm">
                        <span
                            class="avatar-title bg-light text-primary rounded-3"
                        >
                            <i class="ri-phone-line font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
            <!-- end cardbody -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-truncate font-size-14 mb-2">
                            Bus Details
                        </p>
                        <div class="col-sm-6">
                            <div class="dropdown">
                                <button
                                    class="btn dropdown-toggle"
                                    type="button"
                                    id="dropdownMenuButton"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    style="
                                        background-color: rgb(16, 181, 236);
                                        color: white;
                                        border: none;
                                    "
                                >
                                    List of Bus
                                    <i class="mdi mdi-chevron-down"></i>
                                </button>
                                <div
                                    class="dropdown-menu"
                                    aria-labelledby="dropdownMenuButton"
                                >
                                    <a class="dropdown-item" href="#">BN-01</a>
                                    <a class="dropdown-item" href="#">BN-02</a>
                                    <a class="dropdown-item" href="#">BN-03</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="avatar-sm">
                        <span
                            class="avatar-title bg-light text-primary rounded-3"
                        >
                            <i class="ri-bus-line font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
            <!-- end cardbody -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
</div>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Students Management</h4>
            <div class="page-title-right">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="ri-add-line align-middle me-1"></i> Add Student
                </button>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-4">Students List</h4>
        <div class="table-responsive">
            <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>RFID Number</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Roll Number</th>
                        <th>Parent</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td><h6 class="mb-0">{{ $student->name }}</h6></td>
                        <td>{{ $student->rfid_number }}</td>
                        <td>{{ $student->class }}</td>
                        <td>{{ $student->section }}</td>
                        <td>{{ $student->roll_number }}</td>
                        <td>{{ $student->parent->name }}</td>
                        <td>
                            <div class="font-size-13">
                                <i class="ri-checkbox-blank-circle-fill font-size-10 
                                    {{ $student->status ? 'text-success' : 'text-warning' }} align-middle me-2"></i>
                                {{ $student->status ? 'Active' : 'Inactive' }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-primary edit-student" data-id="{{ $student->id }}">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-student" data-id="{{ $student->id }}">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('students.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">RFID Number</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('rfid_number') is-invalid @enderror" 
                                   name="rfid_number" id="rfidInput" 
                                   value="{{ old('rfid_number') }}" required>
                            <div class="input-group-append">
                                <div class="form-check form-switch mt-2 ms-2">
                                    <input class="form-check-input" type="checkbox" id="scanModeSwitch">
                                    <label class="form-check-label" for="scanModeSwitch">Scan Mode</label>
                                </div>
                            </div>
                        </div>
                        <div id="rfidStatus" class="mt-2"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parent</label>
                        <select class="form-select @error('parent_id') is-invalid @enderror" 
                                name="parent_id" required>
                            <option value="">Select Parent</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" 
                                    {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class</label>
                            <input type="text" class="form-control @error('class') is-invalid @enderror" 
                                   name="class" value="{{ old('class') }}">
                            @error('class')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control @error('section') is-invalid @enderror" 
                                   name="section" value="{{ old('section') }}">
                            @error('section')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Roll Number</label>
                        <input type="text" class="form-control @error('roll_number') is-invalid @enderror" 
                               name="roll_number" value="{{ old('roll_number') }}">
                        @error('roll_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  name="address">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<!-- end row -->

<div class="row">
    <!-- end col -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Todays Attendance</h4>

                <div class="row" style="display: flex">
                    <div class="col-4" style="flex: 1">
                        <div class="text-center mt-4">
                            <h5>34</h5>
                            <p class="mb-2 text-truncate">Present</p>
                        </div>
                    </div>
                    <!-- end col -->
                    <div class="col-4" style="flex: 1">
                        <div class="text-center mt-4">
                            <h5>28</h5>
                            <p class="mb-2 text-truncate">Abscent</p>
                        </div>
                    </div>
                    <!-- end col -->
                    <!-- <div class="col-4">
                        <div class="text-center mt-4">
                            <h5>9062</h5>
                            <p class="mb-2 text-truncate">Last Month</p>
                        </div>
                    </div> -->
                    <!-- end col -->
                </div>
                <!-- end row -->

                <div class="mt-4">
                    <div id="donut-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
</div>
@push('scripts')
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    let scanInterval;
    
    // Handle scan mode switch
    $('#scanModeSwitch').change(function() {
        const isChecked = $(this).is(':checked');
        const rfidInput = $('#rfidInput');
        
        if (isChecked) {
            // Start scanning mode
            $.post('/start-adding-student', function(response) {
                if(response.success) {
                    rfidInput.attr('readonly', true);
                    $('#rfidStatus').html('<div class="alert alert-info">Waiting for RFID scan...</div>');
                    // Start polling for RFID
                    scanInterval = setInterval(checkForRFID, 1000);
                }
            });
        } else {
            // Stop scanning mode
            $.post('/cancel-adding-student', function(response) {
                if(response.success) {
                    rfidInput.attr('readonly', false);
                    $('#rfidStatus').html('');
                    clearInterval(scanInterval);
                }
            });
        }
    });

    function checkForRFID() {
        $.ajax({
            url: '/check-last-rfid',
            method: 'GET',
            success: function(response) {
                if (response.success && response.rfid_number) {
                    // RFID found
                    $('#rfidInput').val(response.rfid_number);
                    $('#rfidStatus').html('<div class="alert alert-success">RFID scanned successfully!</div>');
                    $('#scanModeSwitch').prop('checked', false).trigger('change');
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    // No new RFID scan found, continue polling
                } else {
                    $('#rfidStatus').html('<div class="alert alert-danger">Error checking RFID</div>');
                }
            }
        });
    }

    // Clear interval when modal is closed
    $('#addStudentModal').on('hidden.bs.modal', function () {
        if($('#scanModeSwitch').is(':checked')) {
            $.post('/cancel-adding-student', function() {
                clearInterval(scanInterval);
                $('#scanModeSwitch').prop('checked', false);
                $('#rfidStatus').html('');
            });
        }
    });

    // Handle form submission
    $('form').on('submit', function() {
        clearInterval(scanInterval);
    });

    // Add error handling for AJAX requests
    $(document).ajaxError(function(event, xhr, settings) {
        if (xhr.status === 419) { // CSRF token mismatch
            alert('Your session has expired. Please refresh the page.');
        }
    });
});
</script>
@endpush
@endsection