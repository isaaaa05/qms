<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$pageTitle = QMS_ORG_NAME . ' • Kiosk';
require __DIR__ . '/partials/layout_top.php';
?>

<div class="grid cols-2">
  <section class="card" data-page="kiosk">
    <div class="card-h">
      <div>
        <div class="title">Get Your Queue Number</div>
        <div class="hint">Choose your first service, then print/remember your ticket</div>
      </div>
      <div class="pill"><span class="dot"></span> Ready</div>
    </div>
    <div class="card-b">
      <div class="label">Select first counter</div>
      <select id="kioskCounter">
        <?php foreach (QMS_COUNTERS as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="help">This assigns your number to the first counter. You may be forwarded later by staff if needed.</div>

      <div class="row" style="margin-top:14px;">
        <button class="btn primary" id="kioskIssue">Issue Number</button>
        <a class="btn" href="tv.php" style="text-decoration:none;">Open TV Display</a>
      </div>
    </div>
  </section>

  <aside class="card">
    <div class="card-h">
      <div>
        <div class="title">Your Ticket</div>
        <div class="hint">Show this number when called</div>
      </div>
    </div>
    <div class="card-b">
      <div class="label">QUEUE NUMBER</div>
      <div class="big-number" id="kioskTicket">—</div>
      <div class="label">GO TO</div>
      <div style="font-weight:900; font-size:18px;" id="kioskTo">—</div>
      <div class="help">Tip: Keep the same number even if forwarded to another counter.</div>
    </div>
  </aside>
</div>

<?php require __DIR__ . '/partials/layout_bottom.php'; ?>

