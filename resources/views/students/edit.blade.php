@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')

<h2>Edit Student</h2>

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

    <label>Attendance (%)</label><br>
    <input type="number" step="0.01" name="attendance" 
           value="{{ old('attendance', $student->attendance) }}" required>
    <br><br>

    <label>Grade</label><br>
    <input type="number" step="0.01" name="grade" 
           value="{{ old('grade', $student->grade) }}" required>
    <br><br>

    {{-- 💰 TUITION SECTION --}}
    <label>Tuition Total</label><br>
    <input type="number" step="0.01" name="tuition_total" 
           value="{{ old('tuition_total', $student->tuition_total) }}">
    <br><br>

    <label>Tuition Paid</label><br>
    <input type="number" step="0.01" name="tuition_paid" 
           value="{{ old('tuition_paid', $student->tuition_paid) }}">
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
        <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>
            Active
        </option>
        <option value="dropped" {{ old('status', $student->status) == 'dropped' ? 'selected' : '' }}>
            Dropped
        </option>
    </select>
    <br><br>

    <label>Enrollment Year</label><br>
    <input type="number" name="enrollment_year"
           value="{{ old('enrollment_year', $student->enrollment_year) }}">
    <br><br>

    <button type="submit">Update Student</button>

</form>

{{-- ✅ Auto Show / Hide Scholarship Type --}}
<script>
function toggleScholarshipType() {
    const hasScholarship = document.getElementById('has_scholarship').value;
    const container = document.getElementById('scholarship_type_container');

    container.style.display = hasScholarship == 1 ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    toggleScholarshipType();
});

document.getElementById('has_scholarship')
    .addEventListener('change', toggleScholarshipType);
</script>

@endsection