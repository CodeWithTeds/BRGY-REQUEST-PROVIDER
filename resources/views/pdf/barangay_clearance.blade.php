<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Barangay Clearance</title>
    <style>
        @page { size: A4; margin: 16mm; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; }

        .page { border: 3px solid #000; padding: 14mm; }

        /* Header */
        .header { text-align: center; margin-bottom: 8mm; }
        .header-top { font-size: 12px; line-height: 1.4; }
        .barangay-name { font-size: 24px; font-weight: 700; color: #0a4b78; margin-top: 2mm; }
        .rule { height: 1px; background: #000; margin: 6mm 0 4mm; }

        .seal { display: inline-block; height: 54px; width: auto; position: relative; top: -6px; margin-right: 6px; }

        /* Document titles */
        .office { text-align: center; font-weight: 700; margin-top: 1mm; }
        .doc-title { text-align: center; font-size: 18px; font-weight: 800; margin: 2mm 0 6mm; }

        /* Body */
        .to-whom { font-weight: 700; margin-bottom: 3mm; }
        .paragraph { font-size: 13px; line-height: 1.65; text-align: justify; margin: 0 0 4mm; }

        /* Signature */
        .signature { margin-top: 10mm; display: block; }
        .signature .name { font-weight: 700; }
        .signature .title { font-size: 12px; opacity: 0.8; }
        .sig-line { margin-top: 12mm; border-bottom: 1px dashed #444; width: 220px; }
        .sig-block { text-align: right; }

        /* Footer meta */
        .meta { margin-top: 14mm; font-size: 12px; }
        .meta td { padding: 2mm 4mm 0 0; }
    </style>
</head>
<body>
@php
    $primary = ($clearance['addresses'] ?? []) ? $clearance['addresses'][0] : null;
    $barangay = $primary['barangay'] ?? '—';
    $city = $primary['city'] ?? '—';
    $province = $primary['province'] ?? '—';
    $fullName = $clearance['full_name'] ?? '—';
    $dob = $clearance['applicant_profile']['date_of_birth'] ?? null;
    $age = $dob ? \Carbon\Carbon::parse($dob)->age : '—';
    $gender = strtolower($clearance['gender'] ?? '');
    $pronoun = $gender === 'male' ? 'he' : ($gender === 'female' ? 'she' : 'he/she');
    $issueDate = $clearance['issue_date'] ?? ($clearance['application_date'] ?? null);
    $issuedDay = $issueDate ? \Carbon\Carbon::parse($issueDate)->format('d') : now()->format('d');
    $issuedMonth = $issueDate ? \Carbon\Carbon::parse($issueDate)->format('F') : now()->format('F');
    $issuedYear = $issueDate ? \Carbon\Carbon::parse($issueDate)->format('Y') : now()->format('Y');
    $orNumber = $clearance['clearance_number'] ?? '____________________';
@endphp

<div class="page">
    <div class="header">
        @if (!empty($logoPath))
            <img src="{{ $logoPath }}" alt="Barangay Seal" class="seal" />
        @endif
        <div class="header-top">
            Republic of the Philippines<br/>
            MUNICIPALITY OF TIBIAO, ANTIQUE
        </div>

        <div class="rule"></div>
    </div>

    <div class="office">OFFICE OF THE BARANGAY CAPTAIN</div>
    <div class="doc-title">BARANGAY CLEARANCE</div>

    <div class="to-whom">TO WHOM IT MAY CONCERN:</div>

    <p class="paragraph">
        This is to certify that <strong>{{ $fullName }}</strong>, <strong>{{ $age }}</strong> years old,
        and a resident of Barangay {{ $barangay }}, {{ $city }}, {{ $province }}, is known to be of good moral character
        and a law-abiding citizen in the community.
    </p>
    <p class="paragraph">
        To certify further, that {{ $pronoun }} has no derogatory and/or criminal records filed in this barangay.
    </p>
    <p class="paragraph">
        <strong>ISSUED</strong> this <strong>{{ $issuedDay }}</strong> day of <strong>{{ $issuedMonth }}</strong>,
        <strong>{{ $issuedYear }}</strong> at Barangay {{ $barangay }}, {{ $city }}, {{ $province }}, upon request of the interested party
        for whatever legal purposes it may serve.
    </p>

    <div class="signature sig-block">
        <div class="sig-line"></div>
        <div class="name">Barangay Captain</div>
        <div class="title">Barangay {{ $barangay }}</div>
    </div>

    <table class="meta">
        <tr>
            <td>O.R. No.: {{ $orNumber }}</td>
            <td>Date Issued: {{ $issueDate ?? '____________________' }}</td>
            <td>Doc. Stamp: Paid</td>
        </tr>
    </table>
</div>
</body>
</html>