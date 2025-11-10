const stockInput = document.getElementById('stockInput');
const suggestionsEl = document.getElementById('suggestions');
const selectedSymbolEl = document.getElementById('selectedSymbol');
const priceEl = document.getElementById('price');
const limitPriceInput = document.getElementById('limitPrice');
const quantityInput = document.getElementById('quantity');
const buyBtn = document.getElementById('buyBtn');
const sellBtn = document.getElementById('sellBtn');
const statusEl = document.getElementById('status');

let scripList = [];
let selected = null;

// --- Load Scrip Master ---
async function loadScripMaster() {
  try {
    const res = await fetch('server.php?action=scrip_master', { cache: 'no-store' });
    const text = await res.text();

    if (text.trim().startsWith('<')) throw new Error('Server returned HTML instead of JSON.');

    scripList = JSON.parse(text);
    statusEl.textContent = `✅ Loaded ${scripList.length} stocks.`;
  } catch (err) {
    console.error('Scrip Master Load Error:', err);
    statusEl.textContent = '❌ Failed to load stock list.';
  }
}

// --- Show Suggestions ---
function showSuggestions(matches) {
  suggestionsEl.innerHTML = '';
  if (!matches.length) {
    suggestionsEl.style.display = 'none';
    return;
  }
  matches.forEach((m) => {
    const li = document.createElement('li');
    li.textContent = `${m.symbol} — ${m.name}`;
    li.addEventListener('click', () => selectStock(m));
    suggestionsEl.appendChild(li);
  });
  suggestionsEl.style.display = 'block';
}

// --- Filter Suggestions ---
stockInput.addEventListener('input', (e) => {
  const q = e.target.value.trim().toUpperCase();
  if (!q) return (suggestionsEl.style.display = 'none');

  const matches = scripList
    .filter(
      (s) =>
        s.symbol.toUpperCase().startsWith(q) ||
        s.name.toUpperCase().includes(q)
    )
    .slice(0, 20);
  showSuggestions(matches);
});

// --- Enter Key Select ---
stockInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    const first = suggestionsEl.querySelector('li');
    if (first) first.click();
  }
});

// --- Select Stock ---
function selectStock(stock) {
  selected = stock;
  stockInput.value = `${stock.symbol} - ${stock.name}`;
  selectedSymbolEl.textContent = stock.symbol;
  suggestionsEl.style.display = 'none';
  fetchQuoteAndSet(stock.symbol);
}

// --- Fetch Quote ---
async function fetchQuoteAndSet(symbol) {
  try {
    priceEl.textContent = '...';
    const res = await fetch(`server.php?action=quote&symbol=${encodeURIComponent(symbol)}`);
    const text = await res.text();

    if (text.trim().startsWith('<')) throw new Error('Server returned HTML instead of JSON for quote.');

    const data = JSON.parse(text);
    const price = data.ltp ?? data.lastPrice ?? data.price ?? null;

    if (!price) {
      priceEl.textContent = '—';
      statusEl.textContent = '⚠️ Price not found (check API limit or symbol)';
      console.warn('Quote raw:', data);
      return;
    }

    priceEl.textContent = Number(price).toFixed(2);
    limitPriceInput.value = Number(price).toFixed(2);
    statusEl.textContent = `💰 Live: ₹${Number(price).toFixed(2)} (${symbol})`;
  } catch (err) {
    console.error('Quote Fetch Error:', err);
    priceEl.textContent = '—';
    statusEl.textContent = '❌ Failed to fetch quote.';
  }
}

// --- Auto Refresh Every 10s (if a stock is selected) ---
setInterval(() => {
  if (selected) fetchQuoteAndSet(selected.symbol);
}, 10000);

// --- Place Order ---
async function placeOrder(side) {
  if (!selected) return alert('Please select a stock first.');

  const payload = {
    trading_symbol: selected.symbol,
    exchange_segment: 'NSE_CM',
    transaction_type: side,
    product: document.getElementById('product').value,
    order_type: document.getElementById('orderType').value,
    validity: document.getElementById('validity').value,
    price: Number(limitPriceInput.value || 0),
    quantity: Number(quantityInput.value || 0),
  };

  if (payload.quantity <= 0) return alert('Enter valid quantity');
  if (payload.order_type === 'LIMIT' && payload.price <= 0)
    return alert('Enter valid price for LIMIT order');

  statusEl.textContent = '🚀 Placing order...';

  try {
    const res = await fetch('server.php?action=place_order', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const result = await res.json();
    statusEl.textContent = result.message || '✅ Order placed!';
  } catch (err) {
    console.error('Order Error:', err);
    statusEl.textContent = '❌ Order failed.';
  }
}

buyBtn.addEventListener('click', () => placeOrder('BUY'));
sellBtn.addEventListener('click', () => placeOrder('SELL'));

// --- Init ---
loadScripMaster();
