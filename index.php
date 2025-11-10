<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: login.php");
  exit;
}

$config = require __DIR__ . '/secure.php';

// Validate
if (empty($_SESSION['trade_token']) || empty($_SESSION['trade_sid']) || empty($_SESSION['trade_base'])) {
  header("Location: validate.php");
  exit;
}

$BASE = $_SESSION['trade_base'];
$AUTH = $_SESSION['trade_token'];
$SID  = $_SESSION['trade_sid'];
$ACCESS = $config['access_token'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kotak Trade Dashboard</title>
  <style>
    :root {
      --bg: #f5f6fa;
      --card: #fff;
      --border: #e5e7eb;
      --muted: #6b7280;
      --green: #0aa70a;
      --red: #d32f2f;
      --text: #111827;
    }

    body {
      background: var(--bg);
      font-family: "Inter", sans-serif;
      margin: 0;
      padding: 20px;
      color: var(--text);
    }

    .container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      max-width: 1300px;
      margin: auto;
    }

    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 14px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
      padding: 18px 20px;
    }

    h2 {
      margin: 0 0 12px 0;
      font-size: 16px;
      font-weight: 600;
    }

    .muted {
      color: var(--muted);
      font-size: 12px;
    }

    .green {
      color: var(--green);
    }

    .red {
      color: var(--red);
    }

    .index-wrap {
      display: flex;
      justify-content: space-between;
      gap: 10px;
    }

    .index-box {
      flex: 1;
      border: 1px dashed var(--border);
      border-radius: 12px;
      padding: 10px;
      text-align: center;
    }

    .index-name {
      font-weight: 600;
      font-size: 13px;
    }

    .index-value {
      font-weight: 800;
      font-size: 20px;
      margin: 4px 0;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-size: 13px;
    }

    input,
    select {
      width: 100%;
      padding: 10px;
      border: 1px solid var(--border);
      border-radius: 10px;
      margin-bottom: 10px;
      font-size: 14px;
    }

    .btn {
      display: inline-block;
      padding: 12px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 700;
      color: #fff;
      width: 48%;
      position: relative;
      overflow: hidden;
      transition: 0.3s ease;
    }

    .btn::after {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.35) 10%, transparent 60%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .btn:hover::after {
      opacity: 1;
    }

    .buy {
      background: linear-gradient(90deg, #0aa70a, #17c317);
      box-shadow: 0 0 12px rgba(10, 167, 10, 0.35);
    }

    .sell {
      background: linear-gradient(90deg, #d32f2f, #f44336);
      box-shadow: 0 0 12px rgba(244, 67, 54, 0.35);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }

    th,
    td {
      text-align: left;
      padding: 8px;
      border-bottom: 1px solid var(--border);
      font-size: 13px;
      vertical-align: top;
    }

    th {
      background: #f8fafc;
      font-weight: 600;
    }

    .total {
      font-weight: 700;
    }

    .orders-container {
      max-height: 530px;
      overflow-y: auto;
    }
    .hold-container {
      max-height: 630px;
      overflow-y: auto;
    }


    .orders-container::-webkit-scrollbar {
      width: 6px;
    }

    .orders-container::-webkit-scrollbar-thumb {
      background: #d1d5db;
      border-radius: 4px;
    }

    .rej-reason {
      display: block;
      color: #9b1c1c;
      font-size: 11px;
      margin-top: 2px;
    }

    .header-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #fff;
      padding: 10px 20px;
      border-radius: 10px;
      margin-bottom: 20px !important;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      max-width: 1260px;
      margin: auto;
    }

    .logout {
      background: #d32f2f;
      color: #fff;
      padding: 8px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
    }

    #holdRefreshBtn {
      position: absolute;
      top: 5px;
      right: 20px;
      background: linear-gradient(135deg, #2563eb, #1e40af);
      color: #fff;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      padding: 5px 12px;
      cursor: pointer;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
      transition: 0.3s ease;
      margin-top: 10px;
    }

    #holdRefreshBtn:hover {
      background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
      transform: scale(1.05);
    }

    #holdRefreshBtn:active {
      transform: scale(0.96);
    }


    .cancel-btn {
      background: linear-gradient(135deg, #ef4444, #b91c1c);
      border: none;
      color: white;
      padding: 4px 10px;
      font-size: 12px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      box-shadow: 0 0 6px rgba(239, 68, 68, 0.5);
      transition: 0.2s;
    }

    .cancel-btn:hover {
      background: linear-gradient(135deg, #dc2626, #7f1d1d);
      transform: scale(1.05);
    }

    .cancel-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    #refreshOrdersBtn {
      float: right;
      background: linear-gradient(90deg, #2563eb, #1d4ed8);
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 4px 10px;
      font-size: 13px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    #refreshOrdersBtn:hover {
      background: linear-gradient(90deg, #1e40af, #1d4ed8);
      transform: scale(1.05);
    }

    .refresh-btn {
      background: linear-gradient(90deg, #2563eb, #1d4ed8);
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 5px 10px;
      font-size: 13px;
      cursor: pointer;
      font-weight: 600;
      box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
      transition: 0.3s;
    }

    .refresh-btn:hover {
      background: linear-gradient(90deg, #1e40af, #1d4ed8);
      transform: scale(1.05);
      box-shadow: 0 3px 8px rgba(37, 99, 235, 0.4);
    }

    .refresh-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  </style>
</head>

<body>
  <div class="header-bar">
    <h1 style="font-size:18px;">Kotak Trade Dashboard</h1>
    <a href="logout.php" class="logout">Logout</a>
  </div>

  <div class="container">

    <!-- Left Side -->
    <div>

      <!-- Market Indices -->
      <div class="card">
        <h2 style="display:flex;justify-content:space-between;align-items:center;">
          <span>Market Indices</span>
          <button id="refreshIndicesBtn" class="refresh-btn" title="Refresh Market Data">🔄 Refresh</button>
        </h2>
        <div class="index-wrap">
          <div class="index-box" id="nifty50">
            <div class="index-name">Nifty 50</div>
            <div class="index-value" id="n50val">—</div>
            <div class="muted" id="n50chg">—</div>
          </div>
          <div class="index-box" id="banknifty">
            <div class="index-name">Nifty Bank</div>
            <div class="index-value" id="bnval">—</div>
            <div class="muted" id="bnchg">—</div>
          </div>
        </div>
      </div>


      <!-- Place Order -->
      <div class="card" style="margin-top:16px">
        <h2>Place Order</h2>
        <label>Trading Symbol (CSV)</label>
        <input id="stockInput" placeholder="e.g. INFY-EQ, TATASTEEL-EQ" autocomplete="off" />
        <div id="suggestions" style="border:1px solid var(--border);display:none;max-height:200px;overflow:auto;border-radius:8px;"></div>

        <div class="muted" style="margin-bottom:8px;">
          Selected: <b id="selectedSymbol">—</b> • Price ₹<b id="price">—</b>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div>
            <label>Product</label>
            <select id="product">
              <option>CNC</option>
              <option>MTF</option>
            </select>
          </div>
          <div>
            <label>Order Type</label>
            <select id="orderType">
              <option value="LIMIT">LIMIT</option>
              <option value="MARKET">MARKET</option>
            </select>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div>
            <label>Price (₹)</label>
            <input id="limitPrice" type="number" step="0.01" placeholder="0.00">
          </div>
          <div>
            <label>Quantity</label>
            <input id="quantity" type="number" value="1" min="1">
          </div>
        </div>

        <div class="muted" style="margin:5px 0;">Total: ₹<span class="total" id="totalVal">0.00</span></div>

        <div style="display:flex;justify-content:space-between;margin-top:10px;">
          <button id="buyBtn" class="btn buy">BUY</button>
          <button id="sellBtn" class="btn sell">SELL</button>
        </div>
        <div id="status" class="muted" style="margin-top:10px;min-height:18px"></div>
      </div>

      <!-- Orders -->
      <!-- Orders -->
      <div class="card" style="margin-top:16px; position: relative;">
        <h2>
          Orders
          <button id="refreshOrdersBtn" title="Refresh Orders">🔄 Refresh</button>
        </h2>
        <div class="orders-container">
          <table id="ordersTbl">
            <thead>
              <tr>
                <th>Time</th>
                <th>Symbol</th>
                <th>Qty</th>
                <th>Side</th>
                <th>Price</th>
                <th>Avg Prc</th>
                <th>Status</th>
                <th>Cancel</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="8">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Right Side -->
    <div>
      <!-- Funds -->
      <!-- Funds -->
      <!-- Funds -->
      <!-- Funds -->
      <div class="card" style="position: relative;">
        <h2 style="display:flex;justify-content:space-between;align-items:center;">
          <span>Funds</span>
          <button id="refreshFundsBtn" class="refresh-btn" title="Refresh funds">🔄 Refresh</button>
        </h2>

        <div class="funds-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">

          <div>
            <div class="muted">Balance</div>
            <div style="font-size:20px;font-weight:700;color:#111" id="avlMrgn">—</div>
          </div>

          <div>
            <div class="muted">Available Cash</div>
            <div style="font-size:16px;font-weight:600;color:#555"><span id="avlCash">—</span></div>
          </div>

          <div>
            <div class="muted">Reqd Margin</div>
            <div style="font-size:16px;font-weight:600;color:#555">₹<span id="reqMrgn">—</span></div>
          </div>

          <div>
            <div class="muted">Used Margin</div>
            <div style="font-size:16px;font-weight:600;color:#555">₹<span id="usedMrgn">—</span></div>
          </div>

          <div>
            <div class="muted">Total Margin Used</div>
            <div style="font-size:16px;font-weight:600;color:#555">₹<span id="totMrgnUsd">—</span></div>
          </div>

          <div>
            <div class="muted">Order Margin</div>
            <div style="font-size:16px;font-weight:600;color:#555">₹<span id="ordMrgn">—</span></div>
          </div>

          <div>
            <div class="muted">Insufficient Fund</div>
            <div style="font-size:16px;font-weight:600;color:#d32f2f">₹<span id="insufFund">—</span></div>
          </div>

        </div>

        <div style="margin-top:10px;border-top:1px solid #eee;padding-top:6px;">
          <span class="muted">RMS Validation:</span>
          <span id="rmsVldtd" style="font-weight:600;color:#111">—</span>
          <span class="muted" style="float:right;">⏱ <span id="fundTime">—</span></span>
        </div>
      </div>


      <!-- Holdings -->

      <!-- Holdings -->
      <div class="card hold-container" style="margin-top:16px">
        <h2 style="display:flex;justify-content:space-between;align-items:center;">
          <span>Holdings</span>
          <button id="refreshHoldingsBtn" class="refresh-btn">🔄 Refresh</button>
        </h2>
        <table id="holdTbl">
          <thead>
            <tr>
              <th>Symbol</th>
              <th>Qty</th>
              <th>Avg</th>
              <th>LTP</th>
              <th>P&L (₹ / %)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="5">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>


    </div>

  </div>

  <script>
    // ===== Market Indices (Manual Refresh Only) =====
    async function refreshIndices() {
      const btn = document.getElementById("refreshIndicesBtn");
      btn.disabled = true;
      btn.textContent = "⏳ Refreshing...";

      try {
        const r = await fetch("fetch_market.php", {
          cache: "no-store"
        });
        const arr = await r.json();

        const n50 = arr.find(x => (x.exchange_token || "").includes("Nifty 50"));
        const bn = arr.find(x => (x.exchange_token || "").includes("Nifty Bank"));

        if (n50) {
          document.getElementById("n50val").textContent = Number(n50.ltp).toFixed(2);
          const t = `${Number(n50.change) >= 0 ? "▲" : "▼"} ${Number(n50.change).toFixed(2)} (${Number(n50.per_change).toFixed(2)}%)`;
          document.getElementById("n50chg").textContent = t;
          document.getElementById("nifty50").className = "index-box " + (Number(n50.change) >= 0 ? "green" : "red");
        }

        if (bn) {
          document.getElementById("bnval").textContent = Number(bn.ltp).toFixed(2);
          const t = `${Number(bn.change) >= 0 ? "▲" : "▼"} ${Number(bn.change).toFixed(2)} (${Number(bn.per_change).toFixed(2)}%)`;
          document.getElementById("bnchg").textContent = t;
          document.getElementById("banknifty").className = "index-box " + (Number(bn.change) >= 0 ? "green" : "red");
        }

        btn.textContent = "✅ Updated";
        setTimeout(() => {
          btn.textContent = "🔄 Refresh";
          btn.disabled = false;
        }, 1500);
      } catch (err) {
        console.error("❌ Market fetch error:", err);
        btn.textContent = "❌ Error";
        setTimeout(() => {
          btn.textContent = "🔄 Refresh";
          btn.disabled = false;
        }, 2000);
      }
    }

    // 🟦 Manual trigger
    document.getElementById("refreshIndicesBtn").addEventListener("click", refreshIndices);

    // 🔰 Load once on page load
    refreshIndices();


    // ===== Suggestions =====
    const input = document.getElementById('stockInput');
    const sug = document.getElementById('suggestions');
    const selectedSymbol = document.getElementById('selectedSymbol');
    const priceEl = document.getElementById('price');
    const qtyEl = document.getElementById('quantity');
    const priceIn = document.getElementById('limitPrice');
    const totalEl = document.getElementById('totalVal');
    let currentSymbol = '';

    function calcTotal() {
      const p = parseFloat(priceIn.value || priceEl.textContent || 0),
        q = parseInt(qtyEl.value || 1);
      totalEl.textContent = (p * q).toFixed(2);
    }
    qtyEl.oninput = calcTotal;
    priceIn.oninput = calcTotal;

    input.addEventListener('input', async e => {
      const q = e.target.value.trim();
      if (!q) {
        sug.style.display = 'none';
        return;
      }
      const r = await fetch('fetch_suggestions.php?q=' + encodeURIComponent(q));
      const list = await r.json();
      sug.innerHTML = list.map(s => `<div data-sym="${s.symbol}" style="padding:8px;cursor:pointer;">${s.symbol} • ${s.name||''}</div>`).join('');
      sug.style.display = list.length ? 'block' : 'none';
    });

    sug.addEventListener('click', async e => {
      const div = e.target.closest('div[data-sym]');
      if (!div) return;
      currentSymbol = div.dataset.sym;
      input.value = currentSymbol;
      selectedSymbol.textContent = currentSymbol;
      sug.style.display = 'none';
      const res = await fetch('fetch_price.php?symbol=' + encodeURIComponent(currentSymbol));
      const data = await res.json();
      if (data && data.ltp) {
        priceEl.textContent = Number(data.ltp).toFixed(2);
        priceIn.value = Number(data.ltp).toFixed(2);
        calcTotal();
      }
    });

    // ===== Place Order =====

    document.addEventListener("DOMContentLoaded", function() {
      const orderTypeSelect = document.getElementById("orderType");
      const priceInput = document.getElementById("limitPrice");
      const buyBtn = document.getElementById("buyBtn");
      const sellBtn = document.getElementById("sellBtn");
      const statusDiv = document.getElementById("status");

      // ✅ Default order type = LIMIT
      orderTypeSelect.value = "LIMIT";
      priceInput.disabled = false;

      // 🧠 Auto-disable price if order type = MARKET
      orderTypeSelect.addEventListener("change", function() {
        if (this.value === "MARKET") {
          priceInput.value = "";
          priceInput.disabled = true;
          priceInput.placeholder = "Auto @ Market Price";
        } else {
          priceInput.disabled = false;
          priceInput.placeholder = "Enter Limit Price";
        }
      });

      // 🧾 Function to place order
      async function placeOrder(side) {
        const symbol = document.getElementById("stockInput").value.trim().toUpperCase();
        const product = document.getElementById("product").value;
        const orderType = orderTypeSelect.value;
        const priceValue = parseFloat(priceInput.value || 0);
        const qty = parseInt(document.getElementById("quantity").value || 1);
        const validity = document.getElementById("validity").value;

        // 🛑 Validation
        if (!symbol) {
          statusDiv.textContent = "⚠️ Please enter a trading symbol.";
          return;
        }

        if (orderType === "LIMIT" && (!priceValue || priceValue <= 0)) {
          statusDiv.textContent = "⚠️ Please enter a valid Limit Price.";
          priceInput.focus();
          return;
        }

        // ✅ Build correct price for payload
        const orderPrice = orderType === "MARKET" ? "0" : priceValue.toString();

        const payload = new URLSearchParams({
          jData: JSON.stringify({
            am: "NO",
            dq: "0",
            es: "nse_cm",
            mp: "0",
            pc: product,
            pf: "N",
            pr: orderPrice,
            pt: orderType,
            qt: qty.toString(),
            rt: validity,
            tp: "0",
            ts: symbol,
            tt: side
          })
        });

        try {
          statusDiv.innerHTML = "⏳ Placing order...";
          const res = await fetch("place_order.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: payload
          });

          const data = await res.json();
          console.log("📦 FULL RESPONSE FROM API:", data);

          // 🧩 Extract useful details from all formats
          const o = data.raw_response?.data?.[0] || data.data?.[0] || data || {};

          if (o.stat === "Ok" && o.nOrdNo) {
            statusDiv.innerHTML = `
          ✅ <b>Order Placed Successfully</b><br>
          <b>Symbol:</b> ${o.trdSym || symbol}<br>
          <b>Qty:</b> ${o.qty || qty}<br>
          <b>Type:</b> ${o.pt || orderType} (${o.pc || product})<br>
          <b>Price:</b> ₹${o.prc || orderPrice}<br>
          <b>Status:</b> ${o.ordSt || "Open"}<br>
          <b>Order No:</b> ${o.nOrdNo}<br>
        `;
          } else if (o.ordSt === "rejected" || o.stat === "Not_Ok") {
            statusDiv.innerHTML = `
          ❌ <b>Order Rejected</b><br>
          <b>Symbol:</b> ${o.trdSym || symbol}<br>
          <b>Status:</b> ${o.ordSt || o.stat}<br>
          <b>Reason:</b> ${o.rejRsn || o.emsg || "Unknown reason"}<br>
        `;
          } else {
            statusDiv.innerHTML = `
          ⚠️ <b>Unexpected Response</b><br>
          <pre style="text-align:left;background:#f9f9f9;padding:8px;border-radius:8px;">
          ${JSON.stringify(data, null, 2)}
          </pre>
        `;
          }
        } catch (err) {
          statusDiv.textContent = "❌ Error placing order: " + err.message;
        }
      }

      // 🟢 Buy / Sell handlers
      buyBtn.addEventListener("click", () => placeOrder("B"));
      sellBtn.addEventListener("click", () => placeOrder("S"));
    });

    async function place(side) {
      const ts = (currentSymbol || input.value || '').trim().toUpperCase();
      if (!ts) return document.getElementById('status').textContent = 'Enter symbol';
      const payload = {
        es: 'nse_cm',
        pc: document.getElementById('product').value,
        pt: document.getElementById('orderType').value,
        qt: document.getElementById('quantity').value,
        rt: 'DAY',
        ts: ts,
        tt: side === 'B' ? 'B' : 'S',
        pr: (document.getElementById('orderType').value === 'LIMIT') ? (document.getElementById('limitPrice').value || '0') : '0'
      };
      document.getElementById('status').textContent = 'Placing...';
      const r = await fetch('place_order.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const d = await r.json();
      console.log("📦 FULL ORDER RESPONSE:", d);

      if (d.status && d.status.toLowerCase() === "rejected") {
        document.getElementById('status').innerHTML =
          `❌ <b>Order Rejected</b><br>
     <b>Reason:</b> ${d.reason || "Unknown reason"}<br>
     <b>Symbol:</b> ${d.symbol || "-"} • <b>Qty:</b> ${d.qty || "-"}<br>`;
      } else if (d.http_code === 200 && (d.status || '').toLowerCase() === "open") {
        document.getElementById('status').innerHTML =
          `✅ <b>Order Placed Successfully</b><br>
     <b>Order No:</b> ${d.order_no || "-"}<br>
     <b>Symbol:</b> ${d.symbol || "-"} • <b>Qty:</b> ${d.qty || "-"}<br>`;
      } else {
        // Fallback detailed view for debugging
        document.getElementById('status').innerHTML =
          `⚠️ <b>Response</b><pre>${JSON.stringify(d, null, 2)}</pre>`;
      }

    }
    document.getElementById('buyBtn').onclick = () => place('B');
    document.getElementById('sellBtn').onclick = () => place('S');

    // ===== Orders =====
    // ===== Orders =====
    async function loadOrders() {
      try {
        const r = await fetch("fetch_orders.php", {
          cache: "no-store"
        });
        const d = await r.json();
        const tb = document.querySelector("#ordersTbl tbody");
        tb.innerHTML = "";

        if (d.stat !== "Ok" || !Array.isArray(d.data)) {
          tb.innerHTML = "<tr><td colspan='8'>No orders</td></tr>";
          return;
        }

        // 🧠 Sort latest first
        const sorted = d.data
          .filter(o => o.ordEntTm)
          .sort((a, b) => {
            const ta = new Date(a.ordEntTm);
            const tb2 = new Date(b.ordEntTm);
            return tb2 - ta;
          });

        // 🧾 Display top 15
        sorted.slice(0, 15).forEach(o => {
          const isOpen = (o.ordSt || "").toLowerCase() === "open";
          const rej = o.rejRsn ?
            `<span class='rej-reason' style="color:#d32f2f; font-size:12px; display:block;">${o.rejRsn}</span>` :
            "";
          const cancelBtn = isOpen ?
            `<button class="cancel-btn" data-order="${o.nOrdNo}" title="Cancel Order">Cancel</button>` :
            "-";

          tb.insertAdjacentHTML(
            "beforeend",
            `<tr>
          <td>${o.ordEntTm || "-"}</td>
          <td>${o.trdSym || "-"}</td>
          <td>${o.qty || "-"}</td>
          <td>${o.trnsTp || "-"}</td>
          <td>${o.prc || "-"}</td>
          <td>${o.avgPrc || "-"}</td>
          <td>${o.ordSt || o.stat || "-"}${rej}</td>
          <td>${cancelBtn}</td>
        </tr>`
          );
        });

        // 🔴 Add cancel button listeners
        document.querySelectorAll(".cancel-btn").forEach(btn => {
          btn.addEventListener("click", async e => {
            const orderNo = e.target.dataset.order;
            if (!confirm(`❌ Cancel Order #${orderNo}?`)) return;

            e.target.disabled = true;
            e.target.textContent = "Cancelling...";

            try {
              const res = await fetch("cancel_order.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json"
                },
                body: JSON.stringify({
                  on: orderNo
                })
              });

              const result = await res.json();
              if (result.stat === "Ok") {
                e.target.textContent = "✅ Cancelled";
                e.target.style.background = "linear-gradient(90deg,#16a34a,#15803d)";
                setTimeout(loadOrders, 2000);
              } else {
                e.target.textContent = "❌ Failed";
                alert("Cancel failed: " + (result.emsg || JSON.stringify(result)));
                e.target.disabled = false;
              }
            } catch (err) {
              alert("Error: " + err.message);
              e.target.disabled = false;
            }
          });
        });
      } catch (err) {
        console.error("❌ Error loading orders:", err);
      }
    }

    // 🔁 Manual + Auto Refresh
    document.getElementById("refreshOrdersBtn").addEventListener("click", loadOrders);
    setInterval(loadOrders, 10000);
    loadOrders();



    // ===== Funds (Manual Refresh) =====
    async function loadFunds() {
      const btn = document.getElementById("refreshFundsBtn");
      if (btn) {
        btn.disabled = true;
        btn.textContent = "⏳ Refreshing...";
      }

      try {
        const res = await fetch("fetch_funds.php", {
          cache: "no-store"
        });
        const d = await res.json();

        if (d && d.stat === "Ok") {
          const avlMrgn = Number(d.avlMrgn || 0);
          const usedMrgn = Number(d.mrgnUsd || 0);
          const balance = avlMrgn - usedMrgn;

          document.getElementById("avlCash").textContent = "₹" + Number(d.avlCash || 0).toFixed(2);
          document.getElementById("reqMrgn").textContent = Number(d.reqdMrgn || 0).toFixed(2);
          document.getElementById("usedMrgn").textContent = usedMrgn.toFixed(2);
          document.getElementById("totMrgnUsd").textContent = Number(d.totMrgnUsd || 0).toFixed(2);
          document.getElementById("ordMrgn").textContent = Number(d.ordMrgn || 0).toFixed(2);
          document.getElementById("insufFund").textContent = Number(d.insufFund || 0).toFixed(2);
          document.getElementById("avlMrgn").textContent = "₹" + balance.toFixed(2);
          document.getElementById("rmsVldtd").textContent = d.rmsVldtd || "—";
          document.getElementById("rmsVldtd").style.color = d.rmsVldtd === "OK" ? "#0aa70a" : "#d32f2f";
          document.getElementById("fundTime").textContent = d.timestamp || new Date().toLocaleTimeString();
        } else {
          document.getElementById("avlCash").textContent = "—";
        }

        if (btn) {
          btn.textContent = "✅ Updated";
          setTimeout(() => {
            btn.textContent = "🔄 Refresh";
            btn.disabled = false;
          }, 1500);
        }
      } catch (err) {
        console.error("Funds Fetch Error:", err);
        if (btn) {
          btn.textContent = "❌ Error";
          setTimeout(() => {
            btn.textContent = "🔄 Refresh";
            btn.disabled = false;
          }, 2000);
        }
      }
    }

    // 🟢 Manual trigger
    document.getElementById("refreshFundsBtn").addEventListener("click", loadFunds);

    // 🔰 Load once on page load
    loadFunds();


    // ===== Holdings =====
    async function loadHoldings() {
      try {
        const res = await fetch("fetch_holdings.php", {
          cache: "no-store"
        });
        const data = await res.json();
        const tb = document.querySelector("#holdTbl tbody");
        tb.innerHTML = "";

        const holdings = Array.isArray(data.data) ? data.data : [];

        if (holdings.length === 0) {
          tb.innerHTML = "<tr><td colspan='5'>No holdings found</td></tr>";
          return;
        }

        // 🔠 Sort alphabetically by symbol (case-insensitive)
        holdings.sort((a, b) => {
          const s1 = (a.symbol || "").toUpperCase();
          const s2 = (b.symbol || "").toUpperCase();
          return s1.localeCompare(s2);
        });

        // 🧾 Render rows
        holdings.forEach(h => {
          const avg = Number(h.averagePrice || 0);
          const ltp = Number(h.closingPrice || 0);
          const qty = Number(h.sellableQuantity || 0);
          const gain = (ltp - avg) * qty;
          const gainPct = avg ? ((ltp - avg) / avg) * 100 : 0;
          const gainColor = gain >= 0 ? "green" : "red";
          const logo = h.logoUrl ?
            `<img src="${h.logoUrl}" width="20" style="vertical-align:middle;border-radius:4px;margin-right:6px;">` :
            "";

          tb.insertAdjacentHTML(
            "beforeend",
            `
        <tr>
          <td>
            ${logo}<b>${h.symbol || "-"}</b><br>
            <span class="muted" style="font-size:11px;">${h.instrumentName || ""}</span>
          </td>
          <td>${qty}</td>
          <td>${avg.toFixed(2)}</td>
          <td>${ltp.toFixed(2)}</td>
          <td class="${gainColor}">
            ${gain >= 0 ? "▲" : "▼"} ₹${gain.toFixed(2)} (${gainPct.toFixed(2)}%)
          </td>
        </tr>
      `
          );
        });
      } catch (err) {
        console.error("❌ Error loading holdings:", err);
        document.querySelector("#holdTbl tbody").innerHTML =
          "<tr><td colspan='5'>⚠️ Error fetching holdings</td></tr>";
      }
    }

    // 🟢 Manual + Auto Refresh (Optional)
    document.getElementById("refreshHoldingsBtn")?.addEventListener("click", loadHoldings);
    setInterval(loadHoldings, 10000);
    loadHoldings();
  </script>


</body>

</html>