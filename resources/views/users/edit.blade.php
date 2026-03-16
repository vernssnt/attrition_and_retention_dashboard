@extends('layouts.app')

@section('content')

<div class="container">

<h2>Edit User</h2>

@if ($errors->any())
<div class="alert alert-danger">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif


<form action="{{ route('users.update', $user->id) }}" method="POST">

@csrf
@method('PUT')

<div class="mb-3">
<label>Name</label>
<input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
</div>

<div class="mb-3">
<label>Role</label>
<select name="role" class="form-control">

<option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
<option value="registrar" {{ $user->role == 'registrar' ? 'selected' : '' }}>Registrar</option>
<option value="cashier" {{ $user->role == 'cashier' ? 'selected' : '' }}>Cashier</option>
<option value="viewer" {{ $user->role == 'viewer' ? 'selected' : '' }}>Viewer</option>

</select>
</div>

<button type="submit" class="btn btn-success">
Update User
</button>

<a href="{{ route('users.index') }}" class="btn btn-secondary">
Cancel
</a>

</form>

</div>

@endsection