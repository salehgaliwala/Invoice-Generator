<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Export Declaration Form (EDF)</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }
        body {
            font-family: 'Liberation Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            line-height: 1.18;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .classification {
            text-align: center;
            font-size: 8pt;
            color: #0000d0;
            margin-bottom: 4px;
        }
        .classification-footer {
            text-align: center;
            font-size: 8pt;
            color: #0000d0;
            margin-top: 15px;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 9.5pt;
            text-transform: uppercase;
            margin-bottom: 6px;
            padding: 4px;
            border: 1px solid #000;
            background-color: #f8f8f8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table.bordered th, table.bordered td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: top;
        }
        .section-header {
            background-color: #eef2f5;
            font-weight: bold;
            font-size: 8.5pt;
        }
        .fw-bold {
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .legal-text {
            font-size: 7.5pt;
            text-align: justify;
            line-height: 1.15;
            margin-bottom: 4px;
        }
        .debit-line {
            font-size: 8pt;
            font-weight: bold;
            padding: 4px;
            border: 1px solid #000;
            background-color: #fafafa;
            margin-bottom: 6px;
        }
        .contact-line {
            font-size: 8pt;
            margin-bottom: 6px;
        }
        .authority-box {
            background-color: #00a651;
            color: #000;
            padding: 8px;
            border: 1px solid #000;
            margin-top: 4px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    @php
        $customer = $customer ?? $invoice->customer ?? null;
        $customerBankName = $customer->bank_name ?? 'HDFC BANK LTD';
        $customerBankBranch = $customer->bank_branch ?? 'Industry House Churchgate';
        $customerBankCity = $customer->bank_city ?? 'Mumbai';
        $customerAccNo = $customer->account_number ?? '(FULL ACCOUNT NUMBER)';
        $customerAdCode = $customer->ad_code ?? '0511626 6000009';
        $customerAdAddress = $customer->ad_name_address ?? 'HDFC Bank Ltd Ground Floor Industry House Churchgate Branch Mumbai';

        $totalFobInWords = $edf->total_fob_in_words ?? (isset($numberToWords) ? $numberToWords : 'RUPEES ONLY');
    @endphp

    <!-- Top Stamp -->
    <div class="classification">Classification - Internal</div>

    <!-- Main Header Banner -->
    <div class="header-title">
        Request letter for Export of Services & EDF Filing Cum Disposal Instructions for Credit
    </div>

    <!-- Header Bank & Exporter Metadata Grid -->
    <table class="bordered">
        <tr>
            <td width="60%">
                <span class="fw-bold">To,</span><br>
                {{ $customerBankName }}<br>
                {{ $customerBankBranch }}, {{ $customerBankCity }}
            </td>
            <td width="40%" class="text-right">
                <span class="fw-bold">Date:</span> {{ isset($edf->created_at) ? $edf->created_at->format('d-M-Y') : date('d-M-Y') }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="fw-bold">Account no. to be Credited:</span> {{ $customerAccNo }}<br>
                <span class="fw-bold">Purpose Code & Details:</span> {{ $edf->purpose_code ?? 'P0802 - Software Implementation / IT Services' }}<br>
                <span class="fw-bold">Exchange Rate / Forward Contract details if any:</span> {{ $edf->exchange_rate_details ?? 'Spot Rate / Market Rate' }}
            </td>
        </tr>
        <tr>
            <td width="50%">
                <span class="fw-bold">AD Code:</span> {{ $customerAdCode }}<br>
                <span class="fw-bold">AD Name & Address:</span> {{ $customerAdAddress }}
            </td>
            <td width="50%">
                <span class="fw-bold">Exporter PAN:</span> {{ $company->pan ?? '(Mandatory)' }}<br>
                <span class="fw-bold">IEC Code:</span> {{ $edf->iec_number ?? $company->iec ?? '(Mention IEC Code)' }}<br>
                <span class="fw-bold">GSTIN:</span> {{ $company->gstin ?? 'Input Correct GST Number' }}
            </td>
        </tr>
    </table>

    <!-- Exporter & Export Classification Matrix -->
    <table class="bordered">
        <tr>
            <td colspan="2" class="section-header">1. EXPORTER & EXPORT CLASSIFICATION PARAMETERS</td>
        </tr>
        <tr>
            <td width="50%">
                <span class="fw-bold">Exporter Name & Address:</span><br>
                {{ $company->legal_name }}<br>
                {{ $company->registered_address }}<br>
                {{ $company->state }} - {{ $company->state_code }}
            </td>
            <td width="50%">
                <span class="fw-bold">Type of Export:</span> Export of Services<br>
                <span class="fw-bold">Category of Exporter:</span> Service Exporter (DTA)<br>
                <span class="fw-bold">Mode of Realisation:</span> {{ $edf->mode_of_realisation ?? 'Others (advance payment, etc. including transfer/remittance to bank)' }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="fw-bold">Mode of Transport:</span> {{ $edf->mode_of_transport ?? 'Internet / Telecommunication' }}<br>
                <span class="fw-bold">Category of Export:</span> {{ $edf->export_category ?? 'Regular Export Custom (DTA units)' }}<br>
                <span class="fw-bold">Dispatch Indicator:</span> {{ $edf->dispatch_indicator ?? 'Non dispatch' }}
            </td>
            <td>
                <span class="fw-bold">Date of Export / Expected Date of Service:</span> {{ $edf->shipping_bill_date ? $edf->shipping_bill_date->format('d-m-Y') : date('d-m-Y') }}<br>
                <span class="fw-bold">L/C No. (If Any):</span> {{ $edf->lc_number ?? 'N/A' }}<br>
                <span class="fw-bold">Country of Final Destination:</span> {{ $customer->billing_country ?? 'UNITED STATES' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="fw-bold">Description of Services:</span> Software Development, Consulting and Information Technology Services<br>
                <span class="fw-bold">Total FOB Value (IN WORDS):</span> {{ strtoupper($totalFobInWords) }}<br>
                <span class="fw-bold">Third Party (Yes/No):</span> {{ $edf->third_party_details ?? 'No' }}
            </td>
        </tr>
    </table>

    <!-- Section 2B: Details of Export Value of Services Table -->
    <div class="fw-bold" style="margin-bottom: 3px;">2B. Details of Export Value of Services:</div>
    <table class="bordered" style="font-size: 7.5pt;">
        <thead>
            <tr class="section-header text-center">
                <th width="4%">Sr no.</th>
                <th width="15%">Service Recipient Name & Address</th>
                <th width="8%">Country</th>
                <th width="10%">Invoice No.</th>
                <th width="8%">Invoice Date</th>
                <th width="6%">Currency</th>
                <th width="9%">Amount</th>
                <th width="9%">Net Realisable Value</th>
                <th width="9%">Contract No.</th>
                <th width="12%">Description of Services</th>
                <th width="5%">SAC Code</th>
                <th width="5%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($invoice))
            <tr>
                <td class="text-center">1</td>
                <td>{{ $customer->name ?? 'Recipient' }}<br>{{ $customer->billing_address ?? '' }}</td>
                <td>{{ $customer->billing_country ?? 'USA' }}</td>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                <td class="text-center">{{ $edf->currency_of_realization ?? 'USD' }}</td>
                <td class="text-right">{{ number_format($edf->invoice_value_fcy ?? $invoice->total_amount, 2) }}</td>
                <td class="text-right">{{ number_format($edf->total_realizable_value_fcy ?? $invoice->total_amount, 2) }}</td>
                <td>{{ $edf->nature_of_contract ?? 'N/A' }}</td>
                <td>IT & Software Services</td>
                <td class="text-center">998314</td>
                <td>{{ $edf->remarks ?? 'N/A' }}</td>
            </tr>
            @endif
            @for($i = (isset($invoice) ? 2 : 1); $i <= 4; $i++)
            <tr>
                <td class="text-center">{{ $i }}</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            @endfor
        </tbody>
    </table>

    <!-- Customer Account Debit Row -->
    <div class="debit-line">
        Debit all processing charges from account no. {{ $customerAccNo }}
    </div>

    <!-- Contact Line -->
    <div class="contact-line">
        In case of any queries please contact us on Tel. No <span class="fw-bold">{{ $company->authorized_signatory_phone ?? $company->phone ?? '91-22-12345678' }}</span> or email us at <span class="fw-bold">{{ $company->authorized_signatory_email ?? $company->email ?? 'exports@company.com' }}</span>
    </div>

    <!-- Statutory Declarations -->
    <div class="legal-text">
        <span class="fw-bold">OFAC Declaration:</span> I/We hereby declare that the above transaction does not involve and is not designed for the purpose of any contravention or evasion of the provision of the OFAC.
    </div>

    <div class="legal-text">
        <span class="fw-bold">FEMA DECLARATION-CUM-UNDERTAKING:</span><br>
        I / We hereby declare that the statements made by us on this form are true and correct to the best of my/our knowledge and belief. I/We undertake to produce and deliver to the Authorized Dealer / Reserve Bank of India any document or record relating to the export of services specified above which may be required under the Foreign Exchange Management Act, 1999. I/We further declare that I/We am/are authorized to sign this declaration on behalf of the exporter.
    </div>

    <div class="legal-text">
        <span class="fw-bold">Declaration 4:</span><br>
        I / We hereby declare that I / We am / are the seller / exporter of the goods/services described above and that the particulars given above are true and correct. I / We further declare that the full value of the goods/services will be realized and repatriated to India through an Authorized Dealer in Foreign Exchange within the period prescribed under the Foreign Exchange Management Act, 1999 and rules/guidelines framed thereunder.
    </div>

    <div class="legal-text fw-bold">
        We are eligible to export the above mentioned Services under the current Foreign Trade policy in place.
    </div>

    <!-- Signatures & Authority Section -->
    <table class="bordered" style="margin-top: 6px;">
        <tr>
            <td width="20%" class="fw-bold">Remark (If Any)</td>
            <td width="80%">{{ $edf->remarks ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">For</td>
            <td>
                <span class="fw-bold">For {{ $company->trade_name ?? $company->legal_name }}</span><br><br><br>
                ___________________________________<br>
                <span class="fw-bold">AUTHORISED SIGNATORY</span><br>
                (Signature as per Bank record with company seal)
            </td>
        </tr>
        <tr>
            <td colspan="2" class="fw-bold section-header">
                5. Space for use of Specified Authority (Customs/SEZ/AD/STPI):
            </td>
        </tr>
    </table>

    <div class="authority-box">
        <div style="font-size: 8pt; line-height: 1.25; margin-bottom: 12px;">
            Certified, on the basis of above declaration at 4, that the goods/services described above and the export value declared by the exporter in this form is as per the corresponding invoice/gist of invoices submitted and declared by the exporter.
        </div>
        <div style="font-size: 8pt;">
            Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            (Signature of Designated/Authorised officials of Custom /SEZ/ Authorised Dealer/STPI)
        </div>
    </div>

    <div class="classification-footer">Classification - Internal</div>

</body>
</html>
