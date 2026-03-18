<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$pageTitle = QMS_ORG_NAME . ' • Staff Home';
require __DIR__ . '/partials/layout_top.php';
?>

<div class="grid cols-2">
  <section class="card">
    <div class="card-h">
      <div>
        <div class="title">Staff Screens</div>
        <div class="hint">Open your counter interface or the TV display</div>
      </div>
      <div class="pill"><span class="dot"></span> LAN Ready</div>
    </div>
    <div class="card-b">
      <div class="label">Select counter</div>
      <select id="homeCounter">
        <?php foreach (QMS_COUNTERS as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="row" style="margin-top:12px;">
        <a class="btn primary" id="openCounter" href="#" style="text-decoration:none;">Open Counter Screen</a>
        <a class="btn" href="tv.php" style="text-decoration:none;">Open TV Display</a>
        <a class="btn" href="report.php" style="text-decoration:none;">Reports</a>
      </div>
      <div class="help">
        Tip: Bookmark your counter URL on each staff PC, e.g.
        <span style="font-weight:800;">/QMS/counter.php?counter=CASHIER%2FRELEASING</span>
      </div>
    </div>
  </section>

  <aside class="card">
    <div class="card-h">
      <div>
        <div class="title">Daily workflow (no kiosk)</div>
        <div class="hint">How clients enter the queue without a dedicated attendant</div>
      </div>
    </div>
    <div class="card-b">
      <div class="list">
        <div class="item">
          <div class="left"><span class="badge">A</span> <span>Client goes directly to their first window</span></div>
          <span class="hint">Best for small office</span>
        </div>
        <div class="item">
          <div class="left"><span class="badge">B</span> <span>Each counter issues its own ticket when client arrives</span></div>
          <span class="hint">No bottleneck</span>
        </div>
        <div class="item">
          <div class="left"><span class="badge">C</span> <span>Use a phone/tablet QR “Intake” page at entrance</span></div>
          <span class="hint">Optional upgrade</span>
        </div>
      </div>
      <div class="help">You can start with A/B now; C can be added later without changing the core queue logic.</div>
    </div>
  </aside>
</div>

<script>
  (function () {
    const sel = document.getElementById('homeCounter');
    const a = document.getElementById('openCounter');
    const update = () => {
      const v = sel.value || '';
      a.href = 'counter.php?counter=' + encodeURIComponent(v);
    };
    sel.addEventListener('change', update);
    update();
  })();
</script>

<?php require __DIR__ . '/partials/layout_bottom.php'; ?>

