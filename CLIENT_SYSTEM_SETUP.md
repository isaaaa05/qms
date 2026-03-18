# Client-Based Queue Management System Setup

This document explains the new client-based queue system that replaces ticket numbers with client names.

## What Changed

**Old System:**
- Counters display ticket numbers (001, 002, 003...)
- Limited tracking of actual clients

**New System:**
- Counters display client surnames
- Full client database with contact information
- Client search functionality with minimum 4-character surname requirement
- Ability to add new clients on-the-fly
- Complete service history tracking

## Database Schema

### New Tables

1. **clients** - Stores all client information
   - `id` - Primary key
   - `client_id` - External client ID
   - `surname` - Client surname (indexed for search, min 4 chars)
   - `full_name` - Full name
   - `phone` - Phone number
   - `email` - Email address
   - `address` - Physical address
   - `notes` - Additional notes

2. **queue_entries** - Current queue status
   - `id` - Entry ID
   - `client_id` - Foreign key to clients
   - `counter_number` - Counter serving the client
   - `purpose` - Purpose of visit
   - `status` - waiting/in_service/completed/no_show
   - `called_at` - When client was called
   - `service_started_at` - When service started
   - `completed_at` - When service finished

3. **call_history** - Historical records
   - `id` - History record ID
   - `client_id` - Foreign key to clients
   - `counter_number` - Which counter served
   - `purpose` - Service purpose
   - `called_at` - When called
   - `service_duration` - Duration in seconds
   - Complete audit trail

## Setup Instructions

### Step 1: Create Database Tables

```bash
php scripts/setup.php
```

This will:
- Create all three new tables
- Set up proper indexes and relationships
- Check if clients data exists

### Step 2: Import Client Data from Excel

```bash
php scripts/02_import_clients.php
```

This will:
- Read the Excel file (`9044 clientlistshort031826135150350.xls`)
- Extract surnames and basic information
- Import all valid clients (surname >= 4 characters)
- Report import statistics

### Step 3: Access the New Interface

The new counter interface is available at:
```
counter_client.php?counter=LOAN%20PROCESSING%201
```

Replace `LOAN%20PROCESSING%201` with your actual counter name.

## Counter Interface Features

### Main Display
- **NOW SERVING**: Shows the current client's surname
- **WAITING COUNT**: Total clients waiting for this counter
- **Call Next Client**: Calls the next client from the queue
- **Mark Complete**: Marks current service as finished
- **Remove from Queue**: Removes a client from the queue

### Client Search
1. Click the search box or press Enter
2. Type minimum 4 characters of the client's surname
3. System shows matching clients
4. Click to select a client
5. Enter the service purpose (optional)
6. Client is added to this counter's queue

### Add New Client
1. From search results, click "Add New Client"
2. Fill in required fields:
   - **Surname** (minimum 4 characters) - Required
   - **Full Name** - Optional
   - **Phone** - Optional
   - **Email** - Optional
3. Click "Create & Assign to Queue"
4. Enter service purpose
5. New client is added to queue

## API Endpoints

All new endpoints accept JSON and return JSON responses.

### Search Clients
```
GET /api.php?action=search_clients&surname=SURNAME
```

Response:
```json
{
  "success": true,
  "clients": [
    {
      "id": 1,
      "client_id": "CLI_001",
      "surname": "Smith",
      "full_name": "John Smith",
      "phone": "555-1234",
      "email": "john@example.com"
    }
  ],
  "count": 1
}
```

### Create Client
```
POST /api.php?action=create_client
```

Request:
```json
{
  "surname": "Johnson",
  "full_name": "Jane Johnson",
  "phone": "555-5678",
  "email": "jane@example.com"
}
```

### Assign Client to Counter
```
POST /api.php?action=assign_client
```

Request:
```json
{
  "client_id": 1,
  "counter": 1,
  "purpose": "Loan inquiry"
}
```

### Get Counter Queue
```
GET /api.php?action=counter_queue&counter=1
```

Response:
```json
{
  "ok": true,
  "queue": [
    {
      "id": 1,
      "client_id": 1,
      "surname": "Smith",
      "full_name": "John Smith",
      "purpose": "Loan inquiry",
      "status": "in_service",
      "called_at": "2024-01-15 10:30:00"
    }
  ]
}
```

### Call Next Client
```
POST /api.php?action=call_next_client
```

Request:
```json
{
  "counter": 1
}
```

### Complete Service
```
POST /api.php?action=complete_service
```

Request:
```json
{
  "entry_id": 1
}
```

## Validation Rules

- **Surname**: Minimum 4 characters (enforced on both client-side and server-side)
- **Database**: UTF-8mb4 charset for international character support
- **Search**: Case-insensitive, supports partial matching
- **Counter**: Numbers 1-4 (configurable in config.php)

## File Structure

```
/lib/
  ├── queue.php          # QueueManager class - main queue logic
  └── clients.php        # ClientManager class - client import/management

/scripts/
  ├── 01_create_tables.sql    # Database schema
  ├── 02_import_clients.php   # Import from Excel
  └── setup.php               # Initial setup

/counter_client.php           # New counter interface with client search
/api.php                       # Enhanced with new client endpoints
```

## Troubleshooting

### "No clients found" on import
- Check the Excel file exists and is readable
- Verify file path is correct
- Check database permissions

### Minimum 4 characters error
- Make sure you're typing at least 4 characters
- Error message will show if less than 4

### Database connection error
- Verify config.php has correct credentials
- Check MySQL service is running
- Ensure database `qms` exists

## Migration from Old System

The old ticket system is still available at `counter.php`. Both systems can run side-by-side:
- **Old**: `counter.php` - Uses ticket numbers
- **New**: `counter_client.php` - Uses client names

You can migrate gradually or switch over completely.

## Support

For issues or questions, check:
1. Database logs for errors
2. Browser console for JavaScript errors
3. PHP error logs for backend errors
4. Ensure all files are properly uploaded
