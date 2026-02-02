@extends('layouts.vertical', ['title' => 'Withdrawal Restricted'])

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-danger">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <iconify-icon icon="mdi:lock-off" class="text-danger" style="font-size: 80px;"></iconify-icon>
                    </div>
                    <h3 class="text-danger mb-3">Withdrawal Access Restricted</h3>
                    <p class="text-muted mb-4">
                        Your withdrawal access has been restricted. If you believe this is an error, 
                        please contact our support team for assistance.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <iconify-icon icon="mdi:home" class="me-2"></iconify-icon>
                            Return to Dashboard
                        </a>
                        <a href="{{ route('support.index') }}" class="btn btn-primary">
                            <iconify-icon icon="mdi:headset" class="me-2"></iconify-icon>
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
