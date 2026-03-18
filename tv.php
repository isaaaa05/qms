<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$pageTitle = QMS_ORG_NAME . ' • TV Display';
$extraHead = '<meta http-equiv="refresh" content="3600">'; // safe refresh every hour
$bodyClass = 'tv-mode';
require __DIR__ . '/partials/layout_top.php';
?>

<section class="tv-wrap" data-page="tv">
  <div class="tv-table-wrap">
    <table class="tv-table" aria-label="Now Serving Table">
      <thead>
        <tr>
          <th style="width:44%;">COUNTER</th>
          <th style="width:26%;">STATUS</th>
          <th style="width:30%;">NOW SERVING</th>
        </tr>
      </thead>
      <tbody id="tvGrid"></tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/partials/layout_bottom.php'; ?>

