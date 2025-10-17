<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Certificate of Residency – Letter</title>
    <style>
        /* Page setup */
        @page { size: A4; margin: 18mm; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #2C4854; }

        /* Letter canvas */
        .letter { width: 100%; }

        /* Curved header line */
        .curve { height: 36px; width: 100%; background: #0a4b78; border-bottom-left-radius: 36px; border-bottom-right-radius: 36px; }

        /* Header content */
        .header-content { margin-top: 16px; display: table; width: 100%; }
        .header-left { display: table-cell; vertical-align: middle; }
        .seal { display: inline-block; height: 54px; width: auto; margin-right: 10px; }
        .company-name { font-size: 18px; font-weight: 700; color: #0a4b78; }
        .company-tagline { font-size: 12px; color: #126d99; }

        /* Body */
        .content { margin-top: 18px; }
        .to-label { font-size: 12px; opacity: 0.7; }
        .to-name { font-size: 16px; font-weight: 700; color: #0a4b78; }
        .to-sub { font-size: 12px; color: #126d99; }
        .to-address { font-size: 12px; opacity: 0.85; }

        .date-label { font-size: 12px; opacity: 0.7; margin-top: 4px; }
        .date-value { font-size: 14px; font-weight: 600; }

        .greeting { font-weight: 600; margin-top: 12px; }

        /* Details table */
        .details { margin-top: 14px; width: 100%; border-collapse: collapse; }
        .details td { padding: 6px 8px; border: 1px solid #d1d5db; font-size: 12px; }
        .details .label { opacity: 0.7; }
        .details .value { font-weight: 600; }

        /* From/signature */
        .from { margin-top: 24px; }
        .from-name { font-weight: 700; color: #126d99; }
        .from-title { font-size: 12px; opacity: 0.8; }
        .signature-line { margin-top: 18px; font-size: 12px; opacity: 0.6; }

        /* Footer */
        .footer { margin-top: 12px; }
        .footer-bar { height: 24px; width: 100%; background: #0a4b78; }
        .footer-content { font-size: 11px; color: #fff; margin-top: 6px; display: table; width: 100%; }
        .footer-content > div { display: table-cell; width: 33%; }
    </style>
    <!-- Ensure image path is local filesystem for DomPDF -->
</head>
<body>
    <div class="letter">
        <!-- Single curved header line -->
        <div class="curve"></div>

        <!-- Header content -->
        <div class="header-content">
            <div class="header-left">
                @if (!empty($logoPath))
                    <img src="{{ $logoPath }}" alt="Barangay Seal" class="seal" />
                @endif
                <span class="company-name">Barangay Office</span><br/>
                <span class="company-tagline">Certificate of Residency</span>
            </div>
        </div>

        <!-- Body -->
        @php 
            $primary = ($certificate['addresses'] ?? []) ? $certificate['addresses'][0] : null; 
            $addressText = '';
            if ($primary) {
                $parts = [];
                if (!empty($primary['line'])) { $parts[] = $primary['line']; }
                if (!empty($primary['barangay'])) { $parts[] = $primary['barangay']; }
                if (!empty($primary['city'])) { $parts[] = $primary['city']; }
                if (!empty($primary['province'])) { $parts[] = $primary['province']; }
                $addressText = implode(', ', $parts);
            }
        @endphp
        <div class="content">
            <div class="to-label">To</div>
            <div class="to-name">{{ $certificate['full_name'] ?? '—' }}</div>
            <div class="to-sub">Applicant</div>
            @if (!empty($addressText))
            <div class="to-address">{{ $addressText }}</div>
            @endif

            <div class="date-label">Date</div>
            <div class="date-value">{{ $certificate['application_date'] ?? now()->format('m/d/Y') }}</div>

            <div class="greeting">Dear Sir/Madam,</div>
            <p class="paragraph">
                This letter certifies that the individual named above is a resident of the barangay listed below, according to our records. Please keep a printed copy of this certificate for your reference.
            </p>

            <table class="details">
                <tr>
                    <td class="label">Residency ID</td>
                    <td class="value">#{{ $certificate['id'] ?? '—' }}</td>
                    <td class="label">Barangay</td>
                    <td class="value">{{ $primary['barangay'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">City/Municipality</td>
                    <td class="value">{{ $primary['city'] ?? '—' }}</td>
                    <td class="label">Province</td>
                    <td class="value">{{ $primary['province'] ?? '—' }}</td>
                </tr>
            </table>

            <p class="paragraph">
                If any information is incorrect, please contact your barangay office to update your records. Thank you for your cooperation.
            </p>

            <div class="from">
                <div class="from-name">Barangay Captain</div>
                <div class="from-title">Office of the Barangay</div>
                <div class="signature-line">Signature</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-bar"></div>
            <div class="footer-content">
                <div>+63 700 00 000</div>
                <div>www.barangay.gov</div>
                <div>{{ $primary['city'] ?? 'Metro Manila' }}, Philippines</div>
            </div>
        </div>
    </div>
</body>
</html>