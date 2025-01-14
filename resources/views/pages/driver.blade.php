@extends('layouts.main')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Driver Dashboard</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card card-h-100">
            <div class="card-body">
                <h4 class="card-title mb-4">Bus Control Panel</h4>
                
                @if($activeBus)
                    <div class="bus-info mb-4">
                        <h5 class="text-muted">Assigned Bus</h5>
                        <div class="d-flex align-items-center">
                            <i class="ri-bus-line fs-2 me-2"></i>
                            <div>
                                <h4 class="mb-0">Bus #{{ $activeBus->bus_number }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="bus-session-controls">
                        @if(!$currentSession)
                            <div class="session-starter">
                                <div class="mb-4">
                                    <label class="form-label">Session Type</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="sessionType" id="morning" value="morning" checked>
                                        <label class="btn btn-outline-primary" for="morning">
                                            <i class="ri-sun-line me-1"></i> Morning
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="sessionType" id="evening" value="evening">
                                        <label class="btn btn-outline-primary" for="evening">
                                            <i class="ri-moon-line me-1"></i> Evening
                                        </label>
                                    </div>
                                </div>
                                <button class="btn btn-primary w-100" id="startSession">
                                    <i class="ri-play-circle-line me-1"></i> Start Bus Session
                                </button>
                            </div>
                        @else
                            <div class="active-session">
                                <div class="alert alert-soft-info border-0 mb-4">
                                    <h5 class="text-info">
                                        <i class="ri-time-line me-1"></i> Active Session
                                    </h5>
                                    <p class="mb-2">
                                        <strong>Type:</strong> 
                                        <span class="badge bg-soft-primary text-primary">
                                            {{ ucfirst($currentSession->session_type) }}
                                        </span>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Started:</strong> 
                                        {{ $currentSession->started_at->format('h:i A') }}
                                    </p>
                                </div>
                                <button class="btn btn-danger w-100" id="endSession">
                                    <i class="ri-stop-circle-line me-1"></i> End Bus Session
                                </button>
                                <div class="delay-controls mt-3">
                                    <button class="btn btn-warning w-100 mb-3" id="reportDelay" data-bs-toggle="modal" data-bs-target="#delayModal">
                                        <i class="ri-time-line me-1"></i> Report Delay
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Add this modal at the end of your content section -->
                            <div class="modal fade" id="delayModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Report Bus Delay</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Estimated Delay</label>
                                                <select class="form-select" id="delayDuration">
                                                    <option value="5">5 minutes</option>
                                                    <option value="10">10 minutes</option>
                                                    <option value="15">15 minutes</option>
                                                    <option value="20">20 minutes</option>
                                                    <option value="30">30 minutes</option>
                                                    <option value="custom">Custom duration</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3 custom-delay-input d-none">
                                                <label class="form-label">Custom Delay (minutes)</label>
                                                <input type="number" class="form-control" id="customDelayDuration" min="1" max="120">
                                            </div>
                            
                                            <div class="mb-3">
                                                <label class="form-label">Reason for Delay</label>
                                                <select class="form-select" id="delayReason">
                                                    <option value="traffic">Heavy Traffic</option>
                                                    <option value="weather">Bad Weather</option>
                                                    <option value="mechanical">Mechanical Issue</option>
                                                    <option value="accident">Road Accident</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3 other-reason-input d-none">
                                                <label class="form-label">Specify Reason</label>
                                                <input type="text" class="form-control" id="otherDelayReason">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-warning" id="submitDelay">
                                                <i class="ri-send-plane-line me-1"></i> Send Notification
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning border-0">
                        <i class="ri-alert-line me-1"></i> No bus assigned to you. Please contact the administrator.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Today's Attendance Log</h4>
                @if($currentSession)
                    <div class="table-responsive">
                        <table class="table table-centered align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Student</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceLogsTable">
                                <!-- Will be populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-soft-warning border-0">
                        <i class="ri-information-line me-1"></i> Start a bus session to view attendance logs
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    let attendanceRefreshInterval;

    function startAttendanceRefresh() {
        refreshAttendanceLogs();
        attendanceRefreshInterval = setInterval(refreshAttendanceLogs, 1000);
    }

    function stopAttendanceRefresh() {
        clearInterval(attendanceRefreshInterval);
    }

    $('#startSession').click(function() {
        const sessionType = $('input[name="sessionType"]:checked').val();
        $(this).prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Starting...');
        
        $.ajax({
            url: '{{ route("driver.start-session") }}',
            method: 'POST',
            data: {
                bus_id: '{{ $activeBus ? $activeBus->bus_number : "" }}',
                session_type: sessionType,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error',
                    text: xhr.responseJSON.message,
                    icon: 'error'
                });
                $('#startSession').prop('disabled', false)
                    .html('<i class="ri-play-circle-line me-1"></i> Start Bus Session');
            }
        });
    });

    $('#endSession').click(function() {
        Swal.fire({
            title: 'End Session?',
            text: 'Are you sure you want to end this bus session?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, end it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Ending...');
                
                $.ajax({
                    url: '{{ route("driver.end-session") }}',
                    method: 'POST',
                    data: {
                        bus_id: '{{ $activeBus ? $activeBus->bus_number : "" }}',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if(response.success) {
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON.message,
                            icon: 'error'
                        });
                        $('#endSession').prop('disabled', false)
                            .html('<i class="ri-stop-circle-line me-1"></i> End Bus Session');
                    }
                });
            }
        });
    });

    function refreshAttendanceLogs() {
        $.get('{{ route("driver.attendance-logs") }}', function(data) {
            $('#attendanceLogsTable').html(data);
        });
    }

    @if($currentSession)
        startAttendanceRefresh();
    @endif
    $('#delayDuration').change(function() {
        $('.custom-delay-input').toggleClass('d-none', $(this).val() !== 'custom');
    });

    // Handle delay reason selection
    $('#delayReason').change(function() {
        $('.other-reason-input').toggleClass('d-none', $(this).val() !== 'other');
    });

    // Handle delay submission
    $('#submitDelay').click(function() {
        const button = $(this);
        const duration = $('#delayDuration').val() === 'custom' 
            ? $('#customDelayDuration').val() 
            : $('#delayDuration').val();
        
        const reason = $('#delayReason').val() === 'other'
            ? $('#otherDelayReason').val()
            : $('#delayReason option:selected').text();

        if (!duration || (duration === 'custom' && !$('#customDelayDuration').val())) {
            Swal.fire('Error', 'Please specify the delay duration', 'error');
            return;
        }

        if (!reason || (reason === 'other' && !$('#otherDelayReason').val())) {
            Swal.fire('Error', 'Please specify the delay reason', 'error');
            return;
        }

        button.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Sending...');

        $.ajax({
            url: '{{ route("driver.report-delay") }}',
            method: 'POST',
            data: {
                bus_id: '{{ $activeBus ? $activeBus->id : "" }}',
                duration: duration,
                reason: reason,
                session_type: '{{ $currentSession ? $currentSession->session_type : "" }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#delayModal').modal('hide');
                    Swal.fire({
                        title: 'Notification Sent',
                        text: 'Parents have been notified about the delay',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON.message || 'Failed to send notification', 'error');
            },
            complete: function() {
                button.prop('disabled', false)
                    .html('<i class="ri-send-plane-line me-1"></i> Send Notification');
            }
        });
    });
});
</script>

<style>
/* Dark mode specific styles */
.dark-mode .alert-soft-info {
    background-color: rgba(59, 130, 246, 0.1);
    color: #93c5fd;
}

.dark-mode .alert-soft-warning {
    background-color: rgba(245, 158, 11, 0.1);
    color: #fcd34d;
}

.dark-mode .btn-outline-primary {
    border-color: #3b82f6;
    color: #3b82f6;
}

.dark-mode .btn-outline-primary:hover {
    background-color: #3b82f6;
    color: #fff;
}

.dark-mode .badge.bg-soft-primary {
    background-color: rgba(59, 130, 246, 0.1);
    color: #93c5fd;
}

.dark-mode .table-light {
    background-color: rgba(255, 255, 255, 0.05);
}

.dark-mode .bus-info {
    background-color: rgba(255, 255, 255, 0.05);
    padding: 1rem;
    border-radius: 0.5rem;
}
</style>
@endpush