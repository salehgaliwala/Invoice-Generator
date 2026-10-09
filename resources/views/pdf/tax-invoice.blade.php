<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TAX INVOICE - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header-table, .details-table, .items-table, .summary-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .bordered, .bordered th, .bordered td {
            border: 1px solid #777;
        }
        th, td {
            padding: 5px;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            padding: 5px;
            background-color: #e0e0e0;
            border: 1px solid #777;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
        }
        .lut-badge {
            font-weight: bold;
            color: #d32f2f;
            padding: 3px;
            border: 1px dashed #d32f2f;
            margin-top: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>

    <div class="title">
        {{ $invoice->isExport() ? 'TAX INVOICE / EXPORT INVOICE' : 'TAX INVOICE' }}
    </div>

    <table class="header-table bordered">
        <tr>
            <td width="60%">
                <span class="company-name">{{ $company->legal_name }}</span><br>
                @if($company->trade_name) (Trade Name: {{ $company->trade_name }})<br> @endif
                <strong>Address:</strong> {{ $company->registered_address }}<br>
                <strong>State:</strong> {{ $company->state }} (Code: {{ $company->state_code }})<br>
                <strong>GSTIN:</strong> {{ $company->gstin ?: 'N/A' }} | <strong>PAN:</strong> {{ $company->pan }}<br>
                @if($company->iec) <strong>IEC Code:</strong> {{ $company->iec }} <br> @endif
            </td>
            <td width="40%">
                <strong>Invoice No:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d-M-Y') }}<br>
                <strong>Financial Year:</strong> FY{{ $invoice->financial_year }}<br>
                <strong>Place of Supply:</strong> {{ $invoice->place_of_supply_state }} ({{ $invoice->place_of_supply_state_code }})<br>
                @if($invoice->transaction_type === 'export_lut')
                    <div class="lut-badge">
                        EXPORT UNDER LUT WITHOUT PAYMENT OF TAX<br>
                        LUT No: {{ $invoice->lut_number ?: 'N/A' }}
                    </div>
                @elseif($invoice->transaction_type === 'export_igst')
                    <div class="lut-badge">
                        EXPORT ON PAYMENT OF IGST
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <table class="details-table bordered">
        <tr>
            <th width="50%">BILL TO (BUYER)</th>
            <th width="50%">SHIP TO (CONSIGNEE)</th>
        </tr>
        <tr>
            <td>
                <strong>Name:</strong> {{ $customer->name }}<br>
                <strong>Address:</strong> {{ $customer->billing_address }}, {{ $customer->billing_city }}<br>
                <strong>State & Country:</strong> {{ $customer->billing_state }} ({{ $customer->billing_state_code }}), {{ $customer->billing_country }}<br>
                <strong>GSTIN / Tax ID:</strong> {{ $customer->gstin ?: 'URP / Overseas' }}
            </td>
            <td>
                <strong>Name:</strong> {{ $customer->name }}<br>
                <strong>Address:</strong> {{ $customer->shipping_address ?: $customer->billing_address }}, {{ $customer->shipping_city ?: $customer->billing_city }}<br>
                <strong>State & Country:</strong> {{ $customer->shipping_state ?: $customer->billing_state }} ({{ $customer->shipping_state_code ?: $customer->billing_state_code }}), {{ $customer->shipping_country ?: $customer->billing_country }}
            </td>
        </tr>
    </table>

    <table class="items-table bordered">
        <thead>
            <tr>
                <th width="4%">#</th>
                <th width="28%">Description of Goods / Services</th>
                <th width="10%">HSN/SAC</th>
                <th width="8%">Qty</th>
                <th width="6%">UOM</th>
                <th width="10%">Rate</th>
                <th width="10%">Taxable Value</th>
                @if($invoice->cgst_amount > 0 || $invoice->sgst_amount > 0)
                    <th width="12%">CGST</th>
                    <th width="12%">SGST</th>
                @else
                    <th width="12%">IGST</th>
                @endif
                <th width="12%">Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->item_description }}</td>
                    <td class="text-center">{{ $item->hsn_sac_code }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-center">{{ $item->uom }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->taxable_amount, 2) }}</td>
                    @if($invoice->cgst_amount > 0 || $invoice->sgst_amount > 0)
                        <td class="text-right">
                            {{ $item->cgst_rate }}%<br>₹{{ number_format($item->cgst_amount, 2) }}
                        </td>
                        <td class="text-right">
                            {{ $item->sgst_rate }}%<br>₹{{ number_format($item->sgst_amount, 2) }}
                        </td>
                    @else
                        <td class="text-right">
                            {{ $item->igst_rate }}%<br>₹{{ number_format($item->igst_amount, 2) }}
                        </td>
                    @endif
                    <td class="text-right">₹{{ number_format($item->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table bordered">
        <tr>
            <td width="60%" rowspan="7">
                <strong>Amount in Words:</strong><br>
                <em>{{ $invoice->amount_in_words }}</em><br><br>
                <strong>Bank Account Details for Payment:</strong><br>
                Bank Name: {{ $company->bank_name }}<br>
                Branch: {{ $company->bank_branch }}<br>
                A/C No: {{ $company->bank_account_number }}<br>
                IFSC Code: {{ $company->bank_ifsc }}<br>
                @if($company->bank_ad_code) AD Code: {{ $company->bank_ad_code }} <br> @endif
            </td>
            <td width="20%"><strong>Subtotal:</strong></td>
            <td width="20%" class="text-right">₹{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Discount:</strong></td>
            <td class="text-right">₹{{ number_format($invoice->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Taxable Value:</strong></td>
            <td class="text-right">₹{{ number_format($invoice->taxable_amount, 2) }}</td>
        </tr>
        @if($invoice->cgst_amount > 0)
        <tr>
            <td><strong>CGST Total:</strong></td>
            <td class="text-right">₹{{ number_format($invoice->cgst_amount, 2) }}</td>
        </tr>
        <tr>
            <td><strong>SGST Total:</strong></td>
            <td class="text-right">₹{{ number_format($invoice->sgst_amount, 2) }}</td>
        </tr>
        @else
        <tr>
            <td><strong>IGST Total:</strong></td>
            <td class="text-right">₹{{ number_format($invoice->igst_amount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td><strong>Round Off:</strong></td>
            <td class="text-right">₹{{ number_format($invoice->round_off, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Grand Total:</strong></td>
            <td class="text-right"><strong>₹{{ number_format($invoice->total_amount, 2) }}</strong></td>
        </tr>
    </table>

    <table class="footer-table bordered">
        <tr>
            <td width="50%">
                <strong>Terms & Conditions:</strong><br>
                1. Goods once sold will not be taken back.<br>
                2. Subject to local jurisdiction.<br>
                {{ $invoice->terms_and_conditions }}
            </td>
            <td width="50%" class="text-center" style="height: 80px; vertical-align: bottom;">
                <strong>For {{ $company->legal_name }}</strong><br><br><br>
                _____________________________________<br>
                Authorized Signatory
            </td>
        </tr>
    </table>

</body>
</html>
