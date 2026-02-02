@extends('admin.layouts.vertical')

@section('title', 'Package Expiry Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Package Expiry Settings</h5>
                        <small class="text-muted">Configure expiry multipliers, bot fees, and referral qualification criteria</small>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.expiry-settings.reset') }}" method="POST" class="d-inline" onsubmit="return confirm('Reset all settings to defaults?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <iconify-icon icon="solar:refresh-bold-duotone" class="me-1"></iconify-icon>
                                Reset Defaults
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('admin.expiry-settings.update') }}" method="POST">
                        @csrf

                        <div class="alert alert-info mb-4">
                            <iconify-icon icon="solar:info-circle-bold-duotone" class="me-2"></iconify-icon>
                            <strong>How Package Expiry Works:</strong> Packages expire when total earnings (ROI + commissions) reach the expiry multiplier times the principal amount.
                            <br><small>Base users earn up to 3x their investment. Qualified users with sufficient referrals earn up to 6x.</small>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <div class="card border h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">Expiry Multipliers</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Base Expiry Multiplier</label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       class="form-control @error('base_multiplier') is-invalid @enderror" 
                                                       name="base_multiplier"
                                                       value="{{ old('base_multiplier', $settings['base_multiplier'] ?? 3) }}"
                                                       min="1" 
                                                       max="20" 
                                                       step="0.1">
                                                <span class="input-group-text">x</span>
                                            </div>
                                            <small class="text-muted">Default earnings cap (e.g., 3x = earn up to 3 times principal)</small>
                                            @error('base_multiplier')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Extended Expiry Multiplier</label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       class="form-control @error('extended_multiplier') is-invalid @enderror" 
                                                       name="extended_multiplier"
                                                       value="{{ old('extended_multiplier', $settings['extended_multiplier'] ?? 6) }}"
                                                       min="1" 
                                                       max="20" 
                                                       step="0.1">
                                                <span class="input-group-text">x</span>
                                            </div>
                                            <small class="text-muted">Earnings cap for qualified referrers</small>
                                            @error('extended_multiplier')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border h-100">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">Bot Activation Fee</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-0">
                                            <label class="form-label">One-Time Bot Fee</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" 
                                                       class="form-control @error('bot_fee_amount') is-invalid @enderror" 
                                                       name="bot_fee_amount"
                                                       value="{{ old('bot_fee_amount', $settings['bot_fee_amount'] ?? 10) }}"
                                                       min="0" 
                                                       max="1000" 
                                                       step="0.01">
                                            </div>
                                            <small class="text-muted">Charged once on user's first package activation</small>
                                            @error('bot_fee_amount')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border h-100">
                                    <div class="card-header bg-warning">
                                        <h6 class="mb-0">Direct Referral Threshold</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-0">
                                            <label class="form-label">Qualify with Direct Referrals Only</label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       class="form-control @error('direct_referral_count') is-invalid @enderror" 
                                                       name="direct_referral_count"
                                                       value="{{ old('direct_referral_count', $settings['qualification_option_1']['count'] ?? 30) }}"
                                                       min="0" 
                                                       max="1000" 
                                                       step="1">
                                                <span class="input-group-text">referrals</span>
                                            </div>
                                            <small class="text-muted">Users with this many direct referrals get extended multiplier</small>
                                            @error('direct_referral_count')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @php
                            $tieredLevels = $settings['qualification_option_2']['levels'] ?? ['1' => 10, '2' => 8, '3' => 5, '4' => 3, '5' => 1];
                        @endphp

                        <div class="card border">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Composite Qualification Criteria</h6>
                                <small>Users qualify for extended multiplier if they meet ALL these referral counts across generations</small>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @for($i = 1; $i <= 5; $i++)
                                    <div class="col-md">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <span class="badge bg-{{ $i <= 2 ? 'primary' : ($i <= 4 ? 'info' : 'secondary') }} mb-2">
                                                    Level {{ $i }}
                                                </span>
                                                <div class="input-group">
                                                    <input type="number" 
                                                           class="form-control text-center @error('level_'.$i.'_referrals') is-invalid @enderror" 
                                                           name="level_{{ $i }}_referrals"
                                                           value="{{ old('level_'.$i.'_referrals', $tieredLevels[$i] ?? $tieredLevels[(string)$i] ?? 0) }}"
                                                           min="0" 
                                                           max="1000" 
                                                           step="1">
                                                </div>
                                                <small class="text-muted">
                                                    @switch($i)
                                                        @case(1)
                                                            Direct
                                                            @break
                                                        @case(2)
                                                            2nd Gen
                                                            @break
                                                        @case(3)
                                                            3rd Gen
                                                            @break
                                                        @default
                                                            {{ $i }}th Gen
                                                    @endswitch
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="mt-3 text-muted">
                                    <iconify-icon icon="solar:info-circle-bold-duotone" class="me-1"></iconify-icon>
                                    Users must have at least the specified number of referrals at <strong>each</strong> level to qualify via composite criteria.
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <iconify-icon icon="solar:diskette-bold-duotone" class="me-1"></iconify-icon>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
