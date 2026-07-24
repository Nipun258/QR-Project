<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report — {{ $reportDate->format('d F Y') }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ── Header ── */
        .report-header {
            background: #1e1b4b;
            color: #ffffff;
            padding: 24px 28px;
            margin-bottom: 24px;
        }

        .report-header .brand-row {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .brand-dot {
            width: 14px;
            height: 14px;
            background: #6366f1;
            border-radius: 3px;
            display: inline-block;
            margin-right: 8px;
        }

        .brand-name {
            font-size: 16px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.04em;
        }

        .report-title {
            font-size: 22px;
            font-weight: bold;
            color: #e0e7ff;
            margin-bottom: 4px;
        }

        .report-sub {
            font-size: 11px;
            color: #a5b4fc;
        }

        /* ── Summary Boxes ── */
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 14px 10px;
            border-radius: 8px;
        }

        .summary-box.total  { background: #f1f5f9; border: 1px solid #cbd5e1; }
        .summary-box.present { background: #d1fae5; border: 1px solid #6ee7b7; }
        .summary-box.rejected { background: #fee2e2; border: 1px solid #fca5a5; }

        .summary-val {
            font-size: 26px;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }

        .summary-box.total .summary-val   { color: #1e293b; }
        .summary-box.present .summary-val  { color: #059669; }
        .summary-box.rejected .summary-val { color: #dc2626; }

        .summary-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-weight: bold;
            color: #64748b;
        }

        /* ── Table ── */
        .section-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #6366f1;
            margin-bottom: 6px;
            padding-left: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #1e1b4b;
            color: #ffffff;
        }

        thead th {
            padding: 9px 10px;
            text-align: left;
            font-size: 10px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            color: #334155;
        }

        .reg-pill {
            background: #e2e8f0;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
            color: #1e293b;
        }

        .badge-present {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
        }

        .empty-row td {
            text-align: center;
            padding: 30px;
            color: #94a3b8;
            font-style: italic;
        }

        /* ── Footer ── */
        .report-footer {
            margin-top: 28px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            color: #94a3b8;
            font-size: 9.5px;
            display: flex;
            justify-content: space-between;
        }

        .spacer { width: 12px; display: inline-block; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="report-header">
        <div class="brand-row">
            <span class="brand-dot"></span>
            <span class="brand-name">ScanGuard Attendance System</span>
        </div>
        <div class="report-title">Attendance Report</div>
        <div class="report-sub">
            {{ $reportDate->format('l, d F Y') }}
            &nbsp;&bull;&nbsp;
            Generated: {{ $generatedAt }}
        </div>
    </div>

    <!-- Summary -->
    <table class="summary-row" style="margin: 0 0 20px 0; border-collapse: separate; border-spacing: 10px;">
        <tr>
            <td class="summary-box total">
                <span class="summary-val">{{ $records->count() }}</span>
                <span class="summary-label">Total Scans</span>
            </td>
            <td class="summary-box present">
                <span class="summary-val">{{ $presentCount }}</span>
                <span class="summary-label">✓ Present</span>
            </td>
            <td class="summary-box rejected">
                <span class="summary-val">{{ $rejectedCount }}</span>
                <span class="summary-label">✗ Rejected</span>
            </td>
        </tr>
    </table>

    <!-- Table -->
    <div class="section-label">Scan Records</div>
    <table>
        <thead>
            <tr>
                <th style="width: 28px;">#</th>
                <th style="width: 68px;">Time</th>
                <th style="width: 72px;">Reg No</th>
                <th>Student Name</th>
                <th>Department</th>
                <th style="width: 72px;">Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $i => $record)
                <tr>
                    <td style="color: #94a3b8; font-size: 10px;">{{ $i + 1 }}</td>
                    <td style="font-size: 10px; color: #64748b;">{{ $record->scanned_at->format('h:i:s A') }}</td>
                    <td><span class="reg-pill">{{ $record->register_number }}</span></td>
                    <td style="font-weight: bold;">{{ $record->student ? $record->student->name : 'Unknown User' }}</td>
                    <td style="color: #64748b;">{{ $record->student ? $record->student->department : 'N/A' }}</td>
                    <td>
                        @if($record->status === 'PRESENT')
                            <span class="badge-present">PRESENT</span>
                        @else
                            <span class="badge-rejected">REJECTED</span>
                        @endif
                    </td>
                    <td style="color: #94a3b8; font-size: 10px;">{{ $record->remarks ?? '—' }}</td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="7">No attendance records found for this date.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="report-footer">
        <span>ScanGuard Automated QR &amp; Smart Card Attendance System</span>
        <span>Report Date: {{ $reportDate->format('d/m/Y') }} &nbsp;&bull;&nbsp; Generated: {{ $generatedAt }}</span>
    </div>

</body>
</html>
