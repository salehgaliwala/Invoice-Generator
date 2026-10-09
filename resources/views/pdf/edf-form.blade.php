<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EXPORT DECLARATION FORM (EDF) - {{ $edf->edf_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        .title-header {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            background-color: #f2f2f2;
            padding: 8px;
            border: 1px solid #000;
            margin-bottom: 8px;
        }
        .section-title {
            font-weight: bold;
            background-color: #e0e0e0;
            font-size: 11px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .declaration-box {
            font-size: 9px;
            line-height: 1.3;
        }
        .authority-box {
            background-color: #e8f5e9;
            border: 1px solid #2e7d32;
            padding: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="title-header">
        FORM EDF (EXPORT DECLARATION FORM)<br>
        <span style="font-size: 10px; font-weight: normal;">(Under Foreign Exchange Management Act, 1999)</span>
    </div>

    <table class="main-table">
        <tr>
            <th colspan="2" class="section-title">1. EXPORTER & REGULATORY DETAILS</th>
        </tr>
        <tr>
            <td width="50%">
                <strong>Exporter Legal Name:</strong><br>
                {{ $company->legal_name }}<br><br>
                <strong>Registered Address:</strong><br>
                {{ $company->registered_address }}<br>
                {{ $company->state }} - {{ $company->state_code }}
            </td>
            <td width="50%">
                <strong>IEC Number:</strong> {{ $edf->iec_number }}<br>
                <strong>AD Code (Forex Bank):</strong> {{ $edf->ad_code }}<br>
                <strong>Port of Export:</strong> {{ $edf->port_of_export }}<br>
                <strong>EDF Reference No:</strong> {{ $edf->edf_number ?: 'EDF-' . $invoice->invoice_number }}<br>
                <strong>Date:</strong> {{ date('d-M-Y') }}
            </td>
        </tr>

        <tr>
            <th colspan="2" class="section-title">2. SHIPPING BILL & CUSTOM HOUSE AGENT (CHA) DETAILS</th>
        </tr>
        <tr>
            <td>
                <strong>Shipping Bill No:</strong> {{ $edf->shipping_bill_number ?: 'Pending / Under Process' }}<br>
                <strong>Shipping Bill Date:</strong> {{ $edf->shipping_bill_date ? $edf->shipping_bill_date->format('d-M-Y') : 'N/A' }}
            </td>
            <td>
                <strong>CHA Name:</strong> {{ $edf->cha_name ?: 'N/A' }}<br>
                <strong>CHA License No:</strong> {{ $edf->cha_license_number ?: 'N/A' }}
            </td>
        </tr>

        <tr>
            <th colspan="2" class="section-title">3. BUYER, CONVEYANCE & DESTINATION DETAILS</th>
        </tr>
        <tr>
            <td>
                <strong>Foreign Buyer (Consignee):</strong><br>
                {{ $invoice->customer->name }}<br>
                {{ $invoice->customer->billing_address }}<br>
                {{ $invoice->customer->billing_country }}
            </td>
            <td>
                <strong>Vessel / Flight No:</strong> {{ $edf->vessel_flight_no ?: 'N/A' }}<br>
                <strong>Port of Loading:</strong> {{ $edf->port_of_loading ?: $edf->port_of_export }}<br>
                <strong>Port of Discharge:</strong> {{ $edf->port_of_discharge ?: 'Destination Port' }}<br>
                <strong>Country of Ultimate Destination:</strong> {{ $invoice->customer->billing_country }}
            </td>
        </tr>

        <tr>
            <th colspan="2" class="section-title">4. INVOICE VALUATION & REALIZATION DETAILS</th>
        </tr>
        <tr>
            <td>
                <strong>Export Invoice No:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d-M-Y') }}<br>
                <strong>Nature of Contract:</strong> {{ $edf->nature_of_contract }}
            </td>
            <td>
                <strong>Currency of Realization:</strong> {{ $edf->currency_of_realization }}<br>
                <strong>Exchange Rate (INR):</strong> {{ number_format($edf->exchange_rate, 4) }}<br>
                <strong>Invoice Value (FCY):</strong> {{ $edf->currency_of_realization }} {{ number_format($edf->invoice_value_fcy, 2) }}<br>
                <strong>Total Realizable Value (FCY):</strong> {{ $edf->currency_of_realization }} {{ number_format($edf->total_realizable_value_fcy, 2) }}<br>
                <strong>Total Realizable Value (INR):</strong> ₹{{ number_format($edf->total_realizable_value_inr, 2) }}
            </td>
        </tr>

        <tr>
            <th colspan="2" class="section-title">5. EXPORTER'S STATUTORY DECLARATION</th>
        </tr>
        <tr>
            <td colspan="2" class="declaration-box">
                I / We hereby declare that I / We am / are the seller / exporter of the goods described above and that the particulars given above are true and correct.<br>
                I / We further declare that the full value of the goods will be realized and repatriated to India through an Authorized Dealer in Foreign Exchange within the period prescribed under the Foreign Exchange Management Act, 1999 and rules/guidelines framed thereunder.
                <br><br><br>
                <table width="100%" style="border: none;">
                    <tr>
                        <td width="60%" style="border: none;">
                            <strong>Date:</strong> {{ date('d-M-Y') }}<br>
                            <strong>Place:</strong> {{ $company->state }}
                        </td>
                        <td width="40%" class="text-center" style="border: none;">
                            <strong>For {{ $company->legal_name }}</strong><br><br><br>
                            ___________________________________<br>
                            <strong>AUTHORIZED SIGNATORY</strong><br>
                            (Signature as per Bank record with company seal)
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="authority-box">
        <strong>Space for use of Specified Authority (Customs / SEZ / AD / STPI):</strong><br><br>
        <p style="font-size: 9px; margin: 0;">
            Certified, on the basis of above declaration, that the goods described above and the export value declared by the exporter in this form is as per the corresponding invoice/gist of invoices submitted and declared by the exporter.
        </p>
        <br>
        <strong>Date:</strong> ______________________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>Signature of Designated / Authorised Official</strong>
    </div>

</body>
</html>
