@extends('layouts.app')

@section('content')

<div class="container">

<h2>User Management</h2>

@if(session('success'))
<div class="alert alert-success">
{{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
{{ session('error') }}
</div>
@endif


<a href="{{ route('users.create') }}" class="btn btn-primary mb-3">
Add User
</a>


<table class="table table-bordered">

<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

@foreach($users as $user)

<tr>

<td>{{ $user->id }}</td>
<td>{{ $user->name }}</td>
<td>{{ $user->email }}</td>
<td>{{ ucfirst($user->role) }}</td>

<td>
@if($user->is_active)
Active
@else
Inactive
@endif
</td>

<td>

<a href="{{ route('users.edit',$user->id) }}" class="btn btn-warning btn-sm">
Edit
</a>

<form action="{{ route('users.reset',$user->id) }}" method="POST" style="display:inline;">
@csrf
<button class="btn btn-secondary btn-sm">
Reset Password
</button>
</form>

@if($user->id !== auth()->id())

<form action="{{ route('users.destroy',$user->id) }}" method="POST" style="display:inline">

@csrf
@method('DELETE')

<button class="btn btn-danger btn-sm">
Delete
</button>

</form>

@endif

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

@endsection