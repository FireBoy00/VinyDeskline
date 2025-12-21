# Metrics Integration to Home Page Graphs

## Overview

The home page graphs have been successfully hooked to the desk_metrics database table. Both graphs can now display real usage data from the database, with automatic fallback to generated data if no metrics are available.

## What Was Changed

### 1. Backend - HomeController (`app/Http/Controllers/HomeController.php`)

**Added new method: `getDeskMetrics()`**

-   Fetches desk metrics for the authenticated user's desk
-   Returns metrics from the last 30 days
-   Retrieves: `desk_id`, `height_mm`, `is_sitting`, `recorded_at`
-   Returns JSON response with metrics data or error message

### 2. Routes (`routes/web.php`)

**Added new route:**

```php
Route::get('/home/metrics', [HomeController::class, 'getDeskMetrics'])->name('home.metrics');
```

-   Accessible to authenticated users
-   Endpoint: `/home/metrics`

### 3. Frontend - Home.js (`resources/js/home.js`)

#### New Functions Added:

1. **`fetchDeskMetrics()`** - Async function that fetches real metrics from API

    - Calls `/home/metrics` endpoint
    - Handles errors gracefully with fallback data
    - Returns array of metrics objects

2. **`generateFallbackMetrics()`** - Creates mock data if API fails

    - Generates 30 days of realistic desk data
    - One entry every hour from 8 AM to 5 PM
    - 50/50 sitting vs standing distribution

3. **`calculateDailyDurations(metrics)`** - Analyzes metrics to compute daily sitting/standing time

    - Groups metrics by day
    - Calculates total sitting and standing minutes per day
    - Returns object with daily duration breakdowns

4. **`generateHeightHistoryFromMetrics(metrics)`** - Filters metrics for height chart
    - Extracts last day's data for detailed visualization
    - Falls back to last 48 data points if no full day available
    - Returns x (timestamps) and y (heights) arrays

#### Modified Functions:

1. **`renderDailyUsageChart()`** - Enhanced to use real metrics

    - Uses `calculateDailyDurations()` to get real data
    - Displays last 5 days with data (instead of fixed M-F)
    - Falls back to generated data if metrics unavailable
    - Shows actual minutes spent in each position

2. **`renderHeightHistoryChart()`** - Enhanced to use real metrics

    - Uses `generateHeightHistoryFromMetrics()` for real data
    - Plots actual desk height changes over time
    - Falls back to simulated data if no real metrics

3. **DOM Initialization (DOMContentLoaded event)**
    - Now async to wait for metrics fetch
    - Calls `fetchDeskMetrics()` before rendering charts
    - Charts automatically render with real or fallback data

## Data Source

The metrics table contains:

-   **desk_id**: Identifier of the desk
-   **height_mm**: Current desk height in millimeters
-   **is_sitting**: Boolean flag (true if height < 900mm, false for standing)
-   **recorded_at**: Timestamp when metric was recorded

## Graph Analysis

### Graph 1: "Your Statistics" (Daily Desk Usage Duration)

**Data Available:** ✅ YES

-   Uses `is_sitting` flag to determine position
-   Uses `recorded_at` timestamps to calculate time spent in each position
-   Displays minutes of sitting vs standing per day
-   Shows last 5 days with available data

**Why it works:**
The `is_sitting` boolean and timestamp are all we need to calculate how long the user spent sitting vs standing each day.

### Graph 2: "Desk Height Across One Day" (Height History)

**Data Available:** ✅ YES

-   Uses `height_mm` values directly
-   Uses `recorded_at` timestamps for x-axis
-   Displays height changes throughout the day
-   Shows last day's data or falls back to recent 48 points

**Why it works:**
The `height_mm` field provides the exact desk height at each measurement point, and timestamps show when changes occurred.

## For Non-Admin (Single Desk) Users

Both graphs are designed specifically for single-desk users and show their personal desk metrics:

-   Daily usage broken down by sitting/standing time
-   Height changes throughout a single day
-   Personal statistics for self-improvement

The system automatically uses the user's assigned `desk_id` to fetch their specific metrics.

## Fallback Behavior

If a user has no desk assigned or no metrics data:

1. API returns error response
2. `fetchDeskMetrics()` catches error and calls `generateFallbackMetrics()`
3. Charts render with realistic demo data
4. User can still see how graphs work with sample data

## Data Flow

```
User visits /home
    ↓
JavaScript runs on DOMContentLoaded
    ↓
fetchDeskMetrics() called
    ↓
API request to /home/metrics
    ↓
HomeController::getDeskMetrics()
    ↓
Query desk_metrics table for user's desk
    ↓
Return JSON with real metrics
    ↓
renderDailyUsageChart() + renderHeightHistoryChart()
    ↓
Charts display real user data
```

## Testing

1. Login as Sarah Mitchell (smit@vinydeskline.com)
2. View /home page
3. Check browser console - should show metrics fetched successfully
4. "Your Statistics" graph shows last 5 days of real sitting/standing data
5. "Desk Height Across One Day" shows today's actual desk height changes

Available test data:

-   Sarah Mitchell: 50 metrics (Nov 20 - Dec 19)
-   Michael Chen: 40 metrics
-   Alex Rodriguez: 35 metrics
-   James Peterson: 60 metrics
-   And others as defined in DatabaseSeeder
