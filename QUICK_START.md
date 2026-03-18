# Quick Start Guide - Client Queue System

## ⚡ 3-Step Setup

### Step 1: Initialize Database (1 minute)
```bash
php scripts/setup.php
```

### Step 2: Import Clients (2 minutes)
```bash
php scripts/02_import_clients.php
```

### Step 3: Start Using It!
```
Open: http://localhost/qms/counter_client.php?counter=LOAN%20PROCESSING%201
```

---

## 🎯 Counter Interface Basics

### Search & Assign Client
1. Type client surname (minimum 4 characters)
2. Press Enter or click "Search"
3. Click client from results
4. Enter service purpose (optional)
5. Client appears in queue

### Add New Client
1. From search results, click "Add New Client"
2. Enter surname (minimum 4 characters required)
3. Optionally add full name, phone, email
4. Click "Create & Assign to Queue"

### Manage Queue
- **Call Next Client** - Call next waiting client
- **Mark Complete** - Finish serving current client
- **Skip Client** - Skip to next client
- **Remove from Queue** - Remove client completely

---

## 📊 What Changed

| Aspect | Before | After |
|--------|--------|-------|
| Display | Ticket numbers (001, 002) | Client surnames |
| Client Info | None | Full database with contact info |
| Search | Not available | By surname (4+ chars) |
| New Clients | Manual process | On-the-fly registration |
| History | Limited | Complete audit trail |

---

## ✅ Validation Rules

- **Minimum surname length:** 4 characters
- **Applied at:** Search, creation, assignment
- **Error message:** "Surname must be at least 4 characters long"

---

## 🔍 System Features

✓ Real-time queue updates  
✓ Client database with 9000+ records  
✓ Multi-counter support  
✓ Service history tracking  
✓ UTF-8 support for all names  
✓ Error handling on all operations  
✓ Backward compatible with old system  

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| "No clients found" | Run: `php scripts/02_import_clients.php` |
| "Database connection error" | Check `config.php` database settings |
| "Minimum 4 characters" error | Enter at least 4 characters (intentional) |
| Database tables missing | Run: `php scripts/setup.php` |

---

## 📁 Important Files

```
/lib/
  queue.php          Queue & client management
  clients.php        Client database operations

/scripts/
  setup.php              Initialize database
  02_import_clients.php  Import from Excel
  test_client_system.php Test system

/
  counter_client.php     Main counter interface
  start.php              Dashboard & documentation
```

---

## 🚀 API Quick Reference

```bash
# Search clients
curl "http://localhost/qms/api.php?action=search_clients&surname=Smith"

# Get counter queue
curl "http://localhost/qms/api.php?action=counter_queue&counter=1"

# Create client
curl -X POST "http://localhost/qms/api.php?action=create_client" \
  -H "Content-Type: application/json" \
  -d '{"surname":"Johnson","full_name":"Jane Johnson"}'

# Assign to queue
curl -X POST "http://localhost/qms/api.php?action=assign_client" \
  -H "Content-Type: application/json" \
  -d '{"client_id":1,"counter":1,"purpose":"Loan"}'

# Call next
curl -X POST "http://localhost/qms/api.php?action=call_next_client" \
  -H "Content-Type: application/json" \
  -d '{"counter":1}'

# Complete service
curl -X POST "http://localhost/qms/api.php?action=complete_service" \
  -H "Content-Type: application/json" \
  -d '{"entry_id":1}'
```

---

## 📖 Documentation

- **CLIENT_SYSTEM_SETUP.md** - Full technical documentation
- **IMPLEMENTATION_SUMMARY.md** - What was built
- **QUICK_START.md** - This file
- **counter_client.php** - Interface code with embedded comments

---

## 🧪 Test the System

```bash
php scripts/test_client_system.php
```

This will:
- Check database connection
- Verify all tables exist
- Create test client
- Test search functionality
- Test queue operations
- Report success or errors

---

## 💡 Tips

- Use the search function for existing clients (faster)
- Add new clients when not found in search
- Service purpose is optional but recommended
- System updates queue every 2 seconds automatically
- Both old and new systems can run together

---

## 🔐 Security Notes

- Input validation on all fields
- Server-side validation on all operations
- Prepared statements to prevent SQL injection
- UTF-8mb4 charset for proper data handling
- Minimum 4-character surname requirement prevents spam

---

## 📞 Support

If you encounter issues:

1. Check error messages carefully
2. Run `php scripts/test_client_system.php`
3. Verify setup steps were completed
4. Check database credentials in `config.php`
5. See CLIENT_SYSTEM_SETUP.md troubleshooting section

---

**Ready to use!** 🎉

Start with Step 1 above, then begin using the counter interface.
