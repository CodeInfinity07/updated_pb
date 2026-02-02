@extends('layouts.vertical', ['title' => 'KYC Verification', 'subTitle' => 'Identity Verification'])

@section('content')
    <div class="container-fluid">

        <!-- Status Header Card -->
        <div class="row mb-3 mb-md-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-8">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 me-md-4">
                                        @switch($kycStatus)
                                            @case('pending')
                                                <div class="avatar-md avatar-lg bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="iconamoon:shield-duotone" class="fs-24 fs-md-32 text-warning"></iconify-icon>
                                                </div>
                                            @break
                                            @case('session_created')
                                                <div class="avatar-md avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="iconamoon:shield-duotone" class="fs-24 fs-md-32 text-info"></iconify-icon>
                                                </div>
                                            @break
                                            @case('submitted')
                                                <div class="avatar-md avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="iconamoon:shield-duotone" class="fs-24 fs-md-32 text-info"></iconify-icon>
                                                </div>
                                            @break
                                            @case('under_review')
                                                <div class="avatar-md avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="iconamoon:shield-duotone" class="fs-24 fs-md-32 text-info"></iconify-icon>
                                                </div>
                                            @break
                                            @case('verified')
                                                <div class="avatar-md avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="iconamoon:shield-duotone" class="fs-24 fs-md-32 text-success"></iconify-icon>
                                                </div>
                                            @break
                                            @case('rejected')
                                                <div class="avatar-md avatar-lg bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                    <iconify-icon icon="iconamoon:shield-off-duotone" class="fs-24 fs-md-32 text-danger"></iconify-icon>
                                                </div>
                                            @break
                                        @endswitch
                                    </div>
                                    <div>
                                        <h4 class="mb-2 fs-18 fs-md-24">KYC Verification Status</h4>
                                        <p class="text-muted mb-2 mb-md-3 small">Complete your identity verification to unlock all platform features</p>
                                        @switch($kycStatus)
                                            @case('pending')
                                                <span class="badge bg-warning-subtle text-warning fs-11 fs-md-12 px-2 px-md-3 py-1 py-md-2 rounded-pill">
                                                    Verification Required
                                                </span>
                                            @break
                                            @case('session_created')
                                                <span class="badge bg-info-subtle text-info fs-11 fs-md-12 px-2 px-md-3 py-1 py-md-2 rounded-pill">
                                                    Session Ready - Complete Verification
                                                </span>
                                            @break
                                            @case('submitted')
                                                <span class="badge bg-info-subtle text-info fs-11 fs-md-12 px-2 px-md-3 py-1 py-md-2 rounded-pill">
                                                    Documents Submitted
                                                </span>
                                            @break
                                            @case('under_review')
                                                <span class="badge bg-info-subtle text-info fs-11 fs-md-12 px-2 px-md-3 py-1 py-md-2 rounded-pill">
                                                    Under Review
                                                </span>
                                            @break
                                            @case('verified')
                                                <span class="badge bg-success-subtle text-success fs-11 fs-md-12 px-2 px-md-3 py-1 py-md-2 rounded-pill">
                                                    Verified
                                                </span>
                                            @break
                                            @case('rejected')
                                                <span class="badge bg-danger-subtle text-danger fs-11 fs-md-12 px-2 px-md-3 py-1 py-md-2 rounded-pill">
                                                    Verification Failed
                                                </span>
                                            @break
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 text-start text-lg-end mt-3 mt-lg-0">
                                @if ($kycStatus == 'verified' && $user->profile?->kyc_verified_at)
                                    <small class="text-muted d-block">
                                        <iconify-icon icon="iconamoon:calendar-duotone" class="me-1"></iconify-icon>
                                        Verified {{ $user->profile->kyc_verified_at->diffForHumans() }}
                                    </small>
                                    <small class="text-muted">
                                        {{ $user->profile->kyc_verified_at->format('M d, Y \a\t g:i A') }}
                                    </small>
                                @elseif(in_array($kycStatus, ['submitted', 'under_review']) && $user->profile?->kyc_submitted_at)
                                    <small class="text-muted d-block">
                                        <iconify-icon icon="iconamoon:shield-duotone" class="me-1"></iconify-icon>
                                        Submitted {{ $user->profile->kyc_submitted_at->diffForHumans() }}
                                    </small>
                                    <small class="text-muted">
                                        {{ $user->profile->kyc_submitted_at->format('M d, Y \a\t g:i A') }}
                                    </small>
                                @elseif($kycStatus == 'session_created' && $user->profile?->kyc_session_created_at)
                                    <small class="text-muted d-block">
                                        <iconify-icon icon="iconamoon:shield-duotone" class="me-1"></iconify-icon>
                                        Session created {{ $user->profile->kyc_session_created_at->diffForHumans() }}
                                    </small>
                                    <small class="text-muted">
                                        {{ $user->profile->kyc_session_created_at->format('M d, Y \a\t g:i A') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Based on Status -->
        @if (in_array($kycStatus, ['pending', 'rejected', 'session_created']))
            <!-- Start Verification Section -->
            <div class="row">
                <div class="col-12 col-lg-8 mb-3 mb-lg-0">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-primary text-white">
                            <h5 class="card-title mb-0 text-white fs-16 fs-md-18">
                                <iconify-icon icon="material-symbols:shield-outline-rounded" class="me-2"></iconify-icon>
                                Identity Verification
                            </h5>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            @if ($kycStatus == 'rejected')
                                <div class="alert alert-danger border-0 mb-3 mb-md-4" role="alert">
                                    <div class="d-flex">
                                        <iconify-icon icon="material-symbols:warning" class="me-2 me-md-3 mt-1 fs-18 fs-md-20 flex-shrink-0"></iconify-icon>
                                        <div>
                                            <h6 class="alert-heading mb-1 fs-14 fs-md-16">Verification Unsuccessful</h6>
                                            <p class="mb-0 small">
                                                {{ $user->profile?->kyc_rejection_reason ?? 'Your previous verification attempt was unsuccessful. Please ensure your documents are clear and valid, then try again.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($kycStatus == 'session_created')
                                <div class="alert alert-info border-0 mb-3 mb-md-4" role="alert">
                                    <div class="d-flex">
                                        <iconify-icon icon="iconamoon:info-circle-duotone" class="me-2 me-md-3 mt-1 fs-18 fs-md-20 flex-shrink-0"></iconify-icon>
                                        <div>
                                            <h6 class="alert-heading mb-1 fs-14 fs-md-16">Verification Session Ready</h6>
                                            <p class="mb-0 small">
                                                You have an active verification session. Click "Continue Verification" to proceed with document submission.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(($kycMode ?? 'veriff') === 'manual')
                                {{-- Manual KYC Document Upload Form --}}
                                <div class="py-3 py-md-4">
                                    <div class="text-center mb-4">
                                        <div class="mb-3">
                                            <iconify-icon icon="material-symbols:upload-file-outline" class="text-primary" style="font-size: 3rem;"></iconify-icon>
                                        </div>
                                        <h2 class="mb-2 fs-20 fs-md-28">Upload Your Documents</h2>
                                        <p class="text-muted fs-14 fs-md-16 px-2 px-lg-4">
                                            Please upload clear photos of your government-issued ID document for verification.
                                        </p>
                                    </div>

                                    <form id="manual-kyc-form" enctype="multipart/form-data">
                                        @csrf
                                        
                                        <div class="row g-3 mb-4">
                                            <div class="col-12">
                                                <label for="document_type" class="form-label fw-semibold">Document Type <span class="text-danger">*</span></label>
                                                <select class="form-select" id="document_type" name="document_type" required>
                                                    <option value="">Select document type...</option>
                                                    <option value="passport">Passport</option>
                                                    <option value="id_card">National ID Card</option>
                                                    <option value="drivers_license">Driver's License</option>
                                                </select>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="front_document" class="form-label fw-semibold">Front of Document <span class="text-danger">*</span></label>
                                                <div class="upload-area border border-dashed rounded p-3 text-center position-relative" id="front-upload-area">
                                                    <input type="file" class="position-absolute w-100 h-100 opacity-0" style="top:0;left:0;cursor:pointer;" 
                                                           id="front_document" name="front_document" accept="image/*" required>
                                                    <iconify-icon icon="material-symbols:cloud-upload-outline" class="fs-32 text-muted mb-2 d-block" id="front-icon"></iconify-icon>
                                                    <p class="mb-1 small text-muted" id="front-file-name">Click to upload or drag and drop</p>
                                                    <small class="text-muted" id="front-file-hint">PNG, JPG up to 5MB</small>
                                                </div>
                                                <img id="front-preview" class="img-fluid rounded mt-2 d-none" style="max-height: 150px;">
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="back_document" class="form-label fw-semibold">Back of Document <span class="text-muted">(if applicable)</span></label>
                                                <div class="upload-area border border-dashed rounded p-3 text-center position-relative" id="back-upload-area">
                                                    <input type="file" class="position-absolute w-100 h-100 opacity-0" style="top:0;left:0;cursor:pointer;" 
                                                           id="back_document" name="back_document" accept="image/*">
                                                    <iconify-icon icon="material-symbols:cloud-upload-outline" class="fs-32 text-muted mb-2 d-block" id="back-icon"></iconify-icon>
                                                    <p class="mb-1 small text-muted" id="back-file-name">Click to upload or drag and drop</p>
                                                    <small class="text-muted" id="back-file-hint">PNG, JPG up to 5MB</small>
                                                </div>
                                                <img id="back-preview" class="img-fluid rounded mt-2 d-none" style="max-height: 150px;">
                                            </div>

                                            <div class="col-12">
                                                <label for="selfie" class="form-label fw-semibold">Selfie with Document <span class="text-muted">(optional)</span></label>
                                                <div class="upload-area border border-dashed rounded p-3 text-center position-relative" id="selfie-upload-area">
                                                    <input type="file" class="position-absolute w-100 h-100 opacity-0" style="top:0;left:0;cursor:pointer;" 
                                                           id="selfie" name="selfie" accept="image/*">
                                                    <iconify-icon icon="material-symbols:face-outline" class="fs-32 text-muted mb-2 d-block" id="selfie-icon"></iconify-icon>
                                                    <p class="mb-1 small text-muted" id="selfie-file-name">Click to upload or drag and drop</p>
                                                    <small class="text-muted" id="selfie-file-hint">Hold your document next to your face</small>
                                                </div>
                                                <img id="selfie-preview" class="img-fluid rounded mt-2 d-none" style="max-height: 150px;">
                                            </div>
                                        </div>

                                        <div class="alert alert-info border-0 mb-4">
                                            <div class="d-flex">
                                                <iconify-icon icon="iconamoon:info-circle-duotone" class="me-2 mt-1 flex-shrink-0"></iconify-icon>
                                                <small>
                                                    A verification fee of <strong>$1.00</strong> will be deducted from your account upon submission.
                                                    Your documents will be reviewed by our team within 24-48 hours.
                                                </small>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" id="submit-manual-kyc-btn" class="btn btn-primary btn-lg px-5 py-2">
                                                <iconify-icon icon="iconamoon:upload-duotone" class="me-2"></iconify-icon>
                                                Submit Documents
                                            </button>
                                        </div>
                                    </form>

                                    <div class="mt-4 text-center">
                                        <div class="d-inline-flex align-items-center bg-light rounded-pill px-3 px-md-4 py-2">
                                            <iconify-icon icon="iconamoon:lock-duotone" class="me-2 text-success"></iconify-icon>
                                            <small class="text-muted mb-0">Your documents are encrypted and secure</small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Veriff Automated Verification --}}
                                <div class="text-center py-3 py-md-4">
                                    <div class="mb-3 mb-md-4">
                                        <iconify-icon icon="material-symbols:shield-outline-rounded" class="text-primary" style="font-size: 3rem;"></iconify-icon>
                                    </div>

                                    <h2 class="mb-2 mb-md-3 fs-20 fs-md-28">
                                        @if($kycStatus == 'session_created')
                                            Continue Your Verification
                                        @else
                                            Verify Your Identity
                                        @endif
                                    </h2>
                                    <p class="text-muted mb-3 mb-md-5 fs-14 fs-md-16 px-2 px-lg-4">
                                        @if($kycStatus == 'session_created')
                                            Your verification session is ready. Continue where you left off to complete your identity verification.
                                        @else
                                            We use secure identity verification. The process is quick, safe, and takes just a few minutes to complete.
                                        @endif
                                    </p>

                                    @if($kycStatus != 'session_created')
                                        <!-- Process Steps -->
                                        <div class="row justify-content-center mb-3 mb-md-5">
                                            <div class="col-12">
                                                <div class="row g-3 g-md-4">
                                                    <div class="col-12 col-md-4">
                                                        <div class="text-center position-relative">
                                                            <div class="avatar-lg avatar-xl bg-primary-subtle rounded-circle mx-auto mb-2 mb-md-3 d-flex align-items-center justify-content-center position-relative">
                                                                <iconify-icon icon="streamline:user-identifier-card" class="fs-24 fs-md-32 text-primary"></iconify-icon>
                                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary fs-11">1</span>
                                                            </div>
                                                            <h6 class="mb-1 mb-md-2 fs-14 fs-md-16">Prepare Your ID</h6>
                                                            <small class="text-muted d-block px-2">Have your government-issued ID ready</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="text-center position-relative">
                                                            <div class="avatar-lg avatar-xl bg-warning-subtle rounded-circle mx-auto mb-2 mb-md-3 d-flex align-items-center justify-content-center position-relative">
                                                                <iconify-icon icon="material-symbols:add-a-photo-outline-sharp" class="fs-24 fs-md-32 text-warning"></iconify-icon>
                                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning fs-11">2</span>
                                                            </div>
                                                            <h6 class="mb-1 mb-md-2 fs-14 fs-md-16">Take Photos</h6>
                                                            <small class="text-muted d-block px-2">Capture clear photos of your document</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="text-center position-relative">
                                                            <div class="avatar-lg avatar-xl bg-success-subtle rounded-circle mx-auto mb-2 mb-md-3 d-flex align-items-center justify-content-center position-relative">
                                                                <iconify-icon icon="solar:verified-check-bold" class="fs-24 fs-md-32 text-success"></iconify-icon>
                                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success fs-11">3</span>
                                                            </div>
                                                            <h6 class="mb-1 mb-md-2 fs-14 fs-md-16">Get Verified</h6>
                                                            <small class="text-muted d-block px-2">Receive instant verification</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Start/Continue Button -->
                                    <div class="d-flex justify-content-center gap-2 gap-md-3 flex-wrap">
                                        <button type="button" id="start-verification-btn" class="btn btn-primary btn-lg px-4 px-md-5 py-2 py-md-3" onclick="handleStartVerification('popup')">
                                            @if($kycStatus == 'session_created')
                                                <iconify-icon icon="iconamoon:arrow-right-2-duotone" class="me-2"></iconify-icon>
                                                Continue Verification
                                            @else
                                                <iconify-icon icon="iconamoon:play-duotone" class="me-2"></iconify-icon>
                                                Start Verification
                                            @endif
                                        </button>
                                    </div>

                                    @if($kycStatus == 'session_created')
                                        <!-- Cancel Session Option -->
                                        <div class="mt-2 mt-md-3">
                                            <button type="button" id="cancel-session-btn" class="btn btn-outline-secondary btn-sm">
                                                Start Over
                                            </button>
                                        </div>
                                    @endif

                                    <!-- Security Note -->
                                    <div class="mt-3 mt-md-4">
                                        <div class="d-inline-flex align-items-center bg-light rounded-pill px-3 px-md-4 py-2">
                                            <iconify-icon icon="iconamoon:lock-duotone" class="me-2 text-success"></iconify-icon>
                                            <small class="text-muted mb-0">Your data is encrypted and secure</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Requirements Sidebar -->
                <div class="col-12 col-lg-4">
                    <!-- Requirements Card -->
                    <div class="card shadow-sm border-0 mb-3 mb-md-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0 fs-14 fs-md-16">
                                <iconify-icon icon="iconamoon:info-circle-duotone" class="me-2"></iconify-icon>
                                What You'll Need
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex mb-3 pb-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-primary-subtle rounded d-flex align-items-center justify-content-center">
                                        <iconify-icon icon="ri:pass-valid-line" class="fs-16 fs-md-18 text-primary"></iconify-icon>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fs-13 fs-md-14">Valid ID Document</h6>
                                    <small class="text-muted">Passport, Driver's License, or National ID Card</small>
                                </div>
                            </div>
                            <div class="d-flex mb-3 pb-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-primary-subtle rounded d-flex align-items-center justify-content-center">
                                        <iconify-icon icon="material-symbols:android-camera-outline" class="fs-16 fs-md-18 text-primary"></iconify-icon>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fs-13 fs-md-14">Device Camera</h6>
                                    <small class="text-muted">For taking clear photos of your document</small>
                                </div>
                            </div>
                            <div class="d-flex mb-3 pb-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-primary-subtle rounded d-flex align-items-center justify-content-center">
                                        <iconify-icon icon="material-symbols:network-wifi-3-bar-rounded" class="fs-16 fs-md-18 text-primary"></iconify-icon>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fs-13 fs-md-14">Stable Internet</h6>
                                    <small class="text-muted">Good connection for uploading photos</small>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-primary-subtle rounded d-flex align-items-center justify-content-center">
                                        <iconify-icon icon="iconamoon:shield-duotone" class="fs-16 fs-md-18 text-primary"></iconify-icon>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fs-13 fs-md-14">5-10 Minutes</h6>
                                    <small class="text-muted">Quick and easy process</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Benefits Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0 fs-14 fs-md-16">
                                <iconify-icon icon="iconamoon:star-duotone" class="me-2"></iconify-icon>
                                Benefits After Verification
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <iconify-icon icon="material-symbols:wallet-sharp" class="fs-14 fs-md-16 text-success me-3"></iconify-icon>
                                <small class="text-muted mb-0">Unlimited deposits</small>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <iconify-icon icon="gis:search-feature" class="fs-14 fs-md-16 text-success me-3"></iconify-icon>
                                <small class="text-muted mb-0">Premium features access</small>
                            </div>

                            <div class="d-flex align-items-center">
                                <iconify-icon icon="material-symbols:shield-outline-rounded" class="fs-14 fs-md-16 text-success me-3"></iconify-icon>
                                <small class="text-muted mb-0">Enhanced security</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @elseif(in_array($kycStatus, ['submitted', 'under_review']))
            <!-- Under Review Section -->
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center py-4 py-md-5 px-3">
                            <!-- Animated Processing Icon -->
                            <div class="mb-3 mb-md-4">
                                <div class="position-relative d-inline-block">
                                    <iconify-icon icon="iconamoon:shield-duotone" class="text-info" style="font-size: 3.5rem;"></iconify-icon>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <div class="spinner-border text-info" style="width: 2rem; height: 2rem;" role="status">
                                            <span class="visually-hidden">Processing...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Title and Description -->
                            <h2 class="text-info mb-2 mb-md-3 fs-20 fs-md-28">
                                @if($kycStatus == 'submitted')
                                    Documents Submitted Successfully!
                                @else
                                    Verification in Progress
                                @endif
                            </h2>
                            <p class="text-muted mb-3 mb-md-5 fs-14 fs-md-16 lh-base px-2 px-lg-5">
                                @if($kycStatus == 'submitted')
                                    Your documents have been submitted successfully and our verification team will begin reviewing them shortly.
                                @else
                                    We're currently reviewing your submitted documents. This process is usually completed within minutes, but may take up to 24 hours during peak times.
                                @endif
                            </p>

                            <!-- Processing Timeline -->
                            <div class="row justify-content-center mb-3 mb-md-5">
                                <div class="col-12 col-lg-8">
                                    <div class="card bg-info-subtle border-0">
                                        <div class="card-body p-3 p-md-4">
                                            <div class="row text-center g-2 g-md-3">
                                                <div class="col-4">
                                                    <iconify-icon icon="material-symbols:bookmark-check" class="fs-24 fs-md-32 text-success mb-2"></iconify-icon>
                                                    <h6 class="mb-1 text-success fs-13 fs-md-16">Submitted</h6>
                                                    <small class="text-muted d-none d-md-block">Documents received</small>
                                                </div>
                                                <div class="col-4">
                                                    <iconify-icon icon="iconamoon:shield-duotone" class="fs-24 fs-md-32 text-info mb-2"></iconify-icon>
                                                    <h6 class="mb-1 text-info fs-13 fs-md-16">Processing</h6>
                                                    <small class="text-muted d-none d-md-block">Under review</small>
                                                </div>
                                                <div class="col-4">
                                                    <iconify-icon icon="material-symbols:shield-outline-rounded" class="fs-24 fs-md-32 text-muted mb-2"></iconify-icon>
                                                    <h6 class="mb-1 text-muted fs-13 fs-md-16">Complete</h6>
                                                    <small class="text-muted d-none d-md-block">Verification done</small>
                                                </div>
                                            </div>
                                            
                                            <!-- Progress Bar -->
                                            <div class="progress mt-3 mt-md-4" style="height: 6px;">
                                                <div class="progress-bar bg-info progress-bar-animated progress-bar-striped" 
                                                     role="progressbar" style="width: 65%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estimated Time Card -->
                            <div class="row justify-content-center mb-3 mb-md-4">
                                <div class="col-12 col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body text-center p-3">
                                            <iconify-icon icon="iconamoon:shield-duotone" class="fs-36 fs-md-48 text-info mb-2 mb-md-3"></iconify-icon>
                                            <h6 class="mb-2 fs-14 fs-md-16">Estimated Processing Time</h6>
                                            <h5 class="mb-2 text-info fs-16 fs-md-20">5 minutes - 24 hours</h5>
                                            <small class="text-muted">We'll email you when verification is complete</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Session Info -->
                            @if($user->profile?->kyc_session_id)
                                <div class="alert alert-info border-0 d-inline-block mb-3 mb-md-4 text-break" role="alert">
                                    <iconify-icon icon="iconamoon:info-circle-duotone" class="me-2"></iconify-icon>
                                    <strong>Session ID:</strong> <span class="small">{{ $user->profile->kyc_session_id }}</span>
                                </div>
                            @endif

                            <!-- Auto-refresh Notice -->
                            <div class="alert alert-info border-0 d-inline-block" role="alert">
                                <iconify-icon icon="material-symbols:refresh-rounded" class="me-2"></iconify-icon>
                                <span class="small">This page will automatically update when verification is complete</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- Verified Section -->
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center py-4 py-md-5 px-3">
                            <!-- Success Icon -->
                            <div class="mb-3 mb-md-4">
                                <iconify-icon icon="material-symbols:shield-outline-rounded" class="text-success" style="font-size: 3rem;"></iconify-icon>
                            </div>

                            <!-- Title and Description -->
                            <h1 class="text-success mb-2 mb-md-3 fs-22 fs-md-32">Identity Verified Successfully!</h1>
                            <p class="text-muted mb-3 mb-md-5 fs-14 fs-md-16 lh-base px-2 px-lg-5">
                                Congratulations! Your identity has been verified successfully. You now have full access to all platform features, higher transaction limits, and enhanced security features.
                            </p>

                            <!-- Benefits Grid -->
                            <div class="row justify-content-center mb-3 mb-md-5">
                                <div class="col-12 col-lg-8">
                                    <div class="row g-3 g-md-4">
                                        <div class="col-12 col-md-6">
                                            <div class="card bg-success-subtle border-0 h-100">
                                                <div class="card-body text-center p-3 p-md-4">
                                                    <iconify-icon icon="material-symbols:wallet-sharp" class="fs-36 fs-md-48 text-success mb-2 mb-md-3"></iconify-icon>
                                                    <h6 class="mb-2 fs-14 fs-md-16">Unlimited Deposits</h6>
                                                    <small class="text-muted">No limits on deposit amounts</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="card bg-success-subtle border-0 h-100">
                                                <div class="card-body text-center p-3 p-md-4">
                                                    <iconify-icon icon="gis:search-feature" class="fs-36 fs-md-48 text-success mb-2 mb-md-3"></iconify-icon>
                                                    <h6 class="mb-2 fs-14 fs-md-16">Premium Features</h6>
                                                    <small class="text-muted">Access to advanced features</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-3 px-md-4 py-2 py-md-3">
                                    Make a Deposit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Alert Container -->
        <div id="alert-container" class="position-fixed" style="top: 20px; right: 20px; left: 20px; z-index: 9999; max-width: 400px; margin: 0 auto;"></div>
    </div>

    <style>
        @media (min-width: 768px) {
            #alert-container {
                left: auto;
                margin: 0;
            }
        }
        
        .avatar-md {
            width: 3rem;
            height: 3rem;
        }
        
        @media (min-width: 768px) {
            .avatar-md {
                width: 3.5rem;
                height: 3.5rem;
            }
        }
    </style>
@endsection

@section('script')
<script>
// Global variables
let statusPollingInterval = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeKYCPage();
});

function initializeKYCPage() {
    const kycStatus = '{{ $kycStatus }}';
    const kycMode = '{{ $kycMode ?? 'veriff' }}';
    
    // Setup event listeners
    setupModalEvents();
    
    // Auto-refresh for processing states
    if (['submitted', 'under_review'].includes(kycStatus)) {
        startStatusPolling();
    }
    
    // Setup verification button (for Veriff mode)
    const startBtn = document.getElementById('start-verification-btn');
    const cancelBtn = document.getElementById('cancel-session-btn');
    
    if (startBtn) {
        startBtn.addEventListener('click', () => handleStartVerification('popup'));
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', handleCancelSession);
    }

    // Setup manual KYC form (for manual mode)
    if (kycMode === 'manual') {
        setupManualKycForm();
    }
}

function setupManualKycForm() {
    const form = document.getElementById('manual-kyc-form');
    if (!form) return;

    // File preview handlers with enhanced feedback
    const fileFields = [
        { input: 'front_document', preview: 'front-preview', fileName: 'front-file-name', icon: 'front-icon', hint: 'front-file-hint', area: 'front-upload-area' },
        { input: 'back_document', preview: 'back-preview', fileName: 'back-file-name', icon: 'back-icon', hint: 'back-file-hint', area: 'back-upload-area' },
        { input: 'selfie', preview: 'selfie-preview', fileName: 'selfie-file-name', icon: 'selfie-icon', hint: 'selfie-file-hint', area: 'selfie-upload-area' }
    ];
    
    fileFields.forEach(field => {
        const input = document.getElementById(field.input);
        const preview = document.getElementById(field.preview);
        const fileName = document.getElementById(field.fileName);
        const icon = document.getElementById(field.icon);
        const hint = document.getElementById(field.hint);
        const area = document.getElementById(field.area);
        
        if (input && preview) {
            input.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    
                    // Update file name with success styling
                    if (fileName) {
                        fileName.textContent = file.name;
                        fileName.classList.remove('text-muted');
                        fileName.classList.add('text-success', 'fw-semibold');
                    }
                    
                    // Update icon to checkmark
                    if (icon) {
                        icon.setAttribute('icon', 'material-symbols:check-circle');
                        icon.classList.remove('text-muted');
                        icon.classList.add('text-success');
                    }
                    
                    // Update hint to show file size
                    if (hint) {
                        const fileSizeKB = (file.size / 1024).toFixed(1);
                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        hint.textContent = fileSizeMB > 1 ? `${fileSizeMB} MB` : `${fileSizeKB} KB`;
                        hint.classList.remove('text-muted');
                        hint.classList.add('text-success');
                    }
                    
                    // Add success border to upload area
                    if (area) {
                        area.classList.add('border-success');
                        area.style.borderStyle = 'solid';
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        handleManualKycSubmit();
    });
}

function handleManualKycSubmit() {
    const form = document.getElementById('manual-kyc-form');
    const submitBtn = document.getElementById('submit-manual-kyc-btn');
    
    if (!form) return;

    // Validate required fields
    const documentType = document.getElementById('document_type').value;
    const frontDocument = document.getElementById('front_document').files[0];

    if (!documentType) {
        showAlert('danger', 'Please select a document type.');
        return;
    }

    if (!frontDocument) {
        showAlert('danger', 'Please upload the front of your document.');
        return;
    }

    // Set button to loading state
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
    }

    const formData = new FormData(form);

    fetch('{{ route('kyc.submit-manual') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Documents submitted successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAlert('danger', data.message || 'Failed to submit documents. Please try again.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<iconify-icon icon="iconamoon:upload-duotone" class="me-2"></iconify-icon>Submit Documents';
            }
        }
    })
    .catch(error => {
        console.error('Manual KYC submission error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<iconify-icon icon="iconamoon:upload-duotone" class="me-2"></iconify-icon>Submit Documents';
        }
    });
}

function handleStartVerification(method = 'popup') {
    const btn = document.getElementById('start-verification-btn');
    
    console.log('Starting verification process...');
    
    // Set button to loading state
    if (btn) setButtonState(btn, 'loading');
    
    // Create verification session
    createVerificationSession()
        .then(sessionData => {
            console.log('Session created successfully:', sessionData);
            
            // Check if session data has the expected structure
            if (!sessionData || !sessionData.session) {
                throw new Error('Invalid session response from server');
            }
            
            // DON'T update KYC status here - only when user actually submits documents
            // The session creation already sets status to 'session_created' on the backend
            
            // Open verification with specified method
            openVerification(sessionData.session.verification.url, method);
        })
        .catch(error => {
            console.error('Verification error:', error);
            showAlert('danger', error.message || 'Failed to start verification. Please try again.');
            
            // Reset button
            if (btn) setButtonState(btn, 'default');
        });
}

function handleCancelSession() {
    const cancelBtn = document.getElementById('cancel-session-btn');
    
    if (cancelBtn) {
        cancelBtn.disabled = true;
        cancelBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Canceling...';
    }
    
    fetch('{{ route('kyc.cancel') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('info', 'Session canceled. You can start a new verification process.');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('danger', data.message || 'Failed to cancel session');
            if (cancelBtn) {
                cancelBtn.disabled = false;
                cancelBtn.innerHTML = 'Start Over';
            }
        }
    })
    .catch(error => {
        console.error('Cancel session error:', error);
        showAlert('danger', 'Failed to cancel session');
        if (cancelBtn) {
            cancelBtn.disabled = false;
            cancelBtn.innerHTML = 'Start Over';
        }
    });
}

function createVerificationSession() {
    console.log('Creating verification session...');
    
    return fetch('{{ route('kyc.session') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        console.log('Session response status:', response.status);
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Server error: ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Session response data:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to create verification session');
        }
        
        if (!data.session || !data.session.verification || !data.session.verification.url) {
            console.error('Invalid session structure:', data);
            throw new Error('Invalid session data received from server');
        }
        
        return data;
    });
}

// Function to update status only when user actually submits documents
function updateKYCStatusToSubmitted() {
    fetch('{{ route('kyc.start') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('KYC status updated to submitted');
            if (data.fee_deducted) {
                showAlert('success', 'Verification fee processed successfully');
            }
        } else {
            console.warn('Failed to update KYC status:', data.message);
            // Show error if insufficient balance
            if (data.message.includes('Insufficient balance')) {
                showAlert('danger', data.message);
                setTimeout(() => {
                    window.location.href = '{{ route('kyc.index') }}';
                }, 3000);
            }
        }
    })
    .catch(error => {
        console.error('Status update error:', error);
    });
}

function openVerification(sessionUrl, method = 'popup') {
    console.log('Opening verification with URL:', sessionUrl, 'method:', method);
    
    // Reset button state
    const btn = document.getElementById('start-verification-btn');
    if (btn) setButtonState(btn, 'default');
    
    // Validate URL
    if (!sessionUrl) {
        showAlert('danger', 'No verification URL available. Please try again.');
        return;
    }
    
    // Detect if mobile device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    // On mobile, always use redirect for better UX
    if (isMobile) {
        method = 'redirect';
    }
    
    switch (method) {
        case 'redirect':
            // Direct redirect (user leaves current page)
            showAlert('info', 'Redirecting to verification...');
            setTimeout(() => {
                window.location.href = sessionUrl;
            }, 1000);
            break;
            
        case 'tab':
            // New tab (user can return to current page)
            const newTab = window.open(sessionUrl, '_blank');
            if (!newTab) {
                showAlert('danger', 'New tab was blocked. Please allow popups and try again.');
                return;
            }
            showAlert('info', 'Verification opened in new tab. Return here when complete.');
            break;
            
        case 'popup':
        default:
            // Popup window (recommended for desktop)
            showAlert('info', 'Opening verification window...');
            
            const veriffWindow = window.open(
                sessionUrl,
                'veriff-verification',
                'width=800,height=700,scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,centerscreen=yes'
            );
            
            if (!veriffWindow) {
                showAlert('warning', 'Popup was blocked. Redirecting in same tab...');
                setTimeout(() => {
                    window.location.href = sessionUrl;
                }, 3000);
                return;
            }
            
            // Mark that verification popup is active
            if (typeof window.setVerificationPopupActive === 'function') {
                window.setVerificationPopupActive(true);
            }
            
            // Focus the popup
            veriffWindow.focus();
            
            // Monitor the popup window
            const checkClosed = setInterval(() => {
                if (veriffWindow.closed) {
                    clearInterval(checkClosed);
                    showAlert('info', 'Verification window closed. Checking status...');
                    
                    // Check verification status after window closes
                    setTimeout(() => {
                        checkVerificationStatus();
                    }, 2000);
                }
            }, 1000);
            break;
    }
}

function setupModalEvents() {
    // Listen for messages from Veriff popup
    window.addEventListener('message', function(event) {
        // Only accept messages from Veriff
        if (!event.origin.includes('veriff.com')) return;
        
        console.log('Veriff message received:', event.data);
        
        try {
            const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
            
            if (data && data.type) {
                switch(data.type) {
                    case 'STARTED':
                        // User actually started the verification process
                        showAlert('info', 'Verification started...');
                        break;
                        
                    case 'SUBMITTED':
                        // User submitted documents - NOW update status
                        showAlert('success', 'Documents submitted! Processing...');
                        updateKYCStatusToSubmitted();
                        break;
                        
                    case 'FINISHED':
                        // Verification completed
                        showAlert('success', 'Verification completed! We are processing your documents...');
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                        break;
                        
                    case 'CANCELED':
                        // User canceled - don't update status
                        showAlert('info', 'Verification canceled. You can try again anytime.');
                        break;
                        
                    case 'ERROR':
                        showAlert('danger', 'Verification error occurred. Please try again.');
                        break;
                }
            }
        } catch (e) {
            console.log('Non-JSON message from Veriff:', event.data);
        }
    });
    
    // Listen for focus events to check if user returned from verification
    // Only for Veriff mode - not for manual KYC mode
    if (kycMode !== 'manual') {
        let isAwayForVerification = false;
        let verificationPopupActive = false;
        
        window.addEventListener('blur', function() {
            // Only track if we're actually in a Veriff verification session
            if (verificationPopupActive) {
                isAwayForVerification = true;
            }
        });
        
        window.addEventListener('focus', function() {
            if (isAwayForVerification && verificationPopupActive) {
                isAwayForVerification = false;
                verificationPopupActive = false;
                
                // User returned from Veriff popup, check status after a short delay
                setTimeout(() => {
                    showAlert('info', 'Welcome back! Checking your verification status...');
                    checkVerificationStatus();
                }, 1000);
            }
        });
        
        // Expose function to set popup active state
        window.setVerificationPopupActive = function(active) {
            verificationPopupActive = active;
        };
    }
}

function checkVerificationStatus() {
    fetch('{{ route('kyc.status') }}', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const currentStatus = '{{ $kycStatus }}';
            const newStatus = data.kyc_status;
            
            console.log('Status check:', { current: currentStatus, new: newStatus });
            
            if (currentStatus !== newStatus && ['submitted', 'under_review', 'verified', 'session_created'].includes(newStatus)) {
                showAlert('info', 'Status updated! Refreshing page...');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        }
    })
    .catch(error => {
        console.error('Status check error:', error);
    });
}

function startStatusPolling() {
    // Clear any existing interval
    if (statusPollingInterval) {
        clearInterval(statusPollingInterval);
    }
    
    // Start polling every 30 seconds
    statusPollingInterval = setInterval(() => {
        checkVerificationStatus();
    }, 30000);
    
    // Stop polling after 30 minutes to prevent excessive requests
    setTimeout(() => {
        if (statusPollingInterval) {
            clearInterval(statusPollingInterval);
            statusPollingInterval = null;
        }
    }, 1800000); // 30 minutes
}

function setButtonState(button, state) {
    if (!button) return;
    
    const kycStatus = '{{ $kycStatus }}';
    
    switch (state) {
        case 'loading':
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Starting...';
            break;
            
        case 'default':
        default:
            button.disabled = false;
            if (kycStatus === 'session_created') {
                button.innerHTML = '<iconify-icon icon="iconamoon:arrow-right-2-duotone" class="me-2"></iconify-icon>Continue Verification';
            } else {
                button.innerHTML = '<iconify-icon icon="iconamoon:play-duotone" class="me-2"></iconify-icon>Start Verification';
            }
            break;
    }
}

function showAlert(type, message) {
    const container = document.getElementById('alert-container');
    if (!container) return;
    
    // Remove existing alerts
    container.innerHTML = '';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show shadow-sm border-0`;
    
    const iconMap = {
        'success': 'material-symbols:bookmark-check',
        'danger': 'material-symbols:warning',
        'info': 'iconamoon:info-circle-duotone',
        'warning': 'iconamoon:alert-duotone'
    };
    
    alertDiv.innerHTML = `
        <div class="d-flex align-items-start">
            <iconify-icon icon="${iconMap[type] || iconMap.info}" class="me-2 mt-1 flex-shrink-0"></iconify-icon>
            <div class="flex-grow-1 small">${message}</div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    container.appendChild(alertDiv);
    
    // Auto-remove after 6 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            try {
                const alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
                alert.close();
            } catch (e) {
                alertDiv.remove();
            }
        }
    }, 6000);
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (statusPollingInterval) {
        clearInterval(statusPollingInterval);
    }
});
</script>
@endsection