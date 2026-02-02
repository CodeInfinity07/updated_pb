@extends('admin.layouts.vertical', ['title' => 'Tracking Pixels', 'mode' => 'admin'])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tracking Pixels</li>
                    </ol>
                </div>
                <h4 class="page-title">Tracking Pixels</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <iconify-icon icon="mdi:google-ads" class="me-2"></iconify-icon>
                        Manage Tracking Pixels
                    </h5>
                    <span class="text-muted small">Configure pixels for Facebook, Google, and TikTok</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.pixels.update') }}" method="POST">
                        @csrf

                        @foreach($platforms as $platformKey => $platform)
                            @php
                                $pixel = $pixels[$platformKey] ?? null;
                            @endphp
                            <div class="card mb-4 border {{ $pixel && $pixel->is_active ? 'border-success' : 'border-secondary' }}">
                                <div class="card-header d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <iconify-icon icon="{{ $platform['icon'] }}" class="fs-32"></iconify-icon>
                                        <div>
                                            <h5 class="mb-0">{{ $platform['name'] }}</h5>
                                            <small class="text-muted">{{ $platform['description'] }}</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="pixels[{{ $loop->index }}][platform]" value="{{ $platformKey }}">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                               name="pixels[{{ $loop->index }}][is_active]" 
                                               id="toggle_{{ $platformKey }}"
                                               value="1"
                                               {{ $pixel && $pixel->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="toggle_{{ $platformKey }}">
                                            {{ $pixel && $pixel->is_active ? 'Active' : 'Inactive' }}
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="pixel_id_{{ $platformKey }}">
                                                {{ $platform['id_label'] }}
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="pixel_id_{{ $platformKey }}"
                                                   name="pixels[{{ $loop->index }}][pixel_id]" 
                                                   value="{{ old("pixels.{$loop->index}.pixel_id", $pixel->pixel_id ?? '') }}"
                                                   placeholder="{{ $platform['id_placeholder'] }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="pixel_code_{{ $platformKey }}">
                                                Custom Pixel Code (Optional)
                                            </label>
                                            <textarea class="form-control" 
                                                      id="pixel_code_{{ $platformKey }}"
                                                      name="pixels[{{ $loop->index }}][pixel_code]" 
                                                      rows="4"
                                                      placeholder="Paste your custom pixel code here (optional - use if you need to add custom tracking code beyond the pixel ID)">{{ old("pixels.{$loop->index}.pixel_code", $pixel->pixel_code ?? '') }}</textarea>
                                            <small class="text-muted">
                                                Leave empty to use the standard pixel implementation with the ID above.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @if($pixel)
                                    <div class="card-footer bg-transparent">
                                        <small class="text-muted">
                                            Last updated: {{ $pixel->updated_at->format('M d, Y H:i') }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <iconify-icon icon="mdi:content-save" class="me-1"></iconify-icon>
                                Save All Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <iconify-icon icon="mdi:information-outline" class="me-2"></iconify-icon>
                        How to Use Tracking Pixels
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="d-flex align-items-start gap-3">
                                <iconify-icon icon="logos:facebook" class="fs-24 flex-shrink-0"></iconify-icon>
                                <div>
                                    <h6>Facebook Pixel</h6>
                                    <small class="text-muted">
                                        1. Go to Facebook Events Manager<br>
                                        2. Create or select your Pixel<br>
                                        3. Copy the Pixel ID (numeric)<br>
                                        4. Paste it above
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start gap-3">
                                <iconify-icon icon="logos:google-analytics" class="fs-24 flex-shrink-0"></iconify-icon>
                                <div>
                                    <h6>Google Analytics</h6>
                                    <small class="text-muted">
                                        1. Go to Google Analytics<br>
                                        2. Admin > Data Streams<br>
                                        3. Copy Measurement ID (G-XXXXX)<br>
                                        4. Paste it above
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start gap-3">
                                <iconify-icon icon="logos:tiktok-icon" class="fs-24 flex-shrink-0"></iconify-icon>
                                <div>
                                    <h6>TikTok Pixel</h6>
                                    <small class="text-muted">
                                        1. Go to TikTok Ads Manager<br>
                                        2. Assets > Events > Web Events<br>
                                        3. Create or select your Pixel<br>
                                        4. Copy and paste the Pixel ID
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.querySelectorAll('.form-check-input[role="switch"]').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const label = this.nextElementSibling;
            label.textContent = this.checked ? 'Active' : 'Inactive';
            const card = this.closest('.card');
            if (this.checked) {
                card.classList.remove('border-secondary');
                card.classList.add('border-success');
            } else {
                card.classList.remove('border-success');
                card.classList.add('border-secondary');
            }
        });
    });
</script>
@endsection
