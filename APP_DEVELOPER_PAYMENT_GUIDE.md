# Balance App — Payment Integration Guide for App Developer

**Production Base URL:** `https://backend.balancekw.app/api`

> All requests:
> `Content-Type: application/json`
> `Accept: application/json`

---

## Overview — Three Payment Methods

| Method | `payment_method` value | How it works |
|---|---|---|
| Cash on delivery | `cash` | No card needed. Subscription created immediately with `pending` payment. |
| Debit / Credit Card | `debit_card` or `credit_card` | App collects card details → sends to backend → Hesabe charges card instantly → subscription created on success. |
| KNET | `knet` | Backend gives app a payment URL → app opens it in WebView → user pays on Hesabe's page → backend creates subscription on callback. |

---

## IMPORTANT — Run on server before testing KNET

The backend developer must run this on the server once:

```bash
php artisan migrate
```

And add these to production `.env`:
```env
HESABE_BASE_URL=https://api.hesabe.com
HESABE_PAYMENT_RETURN_URL=https://backend.balancekw.app/api/v1/payment/callback
HESABE_PAYMENT_FAILURE_URL=https://backend.balancekw.app/api/v1/payment/callback/failure
```

---

## Payment Method 1 — Cash on Delivery

### Request

**POST** `/api/v1/payment/checkout`

```json
{
  "payment_method": "cash",
  "user_id": 5,
  "subcrption_plans_id": 2,
  "area_id": 3,
  "start_date": "2026-07-01",
  "selected_days": ["sunday", "monday", "tuesday"],
  "address": {
    "first_name": "Ahmed",
    "last_name": "Al-Rashidi",
    "block_number": "5",
    "street": "Al Nuzha St",
    "house_building": "12",
    "floor_apartment": "3",
    "phone_number": "+96599887766",
    "category": "home",
    "preferred_delivery_slot": "four_pm_to_eight_pm",
    "delivery_notes": "Ring the bell"
  }
}
```

### Success Response `201`

```json
{
  "success": true,
  "message": "Order placed successfully. Cash will be collected on delivery.",
  "data": {
    "payment": {
      "method": "cash",
      "reference": "CASH-1751234567-AB12CD",
      "amount": 150.000,
      "currency": "KWD",
      "status": "pending"
    },
    "subscription": { ... }
  }
}
```

### App Flow

```
User taps "Cash on Delivery" → taps Confirm
→ POST /payment/checkout  (payment_method: cash)
→ 201 success
→ Show success screen
```

---

## Payment Method 2 — Debit / Credit Card (Direct)

The app collects card details and sends them to the backend. Hesabe charges the card immediately. No WebView needed.

### Request

**POST** `/api/v1/payment/checkout`

```json
{
  "payment_method": "debit_card",
  "user_id": 5,
  "subcrption_plans_id": 2,
  "area_id": 3,
  "start_date": "2026-07-01",
  "selected_days": ["sunday", "monday", "tuesday"],
  "amount": 150.000,
  "currency": "KWD",

  "card_holder_name": "Ahmed Al-Rashidi",
  "card_number": "4111111111111111",
  "card_expiry_month": "12",
  "card_expiry_year": "2028",
  "card_cvv": "123",
  "save_card": true,

  "address": {
    "first_name": "Ahmed",
    "last_name": "Al-Rashidi",
    "block_number": "5",
    "street": "Al Nuzha St",
    "house_building": "12",
    "floor_apartment": "3",
    "phone_number": "+96599887766",
    "category": "home",
    "preferred_delivery_slot": "four_pm_to_eight_pm",
    "delivery_notes": ""
  }
}
```

> Use `"payment_method": "credit_card"` for credit card. Everything else is the same.

### Success Response `201`

```json
{
  "success": true,
  "message": "Payment successful and subscription created.",
  "data": {
    "payment": {
      "method": "debit_card",
      "reference": "TXN-HESABE-XYZ123",
      "gateway": "hesabe",
      "amount": 150.000,
      "currency": "KWD",
      "card_brand": "visa",
      "card_last_four": "1111",
      "status": "paid"
    },
    "subscription": { ... }
  }
}
```

### Failure Response `502`

```json
{
  "success": false,
  "message": "Payment was not successful. Please try again."
}
```

### App Flow

```
User enters card details → taps Pay
→ POST /payment/checkout  (payment_method: debit_card)
→ 201 → show success screen
→ 502 → show error message from response
```

---

## Payment Method 3 — KNET (Hosted WebView)

This is a 3-step flow. The user is redirected to Hesabe's page to complete KNET payment.

---

### Step 1 — Initiate Payment

**POST** `/api/v1/payment/initiate`

Send the full subscription data with `payment_method: "knet"`. No card fields needed.

```json
{
  "payment_method": "knet",
  "user_id": 5,
  "subcrption_plans_id": 2,
  "area_id": 3,
  "start_date": "2026-07-01",
  "selected_days": ["sunday", "monday", "tuesday"],
  "amount": 150.000,
  "currency": "KWD",
  "address": {
    "first_name": "Ahmed",
    "last_name": "Al-Rashidi",
    "block_number": "5",
    "street": "Al Nuzha St",
    "house_building": "12",
    "floor_apartment": "3",
    "phone_number": "+96599887766",
    "category": "home",
    "preferred_delivery_slot": "four_pm_to_eight_pm",
    "delivery_notes": ""
  }
}
```

### Success Response `200`

```json
{
  "success": true,
  "message": "Payment initiated. Please complete payment via the provided URL.",
  "data": {
    "order_token": "550e8400-e29b-41d4-a716-446655440000",
    "payment_url": "https://api.hesabe.com/checkout?data=ENCRYPTED_TOKEN",
    "amount": 150.000,
    "currency": "KWD"
  }
}
```

> **Save `order_token`** — you need it to poll for payment status in Step 3.

---

### Step 2 — Open WebView

Open `payment_url` in an in-app WebView or browser.

The user sees Hesabe's KNET payment page and completes payment there.

```
[App opens WebView with payment_url]
    ↓
User enters KNET PIN on Hesabe page
    ↓
Hesabe calls backend callback automatically (app does not do this)
    ↓
Backend creates subscription in database
```

**WebView URL monitoring (detect completion):**

| URL contains | Meaning |
|---|---|
| `/api/v1/payment/callback` | Payment succeeded |
| `/api/v1/payment/callback/failure` | Payment failed |

When the WebView URL changes to either of these → close the WebView → go to Step 3.

---

### Step 3 — Poll Payment Status

After WebView closes, poll this endpoint to get the final result:

**GET** `/api/v1/payment/status/{order_token}`

Example: `GET /api/v1/payment/status/550e8400-e29b-41d4-a716-446655440000`

### Response — Still waiting

```json
{
  "success": true,
  "data": {
    "order_token": "550e8400-e29b-41d4-a716-446655440000",
    "status": "pending",
    "payment_method": "knet",
    "amount": 150.000,
    "currency": "KWD"
  }
}
```

### Response — Payment Successful

```json
{
  "success": true,
  "data": {
    "order_token": "550e8400-e29b-41d4-a716-446655440000",
    "status": "paid",
    "payment_method": "knet",
    "amount": 150.000,
    "currency": "KWD",
    "subscription_id": 42,
    "subscription": { ... }
  }
}
```

### Response — Payment Failed

```json
{
  "success": true,
  "data": {
    "order_token": "550e8400-e29b-41d4-a716-446655440000",
    "status": "failed",
    "payment_method": "knet",
    "amount": 150.000,
    "currency": "KWD"
  }
}
```

---

### Full KNET Flow Diagram

```
APP                              BACKEND                       HESABE
 |                                  |                             |
 |-- POST /payment/initiate ------->|                             |
 |   (subscription + knet)          |-- POST /payment ----------->|
 |                                  |<-- paymentToken ------------|
 |<-- { payment_url, order_token } -|                             |
 |                                  |                             |
 | [Open WebView with payment_url]  |                             |
 |                                  |                             |
 | [User completes KNET on Hesabe]  |                             |
 |                                  |<-- POST /callback ----------|
 |                                  | (Hesabe calls this)         |
 |                                  | Backend creates subscription|
 |                                  |                             |
 | [WebView URL changes → close it] |                             |
 |                                  |                             |
 |-- GET /payment/status/{token} -->|                             |
 |<-- { status: "paid" } -----------|                             |
 |                                  |                             |
 | [Show success screen]            |                             |
```

---

### Polling Recommendation

Poll every **3 seconds** up to a maximum of **2 minutes**:

```
poll /payment/status/{order_token} every 3s

if status == "paid"   → stop polling → show success
if status == "failed" → stop polling → show failure
if 2 minutes pass     → stop polling → show "Payment timed out, please check your subscription"
```

---

## Field Reference

### `selected_days` valid values

```
"sunday"    "monday"    "tuesday"    "wednesday"
"thursday"  "friday"    "saturday"
```

Can be sent as array or comma-separated string:
- `["sunday", "monday"]`
- `"sunday,monday"`

---

### `address.category` valid values

```
"home"    "office"
```

---

### `address.preferred_delivery_slot` valid values

```
"four_pm_to_eight_pm"      →  4:00 PM – 8:00 PM
"eight_pm_to_midnight"     →  8:00 PM – Midnight
```

---

### Card fields (only for debit_card / credit_card)

| Field | Format | Example |
|---|---|---|
| `card_holder_name` | String | `"Ahmed Al-Rashidi"` |
| `card_number` | Digits only, no spaces | `"4111111111111111"` |
| `card_expiry_month` | 2 digits | `"06"` |
| `card_expiry_year` | 4 digits | `"2028"` |
| `card_cvv` | 3 or 4 digits | `"123"` |
| `save_card` | Boolean | `true` or `false` |

---

## All Payment Endpoints Summary

| Method | Endpoint | Use |
|---|---|---|
| POST | `/api/v1/payment/checkout` | Cash + Debit/Credit card |
| POST | `/api/v1/payment/initiate` | KNET — get payment URL |
| GET | `/api/v1/payment/status/{token}` | Poll KNET result |
| GET/POST | `/api/v1/payment/callback` | Hesabe callback (do NOT call this from app) |
| GET/POST | `/api/v1/payment/callback/failure` | Hesabe failure callback (do NOT call from app) |

---

## Error Responses

### Validation Error `422`

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "card_number": ["Card number must be 13 to 19 digits."],
    "selected_days": ["This plan requires at least 3 day(s) per week."]
  }
}
```

### Payment Disabled `422`

```json
{
  "success": false,
  "message": "KNET payments are currently disabled."
}
```

### Gateway Error `502`

```json
{
  "success": false,
  "message": "Unable to initiate Hesabe checkout. Error: ..."
}
```

### Server Error `500`

```json
{
  "success": false,
  "message": "Payment processing failed. Please try again."
}
```

---

## Other Endpoints Used During Checkout

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/v1/subcrption-plans` | Load available plans |
| GET | `/api/v1/areas` | Load delivery areas |
| GET | `/api/v1/protein-options` | Load options for personalized plan |
| GET | `/api/v1/settings` | Check which payment methods are enabled |
| POST | `/api/v1/coupons/validate` | Validate a coupon code |

---

## Notes for Developer

1. **Always check `GET /api/v1/settings` first** to know which payment methods are enabled before showing them to the user.

2. **For KNET** — save the `order_token` as soon as you receive it in Step 1. You need it for polling even if the app is backgrounded.

3. **Do not call `/payment/callback`** from the app. That URL is only for Hesabe's servers.

4. **Currency** — always use `KWD`. Amount uses 3 decimal places (e.g. `150.000`).

5. **If `amount` is not sent** — the backend automatically uses the plan's price.

6. **Subscription queuing** — if the user already has an active subscription, the new one is automatically queued to start after the current one ends. The `start_date` you send may be overridden by the backend.
