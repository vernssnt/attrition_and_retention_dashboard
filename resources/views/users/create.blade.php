@extends('layouts.app')

@section('content')

<div class="container">

<h2>Add User</h2>

@if ($errors->any())
<div class="alert alert-danger">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif


<form action="{{ route('users.store') }}" method="POST">

@csrf

<div class="mb-3">
<label>Name</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="mb-3">
<label>Role</label>
<select name="role" class="form-control" required>
<option value="admin">Admin</option>
<option value="registrar">Registrar</option>
<option value="cashier">Cashier</option>
<option value="viewer">Viewer</option>
</select>
</div>

<button type="submit" class="btn btn-success">
Create User
</button>

<a href="{{ route('users.index') }}" class="btn btn-secondary">
Back
</a>

</form>

</div>

@endsection