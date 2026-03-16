<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>@yield('title', 'Attrition & Retention Dashboard')</title>

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<!-- Dashboard CSS -->
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

</head>

<body>

<div class="app-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <h2 class="logo">Analytics</h2>

        <nav class="sidebar-menu">

<a href="{{ route('dashboard') }}"
class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
<i class="bi bi-speedometer2"></i>
<span>Dashboard</span>
</a>

<a href="{{ route('students.index') }}"
class="{{ request()->routeIs('students.*') ? 'active' : '' }}">
<i class="bi bi-mortarboard"></i>
<span>Students</span>
</a>

</nav>

        @auth
        <div class="sidebar-footer">

            <div class="user-info">
                {{ Auth::user()->name }} <br>
                <small>{{ ucfirst(Auth::user()->role) }}</small>
            </div>

            <a href="{{ route('account.settings') }}">
    <i class="bi bi-gear"></i>
    <span>Settings</span>
</a>

<form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit" class="logout-btn">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </button>
</form>

        </div>
        @endauth

    </aside>


    <!-- MAIN CONTENT -->
    <main class="main-content">

        <div class="dashboard-wrapper">
            @yield('content')
        </div>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const toggle = document.getElementById("sidebarToggle");
    const sidebar = document.querySelector(".sidebar");

    toggle.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
    });

});
</script>

</body>
</html>
