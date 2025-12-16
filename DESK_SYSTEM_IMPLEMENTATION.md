# Desk Management System Implementation Guide

## Overview
This document outlines the comprehensive desk management system implementation for VinyDeskline. The system integrates with an external Desk API to manage standing desks, track user metrics, and organize office spaces.

## Database Structure

### New Tables

1. **floors** - Manages office floors
   - `id`: Primary key
   - `name`: Floor name (e.g., "Ground Floor")
   - `floor_number`: Unique floor identifier
   - `description`: Optional description
   - `timestamps`

2. **rooms** - Manages office rooms
   - `id`: Primary key
   - `name`: Room name
   - `floor_id`: Foreign key to floors (nullable)
   - `description`: Optional description
   - `timestamps`

3. **desks** - Manages desk information (replaces old desks table)
   - `id`: Primary key
   - `desk_id`: Unique identifier from API
   - `room_id`: Foreign key to rooms (nullable)
   - `floor_id`: Foreign key to floors (nullable)
   - `is_removed_from_api`: Boolean flag for API sync
   - `name`: Desk name from API
   - `manufacturer`: Manufacturer info
   - `position_mm`: Current height position
   - `speed_mms`: Current speed
   - `status`: Desk status
   - `activations_counter`: Usage counter
   - `sit_stand_counter`: Sit/stand transitions
   - `last_synced_at`: Last API sync timestamp
   - `timestamps`

4. **desk_metrics** - Tracks desk height and usage over time
   - `id`: Primary key
   - `desk_id`: Desk identifier (matches desks.desk_id)
   - `height_mm`: Height in millimeters at recording time
   - `is_sitting`: Boolean (true if height < 1000mm)
   - `recorded_at`: Timestamp of metric recording
   - `timestamps`
   - Indexes on `desk_id`, `recorded_at`

### Modified Tables

- **users** table already has `desk_id` column for desk assignment

## API Integration

### Service: `App\Services\DeskApiService`
Handles all communication with the external Desk API:
- `getAllDeskIds()`: Fetches all desk IDs
- `getDeskData($deskId)`: Fetches specific desk data
- `updateDeskPosition($deskId, $positionMm)`: Updates desk height

### Environment Variables Required
```env
DESK_API_BASE_URL=http://localhost:8000
DESK_API_KEY=your-api-key-here
```

## Scheduled Tasks

### 1. Desk Synchronization (`desks:sync`)
- **Frequency**: Hourly
- **Purpose**: Syncs desk data from API to local database
- **Actions**:
  - Fetches all desk IDs from API
  - Creates new desks in database
  - Updates existing desk information
  - Marks removed desks with `is_removed_from_api = true`

### 2. Metrics Collection (`desks:collect-metrics`)
- **Frequency**: Every 5 minutes
- **Purpose**: Collects desk height metrics for analytics
- **Actions**:
  - Fetches current position for all active desks
  - Calculates sitting/standing status
  - Stores metric record with timestamp

### Running Schedules
Ensure Laravel scheduler is running:
```bash
# In production, add to cron:
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

# For development, run:
php artisan schedule:work
```

## Controllers

### DeskController
- `index()`: List all desks with assignments
- `show($deskId)`: Get specific desk details
- `setHeight(Request, $deskId)`: Update desk height via API
- `assignUser(Request, $deskId)`: Assign user to desk
- `unassignUser($deskId)`: Remove user assignment
- `getMetrics($deskId, Request)`: Get desk usage metrics
- `stats()`: Get aggregated desk statistics

### OfficeManagementController
Floor Management:
- `getFloors()`: List all floors
- `createFloor(Request)`: Create new floor
- `updateFloor(Request, $id)`: Update floor
- `deleteFloor($id)`: Delete floor

Room Management:
- `getRooms()`: List all rooms
- `createRoom(Request)`: Create new room
- `updateRoom(Request, $id)`: Update room
- `deleteRoom($id)`: Delete room

Desk Location:
- `assignDeskLocation(Request, $deskId)`: Assign desk to floor/room

### UserController (Updated)
- `assignDesk(Request, $userId)`: Assign desk to user
- `unassignDesk($userId)`: Remove desk from user

## Views

### 1. `no-desk-assigned.blade.php`
Shown when user has no desk assigned:
- **For regular users**: Message to contact administrator
- **For admins**: Option to go to admin dashboard

### 2. `office-management.blade.php`
Admin page to manage:
- Floors (create, edit, delete)
- Rooms (create, edit, delete, assign to floors)
- View statistics (room/desk counts per floor)

### 3. `arrangement.blade.php` (TO BE UPDATED)
Needs updates for:
- Displaying desks grouped by floor/room
- Desk assignment UI
- Height adjustment controls
- Room cards (double-sized) to group desks

### 4. `user-management.blade.php` (TO BE UPDATED)
Needs updates for:
- Desk assignment dropdown per user
- Display currently assigned desk
- Prevent duplicate assignments

## Routes

### Admin Routes
```
GET  /admin/office-management - Office management page
GET  /admin/desks - List all desks
GET  /admin/desks/{deskId} - Get desk details
PUT  /admin/desks/{deskId}/height - Update desk height
POST /admin/desks/{deskId}/assign - Assign user to desk
POST /admin/desks/{deskId}/unassign - Unassign user from desk
GET  /admin/desks/{deskId}/metrics - Get desk metrics
```

### API Routes (Admin Only)
```
# Floors
GET    /api/floors - List floors
POST   /api/floors - Create floor
PUT    /api/floors/{id} - Update floor
DELETE /api/floors/{id} - Delete floor

# Rooms
GET    /api/rooms - List rooms
POST   /api/rooms - Create room
PUT    /api/rooms/{id} - Update room
DELETE /api/rooms/{id} - Delete room

# Users
POST /api/users/{userId}/assign-desk - Assign desk to user
POST /api/users/{userId}/unassign-desk - Unassign desk from user

# Desk Location
PUT /api/desks/{deskId}/location - Assign desk to floor/room
```

## User Flow

### For Users Without Desk
1. User logs in
2. System checks if `user.desk_id` is null
3. Redirects to `/no-desk` page
4. User sees message to contact admin

### For Admins Without Desk
1. Admin logs in
2. System checks if `user.desk_id` is null
3. Redirects to `/no-desk` page
4. Admin sees option to proceed to dashboard
5. Admin can assign themselves a desk from Desk Management

### For Users With Desk
1. User logs in
2. Proceeds to `/home` dashboard
3. Can control their assigned desk
4. View their sitting/standing metrics

## Migration Steps

### 1. Run Migrations
```bash
php artisan migrate
```

This will:
- Drop old desks table
- Create floors, rooms, new desks, and desk_metrics tables

### 2. Initial Desk Sync
```bash
php artisan desks:sync
```

This populates the desks table with data from the API.

### 3. Set Up API Credentials
Add to `.env`:
```env
DESK_API_BASE_URL=http://localhost:8000
DESK_API_KEY=your-api-key-from-api_keys.json
```

### 4. Start Scheduler
```bash
# Development
php artisan schedule:work

# Production (add to crontab)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Remaining Implementation Tasks

### Critical
1. **Update `arrangement.blade.php`**:
   - Add floor/room grouping
   - Update desk modal with user assignment UI
   - Add height adjustment controls
   - Remove edit/faulty/available buttons
   - Add room cards

2. **Update `user-management.blade.php`**:
   - Add desk assignment dropdown
   - Display assigned desk info
   - Add assign/unassign buttons

3. **Update `desk-management.js`**:
   - Handle desk assignment API calls
   - Update modal interactions
   - Handle height adjustments

4. **Update `user-management.js`**:
   - Handle desk assignment from user side
   - Update form validations

5. **Add Middleware Check**:
   - Check `user.desk_id` on authenticated routes
   - Redirect to `/no-desk` if null
   - Except for admins and `/no-desk` route

### Optional Enhancements
- Real-time desk status updates via polling
- Desk reservation system
- Advanced metrics visualization
- Desk availability notifications
- User desk preferences

## Testing

### Manual Testing Checklist
- [ ] Run migrations successfully
- [ ] API sync populates desks
- [ ] Metrics collection works every 5 minutes
- [ ] Floor CRUD operations
- [ ] Room CRUD operations
- [ ] Desk assignment to users
- [ ] User without desk sees redirect
- [ ] Admin without desk can access dashboard
- [ ] Desk height adjustment via API
- [ ] Metrics calculation (sitting/standing time)

### API Testing
Use the desk API simulator:
```bash
python simulator/main.py --port 8000
```

## Troubleshooting

### Desks Not Syncing
- Check API is running
- Verify API credentials in `.env`
- Check logs: `storage/logs/laravel.log`
- Run manually: `php artisan desks:sync`

### Metrics Not Collecting
- Verify scheduler is running
- Check desk sync has run first
- Verify active desks exist in database
- Run manually: `php artisan desks:collect-metrics`

### Scheduler Not Running
```bash
# Check if scheduler is configured
php artisan schedule:list

# Run scheduler in foreground (dev)
php artisan schedule:work

# Check cron is configured (production)
crontab -l
```

## Database Queries for Debugging

```sql
-- Check desks
SELECT * FROM desks WHERE is_removed_from_api = 0;

-- Check metrics
SELECT desk_id, COUNT(*) as count, MAX(recorded_at) as last_metric
FROM desk_metrics
GROUP BY desk_id;

-- Check user assignments
SELECT u.full_name, u.desk_id, d.name as desk_name
FROM users u
LEFT JOIN desks d ON u.desk_id = d.desk_id;

-- Check floor/room structure
SELECT f.name as floor, r.name as room, COUNT(d.id) as desk_count
FROM floors f
LEFT JOIN rooms r ON r.floor_id = f.id
LEFT JOIN desks d ON d.room_id = r.id
GROUP BY f.id, r.id;
```

## Commit History
This feature was developed in the branch `feature/desk-management-system` with the following commits:
1. Add database migrations for desk management system
2. Update models with relationships for desk management system
3. Add API service and scheduled commands for desk sync and metrics collection
4. Update controllers for desk assignment and office management
5. Add no-desk-assigned and office-management view pages
6. Add routes for desk management system and update navbar
7. Add CSS and JS for office management (this commit)

## Next Steps
After merging this branch:
1. Complete the remaining view updates (arrangement.blade.php, user-management.blade.php)
2. Implement the JavaScript functionality for desk assignments
3. Add middleware for desk assignment check
4. Test thoroughly with live API
5. Deploy and configure cron for scheduler
