# Sensor System Documentation

## Overview

The VinyDeskline sensor system collects environmental data (temperature, humidity, and light) from Pico W boards distributed throughout the office. This data is used to provide real-time feedback to users on their home page and historical environmental trends to administrators on the dashboard.

## Architecture

1.  **Pico W**: Sends JSON-formatted sensor data to a public MQTT broker.
2.  **MQTT Broker**: `broker.hivemq.com` acts as the intermediary.
3.  **Laravel Listener**: A background process (`app:listen-mqtt`) subscribes to the broker and stores data in the database.
4.  **Database**: The `sensor_metrics` table stores the historical records.
5.  **Frontend**:
    -   **Home Page**: Polls the database every 30 seconds for the latest reading.
    -   **Admin Dashboard**: Fetches the last 24 hours of data for historical graphing.

## Data Format

The system expects JSON messages on the topic `pico/sensors`:

```json
{
    "temperature": 23.5,
    "humidity": 52,
    "light": 610
}
```

## Background Listener

The listener is responsible for capturing data even when no users are active on the site.

-   **Command**: `php artisan app:listen-mqtt`
-   **Storage Interval**: To prevent database bloat, the listener is configured to save a record at most once every **60 seconds**.
-   **Persistence**: The command is hooked into the Laravel Scheduler. Running `php artisan schedule:work` will ensure the listener stays active.

## Testing & Simulation

If you do not have a physical Pico W connected, you can simulate one using the built-in mock command.

### 1. Start the Listener

In your first terminal, start the scheduler or the listener directly:

```powershell
php artisan app:listen-mqtt
```

### 2. Run the Mock Pico

In a second terminal, use the mock command to send data:

```powershell
# Send a single custom reading
php artisan app:mock-pico --temp=25.5 --humid=45 --light=700

# Start a continuous loop (simulates a real Pico W)
# Sends data every 5 seconds with slight random fluctuations
php artisan app:mock-pico --loop --interval=5
```

### 3. Verify the Results

-   **Home Page**: Open `/home`. The sensor carousel will update within 30 seconds of the mock data being sent.
-   **Admin Dashboard**: Open `/admin/dashboard`. The "Environmental Data Overview" graph will show the new data points (refreshes every 10 seconds).

## Database Schema

The `sensor_metrics` table contains:

-   `id`: Primary key
-   `temperature`: Float (Celsius)
-   `humidity`: Float (%)
-   `light`: Float (Lux)
-   `recorded_at`: Timestamp of the reading
-   `created_at/updated_at`: Laravel timestamps
