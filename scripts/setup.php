<?php
/**
 * QMS Setup Script - Initialize database for client-based queue system
 * 
 * Run this script once to:
 * 1. Create the new tables (clients, queue_entries, call_history)
 * 2. Import client data from Excel file
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/clients.php';

echo "=== QMS Setup Script ===\n";
echo "Initializing database for client-based queue system...\n\n";

// Create database connection
$mysqli = new mysqli(QMS_DB_HOST, QMS_DB_USER, QMS_DB_PASS, QMS_DB_NAME);
if ($mysqli->connect_error) {
    die("ERROR: Connection failed: " . $mysqli->connect_error . "\n");
}

$mysqli->set_charset('utf8mb4');

// Step 1: Create tables
echo "Step 1: Creating database tables...\n";

$sqlFile = __DIR__ . '/01_create_tables.sql';
if (!file_exists($sqlFile)) {
    die("ERROR: SQL file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);
$statements = array_filter(array_map('trim', explode(';', $sql)), fn($s) => !empty($s) && strpos($s, '--') !== 0);

foreach ($statements as $statement) {
    if (!$mysqli->query($statement . ';')) {
        echo "WARNING: " . $mysqli->error . "\n";
    } else {
        echo "  ✓ Table created\n";
    }
}

echo "\nStep 2: Checking for clients data...\n";

// Try to find and import Excel file
$excelFile = glob(__DIR__ . '/../*clientlistshort*.xls');
if (!$excelFile) {
    echo "  ! Excel file not found. Skipping import.\n";
    echo "  To import clients later, run: php scripts/02_import_clients.php\n";
} else {
    $excelFile = $excelFile[0];
    echo "  Found: $excelFile\n";
    echo "  To import clients, run: php scripts/02_import_clients.php\n";
}

// Check if clients already exist
$result = $mysqli->query("SELECT COUNT(*) as count FROM clients");
$row = $result->fetch_assoc();
$clientCount = $row['count'] ?? 0;

echo "\nStep 3: Database Status\n";
echo "  Total clients: $clientCount\n";

$mysqli->close();

echo "\n=== Setup Complete ===\n";
echo "Next steps:\n";
echo "1. To import clients from Excel: php scripts/02_import_clients.php\n";
echo "2. Access the new counter interface at: counter_client.php?counter=LOAN%20PROCESSING%201\n";
echo "3. For old interface use: counter.php\n";
