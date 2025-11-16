<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/personalize.css'])
        
        <title>VinyDeskline </title>
    </head>
    <body>
        <div class="login-container">
            <h1 class="login-title"> Welcome to VinyDeskline</h1>
            <p class="login-motto">
                Let us <span class="highlight">elevate</span> your working experience!
            </p>
            <p class="info">
                The app can provide a personalized experience.
                Currently, the height is set to a default value.
                You can choose to share your height with the app to customize your experience. </p>
            <p class="info2">
                Customize your web application by entering your height and age:</p>
            <form class="login-form" method="POST" action="{{ route('personalize.submit') }}">
                @csrf
                <input type="text" id="height" name="height" placeholder="Your Height"><br>
                <input type="text" id="age" name="age" placeholder="Your Age"><br>
                <button type="submit" class="login-btn">Save</button>
            </form>
            <p class="skip"> or <span class="highlight"><a href="{{ route('home') }}">Skip</a></span></p>
        </div>
    </body>
</html>