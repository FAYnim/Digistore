# Order Status PRD Completion Design

**Goal:** Complete the existing order status feature so it matches `PRD-page-check-order.md` MVP acceptance criteria without broad redesign.

## Scope

Complete the public order status flow:

- User opens `/order-status.php` with or without `code`.
- User can submit an order code and see `/order-status.php?code=...`.
- Page fetches `/api/orders.php?code=...`.
- Page shows order detail, status-specific instructions, action buttons, items, payment data, dates, and delivery note when completed.
- API validates public input and returns only safe public fields.

Out of scope:

- Login.
- Upload proof of payment.
- Payment gateway automation.
- Invoice PDF.
- Timeline or progress stepper.

## Architecture

Keep PHP pages as thin HTML shells and move page behavior into `assets/js/order-status.js`. Keep API data assembly in `api/orders.php` using prepared statements and public-safe response data.

## Files

- Modify `api/orders.php`
  - Validate `code`: non-empty, max 50 chars, `^[A-Z0-9-]+$` case-insensitive by normalizing to uppercase.
  - Select public customer fields: `customer_name`, `customer_email`, `customer_phone`.
  - Keep prepared statements.
  - Keep SQL errors hidden.
  - Do not expose internal `id`.

- Modify `order-status.php`
  - Keep navbar, form, message container, result container.
  - Add loading-friendly initial structure.
  - Remove inline status rendering JavaScript.
  - Load `assets/js/order-status.js`.

- Create `assets/js/order-status.js`
  - Read `code` URL parameter.
  - Show empty state when no code exists.
  - Validate empty submit client-side.
  - Redirect/replace URL to include `code`.
  - Fetch order data from API.
  - Render loading, error, and order detail states.
  - Escape all dynamic HTML.
  - Render status badge mappings:
    - `pending` → Menunggu Pembayaran.
    - `paid` → Pembayaran Diterima.
    - `completed` → Selesai.
    - `cancelled` → Dibatalkan.
  - Render instructions and actions by status.
  - Render delivery note only when status is `completed` and note is non-empty.

## UI/Data Requirements

Order detail must display:

- Order code.
- Status badge.
- Customer name.
- Customer email and phone when present.
- Product item list.
- Quantity, price, subtotal.
- Total amount.
- Payment method.
- Created date.
- Payment deadline.
- Delivery note for completed orders.

Actions:

- Pending:
  - Lanjut ke Pembayaran → `payment.php?code={order_code}`.
  - Konfirmasi WhatsApp → `https://wa.me/{admin_whatsapp}?text={encoded_message}`.
  - Kembali ke Katalog → `index.php#produk`.
- Paid, completed, cancelled:
  - Hubungi Admin → WhatsApp link when admin number exists.
  - Kembali ke Katalog → `index.php#produk`.

## Error Handling

- No code: show form and empty-state copy.
- Empty submit: show `Kode order wajib diisi.`.
- Invalid API response/not found: show message from API or `Order tidak ditemukan.`.
- Network/API failure: show `Gagal memuat pesanan.`.
- Completed without delivery note: show `Pesanan selesai. Hubungi admin jika produk belum diterima.`.

## Testing

Manual browser checks:

1. `/order-status.php` shows input and empty state.
2. Empty submit shows validation message.
3. Invalid format code returns safe error.
4. Unknown code shows not found state.
5. Pending order shows payment and WhatsApp buttons.
6. Paid order shows processing instruction.
7. Completed order with delivery note shows note.
8. Completed order without delivery note shows fallback text.
9. Cancelled order shows cancelled instruction.
10. `payment.php?code=...` contains link back to status page.

CLI checks:

- Run PHP syntax checks on modified PHP files.
- Run any available project lint/typecheck command if present.
