<?php
/**
 * Import Clients from Excel File
 * This script extracts client data from the Excel file and imports it into the database
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/clients.php';

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line');
}

echo "Starting client import...\n";

// Create database connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

$clientManager = new ClientManager($mysqli);

// Try to read the Excel file
$excelFile = __DIR__ . '/../9044 clientlistshort031826135150350.xls';

if (!file_exists($excelFile)) {
    // Try alternative paths
    $excelFile = glob(__DIR__ . '/../*clientlistshort*.xls');
    if (!$excelFile) {
        die("Error: Excel file not found\n");
    }
    $excelFile = $excelFile[0];
}

echo "Found Excel file: $excelFile\n";

// Read Excel file - trying simple parsing for XLS format
$clients = readExcelFile($excelFile);

if (empty($clients)) {
    die("Error: No clients found in Excel file\n");
}

echo "Found " . count($clients) . " clients in Excel file\n";

// Import clients
$result = $clientManager->bulkImportClients($clients);

echo "\n=== Import Results ===\n";
echo "Imported: " . $result['imported'] . "\n";
echo "Skipped: " . $result['skipped'] . "\n";

if (!empty($result['errors'])) {
    echo "\nErrors:\n";
    foreach ($result['errors'] as $error) {
        echo "  - $error\n";
    }
}

$totalClients = $clientManager->getTotalClients();
echo "\nTotal clients in database: $totalClients\n";

$mysqli->close();
echo "\nImport completed!\n";

/**
 * Read Excel file (.xls format)
 * Simple parser for basic XLS files
 */
function readExcelFile($filePath) {
    $clients = [];
    
    // Try using PHP built-in functions for XLS reading
    // First, try using SimpleXML for newer Excel formats or other tools
    
    // For older XLS format, we'll need to read binary data
    // This is a simplified approach - for production, consider using PhpSpreadsheet
    
    $handle = fopen($filePath, 'rb');
    if (!$handle) {
        echo "Warning: Could not open Excel file directly. Attempting alternative methods...\n";
        return [];
    }
    
    // Read the entire file into memory
    $content = fread($handle, filesize($filePath));
    fclose($handle);
    
    // Try to extract text between null bytes and control characters
    // This is a very basic approach
    $clients = extractClientsFromBinary($content);
    
    return $clients;
}

/**
 * Extract client data from binary XLS content
 * This is a basic text extraction from XLS binary format
 */
function extractClientsFromBinary($content) {
    $clients = [];
    
    // Remove non-printable characters but keep spaces and common delimiters
    $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $content);
    
    // Split by multiple spaces (likely cell boundaries)
    $lines = preg_split('/[\r\n]+/', $cleaned);
    
    $skipHeader = true;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and likely header rows
        if (empty($line) || strlen($line) < 3) {
            continue;
        }
        
        // Skip rows containing only numbers or dates
        if (preg_match('/^\d+\s*$/', $line)) {
            continue;
        }
        
        // Skip common header keywords
        if (preg_match('/name|client|id|code|list/i', $line)) {
            $skipHeader = true;
            continue;
        }
        
        if ($skipHeader && strlen($line) > 5 && !is_numeric(substr($line, 0, 4))) {
            // Likely a client name
            $parts = preg_split('/\s{2,}/', $line);
            
            if (count($parts) > 0) {
                $surname = trim($parts[0]);
                
                // Only add if surname is at least 4 characters
                if (strlen($surname) >= 4 && !is_numeric($surname)) {
                    $clients[] = [
                        'surname' => $surname,
                        'full_name' => $surname,
                        'client_id' => isset($parts[1]) ? trim($parts[1]) : null
                    ];
                }
            }
        }
    }
    
    return array_unique($clients, SORT_REGULAR);
}
