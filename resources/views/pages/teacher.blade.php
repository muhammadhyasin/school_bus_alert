@extends('layouts.main')
@section('content')
<div class="row">
    <div class="col-12">
    </div>
</div>
<!-- end page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Students Management</h4>
            <div class="page-title-right">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="ri-add-line align-middle me-1"></i> Add Student
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBusModal">
                    <i class="ri-bus-line align-middle me-1"></i> Add Bus
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                    <i class="ri-map-pin-line align-middle me-1"></i> Add Location
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="locationForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" class="form-control" id="locationName" name="location_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="isSchool" name="is_school_card" value="1">
                            <label class="form-check-label" for="isSchool">
                                This is a school location
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Search Location</label>
                        <input type="text" class="form-control" id="searchAddress" 
                               placeholder="Search for an address...">
                    </div>

                    <div class="mb-3">
                        <div id="locationMap" style="height: 400px; border-radius: 8px;"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="text" class="form-control" id="latitude" name="latitude" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="text" class="form-control" id="longitude" name="longitude" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Radius (meters)</label>
                                <input type="number" class="form-control" id="radius" name="radius" 
                                       value="50" min="10" max="1000">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveLocation">
                    <i class="ri-save-line"></i> Save Location
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Add Bus Modal -->
<div class="modal fade" id="addBusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Bus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('buses.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Bus Number</label>
                        <input type="number" class="form-control @error('bus_number') is-invalid @enderror" 
                               name="bus_number" value="{{ old('bus_number') }}" required>
                        @error('bus_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Driver</label>
                        <select class="form-select @error('driver_id') is-invalid @enderror" 
                                name="driver_id">
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" 
                                    {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Bus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editStudentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">RFID Number</label>
                        <div class="input-group">
                            <input type="text" class="form-control" 
                                   name="rfid_number" id="editRfidNumber" required>
                            <div class="input-group-append">
                                <div class="form-check form-switch mt-2 ms-2">
                                    <input class="form-check-input" type="checkbox" id="editScanModeSwitch">
                                    <label class="form-check-label" for="editScanModeSwitch">Scan Mode</label>
                                </div>
                            </div>
                        </div>
                        <div id="editRfidStatus" class="mt-2"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parent</label>
                        <select class="form-select" name="parent_id" id="editParentId" required>
                            <option value="">Select Parent</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class</label>
                            <input type="text" class="form-control" name="class" id="editClass">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" name="section" id="editSection">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Roll Number</label>
                        <input type="text" class="form-control" name="roll_number" id="editRollNumber">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="editAddress"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" id="editPhone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Exit Location</label>
                        <select class="form-select" name="exit_location_id" id="editExitLocation">
                            <option value="">Select Exit Location</option>
                            @foreach($locationCards as $location)
                                <option value="{{ $location->id }}">
                                    {{ $location->location_name }} ({{ $location->rfid_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-6">
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
                                <th>Exit Location</th>
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
                                    @if($student->exit_location_id)
                                        {{ $student->exit_location_id }}
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
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
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Today's Test Attendance Log</h4>
                    <button class="btn btn-danger btn-sm" id="clearLogs">
                        <i class="ri-delete-bin-line me-1"></i> Clear All Logs
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Student Name</th>
                                <th>Type</th>
                                <th>Bus Number</th>
                                <th>Session</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendanceLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('h:i A') }}</td>
                                <td>
                                    <h6 class="mb-0">{{ $log->student->name }}</h6>
                                </td>
                                <td>{{ $log->scan_type }}</td>
                                <td>{{ $log->bus_id }}</td>
                                <td>{{ $log->session }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No attendance records for today</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Generate Fees</h5>
        <form id="generateFeesForm">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <select class="form-select" name="year" required>
                            @php
                                $currentYear = date('Y');
                            @endphp
                            @for($year = $currentYear; $year <= $currentYear + 2; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Amount per Month</label>
                        <input type="number" class="form-control" name="amount" value="1500" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            Generate Fees
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
@push('scripts')
<script>
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(document).ready(function () {
    let scanInterval;
    requestNotificationPermission();
    let lastNotificationIds = []; // Add this
    const notificationSound = new Audio('{{ asset("notify.wav") }}');
   // Add to your existing $(document).ready function
let map, marker, geocoder, circle;

// Initialize map when location modal is shown
$('#addLocationModal').on('shown.bs.modal', function() {
    if (!map) {
        initializeMap();
    }
    google.maps.event.trigger(map, 'resize');
});

function initializeMap() {
    geocoder = new google.maps.Geocoder();
    
    // Try to get user's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            initializeMapWithPosition(position.coords.latitude, position.coords.longitude);
        }, function() {
            // If geolocation fails, use default location
            initializeMapWithPosition(0, 0);
        });
    } else {
        initializeMapWithPosition(0, 0);
    }
}

function initializeMapWithPosition(lat, lng) {
    const defaultLocation = { lat, lng };
    
    map = new google.maps.Map(document.getElementById('locationMap'), {
        zoom: 15,
        center: defaultLocation,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        zoomControl: true,
        mapTypeControl: false,
        streetViewControl: false
    });

    // Create marker
    marker = new google.maps.Marker({
        position: defaultLocation,
        map: map,
        draggable: true
    });

    // Create circle for radius visualization
    circle = new google.maps.Circle({
        map: map,
        fillColor: '#4CAF50',
        fillOpacity: 0.2,
        strokeColor: '#4CAF50',
        strokeWeight: 1,
        radius: parseInt($('#radius').val())
    });

    // Update circle when marker is dragged
    marker.addListener('dragend', function() {
        updateLocationInfo(marker.getPosition());
        circle.setCenter(marker.getPosition());
    });

    // Update circle when radius changes
    $('#radius').on('input', function() {
        circle.setRadius(parseInt($(this).val()));
    });

    // Add click listener to map
    map.addListener('click', function(e) {
        marker.setPosition(e.latLng);
        circle.setCenter(e.latLng);
        updateLocationInfo(e.latLng);
    });

    // Initialize search box
    const searchInput = document.getElementById('searchAddress');
    const searchBox = new google.maps.places.SearchBox(searchInput);

    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });

    searchBox.addListener('places_changed', function() {
        const places = searchBox.getPlaces();
        if (places.length === 0) return;

        const place = places[0];
        if (!place.geometry) return;

        // Update map and marker
        map.setCenter(place.geometry.location);
        marker.setPosition(place.geometry.location);
        circle.setCenter(place.geometry.location);
        updateLocationInfo(place.geometry.location);

        if (!$('#locationName').val()) {
            $('#locationName').val(place.name);
        }
    });
}

function updateLocationInfo(latLng) {
    $('#latitude').val(latLng.lat().toFixed(6));
    $('#longitude').val(latLng.lng().toFixed(6));

    // Get address for the selected location
    geocoder.geocode({ location: latLng }, function(results, status) {
        if (status === 'OK' && results[0]) {
            if (!$('#locationName').val()) {
                $('#locationName').val(results[0].formatted_address.split(',')[0]);
            }
        }
    });
}

// Handle save location button click
$('#saveLocation').click(function() {
    const button = $(this);
    const form = $('#locationForm');
    
    if (!$('#latitude').val() || !$('#longitude').val()) {
        Swal.fire('Error', 'Please select a location on the map', 'error');
        return;
    }

    button.prop('disabled', true)
        .html('<i class="ri-loader-4-line ri-spin"></i> Saving...');

    $.ajax({
        url: '{{ route("locations.store") }}',
        method: 'POST',
        data: form.serialize(),
        success: function(response) {
            if (response.success) {
                $('#addLocationModal').modal('hide');
                Swal.fire({
                    title: 'Success',
                    text: 'Location saved successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to save location', 'error');
        },
        complete: function() {
            button.prop('disabled', false)
                .html('<i class="ri-save-line"></i> Save Location');
        }
    });
});

// Reset form when modal is hidden
$('#addLocationModal').on('hidden.bs.modal', function() {
    $('#locationForm')[0].reset();
    if (marker) {
        marker.setMap(null);
    }
    if (circle) {
        circle.setMap(null);
    }
    map = null;
});

    function toggleNotificationSound() {
        const currentState = localStorage.getItem("notificationSound");
        if (currentState === "disabled") {
            localStorage.setItem("notificationSound", "enabled");
            return true;
        } else {
            localStorage.setItem("notificationSound", "disabled");
            return false;
        }
    }

    // Add notification settings button to your profile dropdown
    $(".user-dropdown .dropdown-menu").append(`
        <a class="dropdown-item" href="javascript:void(0)" onclick="requestNotificationPermission()">
            <i class="ri-notification-line align-middle me-1"></i> 
            Notification Settings
        </a>
    `);

    // Function to handle scan mode switching
    function handleScanMode(isEdit = false) {
        const switchElement = isEdit
            ? "#editScanModeSwitch"
            : "#scanModeSwitch";
        const inputElement = isEdit ? "#editRfidNumber" : "#rfidInput";
        const statusElement = isEdit ? "#editRfidStatus" : "#rfidStatus";

        $(switchElement).change(function () {
            const isChecked = $(this).is(":checked");
            const rfidInput = $(inputElement);

            if (isChecked) {
                $.post("/start-adding-student", function (response) {
                    if (response.success) {
                        rfidInput.attr("readonly", true);
                        $(statusElement).html(
                            '<div class="alert alert-info">Waiting for RFID scan...</div>'
                        );
                        scanInterval = setInterval(
                            () => checkForRFID(isEdit),
                            1000
                        );
                    }
                });
            } else {
                $.post("/cancel-adding-student", function (response) {
                    if (response.success) {
                        rfidInput.attr("readonly", false);
                        $(statusElement).html("");
                        clearInterval(scanInterval);
                    }
                });
            }
        });
    }

    // Function to check for RFID scans
    function checkForRFID(isEdit = false) {
        const inputElement = isEdit ? "#editRfidNumber" : "#rfidInput";
        const statusElement = isEdit ? "#editRfidStatus" : "#rfidStatus";
        const switchElement = isEdit
            ? "#editScanModeSwitch"
            : "#scanModeSwitch";

        $.ajax({
            url: "/check-last-rfid",
            method: "GET",
            success: function (response) {
                if (response.success && response.rfid_number) {
                    $(inputElement).val(response.rfid_number);
                    $(statusElement).html(
                        '<div class="alert alert-success">RFID scanned successfully!</div>'
                    );
                    $(switchElement).prop("checked", false).trigger("change");
                }
            },
            error: function (xhr) {
                if (xhr.status !== 404) {
                    $(statusElement).html(
                        '<div class="alert alert-danger">Error checking RFID</div>'
                    );
                }
            },
        });
    }

    // Initialize scan mode for both forms
    handleScanMode(false); // For add form
    handleScanMode(true); // For edit form

    // Handle modal closing
    $("#addStudentModal, #editStudentModal").on("hidden.bs.modal", function () {
        const isEditModal = $(this).attr("id") === "editStudentModal";
        const switchElement = isEditModal
            ? "#editScanModeSwitch"
            : "#scanModeSwitch";
        const statusElement = isEditModal ? "#editRfidStatus" : "#rfidStatus";

        if ($(switchElement).is(":checked")) {
            $.post("/cancel-adding-student", function () {
                clearInterval(scanInterval);
                $(switchElement).prop("checked", false);
                $(statusElement).html("");
            });
        }
    });

    // Handle form submissions
    $("form").on("submit", function () {
        clearInterval(scanInterval);
    });

    // Edit student handler
    $(document).on("click", ".edit-student", function () {
        let studentId = $(this).data("id");
        $(this).html('<i class="ri-loader-line spinner"></i>');

        $.ajax({
            url: `/students/${studentId}/edit`,
            method: "GET",
            success: (data) => {
                $("#editName").val(data.name);
                $("#editRfidNumber").val(data.rfid_number);
                $("#editParentId").val(data.parent_id);
                $("#editClass").val(data.class);
                $("#editSection").val(data.section);
                $("#editRollNumber").val(data.roll_number);
                $("#editAddress").val(data.address);
                $("#editPhone").val(data.phone);
                $("#editExitLocation").val(data.exit_location_id);

                $("#editStudentForm").attr("action", `/students/${studentId}`);
                $("#editStudentModal").modal("show");
            },
            error: (xhr) => {
                console.error("Error fetching student data:", xhr);
                alert("Error loading student data. Please try again.");
            },
            complete: () => {
                $(this).html('<i class="ri-pencil-line"></i>');
            },
        });
    });

    // Edit bus handler
    $(".edit-bus").click(function () {
        let busId = $(this).data("id");
        let busNumber = $(this).data("number");
        let driverId = $(this).data("driver");

        $("#editBusModal").modal("show");
        $("#editBusForm").attr("action", `/buses/${busId}`);
        $("#editBusNumber").val(busNumber);
        $("#editDriverId").val(driverId);
    });

    // Delete bus handler
    $(".delete-bus").click(function () {
        if (confirm("Are you sure you want to delete this bus?")) {
            let busId = $(this).data("id");
            $.ajax({
                url: `/buses/${busId}`,
                type: "DELETE",
                success: function () {
                    location.reload();
                },
            });
        }
    });

    // Delete student handler
    $(document).on("click", ".delete-student", function () {
        if (confirm("Are you sure you want to delete this student?")) {
            let studentId = $(this).data("id");
            $.ajax({
                url: `/students/${studentId}`,
                type: "DELETE",
                success: function () {
                    location.reload();
                },
            });
        }
    });

    // Error handling for AJAX requests
    $(document).ajaxError(function (event, xhr, settings) {
        if (xhr.status === 419) {
            alert("Your session has expired. Please refresh the page.");
        }
    });

    let unreadCount = 0;

    // Function to update notification dropdown
    function updateNotificationDropdown(notifications) {
        const notificationContainer = $("[data-simplebarr]");
        notificationContainer.empty();
        const soundEnabled =
            localStorage.getItem("notificationSound") !== "disabled";
        const soundToggleHtml = `
            <div class="px-3 py-2 border-bottom">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="notificationSound" 
                           ${soundEnabled ? "checked" : ""}>
                    <label class="form-check-label" for="notificationSound">
                        <i class="ri-volume-${
                            soundEnabled ? "up" : "mute"
                        }-line"></i>
                        Notification Sound
                    </label>
                </div>
            </div>
        `;
        notificationContainer.append(soundToggleHtml);

        notifications.forEach((notification) => {
            const icon = getNotificationIcon(notification.type);
            const bgColor = getNotificationBgColor(notification.type);

            const notificationHtml = `
                <a href="javascript:void(0)" class="text-reset notification-item" data-id="${
                    notification.id
                }">
                    <div class="d-flex">
                        <div class="avatar-xs me-3">
                            <span class="avatar-title ${bgColor} rounded-circle font-size-16">
                                <i class="${icon}"></i>
                            </span>
                        </div>
                        <div class="flex-1">
                            <h6 class="mb-1">${getNotificationTitle(
                                notification.type
                            )}</h6>
                            <div class="font-size-12 text-muted">
                                <p class="mb-1">${notification.message}</p>
                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> ${
                                    notification.time
                                }</p>
                            </div>
                        </div>
                    </div>
                </a>
            `;
            notificationContainer.append(notificationHtml);
        });

        // Update notification count
        if (notifications.length > 0) {
            $(".noti-dot").show();
        } else {
            $(".noti-dot").hide();
        }
    }

    // Function to get notification icon
    function getNotificationIcon(type) {
        switch (type) {
            case "entry":
                return "ri-login-circle-line";
            case "exit":
                return "ri-logout-circle-line";
            case "missed_stop":
                return "ri-error-warning-line";
            case "wrong_stop":
                return "ri-alarm-warning-line";
            case "session_start":
                return "ri-play-circle-line";
            case "session_end":
                return "ri-stop-circle-line";
            default:
                return "ri-notification-line";
        }
    }

    // Function to get notification background color
    function getNotificationBgColor(type) {
        switch (type) {
            case "entry":
                return "bg-success";
            case "exit":
                return "bg-info";
            case "missed_stop":
            case "wrong_stop":
                return "bg-danger";
            case "session_start":
                return "bg-primary";
            case "session_end":
                return "bg-warning";
            default:
                return "bg-secondary";
        }
    }

    // Function to get notification title
    function getNotificationTitle(type) {
        switch (type) {
            case "entry":
                return "Student Entry";
            case "exit":
                return "Student Exit";
            case "missed_stop":
                return "Missed Stop Alert";
            case "wrong_stop":
                return "Wrong Stop Alert";
            case "session_start":
                return "Session Started";
            case "session_end":
                return "Session Ended";
            default:
                return "Notification";
        }
    }

    // Function to fetch notifications
    function fetchNotifications() {
        $.get('{{ route("notifications.latest") }}', function(response) {
            if (response.success) {
                // Check for new notifications by comparing with lastNotificationIds
                const newNotifications = response.notifications.filter(notification => 
                    !lastNotificationIds.includes(notification.id)
                );

                // If there are new notifications, check for missed stops
                if (newNotifications.length > 0) {
                    // Only play sound if there's a missed stop notification
                    const hasMissedStop = newNotifications.some(notification => 
                        notification.type === 'missed_stop' || notification.type === 'wrong_stop'
                    );

                    if (hasMissedStop) {
                        playNotificationSound();
                    }
                    
                    newNotifications.forEach(notification => {
                        // Show browser notification for all new notifications
                        showBrowserNotification(notification);
                        
                        // Show SweetAlert only for critical notifications
                        if (notification.type === 'missed_stop' || notification.type === 'wrong_stop') {
                            Swal.fire({
                                title: getNotificationTitle(notification.type),
                                text: notification.message,
                                icon: 'warning',
                                confirmButtonColor: '#d33',
                                confirmButtonText: 'Acknowledge',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.post(`/notifications/${notification.id}/read`, function(response) {
                                        if (response.success) {
                                            $(`.notification-item[data-id="${notification.id}"]`).fadeOut();
                                            Swal.fire({
                                                title: 'Acknowledged',
                                                text: 'Alert has been marked as read',
                                                icon: 'success',
                                                timer: 1500,
                                                showConfirmButton: false
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                }

                // Update lastNotificationIds with current notification IDs
                lastNotificationIds = response.notifications.map(n => n.id);

                // Update the notification dropdown with all notifications
                updateNotificationDropdown(response.notifications);
            }
        });
    }

    function playNotificationSound() {
        if (localStorage.getItem("notificationSound") !== "disabled") {
            notificationSound.play().catch(function (error) {
                console.log("Sound play failed:", error);
            });
        }
    }
    // Mark notification as read when clicked
    $(document).on("click", ".notification-item", function () {
        const notificationId = $(this).data("id");
        $.post(`/notifications/${notificationId}/read`);
        $(this).fadeOut();
    });

    // Mark all as read
    $(".mark-all-read").click(function () {
        $.post('{{ route("notifications.markAllRead") }}', function () {
            fetchNotifications();
        });
    });

    // Initial fetch and set interval
    fetchNotifications();
    setInterval(fetchNotifications, 1000);
    // Add to your existing script
    $('#clearLogs').click(function() {
        Swal.fire({
            title: 'Clear Attendance Logs?',
            text: 'This will delete all attendance logs. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, clear all!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("test.clear-logs") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Cleared!',
                                text: 'All attendance logs have been cleared.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            refreshAttendanceLog();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to clear logs', 'error');
                    }
                });
            }
        });
    });

    function requestNotificationPermission() {
        if (!("Notification" in window)) {
            console.log("This browser does not support desktop notification");
            return;
        }

        if (
            Notification.permission !== "granted" &&
            Notification.permission !== "denied"
        ) {
            Swal.fire({
                title: "Enable Notifications?",
                text: "We'd like to send you notifications for important alerts",
                icon: "info",
                showCancelButton: true,
                confirmButtonText: "Yes, Enable!",
                cancelButtonText: "No, Thanks",
            }).then((result) => {
                if (result.isConfirmed) {
                    Notification.requestPermission().then(function (
                        permission
                    ) {
                        if (permission === "granted") {
                            Swal.fire(
                                "Enabled!",
                                "You will now receive notifications",
                                "success"
                            );
                        }
                    });
                }
            });
        }
    }

    // Function to show browser notification
    function showBrowserNotification(notification) {
        if (Notification.permission === "granted") {
            const icon = getNotificationIcon(notification.type);
            const title = getNotificationTitle(notification.type);

            const browserNotification = new Notification(title, {
                body: notification.message,
                icon: "/images/notification-icon.png", // Add your icon path
                badge: "/images/notification-badge.png", // Add your badge path
                tag: `notification-${notification.id}`,
                requireInteraction:
                    notification.type === "missed_stop" ||
                    notification.type === "wrong_stop",
            });

            browserNotification.onclick = function () {
                window.focus();
                browserNotification.close();

                // Mark notification as read when clicked
                $.post(`/notifications/${notification.id}/read`).then(() => {
                    fetchNotifications();
                });

                // Show SweetAlert for critical notifications
                if (
                    notification.type === "missed_stop" ||
                    notification.type === "wrong_stop"
                ) {
                    Swal.fire({
                        title: title,
                        text: notification.message,
                        icon: "warning",
                        confirmButtonColor: "#d33",
                        confirmButtonText: "Acknowledge",
                    });
                }
            };
        }
    }
    $('#generateFeesForm').submit(function(e) {
    e.preventDefault();
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    
    submitBtn.prop('disabled', true)
        .html('<i class="ri-loader-4-line ri-spin"></i> Generating...');

    $.ajax({
        url: '{{ route("admin.generate-fees") }}',
        method: 'POST',
        data: form.serialize(),
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', 'Fees generated successfully', 'success');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to generate fees', 'error');
        },
        complete: function() {
            submitBtn.prop('disabled', false).html('Generate Fees');
        }
    });
});
});
</script>
@endpush
@endsection