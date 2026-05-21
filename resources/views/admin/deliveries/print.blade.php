<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note #{{ $deliveryOrder->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #222; background: #fff; }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm 18mm;
            background: #fff;
        }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .logo-area {}
        .logo-text {
            font-size: 32px;
            font-weight: 300;
            letter-spacing: 8px;
            text-transform: uppercase;
            color: #222;
        }
        .logo-text span { font-weight: 400; }
        .logo-tagline { font-size: 11px; color: #888; letter-spacing: 2px; margin-top: 2px; text-align: center; }
        .delivery-meta { text-align: right; font-size: 12px; line-height: 1.8; }
        .delivery-meta strong { font-size: 13px; }

        hr { border: none; border-top: 1px solid #ccc; margin: 12px 0; }
        hr.thick { border-top: 2px solid #222; }

        /* Info block */
        .info-row { display: flex; justify-content: space-between; margin-bottom: 14px; }
        .info-left { font-size: 12px; line-height: 2; }
        .info-right { text-align: right; font-size: 12px; line-height: 1.9; }
        .info-right .customer-name { font-size: 14px; font-weight: 600; }

        /* Items table */
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table thead tr { border-bottom: 2px solid #222; }
        .items-table th { font-size: 12px; font-weight: 700; padding: 6px 4px; text-transform: uppercase; }
        .items-table td { padding: 7px 4px; font-size: 12px; vertical-align: top; border-bottom: 1px solid #eee; }
        .items-table .col-qty { width: 40px; text-align: center; }
        .section-label { font-weight: 700; font-size: 12px; padding: 8px 4px 4px; color: #333; }
        .meal-title { font-weight: 600; }
        .meal-macros { color: #555; font-size: 11px; margin-top: 2px; }
        .meal-topping { color: #777; font-size: 11px; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page { width: 100%; padding: 10mm 14mm; }
        }
    </style>
</head>
<body>

<div class="no-print" style="padding:12px; background:#f8f9fa; border-bottom:1px solid #dee2e6; text-align:right;">
    <button onclick="window.print()" style="padding:6px 18px; background:#343a40; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:13px;">
        <b>⎙ Print</b>
    </button>
    <button onclick="window.close()" style="padding:6px 14px; background:#6c757d; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:13px; margin-left:6px;">
        Close
    </button>
</div>

<div class="page">
    @php
        $sub  = $deliveryOrder->subscription;
        $addr = $sub->address;
        $day  = $deliveryOrder->subscriptionDay;
        $meals  = $day->subscription_meals->where('type', 'is meal');
        $snacks = $day->subscription_meals->where('type', 'is snack');
    @endphp

    {{-- Header --}}
    <div class="header">
        <div class="logo-area">
            <img src="{{ asset('images/balance-text.png') }}" alt="Balance" style="max-height:60px; max-width:220px; object-fit:contain;">
        </div>
        <div class="delivery-meta">
            <strong>Delivery ID #: {{ $deliveryOrder->id }}</strong><br>
            Date: {{ $deliveryOrder->delivery_date->format('Y-m-d') }}<br>
            Time: {{ $addr->preferred_delivery_slot ?? '—' }}
        </div>
    </div>

    <hr class="thick">

    {{-- Subscription / Address info --}}
    <div class="info-row">
        <div class="info-left">
            <strong>Subscription ID:</strong> #{{ str_pad($sub->id, 6, '0', STR_PAD_LEFT) }}<br>
            <strong>Branch:</strong> {{ $sub->branch->name ?? '—' }}
        </div>
        <div class="info-right">
            <div class="customer-name">{{ $sub->user->name ?? '—' }}</div>
            @if($addr)
                City: {{ $addr->area ?? ($sub->area->name ?? '—') }}<br>
                Block: {{ $addr->block_number ?? '—' }},
                Street: {{ $addr->street ?? '—' }},
                House No: {{ $addr->house_building ?? '—' }}<br>
                @if($addr->floor_apartment)
                    Additional Details: {{ $addr->floor_apartment }}<br>
                @endif
                @if($addr->remarks)
                    Remarks: {{ $addr->remarks }}<br>
                @endif
                Mobile No: {{ $addr->phone_number ?? '—' }}
            @endif
        </div>
    </div>

    <hr>

    {{-- Items --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th class="col-qty">Qty</th>
            </tr>
        </thead>
        <tbody>

            {{-- Meals section --}}
            @if($meals->count())
            <tr>
                <td colspan="2" class="section-label">Meals</td>
            </tr>
            @foreach($meals as $sm)
            <tr>
                <td>
                    <div class="meal-title">{{ $sm->meal->title ?? '—' }}</div>
                    @if($sm->meal && $sm->meal->extras)
                        <div class="meal-topping">Toppings:: {{ $sm->meal->extras }}</div>
                    @endif
                    @if($sm->meal)
                    <div class="meal-macros">
                        Macros:
                        @if($sm->meal->calories) Calorie: {{ $sm->meal->calories }} @endif
                        @if($sm->meal->protein_g) Proteins: {{ $sm->meal->protein_g }} @endif
                        @if($sm->meal->carbs_g) Crabs: {{ $sm->meal->carbs_g }} @endif
                        @if($sm->meal->fat_g) Fats: {{ $sm->meal->fat_g }} @endif
                    </div>
                    @endif
                </td>
                <td class="col-qty">1</td>
            </tr>
            @endforeach
            @endif

            {{-- Snacks section --}}
            @if($snacks->count())
            <tr>
                <td colspan="2" class="section-label">Snacks</td>
            </tr>
            @foreach($snacks as $sm)
            <tr>
                <td>
                    <div class="meal-title">{{ $sm->meal->title ?? '—' }}</div>
                    @if($sm->meal)
                    <div class="meal-macros">
                        Macros:
                        @if($sm->meal->calories) Calorie: {{ $sm->meal->calories }} @endif
                        @if($sm->meal->protein_g) Proteins: {{ $sm->meal->protein_g }} @endif
                        @if($sm->meal->carbs_g) Crabs: {{ $sm->meal->carbs_g }} @endif
                        @if($sm->meal->fat_g) Fats: {{ $sm->meal->fat_g }} @endif
                    </div>
                    @endif
                </td>
                <td class="col-qty">1</td>
            </tr>
            @endforeach
            @endif

        </tbody>
    </table>

</div>
</body>
</html>
