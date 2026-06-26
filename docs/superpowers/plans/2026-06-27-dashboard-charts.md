# Dashboard Charts Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Chart.js revenue and order-status charts to the dashboard overview.

**Architecture:** Extend the existing `stats.php` overview API with chart-ready arrays, add two canvas cards to `dashboard/index.php`, and render Chart.js charts in `dashboard/assets/js/dashboard.js`. Keep current dashboard patterns: one overview API call, DOM rendering in `renderOverview()`, existing card styling.

**Tech Stack:** PHP PDO, vanilla JavaScript, Chart.js CDN, Tailwind utility classes, existing dashboard CSS.

---

## File Structure

- Modify `dashboard/api/stats.php`: add 7-day paid/completed revenue query and order-status grouping query; append `income_chart` and `order_status_chart` to JSON response.
- Modify `dashboard/index.php`: load Chart.js CDN and add chart card markup with two canvases and empty-state nodes.
- Modify `dashboard/assets/js/dashboard.js`: add chart instance state, chart color helpers, and chart rendering inside `renderOverview()`.

---

### Task 1: Extend Stats API Chart Data

**Files:**
- Modify: `dashboard/api/stats.php`

- [ ] **Step 1: Add 7-day revenue query**

Insert after the income query block ending at `$income = $i->fetch();`:

```php
$incomeChart = [];
for ($day = 6; $day >= 0; $day--) {
    $date = date('Y-m-d', strtotime("-$day days"));
    $incomeChart[$date] = [
        'date'  => $date,
        'label' => date('d M', strtotime($date)),
        'total' => 0,
    ];
}

$ic = $pdo->prepare(
    'SELECT DATE(created_at) AS order_date, COALESCE(SUM(total_amount), 0) AS total
     FROM orders
     WHERE status IN ("paid", "completed")
       AND DATE(created_at) BETWEEN ? AND ?
     GROUP BY DATE(created_at)'
);
$ic->execute([array_key_first($incomeChart), array_key_last($incomeChart)]);
foreach ($ic->fetchAll() as $row) {
    if (isset($incomeChart[$row['order_date']])) {
        $incomeChart[$row['order_date']]['total'] = (int) $row['total'];
    }
}
$incomeChart = array_values($incomeChart);
```

- [ ] **Step 2: Add order-status grouping query**

Insert after the recent orders block ending at `$recentOrders = $ro->fetchAll();`:

```php
$os = $pdo->query(
    'SELECT status, COUNT(*) AS total
     FROM orders
     GROUP BY status
     ORDER BY total DESC'
);
$orderStatusChart = array_map(function ($row) {
    $labels = [
        'pending'   => 'Menunggu',
        'paid'      => 'Dibayar',
        'completed' => 'Selesai',
        'cancelled' => 'Batal',
    ];

    return [
        'status' => $row['status'],
        'label'  => $labels[$row['status']] ?? $row['status'],
        'total'  => (int) $row['total'],
    ];
}, $os->fetchAll());
```

- [ ] **Step 3: Append chart fields to response**

Update the `json_success()` data array near the end:

```php
json_success('Statistik berhasil dimuat', [
    'total_products'        => (int) $products['total'],
    'active_products'       => (int) $products['available'],
    'available_products'    => (int) $products['available'],
    'out_of_stock_products' => (int) $products['out_of_stock'],
    'processing_products'   => (int) $processingProducts['processing_products'],
    'total_orders'          => (int) $orders['total'],
    'today_orders'          => (int) $orders['today_count'],
    'today_income'          => (int) $income['today_income'],
    'total_income'          => (int) $income['total_income'],
    'total_testimonials'    => (int) $testimonials['total'],
    'average_rating'        => $testimonials['avg_rating'] ? (float) $testimonials['avg_rating'] : 0.0,
    'featured_products'     => $featuredProducts,
    'recent_orders'         => $recentOrders,
    'income_chart'          => $incomeChart,
    'order_status_chart'    => $orderStatusChart,
]);
```

- [ ] **Step 4: Syntax-check PHP**

Run:

```bash
php -l dashboard/api/stats.php
```

Expected:

```text
No syntax errors detected in dashboard/api/stats.php
```

---

### Task 2: Add Overview Chart Markup

**Files:**
- Modify: `dashboard/index.php`

- [ ] **Step 1: Load Chart.js CDN**

Insert before `<?php renderFooter(); ?>`:

```php
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

- [ ] **Step 2: Add chart card row**

Insert after the stats grid line:

```php
  <div class="grid gap-6 xl:grid-cols-2">
    <div class="card min-w-0 p-5">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-black">Pendapatan 7 Hari</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">Pesanan dibayar dan selesai</p>
        </div>
      </div>
      <div class="relative h-72">
        <canvas id="incomeChart"></canvas>
        <p id="incomeChartEmpty" class="hidden absolute inset-0 grid place-items-center text-sm font-bold text-slate-400">Belum ada pendapatan</p>
      </div>
    </div>
    <div class="card min-w-0 p-5">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-black">Pesanan per Status</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">Distribusi semua pesanan</p>
        </div>
      </div>
      <div class="relative h-72">
        <canvas id="orderStatusChart"></canvas>
        <p id="orderStatusChartEmpty" class="hidden absolute inset-0 grid place-items-center text-sm font-bold text-slate-400">Belum ada pesanan</p>
      </div>
    </div>
  </div>
```

The top section should become:

```php
<section class="space-y-6" data-page="overview">
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" id="statsGrid"></div>
  <div class="grid gap-6 xl:grid-cols-2">
    <div class="card min-w-0 p-5">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-black">Pendapatan 7 Hari</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">Pesanan dibayar dan selesai</p>
        </div>
      </div>
      <div class="relative h-72">
        <canvas id="incomeChart"></canvas>
        <p id="incomeChartEmpty" class="hidden absolute inset-0 grid place-items-center text-sm font-bold text-slate-400">Belum ada pendapatan</p>
      </div>
    </div>
    <div class="card min-w-0 p-5">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-black">Pesanan per Status</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">Distribusi semua pesanan</p>
        </div>
      </div>
      <div class="relative h-72">
        <canvas id="orderStatusChart"></canvas>
        <p id="orderStatusChartEmpty" class="hidden absolute inset-0 grid place-items-center text-sm font-bold text-slate-400">Belum ada pesanan</p>
      </div>
    </div>
  </div>
  <div class="grid gap-6">
```

- [ ] **Step 3: Syntax-check PHP**

Run:

```bash
php -l dashboard/index.php
```

Expected:

```text
No syntax errors detected in dashboard/index.php
```

---

### Task 3: Render Chart.js Charts

**Files:**
- Modify: `dashboard/assets/js/dashboard.js`

- [ ] **Step 1: Add chart state and helpers**

Insert after `function badge(s) { ... }` and before `function openModal(id) ...`:

```js
let incomeChartInstance = null;
let orderStatusChartInstance = null;

function isDarkMode() {
  return document.documentElement.classList.contains('dark');
}

function chartTextColor() {
  return isDarkMode() ? '#cbd5e1' : '#475569';
}

function chartGridColor() {
  return isDarkMode() ? 'rgba(51, 65, 85, .7)' : 'rgba(226, 232, 240, .9)';
}

function destroyChart(chart) {
  if (chart) chart.destroy();
}
```

- [ ] **Step 2: Add renderOverviewCharts function**

Insert before `async function renderOverview() {`:

```js
function renderOverviewCharts(data) {
  if (typeof Chart === 'undefined') return;

  const incomeRows = data.income_chart || [];
  const statusRows = data.order_status_chart || [];
  const incomeHasData = incomeRows.some((row) => Number(row.total) > 0);
  const statusHasData = statusRows.some((row) => Number(row.total) > 0);

  $('#incomeChartEmpty')?.classList.toggle('hidden', incomeHasData);
  $('#orderStatusChartEmpty')?.classList.toggle('hidden', statusHasData);

  destroyChart(incomeChartInstance);
  destroyChart(orderStatusChartInstance);

  const incomeEl = $('#incomeChart');
  const statusEl = $('#orderStatusChart');

  if (incomeEl) {
    incomeChartInstance = new Chart(incomeEl, {
      type: 'line',
      data: {
        labels: incomeRows.map((row) => row.label),
        datasets: [{
          label: 'Pendapatan',
          data: incomeRows.map((row) => Number(row.total)),
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37, 99, 235, .12)',
          fill: true,
          tension: .35,
          pointRadius: 4,
          pointHoverRadius: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => rupiah(ctx.parsed.y),
            },
          },
        },
        scales: {
          x: {
            ticks: { color: chartTextColor(), font: { weight: '700' } },
            grid: { color: chartGridColor() },
          },
          y: {
            ticks: { color: chartTextColor(), callback: (value) => rupiah(value) },
            grid: { color: chartGridColor() },
            beginAtZero: true,
          },
        },
      },
    });
  }

  if (statusEl) {
    orderStatusChartInstance = new Chart(statusEl, {
      type: 'doughnut',
      data: {
        labels: statusRows.map((row) => row.label),
        datasets: [{
          data: statusRows.map((row) => Number(row.total)),
          backgroundColor: ['#f59e0b', '#2563eb', '#16a34a', '#dc2626', '#64748b'],
          borderColor: isDarkMode() ? '#0f172a' : '#ffffff',
          borderWidth: 3,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: chartTextColor(), font: { weight: '700' } },
          },
        },
        cutout: '62%',
      },
    });
  }
}
```

- [ ] **Step 3: Call chart renderer**

Insert this inside `renderOverview()` after stats grid rendering and before recent orders rendering:

```js
  renderOverviewCharts(d);
```

Context should become:

```js
  $('#statsGrid').innerHTML = stats.map(([label, value, icon]) =>
    `<div class="card p-5">
       <div class="mb-8 flex items-start justify-between gap-4">
         <p class="text-[13px] font-black text-slate-500 dark:text-slate-400">${label}</p>
         <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-xl text-white"><i class="${icon}"></i></div>
       </div>
       <p class="text-3xl font-black leading-none text-slate-950 dark:text-white">${value}</p>
     </div>`
  ).join('');

  renderOverviewCharts(d);

  $('#recentOrders').innerHTML = (d.recent_orders || []).map((o) =>
```

- [ ] **Step 4: Re-render charts on theme toggle**

Modify `initShell()` theme toggle listener to dispatch an event after localStorage update:

```js
  $('#themeToggle')?.addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('digistore-dashboard-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    window.dispatchEvent(new Event('dashboard-theme-change'));
  });
```

- [ ] **Step 5: Cache overview data for theme re-render**

Add this near the chart instance variables:

```js
let latestOverviewData = null;
```

Set it in `renderOverview()` after `const d = res.data;`:

```js
  latestOverviewData = d;
```

Add listener in `initShell()` after modal listeners:

```js
  window.addEventListener('dashboard-theme-change', () => {
    if (latestOverviewData && $('#incomeChart')) renderOverviewCharts(latestOverviewData);
  });
```

- [ ] **Step 6: Browser check**

Open dashboard overview while logged in. Expected:

- No console error: `Chart is not defined`.
- Line chart appears under stat cards.
- Doughnut chart appears next to line chart on desktop.
- Toggle dark mode: chart label/grid colors update.

---

### Task 4: Final Verification

**Files:**
- Verify: `dashboard/api/stats.php`
- Verify: `dashboard/index.php`
- Verify: `dashboard/assets/js/dashboard.js`

- [ ] **Step 1: PHP syntax checks**

Run:

```bash
php -l dashboard/api/stats.php && php -l dashboard/index.php
```

Expected:

```text
No syntax errors detected in dashboard/api/stats.php
No syntax errors detected in dashboard/index.php
```

- [ ] **Step 2: Manual API check**

In authenticated browser session, open:

```text
/dashboard/api/stats.php
```

Expected JSON includes:

```json
{
  "success": true,
  "data": {
    "income_chart": [
      { "date": "YYYY-MM-DD", "label": "DD Mon", "total": 0 }
    ],
    "order_status_chart": [
      { "status": "pending", "label": "Menunggu", "total": 1 }
    ]
  }
}
```

- [ ] **Step 3: Manual UI check**

Open:

```text
/dashboard/index.php
```

Expected:

- Stat cards unchanged.
- Revenue chart row visible before recent orders.
- Order status chart visible before popular products.
- Empty states visible only when all totals are zero.
- Existing recent orders and popular products still render.

- [ ] **Step 4: Commit if requested**

Only if the user explicitly asks for a commit, run:

```bash
git add dashboard/api/stats.php dashboard/index.php dashboard/assets/js/dashboard.js docs/superpowers/specs/2026-06-27-dashboard-charts-design.md docs/superpowers/plans/2026-06-27-dashboard-charts.md
git commit -m "feat: add dashboard charts"
```

---

## Self-Review

- Spec coverage: API chart arrays, Chart.js CDN, two dashboard chart cards, dark-mode readability, empty states, syntax/manual verification covered.
- Placeholder scan: no TBD/TODO/fill-in-later placeholders.
- Type consistency: `income_chart`, `order_status_chart`, `incomeChart`, `orderStatusChart`, and `latestOverviewData` names consistent across tasks.
