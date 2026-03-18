# Deployment Checklist - Client Queue System

## Pre-Deployment (Before Running Setup)

- [ ] **Backup Database**
  ```bash
  mysqldump -u root qms > qms_backup_$(date +%Y%m%d).sql
  ```
  Location: Document backup file location

- [ ] **Review Changes**
  - Read CHANGES.md
  - Understand what's being added
  - Note: Old system remains unchanged

- [ ] **Check Prerequisites**
  - PHP 7.4+ installed
  - MySQL 5.7+ running
  - File write permissions on /scripts
  - Database accessible

- [ ] **Verify File Permissions**
  ```bash
  ls -la lib/
  ls -la scripts/
  ```

- [ ] **Check Config Credentials**
  - config.php has correct database host
  - Database user has proper permissions
  - Can connect to database: `qms`

---

## Phase 1: Database Setup

- [ ] **Run Setup Script**
  ```bash
  cd /vercel/share/v0-project
  php scripts/setup.php
  ```
  Expected output:
  - ✓ Connected
  - ✓ Table created (3 times)
  - ✓ Setup Complete

- [ ] **Verify Tables Created**
  ```sql
  SHOW TABLES LIKE 'clients%';
  SHOW TABLES LIKE 'queue%';
  SHOW TABLES LIKE 'call%';
  ```
  Expected: 3 tables shown

- [ ] **Check Table Structure**
  ```sql
  DESC clients;
  DESC queue_entries;
  DESC call_history;
  ```
  Verify all columns present

- [ ] **Verify Indexes**
  ```sql
  SHOW INDEXES FROM clients;
  ```
  Check for FULLTEXT index on surname

---

## Phase 2: Data Import

- [ ] **Run Import Script**
  ```bash
  php scripts/02_import_clients.php
  ```
  Expected output:
  - Found Excel file
  - Found 9044 clients
  - Imported: 9040+
  - Skipped: < 10

- [ ] **Verify Import Results**
  ```bash
  # Check client count
  mysql -u root qms -e "SELECT COUNT(*) as client_count FROM clients;"
  ```
  Expected: ~9040 clients

- [ ] **Sample Data Check**
  ```sql
  SELECT * FROM clients LIMIT 5;
  ```
  Verify surnames are present and complete

- [ ] **Check Index Performance**
  ```sql
  SELECT * FROM clients WHERE surname LIKE '%Smith%' LIMIT 1;
  ```
  Should execute quickly (< 100ms)

---

## Phase 3: System Testing

- [ ] **Run Test Suite**
  ```bash
  php scripts/test_client_system.php
  ```
  Expected: All tests pass with ✓ marks

- [ ] **Verify Each Test**
  - [x] Database Connection
  - [x] Tables exist
  - [x] Client creation
  - [x] Search functionality
  - [x] Queue operations
  - [x] 4-character validation

- [ ] **Manual API Test**
  ```bash
  # Test search endpoint
  curl "http://localhost/qms/api.php?action=search_clients&surname=smith"
  
  # Should return JSON with clients
  ```

- [ ] **Check Error Responses**
  ```bash
  # Test with < 4 characters
  curl "http://localhost/qms/api.php?action=search_clients&surname=abc"
  
  # Should return error message
  ```

---

## Phase 4: Interface Testing

- [ ] **Open Counter Interface**
  ```
  http://localhost/qms/counter_client.php?counter=LOAN%20PROCESSING%201
  ```

- [ ] **Verify Display**
  - [ ] NOW SERVING shows "—"
  - [ ] WAITING COUNT shows "—"
  - [ ] Search input visible
  - [ ] Buttons visible but disabled

- [ ] **Test Search**
  - [ ] Enter "test" (< 4 chars) → Error shown
  - [ ] Enter "smith" (4+ chars) → Results shown
  - [ ] Select client → Modal closes
  - [ ] Purpose modal appears

- [ ] **Test New Client**
  - [ ] Click "Add New Client"
  - [ ] Enter "NewClientName"
  - [ ] Click create
  - [ ] Client appears in queue

- [ ] **Test Queue Operations**
  - [ ] Click "Call Next Client" → Client appears
  - [ ] Click "Mark Complete" → Next client appears
  - [ ] Queue updates in real-time

- [ ] **Test Old Interface** (Backward Compatibility)
  - [ ] Open counter.php
  - [ ] Old interface still works
  - [ ] Can issue tickets normally

---

## Phase 5: Performance Verification

- [ ] **Search Performance**
  - [ ] Search returns within 1 second
  - [ ] No browser freezing
  - [ ] Handles 9000+ clients smoothly

- [ ] **Real-time Updates**
  - [ ] Queue updates every 2 seconds
  - [ ] No lag or delays
  - [ ] Multiple counters work independently

- [ ] **Database Performance**
  - [ ] Check slow query log
  - [ ] No warnings in browser console
  - [ ] API responses < 500ms

- [ ] **Memory Usage**
  - [ ] Monitor PHP memory
  - [ ] Check database connections
  - [ ] No memory leaks

---

## Phase 6: Security Verification

- [ ] **SQL Injection Test**
  - [ ] Try malicious surname: `' OR '1'='1`
  - [ ] Should be escaped properly
  - [ ] No database errors shown

- [ ] **Input Validation**
  - [ ] Very long surnames handled
  - [ ] Special characters accepted
  - [ ] No errors or crashes

- [ ] **Access Control**
  - [ ] Can access all counters
  - [ ] Proper error on invalid counter
  - [ ] No unauthorized data access

- [ ] **Error Handling**
  - [ ] No SQL errors shown to user
  - [ ] Errors logged but not exposed
  - [ ] Graceful error messages

---

## Phase 7: Documentation Check

- [ ] **Documentation Files Exist**
  - [ ] CLIENT_SYSTEM_SETUP.md
  - [ ] IMPLEMENTATION_SUMMARY.md
  - [ ] SYSTEM_ARCHITECTURE.md
  - [ ] QUICK_START.md
  - [ ] CHANGES.md
  - [ ] README_CLIENT_SYSTEM.md

- [ ] **Test Documentation Links**
  - [ ] Open start.php
  - [ ] Documentation links work
  - [ ] Guides are readable

- [ ] **Verify Code Comments**
  - [ ] counter_client.php has comments
  - [ ] lib/queue.php has comments
  - [ ] lib/clients.php has comments

---

## Phase 8: Staff Training Preparation

- [ ] **Prepare Training Materials**
  - [ ] Print QUICK_START.md
  - [ ] Create short demo script
  - [ ] Practice the workflow

- [ ] **Identify Power Users**
  - [ ] Select tech-savvy staff
  - [ ] Train them first
  - [ ] They help others

- [ ] **Create Cheat Sheet**
  - [ ] Key operations
  - [ ] 4-character minimum rule
  - [ ] Common errors

- [ ] **Set Up Support Channel**
  - [ ] Who to contact for issues
  - [ ] Response time expectation
  - [ ] Escalation path

---

## Phase 9: Monitoring Setup

- [ ] **Error Logging**
  - [ ] Check PHP error_log
  - [ ] Review MySQL logs
  - [ ] Set up monitoring alerts

- [ ] **Performance Monitoring**
  - [ ] Track query times
  - [ ] Monitor response times
  - [ ] Check server resources

- [ ] **User Feedback**
  - [ ] Ask for issues
  - [ ] Document problems
  - [ ] Plan fixes

---

## Phase 10: Go-Live

- [ ] **Announce to Staff**
  - [ ] Send notification
  - [ ] Provide quick start guide
  - [ ] Schedule brief training

- [ ] **Monitor First Day**
  - [ ] Watch for errors
  - [ ] Respond quickly to issues
  - [ ] Collect feedback

- [ ] **Document Issues**
  - [ ] Log any problems
  - [ ] Note workarounds
  - [ ] Plan fixes

- [ ] **Check Backup**
  - [ ] Verify backup was successful
  - [ ] Test restore procedure
  - [ ] Store safely

---

## Post-Deployment

- [ ] **Day 1 Review**
  - [ ] Any critical issues?
  - [ ] Staff feedback?
  - [ ] System stable?

- [ ] **Day 3 Check**
  - [ ] Patterns stable?
  - [ ] Errors resolved?
  - [ ] Staff comfortable?

- [ ] **Week 1 Analysis**
  - [ ] Usage patterns
  - [ ] Performance metrics
  - [ ] Issues encountered

- [ ] **Optimization**
  - [ ] Apply fixes as needed
  - [ ] Optimize if needed
  - [ ] Plan enhancements

---

## Rollback Plan (If Needed)

- [ ] **Quick Rollback Steps**
  ```bash
  # 1. Restore from backup
  mysql qms < qms_backup_YYYYMMDD.sql
  
  # 2. Remove new files (optional)
  rm lib/queue.php lib/clients.php
  
  # 3. Revert api.php changes
  git checkout api.php config.php
  
  # 4. Test old system
  Open counter.php
  ```

- [ ] **Data Preservation**
  - [ ] New tables preserved if desired
  - [ ] Old data unaffected
  - [ ] Full recovery possible

---

## Sign-Off

### System Ready
- [ ] All phases completed
- [ ] All tests passed
- [ ] Documentation verified
- [ ] Staff prepared

### Approval Required
- [ ] IT Manager: __________________ Date: ____
- [ ] Department Head: __________________ Date: ____
- [ ] System Admin: __________________ Date: ____

### Go-Live Date: ____/____/____

### Support Contact: ____________________

---

## Issues Found & Resolutions

| Issue | Severity | Resolution | Date |
|-------|----------|-----------|------|
|  |  |  |  |
|  |  |  |  |

---

## Notes & Observations

```
[Space for additional notes]
```

---

## Success Criteria (All Must Be True)

- ✅ All tests pass
- ✅ Search works with 4+ characters
- ✅ New clients can be created
- ✅ Queue assigns clients
- ✅ Real-time updates work
- ✅ Old system still works
- ✅ No SQL errors exposed
- ✅ No database performance issues
- ✅ Staff can use interface
- ✅ Documentation available

---

## Deployment Complete!

When all items are checked and approved, the Client Queue System is ready for production use.

**Date Deployed:** _______________  
**Deployed By:** _______________  
**Verified By:** _______________

---

**Questions? See README_CLIENT_SYSTEM.md**
