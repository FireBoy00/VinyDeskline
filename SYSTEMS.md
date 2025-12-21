# VinyDeskline Systems Architecture

This document gives you the breakdown of how all the major systems in the app work together. Think of it as a tour through the codebase.

## 1. Authentication & User Management

**What it does**: Handles login, user roles, and personalization.

**Key Components**:

-   `AuthController` - Manages login/logout flow
-   `IsAdmin` middleware - Checks if user is an admin before allowing access to protected routes
-   `User` model - Stores user data including height, age, and admin status

**How it works**:

1. User logs in with email/password
2. System checks if they're an admin → redirects them differently
3. First-time users see the personalization page (pick height and age)
4. Users can skip personalization and do it later
5. Admin users get extra dashboard access

**Database tables**: `users`

**Routes**: `/login`, `/personalize`, `/home`, `/logout`

**Files to look at**:

-   `app/Http/Controllers/AuthController.php` - The login logic
-   `app/Models/User.php` - User data structure
-   `resources/views/login.blade.php` - The login page UI
-   `resources/views/personalize.blade.php` - Personalization page

**Test it**: Login as smit@vinydeskline.com (admin) vs jpet@vinydeskline.com (regular user)

---

## 2. Desk Management System

**What it does**: Connects to external smart desks, tracks them in the database, and lets admins organize them into offices.

**Key Components**:

-   `DeskApiService` - Talks to the external Desk API (Python REST API)
-   `DeskController` - Admin endpoints for desk operations
-   `Desk` model - Represents a desk in our database
-   `DeskMetric` model - Historical height data from desks
-   `SyncDesksFromApi` command - Syncs desk data from API to database (runs hourly)
-   `CollectDeskMetrics` command - Records desk heights every 5 minutes

**How it works**:

1. External desk API runs (either locally or remotely)
2. Our sync command fetches all desk IDs from the API
3. We store them in the database with their locations (floor/room)
4. Every 5 minutes, we record the height of each desk
5. When users adjust their desk height from the app, we send commands to the API

**Database tables**: `desks`, `desk_metrics`, `floors`, `rooms`

**Key commands**:

```bash
php artisan desks:sync              # Manually sync desks from API
php artisan desks:collect-metrics   # Manually collect height data
php artisan schedule:work           # Run all scheduled tasks
```

**Files to look at**:

-   `app/Services/DeskApiService.php` - The API integration
-   `app/Http/Controllers/DeskController.php` - Admin desk management
-   `app/Console/Commands/SyncDesksFromApi.php` - Sync logic
-   `app/Console/Commands/CollectDeskMetrics.php` - Metrics collection
-   `resources/views/arrangement.blade.php` - The desk control interface

**How data flows**:

```
External Desk API
    ↓
SyncDesksFromApi command (hourly)
    ↓
Desks stored in database
    ↓
Every 5 minutes: CollectDeskMetrics reads desk heights
    ↓
Heights stored in desk_metrics table
    ↓
Admin views them in dashboard, users see them in their charts
```

---

## 3. Environmental Sensor System

**What it does**: Collects temperature, humidity, and light levels from sensors around the office via MQTT.

**Key Components**:

-   `ListenMqtt` command - Listens to an MQTT broker for sensor data
-   `SensorMetric` model - Stores sensor readings
-   Frontend components that display current readings

**How it works**:

1. Pico W boards with sensors publish data to MQTT broker
2. Our listener subscribes to the broker and receives messages
3. We store readings in the database (max once per 60 seconds to avoid spam)
4. Frontend polls the database every 30 seconds to show latest readings
5. Admin dashboard shows 24-hour historical data

**Database tables**: `sensor_metrics`

**Key commands**:

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

**MQTT Details**:

-   Broker: `broker.hivemq.com` (or your own)
-   Topic: `pico/sensors`
-   Expected format: `{ "temperature": 23.5, "humidity": 52, "light": 610 }`

**Files to look at**:

-   `app/Console/Commands/ListenMqtt.php` - The listener
-   `app/Models/SensorMetric.php` - Data storage
-   `resources/views/home.blade.php` - Where sensor data shows up

**Test it**: Run `php artisan app:mock-pico --temp=26.2 --humid=80 --light=850` in one terminal while viewing `/home` in another. Should see updated readings.

---

## 4. Metrics & Analytics System

**What it does**: Collects user desk metrics and creates visualizations.

**Key Components**:

-   `DeskMetric` model - Stores height readings every 5 minutes
-   `HeightCalculationService` - Figures out if user is sitting or standing
-   Frontend charts that visualize the data

**How it works**:

1. Every 5 minutes, we record the height of each user's desk
2. Heights < 900mm = sitting, > 900mm = standing
3. We calculate daily sitting/standing time
4. Charts show personal usage patterns and height history
5. 30 days of historical data is kept

**Database tables**: `desk_metrics`

**What users see**:

-   "Your Statistics" chart - Daily sitting vs standing time
-   "Desk Height Across One Day" chart - Height changes throughout the day

**Files to look at**:

-   `app/Services/HeightCalculationService.php` - Sitting/standing logic
-   `app/Http/Controllers/HomeController.php` - Metrics retrieval
-   `resources/js/home.js` - Chart rendering

**Data example**:

```
User logs in at 8 AM
Desk at 750mm (sitting) 8 AM - 10:30 AM
Desk at 1050mm (standing) 10:30 AM - 12:30 PM
Desk at 750mm (sitting) 12:30 PM - 5 PM
Chart shows: 6.5 hours sitting, 2 hours standing
```

---

## 5. Office Organization System

**What it does**: Lets admins organize desks into floors and rooms, then assign users to desks.

**Key Components**:

-   `OfficeManagementController` - Admin endpoints for floors/rooms
-   `Floor` model - Office floors
-   `Room` model - Office rooms (assigned to floors)
-   `Desk` model - Has relationships to both floor and room

**How it works**:

1. Admin creates a floor (e.g., "Ground Floor")
2. Admin creates rooms on that floor (e.g., "Development Team", "Meeting Room")
3. Admin assigns desks to rooms/floors
4. Admin assigns users to desks
5. System prevents duplicate desk assignments
6. Users without desks get redirected to a "no desk" page

**Database tables**: `floors`, `rooms`, `desks`, `users` (has desk_id)

**Database relationships**:

-   Floor has many Rooms
-   Room has many Desks
-   Desk belongs to Room and Floor
-   User belongs to Desk

**Files to look at**:

-   `app/Http/Controllers/OfficeManagementController.php` - All floor/room operations
-   `app/Models/Floor.php`, `Room.php`, `Desk.php` - Data models
-   `resources/views/office-management.blade.php` - Admin interface

**How locations cascade**:

```
Move Room to different Floor
    ↓
All Desks in that Room automatically move too
    ↓
All Users with those Desks stay assigned
    ↓
Desk counts update automatically
```

---

## 6. Frontend & UI System

**What it does**: Renders the web interface using Blade templates and JavaScript.

**Key Technologies**:

-   **Blade**: Laravel's templating engine (like PHP with superpowers)
-   **Vite**: Bundler for CSS and JavaScript (faster development)
-   **Material Icons**: Icon library we use throughout
-   **Chart.js**: For graphing metrics

**Files to look at**:

-   `resources/views/` - All HTML templates
-   `resources/css/` - Stylesheets
-   `resources/js/` - JavaScript files
-   `resources/views/layouts/` - Base template structure

**Color Scheme** (consistent across the app):

-   Primary Blue: `#0485B9`
-   Dark Accent: `#004F6E`
-   Light Background: `#C6DAE2`
-   Card Background: `#DCE5E9`

**Key Pages**:

-   `login.blade.php` - Login form
-   `personalize.blade.php` - First-time height/age setup
-   `home.blade.php` - User dashboard with metrics charts
-   `dashboard.blade.php` - Admin dashboard
-   `arrangement.blade.php` - Desk control interface (admin)
-   `office-management.blade.php` - Floor/room organization (admin)

---

## 7. Database & Models

**What it does**: Stores all the data and defines relationships between things.

**Key Models**:

-   `User` - People using the system
-   `Desk` - Physical smart desks
-   `DeskMetric` - Height readings from desks
-   `SensorMetric` - Environmental sensor readings
-   `Floor` - Office floors
-   `Room` - Office rooms
-   `Schedule` - Automated desk adjustments

**Relationships** (simplified):

```
User → Desk → DeskMetric (user's desk history)
User → Desk → Room → Floor (user's office location)
Floor → Room → Desk (organizational hierarchy)
Building → SensorMetric (environmental data)
```

**Migration Files** (where schema changes live):

-   `2025_01_01_000000_create_users_table.php`
-   `2025_11_19_143539_create_desks_table.php`
-   And many more...

**Seeders** (populate test data):

-   `DatabaseSeeder.php` - Main seeder that creates 17 test users, desks, metrics, etc.

**Files to look at**:

-   `app/Models/` - All model definitions
-   `database/migrations/` - Schema definitions
-   `database/seeders/` - Test data generation

---

## 8. Scheduled Tasks (Cron Jobs)

**What it does**: Runs background jobs automatically on a schedule.

**Jobs that run**:

-   **Hourly**: Sync desks from API (`desks:sync`)
-   **Every 5 minutes**: Collect desk metrics (`desks:collect-metrics`)
-   **Always running** (in background): Listen for MQTT sensor data (`app:listen-mqtt`)

**How to run them**:

Development:

```bash
php artisan schedule:work
```

Production (add to crontab):

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Files to look at**:

-   `app/Console/Commands/` - All command definitions
-   `app/Providers/AppServiceProvider.php` - Where schedule is defined

---

## 9. API Service Integration

**What it does**: Connects our Laravel app to the external Desk API.

**The External Desk API**:

-   Python REST API that controls physical smart desks
-   Runs separately (usually on `localhost:8000`)
-   Provides endpoints to get desk data and set positions

**Our Integration** (`DeskApiService`):

-   Handles authentication with API key
-   Fetches desk lists and individual desk data
-   Sends height adjustment commands
-   Handles timeouts and errors gracefully

**Configuration** (in `.env`):

```
DESK_API_BASE_URL=http://localhost:8000
DESK_API_KEY=your-api-key
```

**Files to look at**:

-   `app/Services/DeskApiService.php` - The integration logic

---

## 10. Data Flow: From Desk to Chart

Here's how data flows through the entire system:

```
1. DESK SYNC (Hourly)
   ↓
   External Desk API
   ↓
   SyncDesksFromApi command fetches desk IDs
   ↓
   Stored in "desks" table

2. METRICS COLLECTION (Every 5 minutes)
   ↓
   CollectDeskMetrics command runs
   ↓
   For each desk, fetch current height from API
   ↓
   Calculate: is_sitting = (height < 900mm)
   ↓
   Store in "desk_metrics" table

3. USER VIEWS DASHBOARD
   ↓
   Home page loads (/home)
   ↓
   JavaScript calls /home/metrics endpoint
   ↓
   HomeController fetches last 30 days of metrics
   ↓
   JavaScript renders charts:
     - Daily sitting/standing time
     - Height changes throughout day
   ↓
   User sees their personal analytics
```

---

## Quick Dev Checklist

When working on the app, remember:

-   Routes are in `routes/web.php`
-   Controllers handle the logic in `app/Http/Controllers/`
-   Models define data structure in `app/Models/`
-   Views (HTML) are in `resources/views/`
-   Styles are in `resources/css/`
-   JavaScript is in `resources/js/`
-   Database changes go in `database/migrations/`
-   Test data generation in `database/seeders/` and `database/factories/`

When you need to:

-   **Add a feature**: Create controller → add routes → add view
-   **Change database**: Create migration → update model → update seeder
-   **Add a scheduled task**: Create command → register in `AppServiceProvider`
-   **Fix a bug**: Check logs in `storage/logs/laravel.log`

---

## Running Everything Together

To get the full system working:

```bash
# Terminal 1: Web server
php artisan serve

# Terminal 2: Frontend build system
npm run build

# Terminal 3: Scheduled tasks (desk sync, metrics collection)
php artisan schedule:work

# Terminal 4: Sensor listener (if you have MQTT & DID NOT run scheduler)
php artisan app:listen-mqtt

# Or test with mock sensor
php artisan app:mock-pico --loop --interval=5
```

Then visit `http://localhost:8000` and start exploring!

---

That's the whole system. It's a lot, but each part has a clear job. Start with authentication, then explore desk management, then metrics.
