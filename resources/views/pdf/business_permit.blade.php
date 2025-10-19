<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Business Permit</title>
    <style>
        @page { size: A4; margin: 14mm; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #18384f; }
        .wrap { width: 100%; }

        /* Header */
        .header { display: block; width: 100%; }
        .header-left { display: inline-block; width: 65%; vertical-align: top; }
        .seal { display: inline-block; height: 64px; width: auto; margin-right: 12px; }
        .gov { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; opacity: .85; }
        .title { font-size: 22px; font-weight: 800; margin-top: 2px; color: #0c4a6e; }
        .subtitle { font-size: 12px; opacity: .8; }
        .year { display: block; text-align: right; vertical-align: middle; font-size: 40px; font-weight: 900; color: #0c4a6e; }
        .header-right { display: inline-block; width: 35%; text-align: right; vertical-align: middle; }
        .validity-pill { display: inline-block; margin-top: 6px; padding: 4px 10px; border-radius: 9999px; background: #0c4a6e; color: #fff; font-size: 11px; font-weight: 700; letter-spacing: .02em; }
        .divider { height: 2px; background: #0c4a6e; margin: 10px 0 12px; }

        /* Blocks */
        .box { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; margin-bottom: 10px; }
        .label { font-size: 11px; opacity: .7; }
        .value { font-size: 14px; font-weight: 700; }

        /* Grid (table-based for DomPDF robustness) */
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td.cell { padding: 8px 10px; border: 1px solid #e5e7eb; vertical-align: middle; }
        .cell-label { font-size: 11px; opacity: .7; display: block; }
        .cell-value { font-size: 13px; font-weight: 700; display: block; }

        /* Notice & Signatures */
        .notice { font-size: 12px; line-height: 1.5; opacity: .9; }
        .sign { margin-top: 14px; display: table; width: 100%; }
        .sign-col { display: table-cell; width: 50%; padding: 10px 12px; }
        .sign-name { font-size: 13px; font-weight: 800; color: #0c4a6e; }
        .sign-title { font-size: 11px; opacity: .8; }
        .sigpad { height: 60px; border-bottom: 1.5px dashed #0c4a6e; width: 70%; margin: 0 0 8px 0; }
        .stamp-box { display: inline-block; height: 96px; width: 96px; border: 2px dashed #0c4a6e; border-radius: 10px; margin-bottom: 6px; }

        /* Footer */
        .footer { margin-top: 12px; }
        .footer-bar { height: 24px; width: 100%; background: #0c4a6e; }
        .footer-text { font-size: 11px; color: #0c4a6e; text-align: center; margin-top: 8px; font-weight: 700; }
        .caption { font-size: 10px; text-align: center; opacity: .75; margin-top: 4px; }
    </style>
</head>
<body>
@php
    $primary = ($permit['addresses'] ?? []) ? $permit['addresses'][0] : null;
    $addrParts = [];
    if ($primary) {
        if (!empty($primary['line'])) $addrParts[] = $primary['line'];
        if (!empty($primary['barangay'])) $addrParts[] = $primary['barangay'];
        if (!empty($primary['city'])) $addrParts[] = $primary['city'];
        if (!empty($primary['province'])) $addrParts[] = $primary['province'];
    }
    $addressText = implode(', ', $addrParts);
    $issued = $permit['application_date'] ?? null;
    $issuedYear = $issued ? \Carbon\Carbon::parse($issued)->format('Y') : now()->format('Y');
    $status = strtoupper($permit['status'] ?? '');
    $validUntil = \Carbon\Carbon::create(2028, 12, 31)->format('F d, Y');
    $barangayName = $primary['barangay'] ?? '—';
    $cityName = $primary['city'] ?? '—';
    $provinceName = $primary['province'] ?? '—';
    $periodStart = $issued ? \Carbon\Carbon::parse($issued)->format('F d, Y') : now()->format('F d, Y');
    $periodEnd = $validUntil;
    $issuedDay = $issued ? \Carbon\Carbon::parse($issued)->format('d') : now()->format('d');
    $issuedMonth = $issued ? \Carbon\Carbon::parse($issued)->format('F') : now()->format('F');
    $issuedYearFull = $issued ? \Carbon\Carbon::parse($issued)->format('Y') : now()->format('Y');
@endphp

<div class="wrap">
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            @if (!empty($logoPath))
                <img src="{{ $logoPath }}" alt="Seal" class="seal" />
            @endif
            <div class="gov">Municipality of Tibiao, Antique</div>
            <div class="title">Business Permit</div>
            <div class="subtitle">Business Permits and Licensing Office</div>
        </div>
    </div>
    <div class="divider"></div>

    <!-- Permit preamble -->
    <div class="box" style="margin-top: 6px;">
        <div class="label">Business Permit Authorization</div>
        <div class="notice">
            <p>This permit authorizes the above-named proprietor to legally operate and conduct business activities within the territorial jurisdiction of Barangay {{ $barangayName }}, subject to compliance with all existing national and local laws, municipal and city ordinances, and barangay regulations governing business operations.</p>
            <p>The business establishment shall maintain orderliness, cleanliness, and peace within the community.</p>
            <p>This permit shall be valid for the period of {{ $periodStart }} to {{ $periodEnd }}, and must be renewed annually or as required by the Barangay Council. It may be suspended or revoked at any time should the business be found in violation of any barangay ordinance, municipal or city regulation, or national law.</p>
            <p>This permit must be displayed prominently within the business premises and presented upon request by authorized barangay or city officials.</p>
            <p>Issued this {{ $issuedDay }} day of {{ $issuedMonth }}, {{ $issuedYearFull }} at Barangay {{ $barangayName }}, {{ $cityName }}, {{ $provinceName }}.</p>
        </div>
    </div>

    <!-- Top blocks (only show if data exists) -->
    @if (!empty($permit['full_name']))
    <div class="box">
        <div class="label">Applicant / Taxpayer</div>
        <div class="value">{{ $permit['full_name'] }}</div>
    </div>
    @endif

    @if (!empty($addressText))
    <div class="box">
        <div class="label">Address</div>
        <div class="value">{{ $addressText }}</div>
    </div>
    @endif

    <!-- Detail grid: render cells only for available data -->
    <table class="grid" style="margin-top: 8px;">
        <tr>
            @if (!empty($issued))
            <td class="cell">
                <span class="cell-label">Date of Issue</span>
                <span class="cell-value">{{ \Carbon\Carbon::parse($issued)->format('F d, Y') }}</span>
            </td>
            @endif
            @if (!empty($permit['contact_number']))
            <td class="cell">
                <span class="cell-label">Contact Number</span>
                <span class="cell-value">{{ $permit['contact_number'] }}</span>
            </td>
            @endif
            @if (!empty($permit['id']))
            <td class="cell">
                <span class="cell-label">Permit ID</span>
                <span class="cell-value">{{ $permit['id'] }}</span>
            </td>
            @endif
            <td class="cell">
                <span class="cell-label">Valid Until</span>
                <span class="cell-value">{{ $validUntil }}</span>
            </td>
        </tr>
        <tr>
            @if (!empty($permit['gender']))
            <td class="cell">
                <span class="cell-label">Gender</span>
                <span class="cell-value">{{ $permit['gender'] }}</span>
            </td>
            @endif
            @if (!empty($permit['citizenship']))
            <td class="cell">
                <span class="cell-label">Citizenship</span>
                <span class="cell-value">{{ $permit['citizenship'] }}</span>
            </td>
            @endif
            @if (!empty($status))
            <td class="cell">
                <span class="cell-label">Status</span>
                <span class="cell-value">{{ $status }}</span>
            </td>
            @endif
        </tr>
    </table>

    <!-- Optional remarks -->
    @if (!empty($permit['remarks']))
    <div class="box" style="margin-top: 10px;">
        <div class="label">Remarks</div>
        <div class="notice">{{ $permit['remarks'] }}</div>
    </div>
    @endif

    <!-- Notice -->
    <div class="box" style="margin-top: 10px;">
        <div class="label">Notice</div>
        <div class="notice">
            This permit may be revoked if conditions and provisions set forth by ordinances and existing laws are violated, or if public safety, health, or security is at risk. Keep a printed copy of this permit visible at the business premises.
        </div>
    </div>

    <!-- Signatures -->
    <div class="sign">
        <div class="sign-col">
            <div class="sigpad"></div>
            <div class="sign-name">Klemens Bandoja</div>
            <div class="sign-title">Mayor Klemens "Weng Weng" Bandoja</div>
        </div>
        <div class="sign-col" style="text-align: right;">
            <div class="sign-name">Officer-in-Charge</div>
            <div class="sign-title">Business Permits and Licensing Office</div>
        </div>


    <!-- Footer -->
    <div class="footer">
        <div class="footer-bar"></div>
    </div>
</div>
</body>
</html>