@extends('layouts.admin')

@section('title', 'User Management')

@section('header', 'User Management')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
        <li class="breadcrumb-item active">Users</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">All Users</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUserModal">
        <i class="bi bi-plus"></i> Add User
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search name or email..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('settings.users') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>
                        <strong>{{ $user->name }}</strong>
                        @if($user->id === Auth::id())
                        <span class="badge bg-info ms-1">You</span>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->role === 'admin')
                        <span class="badge bg-primary">Admin</span>
                        @else
                        <span class="badge bg-secondary">User</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="text-end">
                        @if($user->id !== Auth::id())
                        <button class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editUser{{ $user->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('settings.users.delete', $user) }}" class="d-inline" onsubmit="return confirm('Delete this user?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-3">No users found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $users->links() !!}
    </div>
    @endif
</div>

<div class="modal fade" id="newUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.users.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($users as $user)
<div class="modal fade" id="editUser{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.users.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection