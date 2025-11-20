# VinyDeskline Auth System - Quick Reference

## 🔐 Login Credentials
**All users have the same password:** `password`

## 📧 Finding User Emails
```bash
php artisan tinker

# Get all users
User::all(['name', 'email', 'is_admin', 'needs_personalization'])

# Get an admin user
User::where('is_admin', true)->first(['name', 'email'])

# Get a regular user
User::where('is_admin', false)->where('needs_personalization', false)->first(['name', 'email'])

# Get a new user (needs onboarding)
User::where('needs_personalization', true)->first(['name', 'email'])
```

## 👥 User Types Created (12 total)
- **3 Admins** - Can access admin dashboard
- **5 Regular Users** - Already personalized, homepage only
- **4 New Users** - Need to complete personalization

## 🎨 UI Improvements

### Homepage Header
✅ Modern user account dropdown card
✅ Hover/click to reveal menu
✅ Shows user avatar, name, and email
✅ Context-aware menu (admin option only for admins)
✅ Smooth animations

### Dashboard Sidebar
✅ Logout button styled like other nav links
✅ Uses proper link structure (not inline button)
✅ Dynamic user name display

### Code Quality
✅ NO inline CSS in any blade file
✅ NO inline JavaScript in any blade file
✅ All styles in dedicated CSS files
✅ Clean, maintainable code structure

### Removed
❌ Footer from homepage (features moved to dropdown)
❌ All inline styles from templates
❌ Hardcoded styling

## 🛡️ Security & Access Control

### Public Routes
- `/` - Login page
- `/login` - Login submission

### Authenticated Routes (All Users)
- `/home` - Homepage
- `/personalize` - Onboarding page
- `/personalize/skip` - Skip onboarding
- `/logout` - Logout (GET or POST)

### Admin-Only Routes
- `/admin/dashboard` - Admin dashboard
- `/desks` - Desk management
- `/desks/{desk_id}` - Desk details

**Non-admins get 403 Forbidden error when accessing admin routes**

## 💡 Key Features

### Simplified Logout
All logout buttons are now simple `<a href="{{ route('logout') }}">` links - no forms, no CSRF tokens, no inline JavaScript needed. The route supports both GET and POST for maximum compatibility.

### Smart User Dropdown
The user dropdown uses JavaScript enhancement for perfect hover behavior:
- Smooth transition when moving mouse from trigger to dropdown
- 150ms delay before hiding (prevents accidental closes)
- Works with both hover and click
- See `resources/js/home.js` for implementation

## 🏭 Creating New Users with Factory

```php
// In tinker or seeders
use App\Models\User;

// Create admin (personalized)
User::factory()->admin()->personalized()->create();

// Create regular user (personalized)
User::factory()->personalized()->create();

// Create new user (needs onboarding)
User::factory()->needsPersonalization()->create();

// Create 10 random users
User::factory()->count(10)->create();

// Create specific user
User::factory()->create([
    'name' => 'John Smith',
    'email' => 'jsmit@vinydeskline.com',
    'is_admin' => true,
    'needs_personalization' => false,
]);
```

## 📝 Email Format
Automatically generated: `{firstLetter}{first4LettersOfLastName}@vinydeskline.com`

Examples:
- John Smith → `jsmit@vinydeskline.com`
- Mary Anderson → `mande@vinydeskline.com`
- David Lee → `dlee@vinydeskline.com`

## 🗄️ Database Fields

### Users Table
```
- id
- name
- email (unique)
- email_verified_at
- password (hashed)
- remember_token
- height (decimal, nullable)
- age (integer, nullable)
- needs_personalization (boolean, default: true)
- is_admin (boolean, default: false)
- created_at
- updated_at
```

## 🎯 Quick Test Flow

1. **Reset database:** `php artisan migrate:fresh --seed`
2. **Get admin email:** Run tinker command above
3. **Login** at `http://localhost:8000`
4. **Test admin access:** Click dropdown → Admin Dashboard
5. **Test regular user:** Login as non-admin, try `/admin/dashboard` (should get 403)
6. **Test onboarding:** Login as new user, complete/skip personalization

## 🎨 Design System

### Colors
- Primary Blue: `#0485B9`
- Dark Accent: `#004F6E`
- Background: `#C6DAE2`
- Card Background: `#DCE5E9`

### Icons
- Material Icons Round throughout
- Consistent 20-24px sizing

### Animations
- Smooth 0.2-0.3s transitions
- Hover effects on all interactive elements
- Slide-in animations on page load

## 📚 Documentation
See `AUTH_IMPLEMENTATION.md` for complete documentation.
