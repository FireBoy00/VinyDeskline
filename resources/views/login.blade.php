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
            <h1 class="login-title"> Welcome to VinyDeskline</h1>
            <p class="login-motto">
                Let us <span class="highlight">elevate</span> your working experience!
            </p>
            <form class="login-form" method="POST" action="{{ route('login.submit') }}">
                @csrf
                <input type="text" id="email" name="email" placeholder="Email"><br>
                <input type="text" id="pass" name="password" placeholder="Password"><br>
                <button type="submit" class="login-btn">Log In</button>
            </form>
            <p class="forgot_pass"> Forgot your password? <span class="highlight"><a href="#">Click here</a></span></p>
        </section>
    </body>
</html>