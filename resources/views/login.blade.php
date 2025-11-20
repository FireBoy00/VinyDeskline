<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/login.css'])
        
        <title>VinyDeskline - Log In</title>
    </head>
    <body>
        <section class="login-container">
            <div class="logo-section">
                <div class="logo-icon">
                    <span class="material-icons-round">desk</span>
                </div>
                <h1 class="login-title">Welcome to VinyDeskline</h1>
                <p class="login-motto">
                    Let us <span class="highlight">elevate</span> your working experience!
                </p>
            </div>

            @if ($errors->any())
                <div class="error-message">
                    <span class="material-icons-round error-icon">error_outline</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form class="login-form" method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="input-group">
                    <span class="material-icons-round input-icon">email</span>
                    <input type="email" id="email" name="email" placeholder="Email" required value="{{ old('email') }}">
                </div>
                <div class="input-group">
                    <span class="material-icons-round input-icon">lock</span>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="login-btn">
                    <span>Log In</span>
                    <span class="material-icons-round">arrow_forward</span>
                </button>
            </form>

            <p class="forgot-pass">
                Need help? <span class="highlight"><a href="#">Contact Admin</a></span>
            </p>
        </section>
    </body>
</html>