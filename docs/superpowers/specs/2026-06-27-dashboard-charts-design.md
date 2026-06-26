# Dashboard Charts Design

## Scope
Add two charts to the dashboard overview: 7-day revenue and order count by status.

## UI
Place a responsive two-column chart row below the stat cards and above the existing recent orders and popular products cards. On small screens, stack charts vertically. Use existing `card` styling and Tailwind utility classes.

## Charts
Use Chart.js from CDN, as requested.

- Revenue chart: line chart, last 7 days, values from paid and completed orders only.
- Order status chart: doughnut chart, grouped by order status.

Both charts show empty states when no data exists.

## API
Extend `dashboard/api/stats.php` response with:

- `income_chart`: array of `{ date, label, total }` for the last 7 days.
- `order_status_chart`: array of `{ status, label, total }` grouped by current order statuses.

Existing response fields remain unchanged.

## Frontend
Update `dashboard/index.php` with two canvas containers. Update `dashboard/assets/js/dashboard.js` to initialize and update Chart.js charts inside `renderOverview()`.

Charts should use dark-mode compatible text/grid colors and avoid duplicate Chart instances on rerender.

## Error Handling
If stats API fails, keep current toast behavior. If chart arrays are empty, show readable empty text inside the chart card.

## Testing
Verify manually in the browser:

1. Overview loads without console errors.
2. Revenue chart displays 7 labels.
3. Order status chart displays grouped statuses.
4. Empty DB state shows fallback text.
5. Dark mode remains readable.
