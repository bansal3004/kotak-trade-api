<?php
$csv_file = __DIR__ . "/nse_cm-v1.csv";
if (!file_exists($csv_file)) die("❌ CSV not found");

// --- Parse CSV dynamically ---
$stocks = [];
if (($h = fopen($csv_file, "r")) !== false) {
    $header = fgetcsv($h);
    $cols = array_flip($header);

    // Columns as per your actual file
    $col_symbol  = $cols['symbol'] ?? 0;
    $col_name    = $cols['symbol_desc'] ?? 1;
    $col_exch    = $cols['exchange'] ?? 3;
    $col_seg     = $cols['segment'] ?? 4;

    while (($r = fgetcsv($h)) !== false) {
        if (empty($r[$col_symbol])) continue;

        $exchange = strtolower(trim($r[$col_exch] ?? 'nse'));
        $segment = strtolower(trim($r[$col_seg] ?? 'cm'));

        $neosymbol = "{$exchange}_{$segment}|{$r[$col_symbol]}";

        $stocks[] = [
            'neosymbol' => $neosymbol,
            'symbol' => trim($r[$col_symbol]),
            'name' => trim($r[$col_name] ?? '')
        ];
    }
    fclose($h);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>📈 Live NSE Stocks</title>
<style>
body{font-family:'Inter',sans-serif;background:#f7f8fc;margin:0;padding:20px}
.container{max-width:1200px;margin:auto}
h1{text-align:center;font-size:20px;color:#111;margin-bottom:16px}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.08)}
th,td{padding:10px 12px;border-bottom:1px solid #eee;font-size:14px;text-align:left}
th{background:#0aa70a;color:white;text-transform:uppercase}
tr:hover{background:#f5f7fa}
.green{color:#0aa70a}
.red{color:#d32f2f}
button{background:#0aa70a;color:white;padding:8px 14px;border:none;border-radius:6px;cursor:pointer;font-weight:600}
button:hover{opacity:0.9}
.small{font-size:12px;color:#777}
</style>
</head>
<body>
<div class="container">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
    <h1>📊 Live NSE Stocks (<?= count($stocks) ?>)</h1>
    <button id="refresh">Refresh Now</button>
  </div>
  <table id="stockTable">
    <thead>
      <tr>
        <th>Symbol</th>
        <th>Company</th>
        <th>Price (₹)</th>
        <th>Change</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($stocks as $s): ?>
      <tr data-neo="<?= htmlspecialchars($s['neosymbol']) ?>" data-symbol="<?= htmlspecialchars($s['symbol']) ?>">
        <td><?= htmlspecialchars($s['symbol']) ?></td>
        <td><?= htmlspecialchars($s['name']) ?></td>
        <td class="price">—</td>
        <td class="change">—</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p class="small" style="margin-top:10px">Auto refresh every 3s • From <?= basename($csv_file) ?></p>
</div>

<script>
const rows = [...document.querySelectorAll("#stockTable tbody tr")];
const refreshBtn = document.getElementById('refresh');

async function updatePrices() {
  for (const row of rows.slice(0, 30)) { // avoid API rate limit
    const neo = row.dataset.neo;
    const priceCell = row.querySelector(".price");
    const changeCell = row.querySelector(".change");

    if (!neo) continue;

    try {
      const res = await fetch(`live_api.php?neosymbol=${encodeURIComponent(neo)}`);
      const data = await res.json();

      if (data.ltp) {
        const ltp = parseFloat(data.ltp).toFixed(2);
        const chg = parseFloat(data.change || 0);
        const per = parseFloat(data.per_change || 0).toFixed(2);
        priceCell.textContent = ltp;
        changeCell.textContent = `${chg >= 0 ? '▲' : '▼'} ${chg} (${per}%)`;
        changeCell.className = "change " + (chg >= 0 ? "green" : "red");
      } else {
        priceCell.textContent = "—";
        changeCell.textContent = "No Data";
        changeCell.className = "change";
      }
    } catch (e) {
      priceCell.textContent = "❌";
      changeCell.textContent = "Error";
      changeCell.className = "change";
    }
  }
}

refreshBtn.onclick = updatePrices;
updatePrices();
setInterval(updatePrices, 3000);
</script>
</body>
</html>
