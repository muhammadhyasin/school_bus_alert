@extends('layouts.main')
@section('content')
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-4">Location Test Tool</h4>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Select Location to Test</label>
                    <select class="form-select" id="testLocationSelect">
                        <option value="">Choose a location...</option>
                        @foreach($locationCards as $location)
                            <option value="{{ $location->id }}" 
                                    data-lat="{{ $location->latitude }}"
                                    data-lng="{{ $location->longitude }}"
                                    data-radius="{{ $location->radius }}">
                                {{ $location->location_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Session Type</label>
                    <select class="form-select" id="testSession">
                        <option value="morning">Morning Session</option>
                        <option value="evening">Evening Session</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Test Coordinates</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="testLat" placeholder="Latitude">
                        <input type="text" class="form-control" id="testLng" placeholder="Longitude">
                        <button class="btn btn-primary" id="testLocation">
                            Test
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="testMap" style="height: 400px; border-radius: 8px;" class="mb-3"></div>
        <div id="testResults" class="alert d-none"></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Simulate Student Attendance</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Select Student</label>
                        <select class="form-select" id="testStudent">
                            <option value="">Choose a student...</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->name }} ({{ $student->rfid_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Bus</label>
                        <select class="form-select" id="testBus">
                            <option value="">Choose a bus...</option>
                            @foreach($buses as $bus)
                                <option value="{{ $bus->id }}">Bus #{{ $bus->bus_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Morning Session</h6>
                            <div class="d-flex gap-2 mb-2">
                                <button class="btn btn-primary" onclick="simulateAttendance('morning', 'entry')">
                                    <i class="ri-login-circle-line"></i> Entry
                                </button>
                                <button class="btn btn-info" onclick="simulateAttendance('morning', 'exit')">
                                    <i class="ri-logout-circle-line"></i> Exit
                                </button>
                            </div>
                            <div id="morningStatus" class="small text-muted"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Evening Session</h6>
                            <div class="d-flex gap-2 mb-2">
                                <button class="btn btn-primary" onclick="simulateAttendance('evening', 'entry')">
                                    <i class="ri-login-circle-line"></i> Entry
                                </button>
                                <button class="btn btn-info" onclick="simulateAttendance('evening', 'exit')">
                                    <i class="ri-logout-circle-line"></i> Exit
                                </button>
                            </div>
                            <div id="eveningStatus" class="small text-muted"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
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
                        <tbody id="attendanceLogsTable">
                            @foreach($attendanceLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('h:i A') }}</td>
                                <td>
                                    <h6 class="mb-0">{{ $log->student->name }}</h6>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->scan_type === 'entry' ? 'success' : 'info' }}">
                                        {{ $log->scan_type }}
                                    </span>
                                </td>
                                <td>{{ $log->bus_id }}</td>
                                <td>{{ $log->session }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
// Add to your existing $(document).ready function
let testMap, testMarker, testCircle, testLocationMarker;

function initializeTestMap() {
    const defaultLocation = { lat: 0, lng: 0 };
    
    testMap = new google.maps.Map(document.getElementById('testMap'), {
        zoom: 15,
        center: defaultLocation,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        zoomControl: true,
        mapTypeControl: false,
        streetViewControl: false
    });

    // Create draggable marker for test position
    testMarker = new google.maps.Marker({
        position: defaultLocation,
        map: testMap,
        draggable: true,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 8,
            fillColor: "#4285F4",
            fillOpacity: 1,
            strokeColor: "#ffffff",
            strokeWeight: 2,
        }
    });

    // Create fixed marker for location position
    testLocationMarker = new google.maps.Marker({
        map: testMap,
        icon: {
            path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
            scale: 6,
            fillColor: "#DB4437",
            fillOpacity: 1,
            strokeColor: "#ffffff",
            strokeWeight: 2,
        }
    });

    // Create circle for radius visualization
    testCircle = new google.maps.Circle({
        map: testMap,
        fillColor: '#4CAF50',
        fillOpacity: 0.2,
        strokeColor: '#4CAF50',
        strokeWeight: 1
    });

    // Update coordinates when marker is dragged
    testMarker.addListener('dragend', function() {
        const position = testMarker.getPosition();
        $('#testLat').val(position.lat().toFixed(6));
        $('#testLng').val(position.lng().toFixed(6));
        checkDistance();
    });

    // Add click listener to map
    testMap.addListener('click', function(e) {
        testMarker.setPosition(e.latLng);
        $('#testLat').val(e.latLng.lat().toFixed(6));
        $('#testLng').val(e.latLng.lng().toFixed(6));
        checkDistance();
    });
}

// Initialize test map
initializeTestMap();

// Handle location selection
$('#testLocationSelect').change(function() {
    const option = $(this).find(':selected');
    if (option.val()) {
        const lat = parseFloat(option.data('lat'));
        const lng = parseFloat(option.data('lng'));
        const radius = parseInt(option.data('radius'));
        
        const position = new google.maps.LatLng(lat, lng);
        
        // Update location marker and circle
        testLocationMarker.setPosition(position);
        testCircle.setCenter(position);
        testCircle.setRadius(radius);
        
        // Center map on location
        testMap.setCenter(position);
        const isSchool = option.data('is-school');
        showTestResult(
            `Selected ${isSchool ? 'School Location' : 'Stop Point'}<br>` +
            `Session: ${$('#testSession').val()}<br>` +
            'Click on map or drag marker to test location',
            'info'
        );
        
        // If test marker is not set, place it at location
        if (!testMarker.getPosition().equals(new google.maps.LatLng(0, 0))) {
            checkDistance();
        }
    }
});
$('#testSession').change(function() {
    const locationId = $('#testLocationSelect').val();
    if (locationId) {
        checkDistance(); // Recheck with new session
    }
});

// Handle test button click
$('#testLocation').click(function() {
    checkDistance();
});

function checkDistance() {
    const locationId = $('#testLocationSelect').val();
    if (!locationId) {
        showTestResult('Please select a location to test', 'warning');
        return;
    }

    const testLat = parseFloat($('#testLat').val());
    const testLng = parseFloat($('#testLng').val());
    
    if (isNaN(testLat) || isNaN(testLng)) {
        showTestResult('Please enter valid coordinates', 'warning');
        return;
    }

    // Get selected location data
    const option = $('#testLocationSelect').find(':selected');
    const locationLat = parseFloat(option.data('lat'));
    const locationLng = parseFloat(option.data('lng'));
    const radius = parseInt(option.data('radius'));

    // Calculate distance
    const distance = google.maps.geometry.spherical.computeDistanceBetween(
        new google.maps.LatLng(testLat, testLng),
        new google.maps.LatLng(locationLat, locationLng)
    );

    // Check if within radius
    const isWithinRadius = distance <= radius;

    // Draw line between points
    if (window.testLine) {
        window.testLine.setMap(null);
    }
    window.testLine = new google.maps.Polyline({
        path: [
            { lat: testLat, lng: testLng },
            { lat: locationLat, lng: locationLng }
        ],
        geodesic: true,
        strokeColor: isWithinRadius ? '#4CAF50' : '#FF0000',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        map: testMap
    });

    // Show result
    const message = `
        <strong>${isWithinRadius ? 'Within Range!' : 'Out of Range'}</strong><br>
        Distance: ${Math.round(distance)}m<br>
        Radius: ${radius}m<br>
        ${isWithinRadius ? 'Alert would be triggered' : 'No alert would be triggered'}
    `;
    
    showTestResult(message, isWithinRadius ? 'success' : 'danger');

    // Simulate alert if within radius
    if (isWithinRadius) {
        simulateLocationAlert(locationId);
    }
}

function showTestResult(message, type) {
    const resultDiv = $('#testResults');
    resultDiv.removeClass('d-none alert-success alert-warning alert-danger')
        .addClass(`alert-${type}`)
        .html(message);
}

function simulateLocationAlert(locationId) {
    const session = $('#testSession').val();
    
    // Store session in cache
    $.post('{{ route("teacher.update-session-mode") }}', {
        mode: session
    }).then(() => {
        // After setting session, simulate alert
        $.ajax({
            url: '{{ route("locations.test-alert") }}',
            method: 'POST',
            data: {
                location_id: locationId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Alert Simulated',
                        html: `
                            <div class="text-start">
                                <p class="mb-2"><strong>Session:</strong> ${session}</p>
                                <p class="mb-0">${response.message}</p>
                            </div>
                        `,
                        icon: 'success'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to simulate alert', 'error');
            }
        });
    });
}
// Add to your existing script
function simulateAttendance(session, scanType) {
    const studentId = $('#testStudent').val();
    const busId = $('#testBus').val();

    if (!studentId || !busId) {
        Swal.fire('Error', 'Please select both student and bus', 'error');
        return;
    }

    $.ajax({
        url: '{{ route("test.simulate-attendance") }}',
        method: 'POST',
        data: {
            student_id: studentId,
            bus_id: busId,
            session: session,
            scan_type: scanType,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                $(`#${session}Status`).html(`
                    <span class="text-success">
                        <i class="ri-checkbox-circle-line"></i> 
                        ${scanType.charAt(0).toUpperCase() + scanType.slice(1)} recorded
                    </span>
                `);
                refreshAttendanceLog();
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to simulate attendance', 'error');
        }
    });
}

function refreshAttendanceLog() {
    const studentId = $('#testStudent').val();
    if (!studentId) return;

    $.get(`/test/attendance-log/${studentId}`, function(response) {
        const logs = response.logs;
        $('#attendanceLog').empty();
        
        logs.forEach(log => {
            $('#attendanceLog').append(`
                <tr>
                    <td>${moment(log.scan_time).format('HH:mm:ss')}</td>
                    <td>${log.session}</td>
                    <td>
                        <span class="badge bg-${log.scan_type === 'entry' ? 'success' : 'info'}">
                            ${log.scan_type}
                        </span>
                    </td>
                    <td>Bus #${log.bus.bus_number}</td>
                </tr>
            `);
        });
    });
}
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

// Refresh log when student is selected
$('#testStudent').change(refreshAttendanceLog);
</script>    
@endpush


@endsection