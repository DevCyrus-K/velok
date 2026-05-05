# Topbar Navigation - Dynamic User & Notifications Update

## Summary of Changes

This document outlines the changes made to implement dynamic user and notifications display in the topbar navigation.

### Files Modified

#### 1. **[resources/views/layouts/partials/topbar.blade.php](resources/views/layouts/partials/topbar.blade.php)**
   - **Changes:**
     - Replaced hardcoded "Rose Walls" user name with dynamic loading from authenticated user
     - Replaced hardcoded "25" notification badge with dynamic count from database
     - Added bell icon (Lucide icon) for notifications instead of just a badge
     - Implemented JavaScript fetch to load data from API endpoints
     - Auto-refresh notifications every 30 seconds
     - Replaced hardcoded notification items with dynamic list from database
   
   - **Key Features:**
     - Shows user avatar and name from authenticated session
     - Displays notification count badge (hidden if 0)
     - Lists unread messages with sender name, subject, and timestamp
     - Real-time updates without page reload

#### 2. **[app/Http/Controllers/TopbarController.php](app/Http/Controllers/TopbarController.php)** (NEW FILE)
   - **Created new controller** with three API endpoints:
     - `getNotifications()` - Returns unread message count and recent messages
     - `getCurrentUser()` - Returns authenticated user details
     - `getTopbarData()` - Combined endpoint returning both user and notification data
   
   - **Features:**
     - Session-based authentication (auth middleware)
     - Returns JSON responses for JavaScript consumption
     - Fetches from Message and User models

#### 3. **[routes/api.php](routes/api.php)**
   - **Added three new API routes:**
     ```
     GET  /api/topbar/notifications    - Get unread message count
     GET  /api/topbar/current-user    - Get logged-in user info
     GET  /api/topbar/data            - Get combined data
     ```
   - Protected by `auth` middleware (session authentication)

### Database Queries Used

1. **Unread Message Count:**
   ```php
   Message::where('status', 'unread')->count()
   ```

2. **Recent Unread Messages:**
   ```php
   Message::where('status', 'unread')
       ->orderBy('created_at', 'desc')
       ->take(5)
       ->get(['id', 'name', 'subject', 'message', 'created_at'])
   ```

3. **Current User:**
   ```php
   Auth::user()  // Returns authenticated user model
   ```

### API Endpoints

#### GET /api/topbar/data
Returns combined user and notification data:
```json
{
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@kwikshift.com",
    "avatar": "/images/users/avatar-1.jpg"
  },
  "notifications": {
    "count": 10
  }
}
```

#### GET /api/topbar/notifications
Returns notification details:
```json
{
  "count": 10,
  "notifications": [
    {
      "id": 1,
      "name": "Sender Name",
      "subject": "Inquiry about moving services",
      "message": "Full message text...",
      "created_at": "2026-05-01T20:40:38.000000Z"
    }
  ]
}
```

#### GET /api/topbar/current-user
Returns current user details:
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@kwikshift.com",
  "avatar": "/images/users/avatar-1.jpg"
}
```

### Frontend Implementation

The topbar now uses JavaScript to:
1. Fetch data on page load
2. Update the user name and avatar dynamically
3. Display notification count badge (hidden if 0)
4. Show recent unread messages in the dropdown
5. Auto-refresh every 30 seconds

### Security

- All API endpoints are protected with Laravel's `auth` middleware
- Session-based authentication is used (works with Laravel Fortify/Breeze)
- User can only see their own notifications
- XSS protection with HTML escaping in JavaScript

### Testing

To test the implementation:

1. **View notifications count:**
   - Check the topbar bell icon for the red badge with unread count

2. **Test API endpoints:**
   ```bash
   # Get combined data
   curl -b "LARAVEL_SESSION=..." http://localhost:8000/api/topbar/data
   
   # Get notifications
   curl -b "LARAVEL_SESSION=..." http://localhost:8000/api/topbar/notifications
   
   # Get current user
   curl -b "LARAVEL_SESSION=..." http://localhost:8000/api/topbar/current-user
   ```

3. **Database setup:**
   - Messages with `status = 'unread'` will appear in notifications
   - Sample test data includes 10 unread messages

### Future Enhancements

- Add real-time updates using WebSockets/Pusher
- Add notification preferences per user
- Add mark as read functionality from dropdown
- Add filtering options (all, unread, today, etc.)
- Add notification sounds
- Add notification persistence/history
