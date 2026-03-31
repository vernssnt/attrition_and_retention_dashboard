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

<<iframe title="dashboard pramis_backup_ulet" width="1140" height="541" src="https://app.powerbi.com/view?r=eyJrIjoiNjkzYWQ2NjktMWZmZC00NTU5LThhZTUtMjExZWFjYWExMTIxIiwidCI6ImRmNzZiMWYxLTVlYTEtNGU1MS04NmVmLTQ4ZjdiOGY1ZWM5YyIsImMiOjEwfQ%3D%3D" frameborder="0" allowFullScreen="true"></iframe>


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

<option value="High" {{ request('risk_level') == 'High' ? 'selected' : '' }}>
    High
</option>

<option value="Medium" {{ request('risk_level') == 'Medium' ? 'selected' : '' }}>
    Medium
</option>

<option value="Low" {{ request('risk_level') == 'Low' ? 'selected' : '' }}>
    Low
</option>

</select>

<select name="year_level" class="toolbar-select">
<option value="">All Years</option>
<option value="1st year" {{ request('year_level') == '1st year' ? 'selected' : '' }}>1st year</option>
<option value="2nd year" {{ request('year_level') == '2nd year' ? 'selected' : '' }}>2nd year</option>
<option value="3rd year" {{ request('year_level') == '3rd year' ? 'selected' : '' }}>3rd year</option>
<option value="4th year" {{ request('year_level') == '4th year' ? 'selected' : '' }}>4th year</option>
</select>
<select name="academic_year" class="toolbar-select">
    <option value="">All Academic Years</option>
    @foreach($academicYears as $year)
        <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>
            {{ $year }}
        </option>
    @endforeach
</select>

<select name="term" class="toolbar-select">
    <option value="">All Terms</option>
    <option value="Term 1" {{ request('term') == 'Term 1' ? 'selected' : '' }}>Term 1</option>
    <option value="Term 2" {{ request('term') == 'Term 2' ? 'selected' : '' }}>Term 2</option>
    <option value="Term 3" {{ request('term') == 'Term 3' ? 'selected' : '' }}>Term 3</option>
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
<th>Year Level</th>
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

@php
$latest = DB::table('enrollments')
    ->where('student_id', $student->id)
    ->latest('id')
    ->first();
@endphp

<tr class="student-row" data-id="{{ $student->id }}">

<td class="toggle-icon">▶</td>

<td>{{ $student->student_no }}</td>
<td>{{ $student->first_name }} {{ $student->last_name }}</td>
<td>{{ $student->program }}</td>
<td>{{ $student->year_level }}</td>

<td>{{ $latest->attendance ?? 'N/A' }}%</td>
<td>{{ $latest->grade ?? 'N/A' }}</td>

<td>
@php
    $score = $student->final_risk ?? 0;
@endphp

@if($score >= 8)
    <span class="risk-badge risk-high">High</span>

@elseif($score >= 5)
    <span class="risk-badge risk-medium">Medium</span>

@else
    <span class="risk-badge risk-low">Low</span>
@endif
</td>
<td>

@php
$intervention = strtolower($student->intervention ?? '');
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

{{-- EDIT --}}
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('students.edit', $student->id) }}" class="action-btn edit-btn">
Edit
</a>
@endif



{{-- DELETE --}}
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

{{-- ✅ GET LATEST ENROLLMENT --}}
@php
$tuition_total = $latest->tuition_total ?? 0;
$tuition_paid = $latest->tuition_paid ?? 0;

$tuition_balance = $tuition_total - $tuition_paid;

$unpaid_percentage = $tuition_total > 0 
    ? ($tuition_balance / $tuition_total) * 100
    : 0;
@endphp

<div class="details-item">
<span>Tuition Balance</span>
<strong>₱{{ number_format($tuition_balance,2) }}</strong>
</div>

<div class="details-item">
<span>Unpaid %</span>
<strong>{{ number_format($unpaid_percentage, 2) }}%</strong>
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

<div class="details-section">
<h4>Academic History</h4>

@php
    $history = $studentHistories[$student->id] ?? [];
@endphp

@if(count($history) > 0)

<ul style="padding-left: 18px;">

@foreach($history as $record)
<li>
    <strong>{{ $record->academic_year }} - {{ $record->term }}</strong>
    → Risk: 
    @if($record->final_risk >= 8)
        <span style="color:red;">High</span>
    @elseif($record->final_risk >= 5)
        <span style="color:orange;">Medium</span>
    @else
        <span style="color:green;">Low</span>
    @endif
    ({{ $record->final_risk }}/10)
</li>
@endforeach

</ul>

@else
<p>No history available</p>
@endif

</div>

@php
$score = $student->final_risk ?? 0;
$width = $score * 10;
@endphp

<div class="details-item" style="flex-direction:column; align-items:flex-start; gap:6px;">

<span>Risk Score</span>

<div class="risk-bar">

<div class="risk-bar-fill
@if($score >= 8) risk-high
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

{{-- ✅ GET LATEST ENROLLMENT DATA --}}
@php
$grade = $latest->grade ?? 0;
$attendance = $latest->attendance ?? 100;
$tuition_total = $latest->tuition_total ?? 0;
$tuition_paid = $latest->tuition_paid ?? 0;

$paidPercent = $tuition_total > 0 
    ? ($tuition_paid / $tuition_total) * 100 
    : 100;
@endphp


{{-- ✅ Academic Risk --}}
@if($grade >= 3.0)
<li>📘 Recommend Academic Tutoring Program</li>
<li>👨‍🏫 Schedule consultation with Academic Advisor</li>
@endif


{{-- ✅ Attendance Risk --}}
@if($attendance < 85)
<li>📅 Schedule Guidance Counseling for Attendance Monitoring</li>
@endif

{{-- ✅ Financial Risk (UPDATED) --}}
@if($paidPercent < 50)
<li>💰 Offer installment payment plan</li>
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