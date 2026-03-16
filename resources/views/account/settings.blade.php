@extends('layouts.app')

@section('title', 'Account Settings')

@section('content')

<h2>Account Settings</h2>

@if(session('success'))
    <div style="color:green; margin-bottom:10px;">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div style="color:red; margin-bottom:10px;">
        {{ $errors->first() }}
    </div>
@endif

<h3>Update Profile</h3>

<form method="POST" action="{{ route('account.update') }}">
    @csrf

    <label>Name</label><br>
    <input type="text" name="name" value="{{ auth()->user()->name }}" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" value="{{ auth()->user()->email }}" required><br><br>

    <label>Role</label><br>
    <input type="text" value="{{ ucfirst(auth()->user()->role) }}" disabled><br><br>

    <label>Account Created</label><br>
    <input type="text" value="{{ auth()->user()->created_at->format('F d, Y') }}" disabled><br><br>

    <button type="submit">Update Profile</button>
</form>

<hr>

<h3>Change Password</h3>

<form method="POST" action="{{ route('account.password') }}">
    @csrf

    <label>Current Password</label><br>
    <input type="password" name="current_password" required><br><br>

    <label>New Password</label><br>
    <input type="password" name="new_password" required><br><br>

    <label>Confirm New Password</label><br>
    <input type="password" name="new_password_confirmation" required><br><br>

    <button type="submit">Change Password</button>

    @if(auth()->user()->role === 'admin')

<hr>

<h3>User Management</h3>
<p>Admin can manage system users.</p>

<a href="{{ route('users.index') }}" class="btn btn-primary">
    Manage Users
</a>

@endif
</form>

@endsection