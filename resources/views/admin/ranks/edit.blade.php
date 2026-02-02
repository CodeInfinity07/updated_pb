@extends('admin.layouts.vertical', ['title' => 'Edit Rank', 'subTitle' => 'Modify Rank Details'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.ranks.index') }}" class="btn btn-outline-secondary">
                    <iconify-icon icon="iconamoon:arrow-left-2-duotone"></iconify-icon>
                </a>
                <div>
                    <h4 class="mb-0">Edit Rank: {{ $rank->name }}</h4>
                    <small class="text-muted">Modify rank settings and requirements</small>
                </div>
            </div>
        </div>
    </div>

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
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Rank Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ranks.update', $rank) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label">Rank Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $rank->name) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="display_order" class="form-label">Display Order <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="{{ old('display_order', $rank->display_order) }}" min="1" required>
                            </div>
                            <div class="col-12">
                                <label for="icon" class="form-label">Icon (Iconify name)</label>
                                <input type="text" class="form-control" id="icon" name="icon" value="{{ old('icon', $rank->icon) }}" placeholder="e.g., iconamoon:badge-duotone">
                                <small class="text-muted">Use icons from <a href="https://icon-sets.iconify.design/" target="_blank">Iconify</a></small>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $rank->description) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="text-primary mb-3">Requirements</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="min_self_deposit" class="form-label">Minimum Self Deposit ($) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="min_self_deposit" name="min_self_deposit" value="{{ old('min_self_deposit', $rank->min_self_deposit) }}" min="0" step="0.01" required>
                                <small class="text-muted">User's own active investment amount</small>
                            </div>
                            <div class="col-md-6">
                                <label for="reward_amount" class="form-label">Reward Amount ($) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="reward_amount" name="reward_amount" value="{{ old('reward_amount', $rank->reward_amount) }}" min="0" step="0.01" required>
                                <small class="text-muted">One-time bonus when rank is achieved</small>
                            </div>
                            <div class="col-md-6">
                                <label for="min_direct_members" class="form-label">Direct Members Required <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="min_direct_members" name="min_direct_members" value="{{ old('min_direct_members', $rank->min_direct_members) }}" min="0" required>
                                <small class="text-muted">Number of direct referrals needed</small>
                            </div>
                            <div class="col-md-6">
                                <label for="min_direct_member_investment" class="form-label">Min Investment per Direct Member ($) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="min_direct_member_investment" name="min_direct_member_investment" value="{{ old('min_direct_member_investment', $rank->min_direct_member_investment) }}" min="0" step="0.01" required>
                                <small class="text-muted">Each direct referral must have this amount invested</small>
                            </div>
                            <div class="col-md-6">
                                <label for="min_team_members" class="form-label">Team Members Required (L2+L3)</label>
                                <input type="number" class="form-control" id="min_team_members" name="min_team_members" value="{{ old('min_team_members', $rank->min_team_members) }}" min="0">
                                <small class="text-muted">Level 2 + Level 3 members only (0 = no requirement)</small>
                            </div>
                            <div class="col-md-6">
                                <label for="min_team_member_investment" class="form-label">Min Investment per Team Member ($)</label>
                                <input type="number" class="form-control" id="min_team_member_investment" name="min_team_member_investment" value="{{ old('min_team_member_investment', $rank->min_team_member_investment) }}" min="0" step="0.01">
                                <small class="text-muted">Each team member must have this amount invested</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ $rank->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <iconify-icon icon="iconamoon:check-duotone" class="me-1"></iconify-icon>
                                Update Rank
                            </button>
                            <a href="{{ route('admin.ranks.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Users Achieved:</span>
                        <span class="fw-bold">{{ $rank->getUsersAchievedCount() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Rewards Paid:</span>
                        <span class="fw-bold text-success">${{ number_format($rank->getTotalRewardsPaid(), 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Help</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2"><strong>Display Order:</strong> Ranks are awarded sequentially. Users must achieve lower-ordered ranks before higher ones.</p>
                    <p class="text-muted small mb-2"><strong>Self Deposit:</strong> The user's total active investment amount required.</p>
                    <p class="text-muted small mb-2"><strong>Direct Members:</strong> Number of Level 1 referrals with qualifying investments.</p>
                    <p class="text-muted small mb-0"><strong>Team Members:</strong> Level 2 + Level 3 referrals only (not all levels) with qualifying investments.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
