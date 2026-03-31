@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="dashboard-wrapper">



{{-- ========================================================= --}}
{{-- FILTERS --}}
{{-- ========================================================= --}}

<div class="container-fluid dashboard-wrapper">

    <!-- HEADER -->
<div class="d-flex align-items-center justify-content-between mb-4">

    <div class="d-flex align-items-center gap-3">

        <button id="sidebarToggle" class="toggle-btn">
            <i class="bi bi-list"></i>
        </button>

        <h5 class="fw-semibold mb-0">
            Attrition & Retention Dashboard
        </h5>

    </div>

    <button class="btn btn-primary btn-sm">
        Refresh Data
    </button>

</div>


   <!-- FILTER CARD -->
<div class="card shadow-sm mb-4 p-3">

<form method="GET" action="{{ route('dashboard') }}">

<div class="row g-3 align-items-end">

    <div class="col-md-3">
        <label class="form-label">Enrollment Year</label>
        <select name="year" class="form-select">
            <option value="">All</option>
            @foreach($years as $year)
            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                {{ $year }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Program</label>
        <select name="program" class="form-select">
            <option value="">All</option>
            @foreach($programs as $program)
            <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                {{ $program }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">
            Apply Filter
        </button>
    </div>

    <div class="col-md-3">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary w-100">
            Reset
        </a>
    </div>

</div>

</form>

</div>


{{-- ========================================================= --}}
{{-- KPI SUMMARY CARDS --}}
{{-- ========================================================= --}}

<div class="row g-3 mb-4">

<div class="col-lg-2 col-md-4 col-sm-6">
<div class="card shadow-sm text-center p-3">
<h4>{{ $totalStudents }}</h4>
<small class="text-muted">Total Students</small>
</div>
</div>

<div class="col-lg-2 col-md-4 col-sm-6">
<div class="card shadow-sm text-center p-3">
<h4>{{ $activeStudents }}</h4>
<small class="text-muted">Active Students</small>
</div>
</div>

<div class="col-lg-2 col-md-4 col-sm-6">
<div class="card shadow-sm text-center p-3">
<h4>{{ $droppedStudents }}</h4>
<small class="text-muted">Dropped Students</small>
</div>
</div>

<div class="col-lg-3 col-md-6">
<div class="card shadow-sm text-center p-3 bg-success-subtle">
<h4 class="text-success">{{ number_format($retentionRate,2) }}%</h4>
<small class="text-muted">Retention Rate</small>
</div>
</div>

<div class="col-lg-3 col-md-6">
<div class="card shadow-sm text-center p-3 bg-danger-subtle">
<h4 class="text-danger">{{ number_format($attritionRate,2) }}%</h4>
<small class="text-muted">Attrition Rate</small>
</div>
</div>

</div>

{{-- ========================================================= --}}
{{-- ATTRITION BY ENROLLMENT YEAR --}}
{{-- ========================================================= --}}

<div class="card shadow-sm p-4 mb-4">

<h5 class="mb-3">Attrition by Enrollment Year</h5>

<table class="table table-striped text-center">

<thead class="table-dark">
<tr>
<th>Enrollment Year</th>
<th>Dropped Students</th>
</tr>
</thead>

<tbody>
@foreach($attritionByYear as $item)
<tr>
<td>{{ $item['academic_year'] }}</td>
<td>{{ $item['total'] }}</td>
</tr>
@endforeach
</tbody>

</table>

</div>



{{-- ========================================================= --}}
{{-- RETENTION BY PROGRAM --}}
{{-- ========================================================= --}}

<div class="card shadow-sm p-4 mb-4">

<h5 class="mb-3">Retention by Program</h5>

<table class="table table-striped text-center">

<thead class="table-dark">
<tr>
<th>Program</th>
<th>Active Students</th>
</tr>
</thead>

<tbody>
@foreach($retentionByProgram as $item)
<tr>
<td>{{ $item['program'] }}</td>
<td>{{ $item['total'] }}</td>
</tr>
@endforeach
</tbody>

</table>

</div>



<hr style="margin:40px 0;">


{{-- ========================================================= --}}
{{-- POWER BI DASHBOARD --}}
{{-- ========================================================= --}}

<div class="card shadow-sm p-4 mb-4">

<h5 class="mb-3">Attrition And Retention Dashboard</h5>

<div class="powerbi-container">

<iframe title="dashboard pramis_backup_ulet" width="1140" height="541" src="https://app.powerbi.com/view?r=eyJrIjoiNjkzYWQ2NjktMWZmZC00NTU5LThhZTUtMjExZWFjYWExMTIxIiwidCI6ImRmNzZiMWYxLTVlYTEtNGU1MS04NmVmLTQ4ZjdiOGY1ZWM5YyIsImMiOjEwfQ%3D%3D" frameborder="0" allowFullScreen="true"></iframe>

</div>

</div>


{{-- ========================================================= --}}
{{-- ADMIN ACTIVITY ANALYTICS --}}
{{-- ========================================================= --}}

@if(Auth::user()->role === 'admin')

<hr style="margin:40px 0;">

<h3 class="dashboard-title">System Activity Overview</h3>

<div class="admin-container">

<div class="admin-card">
<strong>Actions Today</strong><br>
{{ $activityData['actionsToday'] ?? 0 }}
</div>

<div class="admin-card">
<strong>Student Updates</strong><br>
{{ $activityData['studentUpdates'] ?? 0 }}
</div>

<div class="admin-card">
<strong>Excel Uploads</strong><br>
{{ $activityData['excelUploads'] ?? 0 }}
</div>

<div class="admin-card">
<strong>Most Active User</strong><br>
{{ $activityData['mostActiveUser']->user->name ?? 'N/A' }}
</div>

</div>

@endif



{{-- ========================================================= --}}
{{-- POWER BI AUTO REFRESH --}}
{{-- ========================================================= --}}

<script>

function refreshData(){

const btn = document.querySelector(".header-btn")

btn.innerText = "Refreshing..."

fetch("/trigger-flow")

.then(response => response.json())

.then(data => {

alert("Power BI dataset refresh triggered!")

window.location.href = "{{ route('dashboard') }}"

})

.catch(error => {

console.error(error)

btn.innerText = "Refresh Data"

alert("Failed to trigger refresh")

})

}


// Auto refresh Power BI iframe every 60 seconds

setInterval(function(){

const iframe = document.querySelector("iframe")

if(iframe){
iframe.src = iframe.src
}

}, 60000)

</script>

</div>

@endsection