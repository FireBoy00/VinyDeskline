# VinyDeskline - Seeders & Factories Reference Guide

## Overview

This document provides a comprehensive guide to the newly created seeders and factories for the VinyDeskline desk management system. The data is designed to be realistic and cover a wide range of use cases for product testing and demonstration.

## Factories Created

### 1. **FloorFactory** (`database/factories/FloorFactory.php`)

Creates realistic floor objects representing office building levels.

**Default Behavior:**

-   Auto-increments floor numbers (Ground Floor, First Floor, etc.)
-   Includes realistic descriptions for each floor
-   Supports specific floor creation

**Usage Examples:**

```php
// Create a random floor
Floor::factory()->create();

// Create specific floor
Floor::factory()->floor(2)->create();

// Create multiple floors
Floor::factory()->count(5)->create();
```

**Generated Data:**

-   Realistic floor names and numbers
-   Descriptive text about floor purpose
-   Timestamps

---

### 2. **RoomFactory** (`database/factories/RoomFactory.php`)

Generates office rooms with various purposes.

**Default Behavior:**

-   Creates rooms with realistic names (Open Office, Meeting Room, Team Spaces, etc.)
-   Assigns rooms to existing floors
-   Includes descriptive information

**Special Methods:**

-   `onFloor(Floor $floor)` - Place room on specific floor
-   `openOffice()` - Create open office workspace
-   `meetingRoom()` - Create meeting room
-   `team(string $teamName)` - Create team-specific room

**Usage Examples:**

```php
// Create random room
Room::factory()->create();

// Create room on specific floor
$floor = Floor::first();
Room::factory()->onFloor($floor)->create();

// Create specific room types
Room::factory()->meetingRoom()->create();
Room::factory()->team('Development')->create();

// Create multiple rooms
Room::factory()->count(10)->create();
```

**Room Types:**

-   Open Office A, B, C
-   Meeting Rooms 1, 2, 3
-   Executive Suite
-   Conference Room
-   Team-specific spaces (Development, Design, Sales, etc.)
-   Support spaces (HR, Finance, Break Room)
-   Specialized rooms (Server Room, Training, Innovation Lab)

---

### 3. **UserFactory** (`database/factories/UserFactory.php`)

Enhanced factory with comprehensive user profiles.

**Default Behavior:**

-   Generates realistic names (first and last)
-   Creates appropriate email addresses
-   Includes password hashing
-   Sets basic user attributes

**Special Methods:**

#### Personalization States:

-   `personalized()` - User completed onboarding with height/age data
-   `needsPersonalization()` - New user without personalization data
-   `withCustomHeights()` - User with custom height presets
-   `admin()` - Admin user with elevated privileges

#### Height Profiles:

-   `tall()` - User height 185-210cm (optimal heights adjusted accordingly)
-   `short()` - User height 150-165cm
-   `average()` - User height 165-185cm

#### Age Profiles:

-   `young()` - Age 22-35 years
-   `senior()` - Age 50-70 years

#### Other:

-   `withDesk(string $deskId)` - Assign user to specific desk
-   `unverified()` - Email not verified

**Usage Examples:**

```php
// Create personalized admin
User::factory()->admin()->personalized()->create();

// Create tall developer
User::factory()->tall()->personalized()->create();

// Create new employee
User::factory()->needsPersonalization()->create();

// Create user with custom heights
User::factory()->withCustomHeights()->create();

// Create multiple users
User::factory()->count(20)->personalized()->create();
```

**Email Format:**

-   Pattern: `{firstLetter}{first4LettersLastName}@vinydeskline.com`
-   Example: `Sarah Mitchell` → `smit@vinydeskline.com`

**Password:** All factory-created users have password `password`

---

### 4. **DeskFactory** (`database/factories/DeskFactory.php`)

Creates desk objects with realistic identifiers and relationships.

**Default Behavior:**

-   Generates realistic MAC address as desk_id (e.g., `00:ec:eb:50:c2:c8`)
-   Assigns desks to random rooms/floors
-   Creates unique desk names

**Special Methods:**

-   `inRoom(Room $room)` - Place desk in specific room
-   `onFloor(Floor $floor)` - Place desk on specific floor (not in room)
-   `withId(string $deskId)` - Assign specific desk ID
-   `named(string $name)` - Set specific desk name
-   `standing()` - Create standing desk
-   `removed()` - Mark as removed from API

**Usage Examples:**

```php
// Create random desk
Desk::factory()->create();

// Create desk in specific room
$room = Room::first();
Desk::factory()->inRoom($room)->create();

// Create desk with specific ID
Desk::factory()->withId('00:ec:eb:50:c2:c8')->create();

// Create standing desk
Desk::factory()->standing()->create();

// Create multiple desks
Desk::factory()->count(50)->create();
```

**Note:** Desks are typically populated from the external API via sync command. These factories are useful for testing without live API data.

---

### 5. **DeskMetricFactory** (`database/factories/DeskMetricFactory.php`)

Generates desk height metrics simulating real usage patterns.

**Default Behavior:**

-   Creates sitting/standing position metrics with realistic proportions
-   Records height in millimeters (sitting: 700-850mm, standing: 1000-1200mm)
-   Timestamps from last 30 days
-   Automatically calculates sitting/standing status

**Special Methods:**

-   `sitting()` - Create sitting position metric (700-850mm)
-   `standing()` - Create standing position metric (1000-1200mm)
-   `forDesk(string $deskId)` - Assign to specific desk
-   `withHeight(int $heightMm)` - Set specific height
-   `fromDateRange(string $start, string $end)` - Specify date range

**Usage Examples:**

```php
// Create random metric
DeskMetric::factory()->create();

// Create sitting position
DeskMetric::factory()->sitting()->create();

// Create standing position
DeskMetric::factory()->standing()->create();

// Create metric for specific desk
DeskMetric::factory()->forDesk('00:ec:eb:50:c2:c8')->create();

// Create metric from specific date range
DeskMetric::factory()->fromDateRange('-7 days', 'now')->create();

// Create multiple metrics for analysis
DeskMetric::factory()->count(100)->create();
```

**Height Ranges:**

-   **Sitting:** 700-850mm (most realistic when user is seated)
-   **Standing:** 1000-1200mm (typical standing desk height)
-   **Transition:** 850-1000mm (moving between positions)

---

### 6. **ScheduleFactory** (`database/factories/ScheduleFactory.php`)

Creates desk schedule entries for automated adjustments and cleaning.

**Default Behavior:**

-   Creates schedules for uniform (sit/stand rotation) and cleaning
-   Random times and frequencies
-   Includes realistic heights and durations

**Special Methods:**

#### Schedule Types:

-   `uniformSchedule()` - Create posture adjustment schedule
-   `cleaningSchedule()` - Create desk cleaning schedule

#### Position Types:

-   `sitting()` - Sitting position height
-   `standing()` - Standing position height

#### Frequency:

-   `daily()` - Recurring daily schedule
-   `once()` - One-time schedule (future date)
-   `multiple()` - Multiple times schedule

**Usage Examples:**

```php
// Create random schedule
Schedule::factory()->create();

// Create daily uniform schedule
Schedule::factory()->uniformSchedule()->daily()->create();

// Create cleaning schedule
Schedule::factory()->cleaningSchedule()->create();

// Create one-time schedule
Schedule::factory()->once()->create();

// Create multiple schedules
Schedule::factory()->count(10)->create();
```

**Pre-configured Schedules in Seeder:**

-   8:00-10:30 - Morning Sitting Session
-   10:30-12:00 - Late Morning Standing
-   12:00-13:00 - Lunch Break Sitting
-   13:00-16:00 - Afternoon Standing Work
-   16:00-17:30 - End of Day Sitting
-   17:30-19:00 - Evening Desk Sanitization

---

## DatabaseSeeder Overview

The `DatabaseSeeder` creates a comprehensive, realistic test dataset.

### Data Structure Created:

#### 4 Floors

-   Ground Floor (Open Office, Reception, Boardroom)
-   First Floor (Executive Suite, HR)
-   Second Floor (Development Teams, QA, DevOps)
-   Third Floor (Design, Marketing, Sales)

#### 12 Rooms across floors

-   Open offices with 15 desks each
-   Executive and meeting spaces
-   Department-specific workspaces

#### 7 Schedules

-   5 daily sit/stand rotation schedules
-   2 cleaning schedules

#### 22 Users with diverse profiles:

**Admin Users (3):**

1. Sarah Mitchell - CEO, tall, executive preferences
2. Michael Chen - Operations Manager, average height
3. Alex Rodriguez - Tech Lead, short, custom heights

**Regular Users - Development (3):**

1. James Peterson - Backend Developer, tall
2. Emma Thompson - Frontend Developer, young, custom heights
3. Robert Williams - Senior Developer

**Regular Users - Design (2):**

1. Lisa Novak - Designer, tall, custom heights
2. Daniel Park - UI Designer, short

**Regular Users - Business (2):**

1. Jessica Adams - Marketing Manager
2. Kevin Martinez - Sales Rep, tall, young

**Users Needing Onboarding (4):**

1. Christopher Johnson - New employee
2. Amanda White - Recent hire
3. Thomas Brown - Contractor, unverified
4. Victoria Garcia - Returning employee

**Additional Diverse Users (3):**

1. Oliver Anderson - Very tall developer (210cm)
2. Sophie Fisher - Very short employee (155cm), with custom heights
3. William Taylor - Mid-age professional, dual custom heights

### Key Data Characteristics:

**Diversity Coverage:**

-   ✅ Age range: 24-58 years
-   ✅ Height range: 155-210cm (including very short/very tall users)
-   ✅ Personalization states: Onboarded, needs onboarding, mixed
-   ✅ Admin vs regular users
-   ✅ Custom height presets (some users, not all)
-   ✅ Verified and unverified emails

**Realistic Features:**

-   Professional names and departments
-   Meaningful email addresses
-   Height-appropriate desk presets
-   Organizational hierarchy
-   Multiple user states for testing

---

## Running the Seeder

```bash
# Fresh database with seeding
php artisan migrate:fresh --seed

# Seed existing database
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=DatabaseSeeder
```

## Retrieving Seeded Data

```php
// In Tinker or migrations
php artisan tinker

// Get users by type
User::where('is_admin', true)->get();
User::where('needs_personalization', false)->get();
User::where('needs_personalization', true)->get();

// Get users by height profile
User::where('height', '>', 190)->get();  // Very tall
User::where('height', '<', 165)->get();  // Short

// Get floors and rooms
Floor::with('rooms')->get();
Room::where('floor_id', 1)->get();

// Get all schedules
Schedule::get();

// Get desk metrics
DeskMetric::latest()->limit(10)->get();
```

---

## Testing Scenarios

The seeded data supports these testing scenarios:

1. **User Onboarding:** Test with new users (4 users need onboarding)
2. **Admin Features:** Test with 3 admin users with different profiles
3. **Height Variations:** Test UI with very short (155cm) to very tall (210cm) users
4. **Custom Heights:** Test custom preset feature with users who have configured them
5. **Organizational Hierarchy:** Test with multi-floor, multi-room setup
6. **Schedule Management:** Test with diverse daily and cleaning schedules
7. **Desk Assignment:** Test desk management with realistic desk data
8. **Age Demographics:** Test with users from 24 to 58 years old

---

## Notes for API Integration

As mentioned in the requirements, desks are normally populated from the external Viny API via the sync command/schedule. The `DeskFactory` is useful for:

-   Testing without live API
-   Creating specific test desk scenarios
-   Populating desk_metrics without live desk data

When the API sync runs, it will update desk data based on live API responses.
