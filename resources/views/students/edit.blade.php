@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')

<h2>Edit Student</h2>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('students.update', $student->id) }}">
    @csrf
    @method('PUT')

    <label>Student No</label><br>
    <input type="text" name="student_no" 
           value="{{ old('student_no', $student->student_no) }}" required>
    <br><br>

    <label>First Name</label><br>
    <input type="text" name="first_name" 
           value="{{ old('first_name', $student->first_name) }}" required>
    <br><br>

    {{-- ✅ MIDDLE NAME ADDED --}}
    <label>Middle Name</label><br>
    <input type="text" name="middle_name" 
           value="{{ old('middle_name', $student->middle_name) }}">
    <br><br>

    <label>Last Name</label><br>
    <input type="text" name="last_name" 
           value="{{ old('last_name', $student->last_name) }}" required>
    <br><br>

    <label>Gender</label><br>
    <select name="gender">
        <option value="">Select Gender</option>
        <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
        <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
    </select>
    <br><br>

    <label>Date of Birth</label><br>
    <input type="date" name="date_of_birth" 
           value="{{ old('date_of_birth', $student->date_of_birth) }}">
    <br><br>

    <label>Program</label><br>
    <input type="text" name="program" 
           value="{{ old('program', $student->program) }}" required>
    <br><br>

    <label>Year Level</label><br>
    <input type="text" name="year_level" 
           value="{{ old('year_level', $student->year_level) }}" required>
    <br><br>


    {{-- ✅ HAS SCHOLARSHIP --}}
    <label>Has Scholarship?</label><br>
    <select name="has_scholarship" id="has_scholarship">
        <option value="0" {{ old('has_scholarship', $student->has_scholarship) == 0 ? 'selected' : '' }}>
            No
        </option>
        <option value="1" {{ old('has_scholarship', $student->has_scholarship) == 1 ? 'selected' : '' }}>
            Yes
        </option>
    </select>
    <br><br>

    {{-- ✅ SCHOLARSHIP TYPE --}}
    <div id="scholarship_type_container" style="display:none;">
        <label>Scholarship Type</label><br>
        <select name="scholarship_type">
            <option value="">Select Type</option>
            <option value="Indigency" {{ old('scholarship_type', $student->scholarship_type) == 'Indigency' ? 'selected' : '' }}>
                Indigency
            </option>
            <option value="Partnership" {{ old('scholarship_type', $student->scholarship_type) == 'Partnership' ? 'selected' : '' }}>
                Partnership
            </option>
            <option value="SHS Graduate" {{ old('scholarship_type', $student->scholarship_type) == 'SHS Graduate' ? 'selected' : '' }}>
                SHS Graduate
            </option>
        </select>
        <br><br>
    </div>

    <label>Status</label><br>
<select name="status">
    <option value="Active"    {{ old('status', $student->status) == 'Active'    ? 'selected' : '' }}>Active</option>
    <option value="Dropped"   {{ old('status', $student->status) == 'Dropped'   ? 'selected' : '' }}>Dropped</option>
    <option value="Graduated" {{ old('status', $student->status) == 'Graduated' ? 'selected' : '' }}>Graduated</option>
</select>
<br><br>

    <label>Enrollment Year</label><br>
    <input type="number" name="enrollment_year"
           value="{{ old('enrollment_year', $student->enrollment_year) }}">
    <br><br>

    <h3>Academic Records</h3>
    


<div id="records-container">

    @if($enrollments->isEmpty())

    <div class="record-row">

        <select name="records[0][academic_period_id]" required>
            <option value="">Select Term</option>
            @foreach($academicPeriods as $period)
                <option value="{{ $period->id }}">
                    {{ $period->academic_year }} - {{ $period->term }}
                </option>
            @endforeach
        </select>

        <input type="number" step="0.01" name="records[0][grade]" placeholder="Grade">
        <input type="number" step="0.01" name="records[0][attendance]" placeholder="Attendance">
        <input type="number" step="0.01" name="records[0][tuition_total]" placeholder="Tuition Total">
        <input type="number" step="0.01" name="records[0][tuition_paid]" placeholder="Tuition Paid">

    </div>

@endif

    @foreach($enrollments as $index => $record)
    <div class="record-row">

    <!-- ✅ REQUIRED -->
    <input type="hidden" name="records[{{ $index }}][id]" value="{{ $record->id }}">
    <input type="hidden" name="records[{{ $index }}][academic_period_id]" value="{{ $record->academic_period_id }}">

    <input type="text" value="{{ $record->academic_year }}" readonly>
    <input type="text" value="{{ $record->term }}" readonly>

    <input name="records[{{ $index }}][grade]" value="{{ $record->grade }}">
    <input name="records[{{ $index }}][attendance]" value="{{ $record->attendance }}">
    <input type="number" step="0.01"
    name="records[{{ $index }}][tuition_total]"
    placeholder="Tuition Total"
    value="{{ $record->tuition_total > 0 ? $record->tuition_total : '' }}">

<input type="number" step="0.01"
    name="records[{{ $index }}][tuition_paid]"
    placeholder="Tuition Paid"
    value="{{ $record->tuition_paid > 0 ? $record->tuition_paid : '' }}">

</div>
    @endforeach

</div>

<br>
<button type="button" onclick="addRecord()">+ Add Term</button>

<br><br>
<button type="submit">Update Student</button>

{{-- ✅ Auto Show / Hide Scholarship Type --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    let recordIndex = Date.now(); // ✅ FIX

    window.addRecord = function () {

        const container = document.getElementById('records-container');

        if (!container) {
            console.error('records-container not found');
            return;
        }

        let html = `
        <div class="record-row" style="margin-bottom:10px;">

            <select name="records[${recordIndex}][academic_period_id]" required>
                <option value="">Select Term</option>
                @foreach($academicPeriods as $period)
                    <option value="{{ $period->id }}">
                        {{ $period->academic_year }} - {{ $period->term }}
                    </option>
                @endforeach
            </select>

            <input type="number" step="0.01" name="records[${recordIndex}][grade]" placeholder="Grade">
            <input type="number" step="0.01" name="records[${recordIndex}][attendance]" placeholder="Attendance">
            <input type="number" step="0.01" name="records[${recordIndex}][tuition_total]" placeholder="Tuition Total">
            <input type="number" step="0.01" name="records[${recordIndex}][tuition_paid]" placeholder="Tuition Paid">

        </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
        recordIndex++;
    };

});
</script>

@endsection