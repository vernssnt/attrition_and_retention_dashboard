@extends('layouts.app')

@section('title', 'Students')

@section('content')


<div class="students-header">

<h2 class="students-title">Students</h2>

<a href="{{ route('students.create') }}" class="add-student-btn">
<i class="bi bi-plus-lg"></i>
Add Student
</a>

</div>

@if(session('success'))
<div class="success-message">
{{ session('success') }}
</div>
@endif


{{-- ================= EXCEL IMPORT ================= --}}
<div class="upload-card">

<div class="upload-header">
<i class="bi bi-folder2-open"></i>
<h3>Upload Student Excel File</h3>
</div>

<form action="{{ route('students.import') }}"
method="POST"
enctype="multipart/form-data"
class="upload-form">

@csrf

<input type="file" name="file" class="upload-input" required>

<button type="submit" class="upload-btn">
Upload File
</button>

</form>

<p class="upload-hint">Supported format: .xlsx</p>

</div>


{{-- ================= DASHBOARD OVERVIEW ================= --}}
<h3>📊 Dashboard Overview</h3>


<div class="dashboard-kpi">

<div class="kpi-card">
<div class="kpi-icon"><i class="bi bi-people"></i></div>
<div>
<div class="kpi-title">Total Students</div>
<div class="kpi-value">{{ $totalStudents }}</div>
</div>
</div>

<div class="kpi-card">
<div class="kpi-icon"><i class="bi bi-graph-up"></i></div>
<div>
<div class="kpi-title">Retention Rate</div>
<div class="kpi-value">{{ $retentionRate }}%</div>
</div>
</div>

<div class="kpi-card risk-high-card">
<div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
<div>
<div class="kpi-title">High Risk</div>
<div class="kpi-value">{{ $highRisk }}</div>
</div>
</div>

<div class="kpi-card risk-medium-card">
<div class="kpi-icon"><i class="bi bi-dash-circle"></i></div>
<div>
<div class="kpi-title">Medium Risk</div>
<div class="kpi-value">{{ $mediumRisk }}</div>
</div>
</div>

<div class="kpi-card risk-low-card">
<div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
<div>
<div class="kpi-title">Low Risk</div>
<div class="kpi-value">{{ $lowRisk }}</div>
</div>
</div>

</div>

<iframe title="Risk Distribution Dashboard"
width="1140"
height="541"
src="https://app.powerbi.com/reportEmbed?reportId=b1af95dd-160c-457a-bf4a-f6ed34e55e60&autoAuth=true&ctid=df76b1f1-5ea1-4e51-86ef-48f7b8f5ec9c"
frameborder="0"
allowFullScreen="true">
</iframe>

<hr>


{{-- ================= FILTER SECTION ================= --}}
<form method="GET" action="{{ route('students.index') }}" class="student-toolbar">

<input
type="text"
id="studentSearch"
placeholder="🔎 Search student name, number, or program..."
class="toolbar-search"
>

<select name="risk_level" class="toolbar-select">
<option value="">All Risk</option>
<option value="High Risk" {{ request('risk_level') == 'High Risk' ? 'selected' : '' }}>High Risk</option>
<option value="Medium Risk" {{ request('risk_level') == 'Medium Risk' ? 'selected' : '' }}>Medium Risk</option>
<option value="Low Risk" {{ request('risk_level') == 'Low Risk' ? 'selected' : '' }}>Low Risk</option>
</select>

<select name="year_level" class="toolbar-select">
<option value="">All Years</option>
<option value="1st year" {{ request('year_level') == '1st year' ? 'selected' : '' }}>1st year</option>
<option value="2nd year" {{ request('year_level') == '2nd year' ? 'selected' : '' }}>2nd year</option>
<option value="3rd year" {{ request('year_level') == '3rd year' ? 'selected' : '' }}>3rd year</option>
<option value="4th year" {{ request('year_level') == '4th year' ? 'selected' : '' }}>4th year</option>
</select>

<button type="submit" class="toolbar-btn apply-btn">Apply</button>

<a href="{{ route('students.index') }}" class="toolbar-btn reset-btn">
Reset
</a>

</form>

<hr>


{{-- ================= STUDENT TABLE ================= --}}
@if ($students->count() === 0)

<p>No students found.</p>

@else

<div class="student-table-container">

<table class="students-table" id="studentsTable">

<thead>
<tr>
<th></th>
<th>Student No</th>
<th>Name</th>
<th>Program</th>
<th>Year</th>
<th>Attendance</th>
<th>Grade</th>
<th>Risk</th>
<th>Intervention</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

@foreach ($students as $student)

<tr class="student-row" data-id="{{ $student->id }}">

<td class="toggle-icon">▶</td>

<td>{{ $student->student_no }}</td>
<td>{{ $student->first_name }} {{ $student->last_name }}</td>
<td>{{ $student->program }}</td>
<td>{{ $student->year_level }}</td>
<td>{{ $student->attendance ?? 'N/A' }}%</td>
<td>{{ $student->grade ?? 'N/A' }}</td>

<td>
@if($student->risk_level === 'High Risk')
<span class="risk-badge risk-high">High</span>
@elseif($student->risk_level === 'Medium Risk')
<span class="risk-badge risk-medium">Medium</span>
@else
<span class="risk-badge risk-low">Low</span>
@endif
</td>

<td>

@php
$intervention = strtolower($student->intervention_recommendation ?? '');
@endphp

@if(str_contains($intervention,'academic') && str_contains($intervention,'financial'))
<span class="badge-both">Academic + Financial</span>

@elseif(str_contains($intervention,'academic'))
<span class="badge-academic">Academic</span>

@elseif(str_contains($intervention,'financial'))
<span class="badge-financial">Financial</span>

@else
<span class="badge-none">No Action</span>
@endif

</td>

<td>{{ ucfirst($student->status) }}</td>

<td>

@if(auth()->user()->role !== 'viewer')
<a href="{{ route('students.edit', $student->id) }}" class="action-btn edit-btn">
Edit
</a>
@endif
</a>

<form action="{{ route('students.destroy', $student->id) }}"
method="POST"
style="display:inline;">

@csrf
@method('DELETE')

<button type="submit"
class="action-btn delete-btn"
onclick="return confirm('Are you sure?')">
Delete
</button>

</form>

</td>

</tr>


{{-- EXPANDABLE DETAILS ROW --}}
<tr id="details-{{ $student->id }}" class="student-details">

<td colspan="11">

<div class="student-details-container">

{{-- PERSONAL --}}
<div class="details-section">
<h4>Personal</h4>

<div class="details-item">
<span>Gender</span>
<strong>{{ $student->gender ?? 'N/A' }}</strong>
</div>

<div class="details-item">
<span>Age</span>
<strong>{{ $student->age ?? 'N/A' }}</strong>
</div>

<div class="details-item">
<span>Middle Name</span>
<strong>{{ $student->middle_name ?? '-' }}</strong>
</div>

</div>


{{-- FINANCIAL --}}
<div class="details-section">
<h4>Financial</h4>

<div class="details-item">
<span>Tuition Balance</span>
<strong>₱{{ number_format($student->tuition_balance,2) }}</strong>
</div>

<div class="details-item">
<span>Unpaid %</span>
<strong>{{ $student->unpaid_percentage }}%</strong>
</div>

<div class="details-item">
<span>Scholarship</span>
<strong>{{ $student->has_scholarship ? 'Yes' : 'No' }}</strong>
</div>

<div class="details-item">
<span>Scholarship Type</span>
<strong>{{ $student->scholarship_type ?? '-' }}</strong>
</div>

</div>


{{-- RISK ANALYSIS --}}
<div class="details-section">

<h4>Risk Analysis</h4>

@php
$score = $student->risk_score ?? 0;
$width = $score * 10;
@endphp

<div class="details-item" style="flex-direction:column; align-items:flex-start; gap:6px;">

<span>Risk Score</span>

<div class="risk-bar">

<div class="risk-bar-fill
@if($score >= 10) risk-high
@elseif($score >= 5) risk-medium
@else risk-low
@endif
"
style="width: {{ $width }}%">
</div>

</div>

<strong>{{ $score }} / 10</strong>

</div>

<div class="details-item">
<span>Enrollment Year</span>
<strong>{{ $student->enrollment_year }}</strong>
</div>
<hr>

<hr>

<h4>Strategic Plan</h4>

<ul class="strategic-plan">

{{-- Academic Risk --}}
@if(($student->grade ?? 0) >= 3.0)
<li>📘 Recommend Academic Tutoring Program</li>
<li>👨‍🏫 Schedule consultation with Academic Advisor</li>
@endif

{{-- Attendance Risk --}}
@if(($student->attendance ?? 100) < 85)
<li>📅 Schedule Guidance Counseling for Attendance Monitoring</li>
@endif

@php
$paidPercent = ($student->tuition_total > 0) 
    ? ($student->tuition_paid / $student->tuition_total) * 100 
    : 100;
@endphp

{{-- Financial Risk --}}
@if($paidPercent < 50)
<li>💰 Refer student to Financial Aid Office</li>
<li>📄 Offer installment payment plan</li>
@endif

{{-- High Overall Risk --}}
@if(($student->risk_score ?? 0) >= 8)
<li>⚠ Intensive monitoring by Student Affairs</li>
@endif

</ul>

</div>

</div>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>


{{-- TOGGLE SCRIPT --}}
<script>

document.addEventListener("DOMContentLoaded", function(){

document.querySelectorAll(".student-row").forEach(row => {

row.addEventListener("click", function(){

const id = this.dataset.id
const details = document.getElementById("details-"+id)

if(details.style.display === "table-row"){
details.style.display = "none"
this.classList.remove("open")
}else{
details.style.display = "table-row"
this.classList.add("open")
}

})

})

});

</script>
<script>

const searchInput = document.getElementById("studentSearch");

searchInput.addEventListener("keyup", function(){

let filter = searchInput.value.toLowerCase();
let rows = document.querySelectorAll("#studentsTable tbody tr.student-row");

rows.forEach(row => {

let text = row.innerText.toLowerCase();

if(text.includes(filter)){
row.style.display = "";
document.getElementById("details-"+row.dataset.id).style.display = "none";
}else{
row.style.display = "none";
document.getElementById("details-"+row.dataset.id).style.display = "none";
}

});

});

</script>

@endif

@endsection