<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';

$pdo = qms_pdo();
$today = (new DateTimeImmutable('now'))->format('Y-m-d');
$date = (string)($_GET['date'] ?? $today);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = $today;

$start = (string)($_GET['start'] ?? $today);
$end = (string)($_GET['end'] ?? $today);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) $start = $today;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) $end = $today;

$counterFilter = trim((string)($_GET['counter'] ?? ''));
if ($counterFilter !== '' && !in_array($counterFilter, QMS_COUNTERS, true)) {
  $counterFilter = '';
}

$pageTitle = QMS_ORG_NAME . ' • Reports';
require __DIR__ . '/partials/layout_top.php';

function has_col(PDO $pdo, string $table, string $col): bool {
  $stmt = $pdo->prepare('
    SELECT COUNT(*) AS c
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = :t
      AND COLUMN_NAME = :c
  ');
  $stmt->execute([':t' => $table, ':c' => $col]);
  return ((int)$stmt->fetch()['c']) > 0;
}

$hasQueueDate = has_col($pdo, 'queue_numbers', 'queue_date');
$hasFinishReason = has_col($pdo, 'queue_numbers', 'finish_reason');

$where = $hasQueueDate ? 'queue_date = :d' : 'DATE(created_at) = :d';
$skippedExpr = $hasFinishReason ? 'SUM(finish_reason = "Skipped")' : '0';

$sql = "
  SELECT
    current_counter,
    COUNT(*) AS issued,
    SUM(status = 'Finished') AS finished,
    $skippedExpr AS skipped,
    AVG(CASE
      WHEN processing_at IS NOT NULL AND finished_at IS NOT NULL THEN TIMESTAMPDIFF(SECOND, processing_at, finished_at)
      ELSE NULL
    END) AS avg_service_sec
  FROM queue_numbers
  WHERE $where
  GROUP BY current_counter
  ORDER BY current_counter ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':d' => $date]);
$rows = $stmt->fetchAll();

function fmt_sec(?float $sec): string {
  if ($sec === null) return '—';
  $s = (int)round($sec);
  $m = intdiv($s, 60);
  $r = $s % 60;
  return sprintf('%dm %02ds', $m, $r);
}
?>

<section class="card">
  <div class="card-h">
    <div>
      <div class="title">Reports</div>
      <div class="hint">Daily table + chart (no personal data)</div>
    </div>
    <div class="pill"><span class="dot"></span> <?= htmlspecialchars($date) ?></div>
  </div>
  <div class="card-b">
    <form method="get" class="row" style="align-items:flex-end;">
      <div style="min-width:240px;">
        <div class="label">Select date</div>
        <input class="input" type="date" name="date" value="<?= htmlspecialchars($date) ?>" />
      </div>
      <div style="min-width:200px;">
        <div class="label">Counter (optional)</div>
        <select name="counter">
          <option value="">All counters</option>
          <?php foreach (QMS_COUNTERS as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $counterFilter === $c ? 'selected' : '' ?>>
              <?= htmlspecialchars($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn primary" type="submit">View</button>
      <a class="btn" href="home.php" style="text-decoration:none;">Back to Home</a>
    </form>

    <div style="height:14px;"></div>

    <div class="card" style="margin-bottom:14px;">
      <div class="card-h">
        <div>
          <div class="title">Daily Chart</div>
          <div class="hint">Customize date range (Issued / Finished / Skipped)</div>
        </div>
      </div>
      <div class="card-b">
        <form class="row" id="chartForm" style="align-items:flex-end;">
          <div style="min-width:240px;">
            <div class="label">Start</div>
            <input class="input" type="date" id="repStart" value="<?= htmlspecialchars($start) ?>" />
          </div>
          <div style="min-width:240px;">
            <div class="label">End</div>
            <input class="input" type="date" id="repEnd" value="<?= htmlspecialchars($end) ?>" />
          </div>
          <div style="min-width:240px;">
            <div class="label">Counter (optional)</div>
            <select id="repCounter">
              <option value="">All counters</option>
              <?php foreach (QMS_COUNTERS as $c): ?>
                <option value="<?= htmlspecialchars($c) ?>" <?= $counterFilter === $c ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn primary" type="submit">Update Chart</button>
        </form>

        <div class="help" style="margin-top:10px;">Tip: set Start/End to a whole month to see daily volume trends.</div>
        <div class="rep-chart" id="repChart" aria-label="Daily chart"></div>
      </div>
    </div>

    <div class="list">
      <?php if (!$rows): ?>
        <div class="item">
          <div class="left"><span class="badge">—</span> <span>No records for this date</span></div>
          <span class="hint"></span>
        </div>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <div class="item">
            <div class="left">
              <span class="badge"><?= htmlspecialchars((string)$r['current_counter']) ?></span>
              <span>
                <b>Issued:</b> <?= (int)$r['issued'] ?>
                &nbsp; <b>Finished:</b> <?= (int)$r['finished'] ?>
                &nbsp; <b>Skipped:</b> <?= (int)$r['skipped'] ?>
              </span>
            </div>
            <span class="hint"><b>Avg service:</b> <?= htmlspecialchars(fmt_sec($r['avg_service_sec'] !== null ? (float)$r['avg_service_sec'] : null)) ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="help" style="margin-top:10px;">
      Notes: “Avg service” uses time from <b>Processing</b> to <b>Finished</b>. “Skipped” appears after you run the migration and enable `finish_reason`.
    </div>
  </div>
</section>

<script>
  (function () {
    const form = document.getElementById('chartForm');
    const chart = document.getElementById('repChart');
    const startEl = document.getElementById('repStart');
    const endEl = document.getElementById('repEnd');
    const counterEl = document.getElementById('repCounter');

    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function bar(label, issued, finished, skipped, max) {
      const w = max > 0 ? Math.max(2, Math.round((issued / max) * 100)) : 0;
      const wf = issued > 0 ? Math.round((finished / issued) * 100) : 0;
      const ws = issued > 0 ? Math.round((skipped / issued) * 100) : 0;
      return `
        <div class="rep-row">
          <div class="rep-date">${esc(label)}</div>
          <div class="rep-bar">
            <div class="rep-fill" style="width:${w}%">
              <div class="rep-seg finished" style="width:${wf}%"></div>
              <div class="rep-seg skipped" style="width:${ws}%"></div>
            </div>
          </div>
          <div class="rep-num">${issued}</div>
        </div>
      `;
    }

    async function load() {
      chart.innerHTML = '<div class="help">Loading chart…</div>';
      const qs = new URLSearchParams({
        action: 'report_series',
        start: startEl.value,
        end: endEl.value,
        counter: counterEl.value
      });
      const res = await fetch('api.php?' + qs.toString(), { cache: 'no-store' });
      const j = await res.json();
      if (!j || j.ok !== true) {
        chart.innerHTML = '<div class="help">Unable to load chart.</div>';
        return;
      }
      const rows = Array.isArray(j.rows) ? j.rows : [];
      const max = rows.reduce((m, r) => Math.max(m, Number(r.issued || 0)), 0);
      if (rows.length === 0) {
        chart.innerHTML = '<div class="help">No data for this range.</div>';
        return;
      }
      chart.innerHTML = `
        <div class="rep-legend">
          <span><i class="lg issued"></i> Issued</span>
          <span><i class="lg finished"></i> Finished</span>
          <span><i class="lg skipped"></i> Skipped</span>
        </div>
      ` + rows.map(r => bar(r.d, Number(r.issued||0), Number(r.finished||0), Number(r.skipped||0), max)).join('');
    }

    form.addEventListener('submit', (e) => { e.preventDefault(); load(); });
    load();
  })();
</script>

<?php require __DIR__ . '/partials/layout_bottom.php'; ?>

