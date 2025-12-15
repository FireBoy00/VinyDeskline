<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/personalize.css', 'resources/js/personalize.js'])

    <title>VinyDeskline - Personalize Your Experience</title>
</head>

<body>
    <div class="personalize-container">
        <div class="logo-section">
            <div class="logo-icon">
                <span class="material-icons-round">tune</span>
            </div>
            <h1 class="personalize-title">Welcome to VinyDeskline</h1>
            <p class="personalize-motto">
                Let us <span class="highlight">elevate</span> your working experience!
            </p>
        </div>

        <div class="info-card">
            <div class="info-header">
                <span class="material-icons-round info-icon">lightbulb</span>
                <h2>Personalize Your Experience</h2>
            </div>
            <p class="info-text">
                Our app provides a personalized experience based on your physical characteristics.
                Currently, your desk height is set to a default value. Share your height and age
                to customize the app for optimal ergonomics and comfort.
            </p>
        </div>

        @if ($errors->any())
            <div class="error-message">
                <span class="material-icons-round error-icon">error_outline</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form class="personalize-form" method="POST" action="{{ route('personalize.submit') }}">
            @csrf
            <div class="input-row">
                <div class="input-group">
                    <label for="height">
                        <span class="material-icons-round">height</span>
                        Height (cm)
                    </label>
                    <input type="number" id="height" name="height" placeholder="e.g., 175" min="100"
                        max="250" step="0.1" value="{{ old('height') }}">
                    <span class="input-hint">Optional: 100-250 cm</span>
                </div>

                <div class="input-group">
                    <label for="age">
                        <span class="material-icons-round">cake</span>
                        Age
                    </label>
                    <input type="number" id="age" name="age" placeholder="e.g., 30" min="18"
                        max="120" value="{{ old('age') }}">
                    <span class="input-hint">Optional: 18+ years</span>
                </div>
            </div>

            <button type="submit" class="save-btn">
                <span>Save Preferences</span>
                <span class="material-icons-round">check_circle</span>
            </button>
        </form>

        <div class="skip-section">
            <p class="skip-text">
                or
                <a href="{{ route('personalize.skip') }}" class="highlight skip-link">
                    Skip for now
                    <span class="material-icons-round">arrow_forward</span>
                </a>
            </p>
            <p class="skip-note">You can always update this later in your account settings</p>
        </div>
    </div>
</body>

</html>
