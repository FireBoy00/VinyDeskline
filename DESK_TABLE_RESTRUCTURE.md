# Desk Table Restructuring - December 18, 2025

## Problem Summary

The desks table had several design issues:

1. **Redundant Primary Key**: Had both `id` (auto-increment) and `desk_id` (unique identifier from API)
2. **Real-time Data Storage**: Stored API real-time data (position, speed, status, counters, etc.) in the database
3. **Data Staleness**: The sync command would overwrite room/floor assignments with stale API data
4. **Confusion**: Unclear separation between organizational data (what we manage) and real-time data (from API)

## Solution

### Database Changes

The desks table now **only stores organizational data**:

```sql
CREATE TABLE "desks" (
    "desk_id" varchar not null PRIMARY KEY,  -- Changed from auto-increment id
    "room_id" integer,                       -- Our assignment
    "floor_id" integer,                      -- Our assignment
    "is_removed_from_api" tinyint(1) not null default '0',
    "created_at" datetime,
    "updated_at" datetime,
    foreign key("room_id") references "rooms"("id") on delete set null,
    foreign key("floor_id") references "floors"("id") on delete set null
)
```

**Removed columns:**

-   `id` (redundant - desk_id is now primary key)
-   `name` (real-time from API)
-   `manufacturer` (real-time from API)
-   `position_mm` (real-time from API)
-   `speed_mms` (real-time from API)
-   `status` (real-time from API)
-   `activations_counter` (real-time from API)
-   `sit_stand_counter` (real-time from API)
-   `last_synced_at` (no longer needed)

### Code Changes

#### 1. Desk Model (`app/Models/Desk.php`)

-   Set `desk_id` as primary key
-   Removed fillable fields for API data
-   Updated relationships to use correct foreign keys

#### 2. Sync Command (`app/Console/Commands/SyncDesksFromApi.php`)

-   Simplified to only track which desks exist in the API
-   No longer fetches or stores real-time data
-   Preserves room/floor assignments

#### 3. Controllers

-   **DeskController**: Updated to fetch real-time data from API on-demand
-   **OfficeManagementController**: Enriches desk data with real-time API data when requested

## Benefits

1. **No Stale Data**: Real-time information is always fresh from the API
2. **Clear Separation**: Database stores what we manage (assignments), API provides real-time state
3. **Preserved Assignments**: Room/floor assignments are no longer overwritten by sync
4. **Simpler Sync**: The sync command is now lightweight and fast
5. **Better Primary Key**: Using `desk_id` directly eliminates redundancy

## API Endpoints

The existing endpoints continue to work, but now fetch real-time data:

-   `GET /admin/api/desks` - Get all desks with real-time API data
-   `PUT /admin/api/desks/{deskId}/location` - Assign desk to room/floor
-   `GET /admin/desks` - Admin desk listing with real-time data
-   `GET /admin/desks/{deskId}` - Specific desk with real-time data

## Migration

Migration file: `2025_12_18_201238_restructure_desks_table_remove_api_data.php`

The migration:

1. Backs up room/floor assignments
2. Drops and recreates the desks table with new structure
3. Restores assignments
4. Fully reversible with the `down()` method

## Usage

### Syncing Desks

```bash
php artisan desks:sync
```

This command now only:

-   Adds new desks from API
-   Marks removed desks as `is_removed_from_api = true`
-   Preserves all room/floor assignments

## Desk-Room-Floor Relationship Logic

### Database Structure

-   Desks have both `room_id` and `floor_id` columns
-   **If a desk is in a room** (`room_id` is set):
    -   The `floor_id` MUST match the room's `floor_id`
    -   When the room moves to another floor, all desks in it automatically move too
-   **If a desk is directly on a floor** (`room_id` is null):
    -   The `floor_id` indicates which floor it's on
    -   The desk is not in any specific room

### Counting Logic

-   **Floor desk count**: ALL desks with that `floor_id` (both direct desks and desks in rooms on that floor)
-   **Room desk count**: All desks with that `room_id`

### Assignment Rules

1. **Assigning to a room**: Sets `room_id` and automatically sets `floor_id` to the room's floor
2. **Assigning directly to a floor**: Sets `floor_id` and clears `room_id`
3. **Moving a room to another floor**: All desks in that room automatically get the new `floor_id`

### Assigning Desks

```javascript
// Assign to room (desk automatically gets room's floor_id)
PUT /admin/api/desks/{deskId}/location
{
    "room_id": 1,
    "floor_id": null  // Will be set automatically to room's floor
}

// Assign directly to floor (clears room_id)
PUT /admin/api/desks/{deskId}/location
{
    "room_id": null,
    "floor_id": 2
}

// Unassign from everything
PUT /admin/api/desks/{deskId}/location
{
    "room_id": null,
    "floor_id": null
}
```

### Getting Desk Data

When you request desk data through controllers, you automatically get:

-   **Database**: desk_id, room_id, floor_id, room/floor relationships
-   **API (real-time)**: name, manufacturer, position, speed, status, counters

## Testing

After migration:

1. ✅ All room/floor assignments preserved
2. ✅ Sync command works without API calls
3. ✅ desk_id is now the primary key
4. ✅ No more stale real-time data in database
5. ✅ Controllers fetch fresh data from API
6. ✅ Room floor changes cascade to desks
7. ✅ Floor counts include all desks (direct + in rooms)
8. ✅ Desk-room-floor relationship enforced

## Recent Fixes (December 18, 2025)

### Issue: Desks Not Moving with Rooms

**Problem**: When a room was moved to another floor, desks in that room would remain on the old floor, causing incorrect desk counts and desk locations.

**Root Cause**: The `updateRoom` controller method didn't cascade floor changes to desks in the room.

**Solution**:

-   Updated `OfficeManagementController::updateRoom()` to update all desks' `floor_id` when a room's floor changes
-   Updated `OfficeManagementController::assignDeskLocation()` to properly set `floor_id` when assigning to a room
-   Updated `OfficeManagementController::getFloors()` to count ALL desks on a floor (not just desks in rooms)

**Files Modified**:

-   `app/Http/Controllers/OfficeManagementController.php`
-   `app/Models/Desk.php`
