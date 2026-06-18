# Balance App — Backend API Guide for App Developer

**Base URL (Production):** `https://backend.balancekw.app/api`

> All requests must include the header:
> `Content-Type: application/json`
> `Accept: application/json`

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Payment Flow — Overview](#2-payment-flow--overview)
3. [Payment — Cash](#3-payment--cash)
4. [Payment — Card (Debit / Credit via Hesabe)](#4-payment--card-debit--credit-via-hesabe)
5. [Subscription Checkout — Full Request Body](#5-subscription-checkout--full-request-body)
6. [Other Useful Endpoints](#6-other-useful-endpoints)
7. [Error Responses](#7-error-responses)

---

## 1. Authentication

### Login (OTP-based)

**POST** `/api/login`

```json
{
  "mobile": 99787153,
  "otp": 1234
}
```

**Success Response:**
```json
{
  "token": "1|abc123...",
  "user": { ... },
  "active_subscription": { ... },
  "queued_subscriptions": [ ... ]
}
```

> Save the `token`. For authenticated routes send it as:
> `Authorization: Bearer {token}`

---

### Register

**POST** `/api/register`

### Check if user exists

**POST** `/api/check-user`

### Send OTP

**POST** `/api/otp/send`

### Verify OTP

**POST** `/api/otp/verify`

### Logout (requires auth token)

**POST** `/api/v1/logout`

---

## 2. Payment Flow — Overview

There are **two payment methods** the app must support:

| Method | Value to send | How it works |
|---|---|---|
| Cash on delivery | `cash` | No card fields needed. Subscription created with `pending` payment status. |
| Debit card | `debit_card` | Card details sent to backend → backend charges via Hesabe → subscription created on success. |
| Credit card | `credit_card` | Same as debit card flow. |

**Single endpoint handles all payment types:**

**POST** `/api/v1/payment/checkout`

---

## 3. Payment — Cash

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
    "remarks": "",
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

---

## 4. Payment — Card (Debit / Credit via Hesabe)

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
    "remarks": "",
    "delivery_notes": "Ring the bell"
  }
}
```

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

### Payment Failed Response `502`

```json
{
  "success": false,
  "message": "Payment was not successful. Please try again."
}
```

---

## 5. Subscription Checkout — Full Request Body

### All Fields Reference

| Field | Type | Required | Notes |
|---|---|---|---|
| `user_id` | integer | YES | Must exist in users table |
| `subcrption_plans_id` | integer | YES | Must be an active plan |
| `area_id` | integer | YES | Must be an active area |
| `start_date` | string | YES | Format: `YYYY-MM-DD`, today or future |
| `selected_days` | array | YES | See valid values below |
| `payment_method` | string | YES | `cash`, `debit_card`, or `credit_card` |
| `amount` | number | No | If omitted, plan price is used |
| `currency` | string | No | Default: `KWD` |
| `is_personalized` | boolean | No | Default: `false` |
| `protein` | number | If personalized | Required when `is_personalized=true` |
| `carbs` | number | If personalized | Required when `is_personalized=true` |
| `meals` | array | No | Pre-selected meals per day |
| `card_holder_name` | string | If card | Required for `debit_card` / `credit_card` |
| `card_number` | string | If card | 13–19 digits, no spaces |
| `card_expiry_month` | string | If card | 2 digits e.g. `"06"` |
| `card_expiry_year` | string | If card | 4 digits e.g. `"2028"` |
| `card_cvv` | string | If card | 3 or 4 digits |
| `save_card` | boolean | No | Save card token for future use |
| `address` | object | YES | See address fields below |

### Valid `selected_days` Values

```
"sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday"
```

### Valid `address.category` Values

```
"home", "office"
```

### Valid `address.preferred_delivery_slot` Values

```
"four_pm_to_eight_pm"      → 4 PM to 8 PM
"eight_pm_to_midnight"     → 8 PM to Midnight
```

### Meals Array Format (optional, for pre-selected meals)

```json
"meals": [
  { "day": "sunday",   "meal_id": 10, "type": "is meal" },
  { "day": "sunday",   "meal_id": 22, "type": "is snack" },
  { "day": "monday",   "meal_id": 11, "type": "is meal" }
]
```

---

## 6. Other Useful Endpoints

### Get Subscription Plans

**GET** `/api/v1/subcrption-plans`

### Get Available Areas (for delivery area picker)

**GET** `/api/v1/areas`

### Get Protein Options (for personalized plan)

**GET** `/api/v1/protein-options`

### Get App Settings (payment methods, delivery slots)

**GET** `/api/v1/settings`

### Validate Coupon

**POST** `/api/v1/coupons/validate`

```json
{ "code": "SAVE10" }
```

### Get Available Meals

**GET** `/api/v1/meals`

---

### Authenticated Routes (require `Authorization: Bearer {token}`)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/profile` | Get user profile |
| PUT | `/api/v1/profile` | Update profile |
| GET | `/api/v1/my-subscriptions` | List user subscriptions |
| GET | `/api/v1/my-subscriptions/{id}` | Get single subscription |
| GET | `/api/v1/addresses` | List addresses |
| POST | `/api/v1/addresses` | Add address |
| PUT | `/api/v1/addresses/{id}` | Update address |
| DELETE | `/api/v1/addresses/{id}` | Delete address |
| POST | `/api/v1/subscription/{id}/pause-request` | Submit pause request |
| GET | `/api/v1/subscription/{id}/pause-requests` | View pause requests |
| GET | `/api/v1/allergies` | Get user allergies |
| PUT | `/api/v1/allergies` | Update allergies |
| GET | `/api/v1/dislikes` | Get user dislikes |
| PUT | `/api/v1/dislikes` | Update dislikes |

---

## 7. Error Responses

### Validation Error `422`

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "card_number": ["Card number must be 13 to 19 digits."],
    "start_date": ["Start date cannot be in the past."]
  }
}
```

### Payment/Gateway Error `502`

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

### Unauthorized `401`

```json
{
  "error": "Invalid mobile or OTP"
}
```

---

## Quick Notes for App Developer

1. **`selected_days` can be sent as an array** `["sunday","monday"]` **or comma-separated string** `"sunday,monday"` — both are accepted.

2. **`is_personalized` can be sent as boolean or string** (`"true"` / `"false"`) — both are accepted.

3. **Card number** — send digits only, no spaces or dashes.

4. **Amount** — if not sent, the backend uses the plan's price automatically. Only send it if you have a custom/discounted amount.

5. **`save_card: true`** — saves the card token in the backend for display (e.g. "Visa ending in 1111"). The app can show saved cards from `GET /api/v1/profile`.

6. **Currency** — always use `KWD` (3 decimal places, e.g. `150.000`).

7. **The same endpoint `/api/v1/payment/checkout` handles all payment types** — just change `payment_method`.
