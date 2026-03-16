<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:
linear-gradient(rgba(0,0,0,0.70), rgba(0,0,0,0.70)),
url('/images/gpt_bg.png');
background-size:cover;
background-position:center;
background-repeat:no-repeat;
font-family:'Segoe UI', sans-serif;
}

.login-card{
width:400px;
border-radius:15px;
background: rgba(255,255,255,0.85);
backdrop-filter: blur(10px);
box-shadow:0 15px 40px rgba(0,0,0,0.35);
transition:transform .2s ease;
}

.login-card:hover{
transform: translateY(-3px);
}

.logo{
width:200px;
margin-bottom:15px;
}

.login-title{
font-weight:600;
}

.btn-login{
background:#0d6efd;
border:none;
transition:all .2s ease;
}

.btn-login:hover{
background:#0b5ed7;
transform:translateY(-1px);
}

.top-logo{
position:fixed;
top:30px;
left:40px;
width:200px;
z-index:1000;
filter: brightness(1);
}

</style>

</head>

<body>

<!-- TOP LEFT LOGO -->
<img src="{{ asset('images/informatics-logo.png') }}" class="top-logo" alt="Informatics Logo">

<div class="card login-card p-4">

<div class="text-center">

<img src="{{ asset('images/informatics-logo.png') }}" class="logo" alt="Informatics Logo">

<h4 class="login-title">Attrition & Retention System</h4>
<p class="text-muted small">Informatics Northgate</p>
<p class="text-muted">Sign in to continue</p>

</div>

<form method="POST" action="/login">

@csrf

<div class="mb-3">
<label class="form-label">Email</label>
<input 
type="email" 
name="email" 
class="form-control" 
placeholder="Enter your email"
required>
</div>

<div class="mb-3">
<label class="form-label">Password</label>
<input 
type="password" 
name="password" 
class="form-control" 
placeholder="Enter your password"
required>
</div>

<button class="btn btn-login w-100 text-white">
Login
</button>

<p class="text-center text-muted small mt-3">
© 2026 Informatics Northgate
</p>

</form>

</div>

</body>
</html>