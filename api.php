<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/queue.php';
require_once __DIR__ . '/lib/clients.php';

try {
  $action = $_GET['action'] ?? '';

  if ($action === 'counters') {
    qms_json(['ok' => true, 'counters' => QMS_COUNTERS, 'org' => QMS_ORG_NAME]);
  }

  if ($action === 'issue') {
    qms_require_post();
    $body = qms_input_json();
    $counter = (string)($body['counter'] ?? (QMS_COUNTERS[0] ?? ''));
    $ticket = qms_issue_ticket($counter);
    qms_json(['ok' => true, 'ticket' => $ticket]);
  }

  // Deprecated: walkin (kept for backward compatibility, now redirects to attach)
  if ($action === 'walkin' || $action === 'attach') {
    qms_require_post();
    $body = qms_input_json();
    $counter = (string)($body['counter'] ?? (QMS_COUNTERS[0] ?? ''));
    $number = isset($body['number']) ? (int)$body['number'] : 0;
    $ticket = qms_attach_existing_ticket($counter, $number);
    qms_json(['ok' => true, 'ticket' => $ticket]);
  }

  if ($action === 'counter_state') {
    $counter = qms_validate_counter((string)($_GET['counter'] ?? ''));
    $now = qms_now_serving($counter);
    $waiting = qms_list_waiting($counter, 10);
    qms_json([
      'ok' => true,
      'counter' => $counter,
      'accent' => QMS_COUNTER_ACCENTS[$counter] ?? '#1c3268',
      'now' => $now,
      'waiting' => $waiting,
      'pollMs' => QMS_POLL_MS,
    ]);
  }

  if ($action === 'call_next') {
    qms_require_post();
    $body = qms_input_json();
    $counter = qms_validate_counter((string)($body['counter'] ?? ''));
    $called = qms_call_next($counter);
    qms_json(['ok' => true, 'called' => $called]);
  }

  if ($action === 'finish') {
    qms_require_post();
    $body = qms_input_json();
    $id = (int)($body['id'] ?? 0);
    if ($id <= 0) qms_json(['ok' => false, 'error' => 'Invalid id'], 400);
    $called = qms_finish_and_advance($id);
    qms_json(['ok' => true, 'called' => $called]);
  }

  if ($action === 'forward') {
    qms_require_post();
    $body = qms_input_json();
    $id = (int)($body['id'] ?? 0);
    $nextCounter = (string)($body['nextCounter'] ?? '');
    if ($id <= 0) qms_json(['ok' => false, 'error' => 'Invalid id'], 400);
    $called = qms_forward_and_advance($id, $nextCounter);
    qms_json(['ok' => true, 'called' => $called]);
  }

  if ($action === 'skip') {
    qms_require_post();
    $body = qms_input_json();
    $id = (int)($body['id'] ?? 0);
    if ($id <= 0) qms_json(['ok' => false, 'error' => 'Invalid id'], 400);
    $called = qms_skip_and_advance($id);
    qms_json(['ok' => true, 'called' => $called]);
  }

  if ($action === 'tv_state') {
    qms_json([
      'ok' => true,
      'state' => qms_tv_state(),
      'accents' => QMS_COUNTER_ACCENTS,
      'pollMs' => QMS_POLL_MS,
      'org' => QMS_ORG_NAME
    ]);
  }

  if ($action === 'report_series') {
    // GET: start=YYYY-MM-DD&end=YYYY-MM-DD&counter=(optional)
    $start = (string)($_GET['start'] ?? '');
    $end = (string)($_GET['end'] ?? '');
    $counter = trim((string)($_GET['counter'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
      qms_json(['ok' => false, 'error' => 'Invalid date range'], 400);
    }
    if ($counter !== '' && !in_array($counter, QMS_COUNTERS, true)) {
      qms_json(['ok' => false, 'error' => 'Unknown counter'], 400);
    }

    $pdo = qms_pdo();
    $hasQueueDate = (int)$pdo->query("
      SELECT COUNT(*) AS c
      FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'queue_numbers'
        AND COLUMN_NAME = 'queue_date'
    ")->fetch()['c'] > 0;
    $hasFinishReason = (int)$pdo->query("
      SELECT COUNT(*) AS c
      FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'queue_numbers'
        AND COLUMN_NAME = 'finish_reason'
    ")->fetch()['c'] > 0;

    $dateCol = $hasQueueDate ? 'queue_date' : 'DATE(created_at)';
    $skippedExpr = $hasFinishReason ? 'SUM(finish_reason = "Skipped")' : '0';

    $where = "$dateCol BETWEEN :s AND :e";
    $params = [':s' => $start, ':e' => $end];
    if ($counter !== '') {
      $where .= ' AND current_counter = :c';
      $params[':c'] = $counter;
    }

    $sql = "
      SELECT
        $dateCol AS d,
        COUNT(*) AS issued,
        SUM(status = 'Finished') AS finished,
        $skippedExpr AS skipped
      FROM queue_numbers
      WHERE $where
      GROUP BY d
      ORDER BY d ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    qms_json(['ok' => true, 'rows' => $rows]);
  }

  // New client management endpoints
  if ($action === 'search_clients') {
    $surname = (string)($_GET['surname'] ?? '');
    $mysqli = qms_mysqli();
    
    // Create QueueManager instance
    $queueManager = new QueueManager($mysqli);
    $result = $queueManager->searchClients($surname);
    
    qms_json($result);
  }

  if ($action === 'create_client') {
    qms_require_post();
    $body = qms_input_json();
    $mysqli = qms_mysqli();
    
    $queueManager = new QueueManager($mysqli);
    $result = $queueManager->createClient($body);
    
    qms_json($result);
  }

  if ($action === 'assign_client') {
    qms_require_post();
    $body = qms_input_json();
    $client_id = (int)($body['client_id'] ?? 0);
    $counter = (string)($body['counter'] ?? '');
    $purpose = (string)($body['purpose'] ?? '');
    
    if ($client_id <= 0 || empty($counter)) {
      qms_json(['ok' => false, 'error' => 'Invalid client_id or counter'], 400);
    }
    
    $mysqli = qms_mysqli();
    $queueManager = new QueueManager($mysqli);
    $result = $queueManager->assignClientToCounter($client_id, (int)$counter, $purpose);
    
    qms_json($result);
  }

  if ($action === 'counter_queue') {
    $counter = (int)($_GET['counter'] ?? 0);
    if ($counter <= 0) {
      qms_json(['ok' => false, 'error' => 'Invalid counter'], 400);
    }
    
    $mysqli = qms_mysqli();
    $queueManager = new QueueManager($mysqli);
    $queue = $queueManager->getCounterQueue($counter);
    
    qms_json(['ok' => true, 'queue' => $queue]);
  }

  if ($action === 'call_next_client') {
    qms_require_post();
    $body = qms_input_json();
    $counter = (int)($body['counter'] ?? 0);
    
    if ($counter <= 0) {
      qms_json(['ok' => false, 'error' => 'Invalid counter'], 400);
    }
    
    $mysqli = qms_mysqli();
    $queueManager = new QueueManager($mysqli);
    $result = $queueManager->callNextClient($counter);
    
    qms_json($result);
  }

  if ($action === 'complete_service') {
    qms_require_post();
    $body = qms_input_json();
    $entry_id = (int)($body['entry_id'] ?? 0);
    
    if ($entry_id <= 0) {
      qms_json(['ok' => false, 'error' => 'Invalid entry_id'], 400);
    }
    
    $mysqli = qms_mysqli();
    $queueManager = new QueueManager($mysqli);
    $result = $queueManager->completeService($entry_id);
    
    qms_json($result);
  }

  qms_json(['ok' => false, 'error' => 'Unknown action'], 404);
} catch (Throwable $e) {
  qms_json(['ok' => false, 'error' => $e->getMessage()], 500);
}

