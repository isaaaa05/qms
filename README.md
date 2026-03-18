## PERA MPC Queue Management System (QMS) — PHP + MySQL (XAMPP)

This is a LAN-ready, web-based queue management system:

- **Staff Home** (`home.php`): quick launcher for Counter/TV screens
- **Counter page** (`counter.php?counter=Cashier`): call next, finish, forward
- **TV display** (`tv.php`): real-time “Now Serving” per counter
- **Reports** (`report.php`): daily totals per counter

No personal data is stored—only queue numbers, counters, and statuses.

### Setup (phpMyAdmin + XAMPP)

1. **Start XAMPP**
   - Start **Apache**
   - Start **MySQL**

2. **Create database in phpMyAdmin**
   - Open `http://localhost/phpmyadmin`
   - Create database: `qms` (utf8mb4)

3. **Import schema**
   - Select database `qms`
   - Go to **Import**
   - Choose file: `database/qms.sql`
   - Click **Go**

   If you imported an older version already (and you already have the `queue_numbers` table), run: `database/migrate_v2.sql` in phpMyAdmin (SQL tab).

4. **Configure DB credentials**
   - Edit `config.php` and set your DB user/password if needed.

5. **Open the app**
   - Home: `http://localhost/QMS/`
   - TV: `http://localhost/QMS/tv.php`
   - Reports: `http://localhost/QMS/report.php`
   - Counter example: `http://localhost/QMS/counter.php?counter=CASHIER%2FRELEASING`

### Suggested counters

Edit the counters list in `config.php`:

- LOAN PROCESSING 1
- LOAN PROCESSING 2
- CASHIER/RELEASING
- APPROVAL

### Daily reset

Queue numbers automatically reset every day:

- Today starts at **001**
- Tomorrow starts at **001** again


### LAN usage

From other PCs/TV on the same network, open:

- `http://<SERVER_IP>/QMS/tv.php`
- `http://<SERVER_IP>/QMS/counter.php?counter=Loan`

