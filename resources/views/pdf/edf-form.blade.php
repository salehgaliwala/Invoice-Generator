<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EXPORT DECLARATION FORM (EDF) - {{ $edf->edf_number }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.3;
        }
        .classification {
            text-align: center;
            font-size: 9pt;
            color: #0000d0;
            margin-bottom: 10px;
        }
        .classification-footer {
            text-align: center;
            font-size: 9pt;
            color: #0000d0;
            margin-top: 25px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            background-color: #ffffff;
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 0;
        }
        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10pt;
        }
        .declaration-text {
            font-size: 9pt;
            text-align: justify;
            margin-bottom: 15px;
        }
        .page-break {
            page-break-before: always;
        }
        .authority-box {
            background-color: #00a651;
            color: #000;
            padding: 12px;
            border: 1px solid #000;
            margin-top: 0;
        }
        .authority-title {
            font-weight: bold;
            font-size: 9.5pt;
            margin-bottom: 8px;
        }
        .authority-text {
            font-size: 9pt;
            line-height: 1.4;
            margin-bottom: 25px;
        }
        .authority-signature {
            font-size: 9pt;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- PAGE 1 -->
    <div class="classification">Classification - Internal</div>

    <table class="main-table">
        <tr>
            <td colspan="2" class="text-center fw-bold" style="font-size: 11pt; padding: 10px;">
                FORM EDF (EXPORT DECLARATION FORM)<br>
                <span style="font-size: 9pt; font-weight: normal;">(Under Foreign Exchange Management Act, 1999)</span>
            </td>
        </tr>

        <!-- Section 1 -->
        <tr>
            <td colspan="2" class="section-header">1. EXPORTER & REGULATORY DETAILS</td>
        </tr>
        <tr>
            <td width="50%">
                <span class="fw-bold">Exporter Legal Name:</span><br>
                {{ $company->legal_name }}<br><br>
                <span class="fw-bold">Registered Address:</span><br>
                {{ $company->registered_address }}<br>
                {{ $company->state }} - {{ $company->state_code }}
            </td>
            <td width="50%">
                <span class="fw-bold">IEC Number:</span> {{ $edf->iec_number }}<br>
                <span class="fw-bold">AD Code (Forex Bank):</span> {{ $edf->ad_code }}<br>
                <span class="fw-bold">Port of Export:</span> {{ $edf->port_of_export }}<br>
                <span class="fw-bold">EDF Form No:</span> {{ $edf->edf_number ?: 'EDF-' . $invoice->invoice_number }}<br>
                <span class="fw-bold">Date:</span> {{ date('d-M-Y') }}
            </td>
        </tr>

        <!-- Section 2 -->
        <tr>
            <td colspan="2" class="section-header">2. SHIPPING BILL & CUSTOM HOUSE AGENT (CHA) DETAILS</td>
        </tr>
        <tr>
            <td>
                <span class="fw-bold">Shipping Bill No:</span> {{ $edf->shipping_bill_number ?: 'Pending / Under Process' }}<br>
                <span class="fw-bold">Shipping Bill Date:</span> {{ $edf->shipping_bill_date ? $edf->shipping_bill_date->format('d-M-Y') : 'N/A' }}
            </td>
            <td>
                <span class="fw-bold">CHA Name:</span> {{ $edf->cha_name ?: 'N/A' }}<br>
                <span class="fw-bold">CHA License No:</span> {{ $edf->cha_license_number ?: 'N/A' }}
            </td>
        </tr>

        <!-- Section 3 -->
        <tr>
            <td colspan="2" class="section-header">3. BUYER, CONVEYANCE & DESTINATION DETAILS</td>
        </tr>
        <tr>
            <td>
                <span class="fw-bold">Foreign Buyer (Consignee):</span><br>
                {{ $invoice->customer->name }}<br>
                {{ $invoice->customer->billing_address }}<br>
                {{ $invoice->customer->billing_country }}
            </td>
            <td>
                <span class="fw-bold">Vessel / Flight No:</span> {{ $edf->vessel_flight_no ?: 'N/A' }}<br>
                <span class="fw-bold">Port of Loading:</span> {{ $edf->port_of_loading ?: $edf->port_of_export }}<br>
                <span class="fw-bold">Port of Discharge:</span> {{ $edf->port_of_discharge ?: 'Destination Port' }}<br>
                <span class="fw-bold">Country of Ultimate Destination:</span> {{ $invoice->customer->billing_country }}
            </td>
        </tr>

        <!-- Section 4 -->
        <tr>
            <td colspan="2" class="section-header">4. INVOICE VALUATION & REALIZATION DETAILS</td>
        </tr>
        <tr>
            <td>
                <span class="fw-bold">Export Invoice No:</span> {{ $invoice->invoice_number }}<br>
                <span class="fw-bold">Invoice Date:</span> {{ $invoice->invoice_date->format('d-M-Y') }}<br>
                <span class="fw-bold">Nature of Contract:</span> {{ $edf->nature_of_contract }}
            </td>
            <td>
                <span class="fw-bold">Currency of Realization:</span> {{ $edf->currency_of_realization }}<br>
                <span class="fw-bold">Exchange Rate (INR):</span> {{ number_format($edf->exchange_rate, 4) }}<br>
                <span class="fw-bold">Invoice Value (FCY):</span> {{ $edf->currency_of_realization }} {{ number_format($edf->invoice_value_fcy, 2) }}<br>
                <span class="fw-bold">Total Realizable Value (FCY):</span> {{ $edf->currency_of_realization }} {{ number_format($edf->total_realizable_value_fcy, 2) }}<br>
                <span class="fw-bold">Total Realizable Value (INR):</span> ₹{{ number_format($edf->total_realizable_value_inr, 2) }}
            </td>
        </tr>

        <!-- Declaration Section -->
        <tr>
            <td colspan="2" class="section-header">DECLARATION BY EXPORTER</td>
        </tr>
        <tr>
            <td colspan="2">
                <p class="declaration-text">
                    I / We hereby declare that I / We am / are the seller / exporter of the goods/services described above and that the particulars given above are true and correct.<br><br>
                    I / We further declare that the full value of the goods/services will be realized and repatriated to India through an Authorized Dealer in Foreign Exchange within the period prescribed under the Foreign Exchange Management Act, 1999 and rules/guidelines framed thereunder.
                </p>
            </td>
        </tr>
    </table>

    <div class="classification-footer">Classification - Internal</div>

    <!-- PAGE 2 -->
    <div class="page-break"></div>

    <div class="classification">Classification - Internal</div>

    <table class="main-table">
        <tr>
            <td width="20%" class="fw-bold">Remark (If Any)</td>
            <td width="80%">{{ $edf->remarks ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">For</td>
            <td>
                <br><br><br>
                ___________________________________<br>
                <span class="fw-bold">AUTHORISED SIGNATORY</span><br>
                (Signature as per Bank record with company seal)
            </td>
        </tr>
        <tr>
            <td colspan="2" class="fw-bold" style="background-color: #f0f0f0;">
                5. Space for use of Specified Authority (Customs/SEZ/AD/STPI):
            </td>
        </tr>
    </table>

    <div class="authority-box">
        <div class="authority-text">
            Certified, on the basis of above declaration at 4, that the goods/services described above and the export value^ declared by the exporter in this form is as per the corresponding invoice/gist of invoices submitted and declared by the exporter.
        </div>
        <div class="authority-signature">
            Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            (Signature of Designated/Authorised officials of Custom /SEZ/ Authorised Dealer/STPI)
        </div>
    </div>

    <div class="classification-footer" style="margin-top: 300px;">Classification - Internal</div>

</body>
</html>
