<?php
/**
 * Test Client System
 * 
 * Quick test script to verify the client system is working
 * Run: php scripts/test_client_system.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/queue.php';
require_once __DIR__ . '/../lib/clients.php';

echo "=== Client Queue System Test ===\n\n";

try {
    // Test 1: Database Connection
    echo "Test 1: Database Connection...\n";
    $mysqli = new mysqli(QMS_DB_HOST, QMS_DB_USER, QMS_DB_PASS, QMS_DB_NAME);
    if ($mysqli->connect_error) {
        throw new Exception('Connection failed: ' . $mysqli->connect_error);
    }
    echo "  ✓ Connected\n";

    // Test 2: Check Tables
    echo "\nTest 2: Checking Database Tables...\n";
    $tables = ['clients', 'queue_entries', 'call_history'];
    foreach ($tables as $table) {
        $result = $mysqli->query("SELECT COUNT(*) as count FROM $table");
        if (!$result) {
            throw new Exception("Table '$table' does not exist");
        }
        $row = $result->fetch_assoc();
        echo "  ✓ $table (" . $row['count'] . " records)\n";
    }

    // Test 3: Create QueueManager
    echo "\nTest 3: Creating QueueManager...\n";
    $queueManager = new QueueManager($mysqli);
    echo "  ✓ QueueManager created\n";

    // Test 4: Create ClientManager
    echo "\nTest 4: Creating ClientManager...\n";
    $clientManager = new ClientManager($mysqli);
    echo "  ✓ ClientManager created\n";

    // Test 5: Test Client Creation
    echo "\nTest 5: Creating Test Client...\n";
    $testClient = [
        'surname' => 'TestClient',
        'full_name' => 'Test Client Name',
        'phone' => '555-0000',
        'email' => 'test@example.com'
    ];
    $result = $queueManager->createClient($testClient);
    if (!$result['success']) {
        throw new Exception('Failed to create client: ' . $result['error']);
    }
    $testClientId = $result['id'];
    echo "  ✓ Created client ID: $testClientId\n";

    // Test 6: Test Client Search
    echo "\nTest 6: Testing Client Search...\n";
    $searchResult = $queueManager->searchClients('Test');
    if (!$searchResult['success']) {
        throw new Exception('Search failed: ' . $searchResult['error']);
    }
    if (count($searchResult['clients']) === 0) {
        throw new Exception('Client not found in search');
    }
    echo "  ✓ Search found " . count($searchResult['clients']) . " client(s)\n";

    // Test 7: Test Min Length Validation
    echo "\nTest 7: Testing Minimum 4 Character Validation...\n";
    $shortResult = $queueManager->searchClients('abc');
    if ($shortResult['success']) {
        throw new Exception('Should have rejected search < 4 characters');
    }
    echo "  ✓ Correctly rejected: " . $shortResult['error'] . "\n";

    // Test 8: Assign Client to Counter
    echo "\nTest 8: Assigning Client to Counter 1...\n";
    $assignResult = $queueManager->assignClientToCounter($testClientId, 1, 'Test Service');
    if (!$assignResult['success']) {
        throw new Exception('Failed to assign: ' . $assignResult['error']);
    }
    $queueEntryId = $assignResult['queue_entry_id'];
    echo "  ✓ Assigned to queue entry ID: $queueEntryId\n";

    // Test 9: Get Counter Queue
    echo "\nTest 9: Getting Counter 1 Queue...\n";
    $queue = $queueManager->getCounterQueue(1);
    if (count($queue) === 0) {
        throw new Exception('Queue is empty');
    }
    echo "  ✓ Queue has " . count($queue) . " client(s)\n";
    echo "    First client: " . $queue[0]['surname'] . "\n";

    // Test 10: Call Next Client
    echo "\nTest 10: Calling Next Client...\n";
    $callResult = $queueManager->callNextClient(1);
    if (!$callResult['success']) {
        throw new Exception('Failed to call next: ' . $callResult['error']);
    }
    echo "  ✓ Called: " . $callResult['surname'] . "\n";

    // Test 11: Complete Service
    echo "\nTest 11: Completing Service...\n";
    $completeResult = $queueManager->completeService($queueEntryId);
    if (!$completeResult['success']) {
        throw new Exception('Failed to complete: ' . $completeResult['error']);
    }
    echo "  ✓ Service marked complete\n";

    // Test 12: Total Clients
    echo "\nTest 12: Getting Total Clients...\n";
    $total = $clientManager->getTotalClients();
    echo "  ✓ Total clients in database: $total\n";

    echo "\n=== All Tests Passed ✓ ===\n";
    echo "\nSystem is ready to use!\n";
    echo "Access the counter interface at: counter_client.php\n";

} catch (Exception $e) {
    echo "\n❌ Test Failed: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
