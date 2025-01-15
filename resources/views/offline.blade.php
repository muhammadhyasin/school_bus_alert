@extends('layouts.main')

@section('content')
<div class="container">
    <div class="text-center py-5">
        <div class="avatar-lg mx-auto mb-3">
            <div class="avatar-title bg-light rounded-circle text-primary">
                <i class="ri-wifi-off-line font-size-24"></i>
            </div>
        </div>
        <h3>You're offline</h3>
        <p class="text-muted">Please check your internet connection and try again.</p>
        <button class="btn btn-primary" onclick="window.location.reload()">
            <i class="ri-refresh-line me-1"></i> Retry
        </button>
    </div>
</div>
@endsection