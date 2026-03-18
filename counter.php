<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$counter = (string)($_GET['counter'] ?? '');
if (!in_array($counter, QMS_COUNTERS, true)) {
  $counter = QMS_COUNTERS[0] ?? 'Cashier';
}

$accent = QMS_COUNTER_ACCENTS[$counter] ?? '#1c3268';

$pageTitle = QMS_ORG_NAME . ' • Counter • ' . $counter;
$bodyClass = 'wide counter-mode';
require __DIR__ . '/partials/layout_top.php';
?>

<div class="grid cols-2 counter-wrap" style="--accent: <?= htmlspecialchars($accent) ?>;" data-page="counter" data-counter="<?= htmlspecialchars($counter) ?>">
  <section class="card">
    <div class="card-h">
      <div>
        <div class="title">Counter: <?= htmlspecialchars($counter) ?></div>
        <div class="hint">Serve, finish, skip, or forward the next ticket</div>
      </div>
      <div class="pill"><span class="dot"></span> Live</div>
    </div>
    <div class="card-b">
      <div class="grid cols-2">
        <div>
          <div class="label">NOW SERVING</div>
          <div class="big-number" id="cNow">—</div>
        </div>
        <div>
          <div class="label">WAITING COUNT</div>
          <div class="big-number" style="font-size:58px;" id="cWaitingCount">—</div>
          <div class="help">This is how many tickets are waiting for this counter right now.</div>
        </div>
      </div>

      <div class="row" style="margin-top:10px;">
        <button class="btn ok" id="btnFinish" disabled>Finish</button>
        <button class="btn danger" id="btnSkip" disabled>Skip</button>
      </div>

      <div class="card" style="margin-top:14px;">
        <div class="card-h">
          <div>
            <div class="title">Forward</div>
            <div class="hint">Move the current ticket to another counter</div>
          </div>
        </div>
        <div class="card-b">
          <div class="label">Forward to</div>
          <select id="forwardTo">
            <?php foreach (QMS_COUNTERS as $c): if ($c === $counter) continue; ?>
              <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="row" style="margin-top:10px;">
            <button class="btn" id="btnForward" disabled>Forward Ticket</button>
            <a class="btn" href="tv.php" style="text-decoration:none;">Open TV Display</a>
          </div>
          <div class="help">Forwarding keeps the same number and sets status back to Waiting for the next counter.</div>
        </div>
      </div>
    </div>
  </section>

  <aside class="card">
    <div class="card-h">
      <div>
        <div class="title">Waiting List</div>
        <div class="hint">Live list for this counter (oldest first)</div>
      </div>
    </div>
    <div class="card-b">
      <div class="list" id="cWaiting"></div>
    </div>
  </aside>
</div>

<?php require __DIR__ . '/partials/layout_bottom.php'; ?>

