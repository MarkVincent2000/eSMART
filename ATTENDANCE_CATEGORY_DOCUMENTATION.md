# Attendance Category System Documentation

## Overview

This documentation provides a comprehensive guide to the Attendance Category system that has been implemented in the eSMART application. The system allows for flexible categorization of attendance sessions (e.g., Class, Laboratory, Lecture, Exam, Event, etc.).

---

## Table of Contents

1. [File Structure](#file-structure)
2. [Database Structure](#database-structure)
3. [Models](#models)
4. [Controllers](#controllers)
5. [Routes](#routes)
6. [Views](#views)
7. [JavaScript](#javascript)
8. [Usage Guide](#usage-guide)
9. [API Reference](#api-reference)

---

## File Structure

```
eSMART/
├── app/
│   ├── Models/
│   │   └── Attendance/
│   │       ├── Attendance.php (updated)
│   │       └── AttendanceCategory.php (new)
│   └── Http/
│       └── Controllers/
│           └── Attendance/
│               ├── AttendanceController.php (new)
│               └── AttendanceCategoryController.php (new)
├── database/
│   ├── migrations/
│   │   ├── 2025_12_13_000000_create_attendance_categories_table.php (new)
│   │   └── 2025_12_13_000001_add_category_id_to_attendances_table.php (new)
│   └── seeders/
│       └── AttendanceCategorySeeder.php (new)
├── routes/
│   └── attendance/
│       └── web.php (new)
├── resources/
│   └── views/
│       └── attendance/
│           ├── attendance.blade.php (updated)
│           └── index.blade.php (updated)
└── public/
    └── build/
        └── js/
            └── pages/
                └── attendance-categories.js (new)
```

---

## Database Structure

### 1. `attendance_categories` Table

**Purpose:** Stores different types/categories of attendance sessions.

| Column          | Type            | Nullable | Default | Description                      |
| --------------- | --------------- | -------- | ------- | -------------------------------- |
| `id`            | BIGINT UNSIGNED | NO       | -       | Primary key                      |
| `name`          | VARCHAR(255)    | NO       | -       | Category name (unique)           |
| `slug`          | VARCHAR(255)    | NO       | -       | URL-friendly identifier (unique) |
| `description`   | TEXT            | YES      | NULL    | Detailed description             |
| `color`         | VARCHAR(7)      | NO       | #6366f1 | Hex color code                   |
| `icon`          | VARCHAR(255)    | YES      | NULL    | Icon class (e.g., 'mdi-school')  |
| `is_active`     | BOOLEAN         | NO       | TRUE    | Active status                    |
| `display_order` | INTEGER         | NO       | 0       | Sort order for display           |
| `metadata`      | JSON            | YES      | NULL    | Additional data                  |
| `created_at`    | TIMESTAMP       | YES      | NULL    | Creation timestamp               |
| `updated_at`    | TIMESTAMP       | YES      | NULL    | Last update timestamp            |
| `deleted_at`    | TIMESTAMP       | YES      | NULL    | Soft delete timestamp            |

**Indexes:**

-   Primary key on `id`
-   Unique index on `name`
-   Unique index on `slug`
-   Index on `is_active`
-   Index on `display_order`

### 2. `attendances` Table (Updated)

**New Column Added:**

-   `category_id` (BIGINT UNSIGNED, nullable)
    -   Foreign key referencing `attendance_categories.id`
    -   Constraint: `ON DELETE RESTRICT` (prevents category deletion if attendances exist)
    -   Index added for performance

---

## Models

### 1. AttendanceCategory Model

**Location:** `app/Models/Attendance/AttendanceCategory.php`

#### Properties

```php
protected $fillable = [
    'name', 'slug', 'description', 'color',
    'icon', 'is_active', 'display_order', 'metadata'
];

protected $casts = [
    'is_active' => 'boolean',
    'display_order' => 'integer',
    'metadata' => 'array',
];
```

#### Relationships

**`attendances()` - HasMany**

```php
public function attendances(): HasMany
```

Returns all attendance sessions associated with this category.

#### Scopes

| Scope           | Description                     |
| --------------- | ------------------------------- |
| `active()`      | Filter only active categories   |
| `inactive()`    | Filter only inactive categories |
| `ordered()`     | Order by display_order ASC      |
| `bySlug($slug)` | Find category by slug           |

#### Methods

| Method                       | Return Type | Description                        |
| ---------------------------- | ----------- | ---------------------------------- |
| `isActive()`                 | bool        | Check if category is active        |
| `activate()`                 | bool        | Set category as active             |
| `deactivate()`               | bool        | Set category as inactive           |
| `getAttendanceCount()`       | int         | Count all attendances              |
| `getActiveAttendanceCount()` | int         | Count active attendances           |
| `getActiveCategories()`      | Collection  | Get all active categories (static) |

#### Boot Method Features

-   **Auto-slug generation:** Automatically generates slug from name if not provided
-   **Deletion protection:** Prevents deletion if category has associated attendances

---

### 2. Attendance Model (Updated)

**Location:** `app/Models/Attendance/Attendance.php`

#### New Additions

**Fillable Field:**

-   `category_id`

**Cast:**

-   `category_id` => 'integer'

**Relationship:**

```php
public function category(): BelongsTo
{
    return $this->belongsTo(AttendanceCategory::class, 'category_id');
}
```

**New Scopes:**

| Scope                     | Description                                    |
| ------------------------- | ---------------------------------------------- |
| `ofCategory($categoryId)` | Filter attendances by category ID              |
| `withActiveCategory()`    | Filter attendances with active categories only |

---

## Controllers

### 1. AttendanceCategoryController

**Location:** `app/Http/Controllers/Attendance/AttendanceCategoryController.php`

**Purpose:** Manages all CRUD operations for attendance categories.

#### Endpoints

| Method                  | Description                 | Returns                     |
| ----------------------- | --------------------------- | --------------------------- |
| `index()`               | List all categories         | JSON with categories array  |
| `getActiveCategories()` | Get only active categories  | JSON with active categories |
| `store(Request)`        | Create new category         | JSON with created category  |
| `show($id)`             | Get single category details | JSON with category data     |
| `update(Request, $id)`  | Update category             | JSON with updated category  |
| `destroy($id)`          | Delete category             | JSON success/error          |
| `activate($id)`         | Activate category           | JSON with updated category  |
| `deactivate($id)`       | Deactivate category         | JSON with updated category  |
| `updateOrder(Request)`  | Bulk update display order   | JSON success/error          |

#### Validation Rules

**For Store/Update:**

```php
'name' => 'required|string|max:255|unique',
'slug' => 'nullable|string|max:255|unique',
'description' => 'nullable|string',
'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
'icon' => 'nullable|string|max:255',
'is_active' => 'boolean',
'display_order' => 'nullable|integer|min:0',
```

---

### 2. AttendanceController

**Location:** `app/Http/Controllers/Attendance/AttendanceController.php`

**Purpose:** Manages attendance session operations.

#### Endpoints

| Method                    | Description               | Returns                       |
| ------------------------- | ------------------------- | ----------------------------- |
| `index()`                 | Display attendance page   | View                          |
| `getAttendances(Request)` | Get filtered attendances  | JSON with attendances         |
| `getFormData()`           | Get form dropdown data    | JSON with options             |
| `store(Request)`          | Create attendance session | JSON with created attendance  |
| `show($id)`               | Get attendance details    | JSON with attendance data     |
| `update(Request, $id)`    | Update attendance         | JSON with updated attendance  |
| `destroy($id)`            | Delete attendance         | JSON success/error            |
| `lock($id)`               | Lock attendance session   | JSON with locked attendance   |
| `unlock($id)`             | Unlock attendance session | JSON with unlocked attendance |

#### Features

-   Validates attendance data
-   Auto-calculates scheduled duration from start/end times
-   Loads related data (semester, section, category)
-   Prevents modifications to locked attendances
-   Provides filtering capabilities

---

## Routes

**Location:** `routes/attendance/web.php`

**Route Prefix:** `/attendance`

### Attendance Session Routes

```php
GET    /attendance                     → index
GET    /attendance/attendances         → getAttendances
GET    /attendance/form-data           → getFormData
GET    /attendance/{id}                → show
POST   /attendance                     → store
PUT    /attendance/{id}                → update
DELETE /attendance/{id}                → destroy
POST   /attendance/{id}/lock           → lock
POST   /attendance/{id}/unlock         → unlock
```

### Category Routes

**Prefix:** `/attendance/categories`

```php
GET    /attendance/categories          → index
GET    /attendance/categories/active   → getActiveCategories
GET    /attendance/categories/{id}     → show
POST   /attendance/categories          → store
PUT    /attendance/categories/{id}     → update
DELETE /attendance/categories/{id}     → destroy
POST   /attendance/categories/{id}/activate    → activate
POST   /attendance/categories/{id}/deactivate  → deactivate
POST   /attendance/categories/update-order     → updateOrder
```

**Main Routes File Updated:**
`routes/web.php` now includes: `require __DIR__.'/attendance/web.php';`

---

## Views

### 1. attendance.blade.php (Updated)

**Location:** `resources/views/attendance/attendance.blade.php`

#### New Components Added

**1. "Manage Categories" Button**

```html
<button
    class="btn btn-success"
    data-bs-toggle="modal"
    data-bs-target="#createCategoryModal"
>
    <i class="ri-price-tag-3-line align-bottom me-1"></i>
    Manage Categories
</button>
```

**2. Create Category Modal** (`#createCategoryModal`)

-   Form for creating new attendance categories
-   Fields: Name, Slug, Description, Color, Icon, Display Order, Active Status

**3. Edit Category Modal** (`#editCategoryModal`)

-   Form for editing existing categories
-   Pre-populated with category data via JavaScript

#### Modal Structure

Both modals include:

-   Bootstrap 5 modal structure
-   Form validation
-   Color picker for category colors
-   Icon field for Material Design Icons
-   Display order for sorting
-   Active/Inactive toggle switch

---

### 2. index.blade.php (Updated)

**Location:** `resources/views/attendance/index.blade.php`

**Changes:**

-   Added JavaScript file inclusion for category management

```php
<script src="{{ URL::asset('build/js/pages/attendance-categories.js') }}"></script>
```

---

## JavaScript

**Location:** `public/build/js/pages/attendance-categories.js`

### Features

1. **Category Creation**

    - Form submission handling
    - Auto-slug generation from name
    - Validation and error display
    - Success notifications

2. **Category Editing**

    - Loads category data via API
    - Pre-populates form fields
    - Handles update submission

3. **Category Deletion**

    - Confirmation dialog
    - API call to delete endpoint
    - Success/error handling

4. **Status Toggle**

    - Activate/deactivate categories
    - Real-time status updates

5. **Utilities**
    - `generateSlug(text)` - Converts text to URL-friendly slug
    - `showToast(title, message, type)` - Displays notifications
    - API endpoint management

### Key Functions

| Function                                     | Parameters           | Description                     |
| -------------------------------------------- | -------------------- | ------------------------------- |
| `loadCategories()`                           | -                    | Fetches all categories from API |
| `displayCategories(categories)`              | categories array     | Renders categories in UI        |
| `deleteCategory(categoryId)`                 | categoryId           | Deletes a category              |
| `toggleCategoryStatus(categoryId, isActive)` | categoryId, isActive | Toggles active status           |
| `editCategory(categoryId)`                   | categoryId           | Opens edit modal with data      |
| `generateSlug(text)`                         | text string          | Generates URL-friendly slug     |
| `showToast(title, message, type)`            | title, message, type | Shows notification              |

---

## Usage Guide

### 1. Running Migrations

```bash
# Run migrations to create tables
php artisan migrate

# Seed default categories
php artisan db:seed --class=AttendanceCategorySeeder
```

### 2. Default Categories Seeded

The seeder creates 8 default categories:

1. **Class** - Blue (#3b82f6) - Icon: mdi-school
2. **Laboratory** - Purple (#8b5cf6) - Icon: mdi-flask
3. **Lecture** - Cyan (#06b6d4) - Icon: mdi-presentation
4. **Exam** - Red (#ef4444) - Icon: mdi-file-document-edit
5. **Event** - Orange (#f59e0b) - Icon: mdi-calendar-star
6. **Meeting** - Green (#10b981) - Icon: mdi-account-multiple
7. **Workshop** - Indigo (#6366f1) - Icon: mdi-tools
8. **Other** - Gray (#6b7280) - Icon: mdi-dots-horizontal

### 3. Creating Attendance with Category

```php
use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceCategory;

// Get category
$category = AttendanceCategory::bySlug('class')->first();

// Create attendance
$attendance = Attendance::create([
    'title' => 'PHP Programming Class',
    'category_id' => $category->id,
    'semester_id' => 1,
    'section_id' => 1,
    'date' => '2025-12-15',
    'start_time' => '2025-12-15 09:00:00',
    'end_time' => '2025-12-15 11:00:00',
    'created_by' => auth()->id(),
]);
```

### 4. Querying Attendances by Category

```php
// Get all attendances for a specific category
$classAttendances = Attendance::ofCategory($categoryId)->get();

// Get attendances with active categories only
$activeAttendances = Attendance::withActiveCategory()->get();

// Get category with attendance count
$category = AttendanceCategory::with('attendances')->find($id);
$count = $category->getAttendanceCount();
```

### 5. Managing Categories via UI

1. **Access Attendance Page:** Navigate to `/attendance`
2. **Click "Manage Categories"** button
3. **Create Category:**
    - Fill in category details
    - Choose color and icon
    - Set display order
    - Click "Create Category"
4. **Edit Category:**
    - Call `editCategory(categoryId)` function
    - Update details in modal
    - Click "Update Category"
5. **Delete Category:**
    - Call `deleteCategory(categoryId)` function
    - Confirm deletion

---

## API Reference

### Category Endpoints

#### 1. Get All Categories

```http
GET /attendance/categories
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Class",
            "slug": "class",
            "description": "Regular classroom sessions",
            "color": "#3b82f6",
            "icon": "mdi-school",
            "is_active": true,
            "display_order": 1,
            "created_at": "2025-12-13T00:00:00.000000Z",
            "updated_at": "2025-12-13T00:00:00.000000Z"
        }
    ]
}
```

#### 2. Create Category

```http
POST /attendance/categories
Content-Type: application/json
X-CSRF-TOKEN: {token}

{
    "name": "Workshop",
    "description": "Workshop sessions",
    "color": "#6366f1",
    "icon": "mdi-tools",
    "display_order": 5,
    "is_active": true
}
```

**Response:**

```json
{
    "success": true,
    "message": "Category created successfully.",
    "data": {
        "id": 9,
        "name": "Workshop",
        "slug": "workshop",
        ...
    }
}
```

#### 3. Update Category

```http
PUT /attendance/categories/{id}
Content-Type: application/json
X-CSRF-TOKEN: {token}

{
    "name": "Advanced Workshop",
    "description": "Advanced workshop sessions",
    "color": "#8b5cf6",
    "is_active": true
}
```

#### 4. Delete Category

```http
DELETE /attendance/categories/{id}
X-CSRF-TOKEN: {token}
```

**Response:**

```json
{
    "success": true,
    "message": "Category deleted successfully."
}
```

**Error (if category has attendances):**

```json
{
    "success": false,
    "message": "Cannot delete category with existing attendances. Please reassign or delete attendances first."
}
```

#### 5. Activate/Deactivate Category

```http
POST /attendance/categories/{id}/activate
X-CSRF-TOKEN: {token}
```

```http
POST /attendance/categories/{id}/deactivate
X-CSRF-TOKEN: {token}
```

---

## Best Practices

### 1. Category Management

-   **Unique Names:** Ensure category names are descriptive and unique
-   **Color Coding:** Use distinct colors for easy visual identification
-   **Icons:** Use Material Design Icons for consistency
-   **Display Order:** Organize categories logically (e.g., most used first)
-   **Active Status:** Deactivate instead of deleting categories with historical data

### 2. Attendance Session Management

-   **Always Use Categories:** Associate every attendance with a category for better organization
-   **Lock Important Sessions:** Lock attendance sessions after completion to prevent accidental changes
-   **Consistent Naming:** Use clear, descriptive titles for attendance sessions
-   **Location Data:** Include location information when relevant

### 3. Data Integrity

-   **Category Deletion:** Never force-delete categories with existing attendances
-   **Status Changes:** Consider impact before deactivating heavily-used categories
-   **Locked Attendances:** Respect lock status - unlock only when necessary

---

## Troubleshooting

### Common Issues

#### 1. "Cannot delete category with existing attendances"

**Solution:**

-   Reassign attendances to another category first
-   Or keep the category but deactivate it

#### 2. "Category name already exists"

**Solution:**

-   Use a unique name for each category
-   Check if similar category already exists

#### 3. Modal not opening

**Solution:**

-   Ensure JavaScript file is loaded
-   Check browser console for errors
-   Verify Bootstrap 5 is properly included

#### 4. Form submission not working

**Solution:**

-   Check CSRF token is present
-   Verify API endpoints are accessible
-   Check network tab for error responses

---

## Future Enhancements

Potential improvements for the category system:

1. **Category Groups:** Group related categories together
2. **Custom Fields:** Allow custom metadata fields per category
3. **Analytics:** Track usage statistics per category
4. **Permissions:** Role-based access for category management
5. **Import/Export:** Bulk operations for categories
6. **Templates:** Category-specific attendance templates
7. **Color Themes:** Predefined color palettes
8. **Icon Library:** Built-in icon picker

---

## Conclusion

The Attendance Category system provides a flexible and scalable way to organize and manage different types of attendance sessions. By following this documentation and best practices, you can effectively utilize this system in your eSMART application.

For additional support or questions, please refer to the Laravel documentation or contact the development team.

---

**Document Version:** 1.0  
**Last Updated:** December 13, 2025  
**Author:** AI Assistant  
**Project:** eSMART - Educational School Management and Resource Tracking
