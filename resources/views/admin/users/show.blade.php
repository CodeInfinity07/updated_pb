@extends('admin.layouts.vertical', ['title' => 'User Details', 'subTitle' => 'View User Information'])

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1">{{ $user->full_name }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                            <li class="breadcrumb-item active">{{ $user->username }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <iconify-icon icon="iconamoon:arrow-left-2-duotone" class="me-1"></iconify-icon>
                        Back to Users
                    </a>
                    @if(auth()->user()->hasAdminPermission('users.edit'))
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                            <iconify-icon icon="iconamoon:edit-duotone" class="me-1"></iconify-icon>
                            Edit User
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('admin.users.show-details')
@endsection
