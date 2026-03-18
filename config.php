<?php
declare(strict_types=1);

// Database config (XAMPP defaults: user "root", password "")
const QMS_DB_HOST = '127.0.0.1';
const QMS_DB_NAME = 'qms';
const QMS_DB_USER = 'root';
const QMS_DB_PASS = '';
const QMS_DB_CHARSET = 'utf8mb4';

// Blue UI theme
const QMS_ORG_NAME = 'PERA MPC';

// Define available counters here (these names appear in UI and DB)
const QMS_COUNTERS = [
  'LOAN PROCESSING 1',
  'LOAN PROCESSING 2',
  'CASHIER/RELEASING',
  'APPROVAL',
];

// Per-counter accent colors (for clearer distinction on TV/counter screens)
// Feel free to adjust to match your branding.
const QMS_COUNTER_ACCENTS = [
  'LOAN PROCESSING 1' => '#1c3268',
  'LOAN PROCESSING 2' => '#2b4aa0',
  'CASHIER/RELEASING' => '#0f7ea8',
  'APPROVAL' => '#6b2e8f',
];

// Ticket numbering
// - Stored as integer (1, 2, 3...)
// - Displayed as zero-padded (001, 002...)
const QMS_TICKET_PAD = 3;

// Polling interval for realtime updates (milliseconds)
const QMS_POLL_MS = 1200;

// Database constants for new client system
const DB_HOST = QMS_DB_HOST;
const DB_NAME = QMS_DB_NAME;
const DB_USER = QMS_DB_USER;
const DB_PASSWORD = QMS_DB_PASS;

/**
 * Get MySQLi connection
 */
function qms_mysqli() {
  static $mysqli = null;
  if ($mysqli === null) {
    $mysqli = new mysqli(QMS_DB_HOST, QMS_DB_USER, QMS_DB_PASS, QMS_DB_NAME);
    if ($mysqli->connect_error) {
      throw new Exception('Database connection failed: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
  }
  return $mysqli;
}

