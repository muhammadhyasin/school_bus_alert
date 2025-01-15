@extends('layouts.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Parent Dashboard</h4>
        </div>
    </div>
</div>

@foreach($studentStatuses as $status)
<div class="row mb-4">
    <div class="col-12 mb-3">
        <h5>{{ $status['student_name'] }}</h5>
    </div>

    <!-- Status Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-truncate font-size-14 mb-2">On-Boarding Status</p>
                        <h4 class="mb-2 text-{{ $status['status']['color'] }}">
                            {{ $status['status']['text'] }}
                        </h4>
                        <small class="text-muted">{{ $status['status']['description'] }}</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-light text-{{ $status['status']['color'] }} rounded-3">
                            <i class="{{ $status['status']['icon'] }} font-size-24"></i>  
                        </span>
                    </div>
                </div>                                              
            </div>
        </div>
    </div>
<!-- Notifications Section -->
    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Recent Notifications</h5>
                <button class="btn btn-light btn-sm mark-all-read">
                    <i class="ri-check-double-line me-1"></i> Mark all as read
                </button>
            </div>
        </div>
        
        <div class="col-12">
            <div class="notification-container">
                <!-- Notifications will be dynamically inserted here -->
            </div>
        </div>
    </div>
    <!-- Expected Arrival -->

    <!-- Driver Details -->
    @if(isset($status['bus_details']) && $status['bus_details'])
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-truncate font-size-14 mb-2">Driver Contact</p>
                        <h4 class="mb-2">{{ $status['bus_details']['driver_name'] }}</h4>
                        <a href="tel:{{ $status['bus_details']['driver_phone'] }}" class="text-primary">
                            <i class="ri-phone-line me-1"></i>
                            {{ $status['bus_details']['driver_phone'] }}
                        </a>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-light text-primary rounded-3">
                            <i class="ri-user-line font-size-24"></i>  
                        </span>
                    </div>
                </div>                                              
            </div>
        </div>
    </div>

    <!-- Bus Details -->
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-truncate font-size-14 mb-2">Bus Details</p>
                        <h4 class="mb-2">{{ $status['bus_details']['bus_number'] }}</h4>
                        @if($status['location']['exit_location'])
                            <small class="text-muted">Stop: {{ $status['location']['exit_location'] }}</small>
                        @endif
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-light text-primary rounded-3">
                            <i class="ri-bus-line font-size-24"></i>  
                        </span>
                    </div>
                </div>                                              
            </div>
        </div>
    </div>
    @endif
</div>
@endforeach
<!-- Fees Section -->
<div class="row mt-4">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5>Bus Fees</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentHistoryModal">
                <i class="ri-history-line me-1"></i> Payment History
            </button>
        </div>
    </div>
    <div class="col-12">
        <div class="row" id="feesContainer">
            <!-- Fees cards will be dynamically inserted here -->
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="scheduleId">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="text" class="form-control" id="paymentAmount" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethod">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Pay Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Month</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="paymentHistoryBody">
                            <!-- Payment history will be dynamically inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Cards -->
<div class="row mt-4">
    <div class="col-12 mb-3">
        <h5>Important Contacts</h5>
    </div>
    <!-- Add these audio elements -->
<audio id="missedStopSound" preload="auto">
    <source src="{{ asset('missed_stop.wav') }}" type="audio/wav">
</audio>
<audio id="missedReturnSound" preload="auto">
    <source src="{{ asset('missed_return.wav') }}" type="audio/wav">
</audio>
<audio id="busDelaySound" preload="auto">
    <source src="{{ asset('bus_delay.wav') }}" type="audio/wav">
</audio>
    
    <!-- Teacher Contact -->
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-truncate font-size-14 mb-2">Class Teacher</p>
                        <h4 class="mb-2">Teacher</h4>
                        <a href="tel:1234567899" class="text-primary">
                            <i class="ri-phone-line me-1"></i>
                            {{ config('school.teacher_phone') }}
                        </a>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-light text-primary rounded-3">
                            <i class="ri-user-line font-size-24"></i>  
                        </span>
                    </div>
                </div>                                              
            </div>
        </div>
    </div>

    <!-- School Office -->
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-truncate font-size-14 mb-2">School Office</p>
                        <h4 class="mb-2">Main Office</h4>
                        <a href="tel:123456789" class="text-primary">
                            <i class="ri-phone-line me-1"></i>
                            {{ config('school.office_phone') }}
                        </a>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-light text-primary rounded-3">
                            <i class="ri-building-line font-size-24"></i>  
                        </span>
                    </div>
                </div>                                              
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    preloadSounds();
    // Setup AJAX headers
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    function preloadSounds() {
    const sounds = {
        default: '{{ asset("notify.wav") }}',
        missed_stop: '{{ asset("missed_stop.wav") }}',
        missed_return: '{{ asset("missed_return.wav") }}',
        bus_delay: '{{ asset("bus_delay.wav") }}'
    };

    Object.values(sounds).forEach(soundUrl => {
        const audio = new Audio();
        audio.preload = 'auto';
        audio.src = soundUrl;
    });
}

    // Initialize audio and settings
    const notificationSound = new Audio('{{ asset("notify.wav") }}');
    let lastNotificationIds = [];
    let isPolling = true;

    // Initialize notification settings
    initializeNotificationSettings();
    requestNotificationPermission();

    function initializeNotificationSettings() {
        if (!localStorage.getItem("notificationSound")) {
            localStorage.setItem("notificationSound", "enabled");
        }
    }

    function toggleNotificationSound() {
        const currentState = localStorage.getItem("notificationSound");
        localStorage.setItem(
            "notificationSound",
            currentState === "disabled" ? "enabled" : "disabled"
        );

        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
        });

        Toast.fire({
            icon: "success",
            title: `Notification sound ${
                currentState === "disabled" ? "enabled" : "disabled"
            }`,
        });
    }

    function playNotificationSound(notificationType) {
    if (localStorage.getItem('notificationSound') === 'enabled') {
        let soundUrl;
        
        // Select sound based on notification type
        switch(notificationType) {
            case 'missed_stop':
                soundUrl = '{{ asset("missed_stop.wav") }}';
                break;
            case 'missed_return':
                soundUrl = '{{ asset("missed_return.wav") }}';
                break;
            case 'bus_delay':
                soundUrl = '{{ asset("bus_delay.wav") }}';
                break;
            default:
                soundUrl = '{{ asset("notify.wav") }}';
        }

        const sound = new Audio(soundUrl);
        
        // Play sound with error handling
        const playPromise = sound.play();
        if (playPromise !== undefined) {
            playPromise.catch((error) => {
                console.log("Sound play failed:", error);
                // Try playing on user interaction for iOS
                $(document).one('click touchstart', function() {
                    sound.play().catch(error => 
                        console.log("Sound play failed after interaction:", error)
                    );
                });
            });
        }
    }
}
function isIOS() {
return [
    'iPad Simulator',
    'iPhone Simulator',
    'iPod Simulator',
    'iPad',
    'iPhone',
    'iPod'
].includes(navigator.platform)
// iPad on iOS 13 detection
|| (navigator.userAgent.includes("Mac") && "ontouchend" in document);
}

    function getNotificationIcon(type) {
        const icons = {
            entry: "ri-login-circle-line",

            exit: "ri-logout-circle-line",

            missed_stop: "ri-error-warning-line",

            wrong_stop: "ri-alarm-warning-line",

            session_start: "ri-play-circle-line",

            session_end: "ri-stop-circle-line",
        };

        return icons[type] || "ri-notification-line";
    }

    function getNotificationBgColor(type) {
        const colors = {
            entry: "bg-success",

            exit: "bg-info",

            missed_stop: "bg-danger",

            wrong_stop: "bg-danger",

            session_start: "bg-primary",

            session_end: "bg-warning",
        };

        return colors[type] || "bg-secondary";
    }

    function getNotificationTitle(type) {
        const titles = {
            entry: "Pick Up Alert",

            exit: "Drop Off Alert",

            missed_stop: "Missed Stop Alert",

            wrong_stop: "Wrong Stop Alert",

            session_start: "Session Started",

            session_end: "Session Ended",
        };

        return titles[type] || "Notification";
    }

    function showBrowserNotification(notification) {
        if (Notification.permission === "granted") {
            const title = getNotificationTitle(notification.type);
            const options = {
                body: notification.message,
                icon: "/images/notification-icon.png",
                badge: "/images/notification-badge.png",
                tag: `notification-${notification.id}`,
                requireInteraction: ["missed_stop", "wrong_stop"].includes(
                    notification.type
                ),
                silent: false,
            };

            const browserNotification = new Notification(title, options);

            browserNotification.onclick = function () {
                window.focus();
                browserNotification.close();
                handleNotificationClick(notification);
            };
        }
    }

    function handleNotificationClick(notification) {
        markNotificationAsRead(notification.id);

        if (["missed_stop", "wrong_stop"].includes(notification.type)) {
            showCriticalAlert(notification);
        }
    }

    function requestNotificationPermission() {
        
        if (!("Notification" in window)) {
            Swal.fire({
                title: "Browser Not Supported",
                text: "Your browser does not support desktop notifications",
                icon: "error",
            });
            return;
        }

        if (Notification.permission === "denied") {
            Swal.fire({
                title: "Notifications Blocked",
                text: "Please enable notifications in your browser settings",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Open Settings",
                cancelButtonText: "Later",
            }).then((result) => {
                if (result.isConfirmed) {
                    // Open browser settings if possible
                    if (navigator.permissions) {
                        navigator.permissions
                            .query({ name: "notifications" })
                            .then((permission) => {
                                if (permission.state === "denied") {
                                    window.open("about:settings");
                                }
                            });
                    }
                }
            });
            return;
        }

        if (Notification.permission === "default") {
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
                            Swal.fire({
                                title: "Enabled!",
                                text: "You will now receive notifications",
                                icon: "success",
                                timer: 2000,
                                showConfirmButton: false,
                            });
                        }
                    });
                }
            });
        }
    }

    function markNotificationAsRead(id) {
        return $.ajax({
            url: `/parent/notifications/${id}/read`,
            type: "POST",
        })
            .then(function (response) {
                if (response.success) {
                    $(`.notification-item[data-id="${id}"]`).fadeOut(
                        300,
                        function () {
                            $(this).remove();
                            updateNotificationCount();
                        }
                    );

                    // Remove from lastNotificationIds
                    lastNotificationIds = lastNotificationIds.filter(
                        (nId) => nId !== id
                    );

                    const Toast = Swal.mixin({
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 2000,
                    });

                    Toast.fire({
                        icon: "success",
                        title: "Notification marked as read",
                    });
                }
                return response;
            })
            .catch(function (error) {
                console.error("Error marking notification as read:", error);
                Swal.fire({
                    title: "Error",
                    text: "Failed to mark notification as read",
                    icon: "error",
                    timer: 2000,
                    showConfirmButton: false,
                });
            });
    }

    function markAllNotificationsAsRead() {
        return $.ajax({
            url: '{{ route("parent.notifications.readAll") }}',
            type: "POST",
        })
            .then(function (response) {
                if (response.success) {
                    $(".notification-item").fadeOut(300, function () {
                        $(this).remove();
                        updateNotificationCount();
                    });

                    lastNotificationIds = [];

                    const Toast = Swal.mixin({
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 2000,
                    });

                    Toast.fire({
                        icon: "success",
                        title: "All notifications marked as read",
                    });
                }
            })
            .catch(function (error) {
                console.error(
                    "Error marking all notifications as read:",
                    error
                );
                Swal.fire({
                    title: "Error",
                    text: "Failed to mark all notifications as read",
                    icon: "error",
                    timer: 2000,
                    showConfirmButton: false,
                });
            });
    }

    function showCriticalAlert(notification) {
        Swal.fire({
            title: getNotificationTitle(notification.type),
            text: notification.message,
            icon: "warning",
            confirmButtonColor: "#d33",
            confirmButtonText: "Acknowledge",
            showCancelButton: true,
            cancelButtonText: "Dismiss",
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                markNotificationAsRead(notification.id).then(() => {
                    Swal.fire({
                        title: "Acknowledged",
                        text: "Alert has been marked as read",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                });
            }
        });
    }

    function updateNotificationCount() {
        const count = $(".notification-item:visible").length;
        $(".notification-counter").text(count > 0 ? count : "");

        if (count === 0) {
            $(".notification-list").html(`
                <div class="text-center py-4">
                    <div class="avatar-md mx-auto mb-3">
                        <div class="avatar-title bg-light rounded-circle text-primary">
                            <i class="ri-notification-line font-size-24"></i>
                        </div>
                    </div>
                    <h5 class="text-muted">No notifications</h5>
                </div>
            `);
        }
    }

    function addNotificationToUI(notification) {
    const icon = getNotificationIcon(notification.type);
    const bgColor = getNotificationBgColor(notification.type);
    const textColor = bgColor.replace('bg-', 'text-');
    
    const notificationHtml = `
        <div class="col-xl-4 col-md-6 notification-item" data-id="${notification.id}">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <span class="avatar-title ${bgColor} rounded-3">
                                    <i class="${icon} font-size-24"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-2 ${textColor}">${getNotificationTitle(notification.type)}</h5>
                            <p class="text-muted mb-2">${notification.message}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="mdi mdi-clock-outline"></i> ${notification.time}
                                </small>
                                <button class="btn btn-sm btn-light mark-read" onclick="markNotificationAsRead('${notification.id}')">
                                    <i class="ri-check-line"></i> Mark as read
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('.notification-container').prepend(notificationHtml);
}

// Update the empty state function
function showEmptyState() {
    $('.notification-container').html(`
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="avatar-title bg-light rounded-circle text-primary">
                            <i class="ri-notification-line font-size-32"></i>
                        </div>
                    </div>
                    <h5 class="text-muted">No notifications</h5>
                    <p class="text-muted mb-0">You're all caught up!</p>
                </div>
            </div>
        </div>
    `);
}

// Update the refreshNotifications function
function refreshNotifications() {
    if (!isPolling) return;
    
    $.get('{{ route("parent.notifications") }}')
        .done(function(response) {
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
                        playNotificationSound('missed_stop');
                    } else {
                        // Check for other notification types that need sounds
                        newNotifications.forEach(notification => {
                            if (notification.type === 'missed_return') {
                                playNotificationSound('missed_return');
                            } else if (notification.type === 'bus_delay') {
                                playNotificationSound('bus_delay');
                            }
                        });
                    }
                    
                    newNotifications.forEach(notification => {
                        // Show browser notification for all new notifications
                        showBrowserNotification(notification);
                        
                        // Show SweetAlert only for critical notifications
                        if (notification.type === 'missed_stop' || notification.type === 'wrong_stop') {
                            showCriticalAlert(notification);
                        }
                    });

                    // Update UI
                    $('.notification-container').empty();
                    response.notifications.forEach(notification => {
                        addNotificationToUI(notification);
                    });
                } else if (response.notifications.length === 0) {
                    showEmptyState();
                }

                // Update lastNotificationIds with current notification IDs
                lastNotificationIds = response.notifications.map(n => n.id);
            }
        })
        .fail(function(error) {
            console.error('Notification fetch failed:', error);
        });
}

// Initialize with 1-second interval
refreshNotifications();
const pollingInterval = setInterval(refreshNotifications, 1000);

// Cleanup
$(window).on("beforeunload", function() {
    clearInterval(pollingInterval);
});

// Handle visibility
document.addEventListener("visibilitychange", function() {
    isPolling = !document.hidden;
    if (isPolling) refreshNotifications();
});



    // Event handlers
    $(document).on("click", ".mark-all-read", function (e) {
        e.preventDefault();
        markAllNotificationsAsRead();
    });

    // Visibility change handling
    document.addEventListener("visibilitychange", function () {
        if (document.hidden) {
            isPolling = false;
        } else {
            isPolling = true;
            refreshNotifications();
        }
    });

    

    // Cleanup on page unload
    $(window).on("beforeunload", function () {
        clearInterval(pollingInterval);
    });

    $(document).on("click", ".notification-item", function() {
    const notificationId = $(this).data("id");
    markNotificationAsRead(notificationId);
    });

    $(document).on("click", ".mark-all-read", function(e) {
        e.preventDefault();
        markAllNotificationsAsRead();
    });
    // Add this at the end of your existing script
$(document).ready(function() {
    // Load fees initially
    loadFees();

    // Handle payment button click
    $(document).on('click', '.pay-fees', function() {
        const scheduleId = $(this).data('id');
        const amount = $(this).data('amount');

        $('#scheduleId').val(scheduleId);
        $('#paymentAmount').val(amount);
        $('#paymentModal').modal('show');
    });

    // Handle payment form submission
    $('#paymentForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true)
            .html('<i class="ri-loader-4-line ri-spin me-1"></i> Processing...');

        $.ajax({
            url: '{{ route("parent.fees.pay") }}',
            method: 'POST',
            data: {
                schedule_id: $('#scheduleId').val(),
                payment_method: $('#paymentMethod').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#paymentModal').modal('hide');
                    Swal.fire({
                        title: 'Success!',
                        text: 'Payment processed successfully',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadFees(); // Reload fees after payment
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to process payment', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('Pay Now');
            }
        });
    });
});
function loadFees() {
    $.get('{{ route("parent.fees") }}', function(response) {
        if (response.success) {
            const feesContainer = $('#feesContainer');
            feesContainer.empty();

            if (response.feeSchedules.length === 0) {
                feesContainer.html(`
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <div class="avatar-lg mx-auto mb-3">
                                    <div class="avatar-title bg-light rounded-circle text-primary">
                                        <i class="ri-money-dollar-circle-line font-size-32"></i>
                                    </div>
                                </div>
                                <h5 class="text-muted">No fees scheduled</h5>
                            </div>
                        </div>
                    </div>
                `);
                return;
            }

            response.feeSchedules.forEach(schedule => {
                const card = `
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">${schedule.month} ${schedule.year}</h5>
                                    <span class="badge bg-${getBadgeColor(schedule.status)}">
                                        ${schedule.status.toUpperCase()}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Amount</p>
                                    <h4>₹${schedule.amount}</h4>
                                </div>
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Due Date</p>
                                    <h6>${schedule.due_date}</h6>
                                </div>
                                ${schedule.status === 'pending' ? `
                                    <button class="btn btn-primary w-100 pay-fees" 
                                            data-id="${schedule.id}"
                                            data-amount="${schedule.amount}">
                                        <i class="ri-bank-card-line me-1"></i> Pay Now
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
                feesContainer.append(card);
            });

            // Update payment history
            const historyBody = $('#paymentHistoryBody');
            historyBody.empty();

            if (response.payments.length === 0) {
                historyBody.html(`
                    <tr>
                        <td colspan="5" class="text-center py-4">No payment history available</td>
                    </tr>
                `);
                return;
            }

            response.payments.forEach(payment => {
                const row = `
                    <tr>
                        <td>${new Date(payment.paid_at).toLocaleDateString()}</td>
                        <td>${payment.fee_schedule.month} ${payment.fee_schedule.year}</td>
                        <td>₹${payment.amount_paid}</td>
                        <td>${payment.payment_method.toUpperCase()}</td>
                        <td>
                            <span class="badge bg-success">
                                ${payment.status.toUpperCase()}
                            </span>
                        </td>
                    </tr>
                `;
                historyBody.append(row);
            });
        }
    });
}

function getBadgeColor(status) {
    switch(status) {
        case 'paid': return 'success';
        case 'pending': return 'warning';
        case 'overdue': return 'danger';
        default: return 'secondary';
    }
}

});

</script>
@endpush