@extends('layouts.vertical', ['title' => 'Salary Program', 'subTitle' => 'Monthly Earnings'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title d-flex align-items-center mb-0 text-white">
                    <iconify-icon icon="solar:wallet-money-bold-duotone" class="me-2 fs-24"></iconify-icon>
                    Monthly Salary Program
                </h4>
                <p class="text-white-50 mb-0 mt-1">Earn recurring monthly income by growing your network</p>
            </div>
            <div class="card-body">
                @if($eligibility['eligible'])
                    <div class="alert alert-success border-success mb-4">
                        <div class="d-flex align-items-center">
                            <iconify-icon icon="solar:check-circle-bold" class="fs-32 me-3"></iconify-icon>
                            <div>
                                <h5 class="mb-1">Congratulations! You're Eligible</h5>
                                <p class="mb-0">You have met all the requirements for Stage 1. Apply now to start earning monthly salary!</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info border-info mb-4">
                        <div class="d-flex align-items-center">
                            <iconify-icon icon="solar:info-circle-bold" class="fs-32 me-3"></iconify-icon>
                            <div>
                                <h5 class="mb-1">Complete Requirements to Join</h5>
                                <p class="mb-0">Meet the Stage 1 requirements below to become eligible for the salary program.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <h5 class="mb-3">Stage 1 Requirements</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border {{ $eligibility['requirements']['direct_members']['met'] ? 'border-success' : 'border-secondary' }} h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="mb-0">Direct Members</h6>
                                    @if($eligibility['requirements']['direct_members']['met'])
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-secondary">In Progress</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-end justify-content-between">
                                    <div>
                                        <h3 class="mb-0">{{ $eligibility['requirements']['direct_members']['current'] }}</h3>
                                        <small class="text-muted">of {{ $eligibility['requirements']['direct_members']['required'] }} required</small>
                                    </div>
                                    <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="fs-40 text-primary opacity-50"></iconify-icon>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    @php
                                        $directProgress = min(100, ($eligibility['requirements']['direct_members']['current'] / max(1, $eligibility['requirements']['direct_members']['required'])) * 100);
                                    @endphp
                                    <div class="progress-bar {{ $eligibility['requirements']['direct_members']['met'] ? 'bg-success' : 'bg-primary' }}" style="width: {{ $directProgress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border {{ $eligibility['requirements']['self_deposit']['met'] ? 'border-success' : 'border-secondary' }} h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="mb-0">Self Deposit</h6>
                                    @if($eligibility['requirements']['self_deposit']['met'])
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-secondary">In Progress</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-end justify-content-between">
                                    <div>
                                        <h3 class="mb-0">${{ number_format($eligibility['requirements']['self_deposit']['current'], 2) }}</h3>
                                        <small class="text-muted">of ${{ number_format($eligibility['requirements']['self_deposit']['required'], 2) }} required</small>
                                    </div>
                                    <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-40 text-primary opacity-50"></iconify-icon>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    @php
                                        $depositProgress = min(100, ($eligibility['requirements']['self_deposit']['current'] / max(1, $eligibility['requirements']['self_deposit']['required'])) * 100);
                                    @endphp
                                    <div class="progress-bar {{ $eligibility['requirements']['self_deposit']['met'] ? 'bg-success' : 'bg-primary' }}" style="width: {{ $depositProgress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border {{ $eligibility['requirements']['team']['met'] ? 'border-success' : 'border-secondary' }} h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="mb-0">Team Members</h6>
                                    @if($eligibility['requirements']['team']['met'])
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-secondary">In Progress</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-end justify-content-between">
                                    <div>
                                        <h3 class="mb-0">{{ $eligibility['requirements']['team']['current'] }}</h3>
                                        <small class="text-muted">of {{ $eligibility['requirements']['team']['required'] }} required</small>
                                    </div>
                                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-40 text-primary opacity-50"></iconify-icon>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    @php
                                        $teamProgress = min(100, ($eligibility['requirements']['team']['current'] / max(1, $eligibility['requirements']['team']['required'])) * 100);
                                    @endphp
                                    <div class="progress-bar {{ $eligibility['requirements']['team']['met'] ? 'bg-success' : 'bg-primary' }}" style="width: {{ $teamProgress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($eligibility['eligible'])
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h5>How It Works</h5>
                            <ol class="mb-4">
                                <li class="mb-2">Once you apply, you must bring in <strong>new</strong> team members after enrollment to meet targets.</li>
                                <li class="mb-2">Each month, you need to add <strong>35% of your stage requirement</strong> as new team members AND recruit <strong>at least 3 new direct members</strong>.</li>
                                <li class="mb-2">If you meet both targets, you receive your monthly salary payment.</li>
                                <li class="mb-2">As you advance to higher stages, your targets increase based on that stage's requirements!</li>
                            </ol>
                            <p class="mb-0 text-muted"><small>Example: If Stage 1 requires 60 team members, you need 21 NEW members (60 x 0.35 = 21).</small></p>
                        </div>
                    </div>

                    <form action="{{ route('salary.apply') }}" method="POST">
                        @csrf
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <iconify-icon icon="solar:hand-money-bold" class="me-2"></iconify-icon>
                                Apply for Salary Program
                            </button>
                        </div>
                    </form>
                @else
                    <div class="card bg-light border-0">
                        <div class="card-body text-center py-4">
                            <iconify-icon icon="solar:lock-keyhole-minimalistic-bold-duotone" class="fs-48 text-muted mb-3"></iconify-icon>
                            <h5>Complete the requirements above to unlock the salary program</h5>
                            <p class="text-muted mb-0">Keep growing your network and investments to become eligible!</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Salary Stages</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-centered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Stage</th>
                                <th>Monthly Salary</th>
                                <th>Direct Members</th>
                                <th>Self Deposit</th>
                                <th>Team Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\SalaryStage::active()->ordered()->get() as $stage)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $loop->iteration == 1 ? 'primary' : 'secondary' }}">
                                        Stage {{ $stage->stage_order }}
                                    </span>
                                    {{ $stage->name }}
                                </td>
                                <td class="fw-bold text-success">${{ number_format($stage->salary_amount, 2) }}</td>
                                <td>{{ $stage->direct_members_required }}</td>
                                <td>${{ number_format($stage->self_deposit_required, 2) }}</td>
                                <td>{{ $stage->team_required }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
