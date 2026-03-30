@extends('layouts.app')

@section('title', 'Add Student')

@section('content')
<h2>Add Student</h2>
@if ($errors->any())
    <div style="color:red; margin-bottom:10px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form method="POST" action="{{ route('students.store') }}">
    @csrf

    <label>Student No</label><br>
    <input type="text" name="student_no" value="{{ old('student_no') }}" required><br><br>

    <label>First Name</label><br>
    <input type="text" name="first_name" value="{{ old('first_name') }}" required><br><br>

    {{-- ✅ Middle Name --}}
    <label>Middle Name</label><br>
    <input type="text" name="middle_name" value="{{ old('middle_name') }}"><br><br>

    <label>Last Name</label><br>
    <input type="text" name="last_name" value="{{ old('last_name') }}" required><br><br>

    <label>Gender:</label>
    <select name="gender">
        <option value="">Select Gender</option>
        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
    </select>
    <br><br>

    <label>Date of Birth:</label>
    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
    <br><br>

    <label>Program</label><br>
    <input type="text" name="program" value="{{ old('program') }}" required><br><br>

    <label>Year Level</label><br>
    <input type="text" name="year_level" value="{{ old('year_level') }}" required><br><br>


    {{-- ✅ Has Scholarship --}}
    <label>Has Scholarship?</label><br>
    <select name="has_scholarship" id="has_scholarship">
        <option value="0" {{ old('has_scholarship') == 0 ? 'selected' : '' }}>No</option>
        <option value="1" {{ old('has_scholarship') == 1 ? 'selected' : '' }}>Yes</option>
    </select>
    <br><br>

    {{-- ✅ Scholarship Type --}}
    <div id="scholarship_type_container" style="display:none;">
        <label>Scholarship Type</label><br>
        <select name="scholarship_type">
            <option value="">Select Type</option>
            <option value="Indigency" {{ old('scholarship_type') == 'Indigency' ? 'selected' : '' }}>
                Indigency
            </option>
            <option value="Partnership" {{ old('scholarship_type') == 'Partnership' ? 'selected' : '' }}>
                Partnership
            </option>
            <option value="SHS Graduate" {{ old('scholarship_type') == 'SHS Graduate' ? 'selected' : '' }}>
                SHS Graduate
            </option>
        </select>
        <br><br>
    </div>

    <label>Status</label><br>
<select name="status" required>
    <option value="Active"    {{ old('status') == 'Active'    ? 'selected' : '' }}>Active</option>
    <option value="Dropped"   {{ old('status') == 'Dropped'   ? 'selected' : '' }}>Dropped</option>
    <option value="Graduated" {{ old('status') == 'Graduated' ? 'selected' : '' }}>Graduated</option>
</select><br><br>

    <label>Enrollment Year</label><br>
    <input type="number" name="enrollment_year" value="{{ old('enrollment_year', date('Y')) }}"><br><br>
    <h3>Academic Records</h3> 

<div id="records-container">

    <div class="record-row">

        <select name="records[0][academic_period_id]" required>
            <option value="">Select Term</option>
            @foreach($academicPeriods as $period)
                <option value="{{ $period->id }}">
                    {{ $period->academic_year }} - {{ $period->term }}
                </option>
            @endforeach
        </select>

        <button type="button" onclick="openModal()">+ Add Academic Period</button>

        <input type="number" step="0.01" name="records[0][grade]" placeholder="Grade">
        <input type="number" step="0.01" name="records[0][attendance]" placeholder="Attendance">
        <input type="number" step="0.01" name="records[0][tuition_total]" placeholder="Tuition Total">
        <input type="number" step="0.01" name="records[0][tuition_paid]" placeholder="Tuition Paid">

    </div>
</div>


<br>
<button type="button" onclick="addRecord()">+ Add Term</button>

<br><br>
<button type="submit">Save Student</button>

</form>


{{-- ================= MODAL ================= --}}
<div id="periodModal" style="
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    z-index:999;
">

    <div style="
        background:white;
        width:300px;
        margin:10% auto;
        padding:20px;
        border-radius:8px;
    ">

        <h4>Add Academic Period</h4>

        <label>Academic Year</label><br>
        <input type="text" id="modal_year" placeholder="e.g. 2018-2019"><br><br>

        <label>Term</label><br>
        <select id="modal_term">
            <option value="1st">1st</option>
            <option value="2nd">2nd</option>
            <option value="3rd">3rd</option>
        </select><br><br>

        <button type="button" onclick="savePeriod()">Save</button>
        <button type="button" onclick="closeModal()">Cancel</button>

    </div>
</div>


{{-- ================= SCRIPT ================= --}}
<script>

// ================= SCHOLARSHIP =================
document.addEventListener('DOMContentLoaded', function () {

    const hasScholarship = document.getElementById('has_scholarship');

    if (hasScholarship) {
        function toggleScholarshipType() {
            const container = document.getElementById('scholarship_type_container');
            container.style.display = hasScholarship.value == 1 ? 'block' : 'none';
        }

        toggleScholarshipType();
        hasScholarship.addEventListener('change', toggleScholarshipType);
    }

});

// ================= RECORDS =================
let recordIndex = 1;

function addRecord() {
    let container = document.getElementById('records-container');

    let html = `
    <div class="record-row">

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
}


// ================= MODAL =================
function openModal() {
    document.getElementById('periodModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('periodModal').style.display = 'none';
}


// ================= SAVE =================
function savePeriod() {
    let year = document.getElementById('modal_year').value;
    let term = document.getElementById('modal_term').value;

    if (!year || !term) {
        alert('Please fill all fields');
        return;
    }

    fetch('/academic-periods', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            academic_year: year,
            term: term
        })
    })
    .then(res => res.json())
    .then(() => {
        alert('Academic Period Added!');
        location.reload();
    })
    .catch(() => {
        alert('Error saving data');
    });
}

</script>

@endsection