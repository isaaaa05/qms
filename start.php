<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$pageTitle = QMS_ORG_NAME . ' • Queue System';
$bodyClass = 'home-mode';
require __DIR__ . '/partials/layout_top.php';

// Check database connection and client count
$mysqli = new mysqli(QMS_DB_HOST, QMS_DB_USER, QMS_DB_PASS, QMS_DB_NAME);
$dbConnected = !$mysqli->connect_error;
$clientCount = 0;

if ($dbConnected) {
    // Check if new tables exist
    $result = $mysqli->query("SELECT COUNT(*) as count FROM clients");
    if ($result) {
        $row = $result->fetch_assoc();
        $clientCount = $row['count'] ?? 0;
    }
    $mysqli->close();
}
?>

<div class="container" style="max-width: 900px; margin: 40px auto; padding: 20px;">
    <div class="card">
        <div class="card-h">
            <div>
                <div class="title"><?= htmlspecialchars(QMS_ORG_NAME) ?> Queue Management System</div>
                <div class="hint">Choose your interface below</div>
            </div>
        </div>
        <div class="card-b">
            <?php if ($dbConnected && $clientCount > 0): ?>
                <div class="alert alert-success" style="background-color:#e8f5e9;border:1px solid #4caf50;padding:12px;border-radius:4px;margin-bottom:20px;">
                    ✓ Database ready with <?= $clientCount ?> clients
                </div>
            <?php endif; ?>

            <div class="grid cols-2" style="gap:20px;">
                <!-- New Client-Based System -->
                <section class="card" style="border:2px solid #1c3268;">
                    <div class="card-h" style="background-color:#f5f5f5;">
                        <div>
                            <div class="title" style="color:#1c3268;">🆕 Client-Based System</div>
                            <div class="hint">Uses client surnames instead of numbers</div>
                        </div>
                    </div>
                    <div class="card-b">
                        <div class="help" style="margin-bottom:15px;">
                            <strong>Features:</strong>
                            <ul style="margin:10px 0;padding-left:20px;">
                                <li>Search clients by surname (min 4 chars)</li>
                                <li>Add new clients on-the-fly</li>
                                <li>Full client database</li>
                                <li>Service history tracking</li>
                            </ul>
                        </div>

                        <div style="margin-bottom:15px;">
                            <div class="label">Select Counter:</div>
                            <select id="counterSelectNew" onchange="goToCounterClient()">
                                <option value="">-- Select Counter --</option>
                                <?php foreach (QMS_COUNTERS as $counter): ?>
                                    <option value="<?= htmlspecialchars($counter) ?>"><?= htmlspecialchars($counter) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <a href="counter_client.php?counter=<?= urlencode(QMS_COUNTERS[0] ?? 'Cashier') ?>" class="btn ok" style="text-decoration:none;display:inline-block;">
                                Open First Counter →
                            </a>
                        </div>

                        <?php if (!$dbConnected || $clientCount === 0): ?>
                            <div class="help" style="color:#ff9800;margin-top:10px;">
                                ⚠ Setup required. See documentation for setup steps.
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Classic Ticket System -->
                <section class="card" style="border:2px solid #666;">
                    <div class="card-h" style="background-color:#f5f5f5;">
                        <div>
                            <div class="title" style="color:#666;">Classic Ticket System</div>
                            <div class="hint">Traditional number-based queuing</div>
                        </div>
                    </div>
                    <div class="card-b">
                        <div class="help" style="margin-bottom:15px;">
                            <strong>Features:</strong>
                            <ul style="margin:10px 0;padding-left:20px;">
                                <li>Issue numbered tickets</li>
                                <li>Serve by ticket numbers</li>
                                <li>Multi-counter support</li>
                                <li>TV display mode</li>
                            </ul>
                        </div>

                        <div style="margin-bottom:15px;">
                            <div class="label">Select Counter:</div>
                            <select id="counterSelectOld" onchange="goToCounterOld()">
                                <option value="">-- Select Counter --</option>
                                <?php foreach (QMS_COUNTERS as $counter): ?>
                                    <option value="<?= htmlspecialchars($counter) ?>"><?= htmlspecialchars($counter) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <a href="counter.php?counter=<?= urlencode(QMS_COUNTERS[0] ?? 'Cashier') ?>" class="btn" style="text-decoration:none;display:inline-block;">
                                Open First Counter →
                            </a>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Setup Instructions -->
            <div class="card" style="margin-top:20px;background-color:#fff3e0;">
                <div class="card-h">
                    <div>
                        <div class="title">🔧 Setup Instructions</div>
                        <div class="hint">First time setup</div>
                    </div>
                </div>
                <div class="card-b">
                    <div class="help">
                        <ol style="padding-left:20px;">
                            <li><strong>Initialize Database:</strong>
                                <code style="background:#f0f0f0;padding:2px 6px;border-radius:3px;display:block;margin:5px 0;">php scripts/setup.php</code>
                            </li>
                            <li><strong>Import Clients:</strong>
                                <code style="background:#f0f0f0;padding:2px 6px;border-radius:3px;display:block;margin:5px 0;">php scripts/02_import_clients.php</code>
                            </li>
                            <li><strong>Access System:</strong> Use links above after setup
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Documentation -->
            <div class="card" style="margin-top:20px;">
                <div class="card-h">
                    <div>
                        <div class="title">📚 Documentation</div>
                    </div>
                </div>
                <div class="card-b">
                    <div class="row" style="flex-wrap:wrap;gap:10px;">
                        <a href="CLIENT_SYSTEM_SETUP.md" class="btn" download>
                            📄 Setup Guide (Download)
                        </a>
                        <a href="#" onclick="alert('Database schema, API endpoints, and troubleshooting info in CLIENT_SYSTEM_SETUP.md'); return false;" class="btn">
                            ❓ View Full Docs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.alert {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 4px;
}

.alert-success {
    background-color: #e8f5e9;
    border: 1px solid #4caf50;
    color: #2e7d32;
}

code {
    background-color: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}
</style>

<script>
function goToCounterClient() {
    const counter = document.getElementById('counterSelectNew').value;
    if (counter) {
        window.location.href = 'counter_client.php?counter=' + encodeURIComponent(counter);
    }
}

function goToCounterOld() {
    const counter = document.getElementById('counterSelectOld').value;
    if (counter) {
        window.location.href = 'counter.php?counter=' + encodeURIComponent(counter);
    }
}
</script>

<?php require __DIR__ . '/partials/layout_bottom.php'; ?>
