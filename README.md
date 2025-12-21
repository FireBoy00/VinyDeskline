# VinyDeskline

**Making standing desks smarter, one metric at a time.**

VinyDeskline is a web application that monitors and manages standing desks in office environments. We integrated smart desk API monitoring, environmental sensors, and user ergonomic tracking into one clean dashboard. Basically, we wanted to know if people were actually using standing desks, so we built something to track it.

## What's Inside

-   **Desk Management**: Control electric standing desks, track their positions, and assign them to users
-   **Environmental Monitoring**: Real-time temperature, humidity, and light level tracking via MQTT sensors
-   **Metrics & Analytics**: See sitting/standing patterns, desk usage stats, and personalized ergonomic insights
-   **Admin Dashboard**: Manage users, offices, schedules, and view system-wide analytics
-   **User Personalization**: Height and age input for customized desk height recommendations

## Tech Stack

-   **Backend**: Laravel 12 (PHP 8.2+)
-   **Frontend**: Blade templates + Vite + JavaScript
-   **Database**: SQLite/MySQL
-   **Sensors**: MQTT-enabled Pico W boards
-   **Desk API**: Custom Python REST API for desk control

## Getting Started

### Prerequisites

-   PHP 8.2 or higher
-   Node.js 18+
-   Composer
-   Git

### Installation

1. **Clone and setup**:

    ```bash
    git clone https://github.com/FireBoy00/VinyDeskline
    cd VinyDeskline
    ```

2. **Install dependencies**:

    ```bash
    composer install
    npm install
    ```

3. **Configure environment**:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Database setup**:

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

5. **Run the app**:

    ```bash
    # Terminal 1: Laravel development server
    php artisan serve

    # Terminal 2: Scheduler - desk data sync, sensor listener and defined uniform and cleanning schedules inside the app
    php artisan schedule:work
    ```

    The app will be available at `http://localhost:8000`

6. **(Optional) Start the MQTT listener** for real sensor data:

    > **Important:** **DO NOT RUN** if you already have the scheduler running, as it will start another instance of the listener.

    > **Note:** If you don't have physical Pico W sensors, you can simulate data using the mock command described in the [SENSOR_SYSTEM_DOCUMENTATION.md](./SENSOR_SYSTEM_DOCUMENTATION.md) file.

    ```bash
    php artisan app:listen-mqtt
    ```

## First Time Login

We've pre-seeded test data with 17 users. All test users have password: `password`

Find user emails:

```bash
php artisan tinker
User::all(['name', 'email', 'is_admin'])->toArray()
```

**Quick test accounts:**

-   **Admin**: smit@vinydeskline.com (Sarah Mitchell)
-   **Regular User**: jpet@vinydeskline.com (James Peterson)
-   **New User** (onboarding): cjoh@vinydeskline.com (Christopher Johnson)

## Project Structure

```
VinyDeskline/
├── app/
│   ├── Http/Controllers/     # Request handlers
│   ├── Models/               # Database models
│   ├── Services/             # Business logic (API integration, calculations)
│   ├── Observers/            # Database observers for side effects
│   └── Console/Commands/     # Scheduled tasks (desk sync, metrics collection)
├── database/
│   ├── migrations/           # Database schema
│   ├── seeders/              # Test data generation
│   └── factories/            # Factory classes for creating test data
├── resources/
│   ├── views/                # Blade templates
│   ├── css/                  # Stylesheets
│   └── js/                   # JavaScript files
├── routes/                   # Route definitions
├── config/                   # Application configuration
└── storage/logs/             # Application logs
```

## Key Features

### 🏢 Office Management

-   Organize desks by floors and rooms
-   Assign desks directly to floors without any rooms
-   Track desk locations

### 📊 Metrics & Insights

-   5-minute interval desk usage tracking
-   Sitting vs standing time calculations
-   30-day historical data for analysis
-   Personal usage charts

### 🌡️ Environmental Monitoring

-   Real-time temperature, humidity, and light readings
-   MQTT integration with Pico W sensors
-   Historical environmental data for the office (visible on admin dashboard)

### 👥 User Management

-   Admin controls for user assignment and preferences
-   Personalization on first login (height/age)
-   Role-based access control (admin vs regular users)

### 🔐 Authentication

-   Secure login with hashed passwords
-   Session-based authentication
-   Admin dashboard access controls

## Common Tasks

### View Real-Time Sensor Data - [Read More](./SENSOR_SYSTEM_DOCUMENTATION.md)

```bash
# Start the MQTT listener
php artisan app:listen-mqtt

# Or let it run automatically (every hour)
php artisan schedule:work

# In another terminal, simulate a sensor (for testing)
php artisan app:mock-pico --temp=25.5 --humid=60 --light=700

# Or start a continuous loop (simulates a real Pico W)
php artisan app:mock-pico --loop --interval=5
```

### Sync Desks from API

```bash
# Manual sync
php artisan desks:sync

# Or let it run automatically (every hour)
php artisan schedule:work
```

### Create New Users

```bash
php artisan tinker

# Admin user
User::factory()->admin()->personalized()->create();

# Regular user
User::factory()->personalized()->create();

# User needing onboarding
User::factory()->needsPersonalization()->create();
```

### View Database Data

```bash
php artisan tinker

# Check all users
User::all(['name', 'email', 'is_admin'])->toArray()

# Check desks
Desk::all()->count()

# Check desk metrics for one user
User::find(1)->desk->metrics()->count()
```

## Configuration

Key environment variables (`.env`):

```
DESK_API_BASE_URL=http://localhost:8000
DESK_API_KEY=your-api-key-from-desk-api
```

## Development

### Running Tests

```bash
php artisan test
```

### Database Reset

```bash
# Reset and reseed with fresh test data
php artisan migrate:fresh --seed
```

### Scheduler (for automatic tasks)

```bash
# Run scheduler in foreground (development)
php artisan schedule:work

# Or add to cron (production)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Troubleshooting

**"MQTT connection failed"**: Make sure the MQTT broker is accessible or skip sensor monitoring for now.

**Desks not syncing**: Check that `DESK_API_BASE_URL` is correct and the API server is running.

**Database locked**: Try resetting: `php artisan migrate:fresh --seed`

**Blank dashboard**: Desks need to be synced first with `php artisan desks:sync`

## Team

Developed by 6 students as part of our semester project, **VinyDeskline** brings together knowledge and skills from all the courses we took this term. We combined our expertise to create a fully functional desk management and ergonomics tracking system in just a few weeks.

**Team Members:**

-   Adrian Cristian Stancu
-   Gabija Staškevičiūtė
-   Aleksandra Kwiatkowska
-   Dorina Petra Nagy
-   Jakub Cuninka
-   Tomass Zarins

## License

MIT
