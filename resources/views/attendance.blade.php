<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Automated QR / Card Attendance System</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-primary: #090d16;
            --bg-card: rgba(17, 24, 39, 0.75);
            --border-color: rgba(255, 255, 255, 0.08);
            --accent-glow: rgba(99, 102, 241, 0.15);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            
            --success-color: #10b981;
            --success-bg: rgba(16, 185, 129, 0.15);
            --success-border: rgba(16, 185, 129, 0.4);
            
            --error-color: #ef4444;
            --error-bg: rgba(239, 68, 68, 0.15);
            --error-border: rgba(239, 68, 68, 0.4);

            --info-color: #38bdf8;
            --info-bg: rgba(56, 189, 248, 0.12);
            --info-border: rgba(56, 189, 248, 0.4);

            --primary-indigo: #6366f1;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-primary);
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(99, 102, 241, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(16, 185, 129, 0.05) 0%, transparent 45%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Top Header */
        header {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .brand-title {
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-subtitle {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .live-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 0.4rem 0.8rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--success-color);
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: var(--success-color);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            flex: 1;
        }

        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }
        }

        /* Glass Card */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .card-title i {
            color: var(--primary-indigo);
        }

        /* Stats Bar */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1rem;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-top: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Scanner Focus Target Box */
        .scanner-target-box {
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.5) 0%, rgba(15, 23, 42, 0.8) 100%);
            border: 2px dashed rgba(99, 102, 241, 0.4);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .scanner-target-box.focused {
            border-color: var(--primary-indigo);
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.25);
            background: linear-gradient(180deg, rgba(49, 46, 129, 0.3) 0%, rgba(15, 23, 42, 0.9) 100%);
        }

        .scanner-icon-wrap {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            font-size: 1.5rem;
            color: var(--primary-indigo);
            animation: beam 2s infinite alternate;
        }

        @keyframes beam {
            0% { box-shadow: 0 0 5px rgba(99, 102, 241, 0.2); }
            100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.8); }
        }

        /* Scanner input is fully invisible — off-screen capture only */
        .scanner-input {
            position: fixed;
            top: -9999px;
            left: -9999px;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .scanner-status-text {
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.05em;
            color: #ffffff;
            margin-bottom: 0.3rem;
        }

        .scanner-sub-text {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Result Display Box */
        .result-card {
            border-radius: 16px;
            padding: 1.75rem;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.02);
            position: relative;
            overflow: hidden;
        }

        /* Default Idle State */
        .result-card.idle {
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Active / Eligible State */
        .result-card.active {
            background: var(--success-bg);
            border: 2px solid var(--success-color);
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.25);
            animation: bounceIn 0.4s ease;
        }

        /* Inactive / Ineligible State */
        .result-card.inactive {
            background: var(--error-bg);
            border: 2px solid var(--error-color);
            box-shadow: 0 0 40px rgba(239, 68, 68, 0.25);
            animation: shake 0.4s ease;
        }

        /* Duplicate / Already Scanned State */
        .result-card.duplicate {
            background: var(--info-bg);
            border: 2px solid var(--info-color);
            box-shadow: 0 0 40px rgba(56, 189, 248, 0.2);
            animation: bounceIn 0.4s ease;
        }

        .result-card.duplicate .result-status-badge {
            background: var(--info-color);
            color: #000000;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.5);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.92); opacity: 0; }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }

        .result-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .result-card.active .result-status-badge {
            background: var(--success-color);
            color: #000000;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.5);
        }

        .result-card.inactive .result-status-badge {
            background: var(--error-color);
            color: #ffffff;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.5);
        }

        .student-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            width: 100%;
            text-align: left;
            margin-top: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .student-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 800;
            color: #ffffff;
            flex-shrink: 0;
        }

        .student-info {
            flex: 1;
            overflow: hidden;
        }

        .student-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .student-reg {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
        }

        .student-dept {
            font-size: 0.8rem;
            color: #cbd5e1;
            margin-top: 0.15rem;
        }

        /* Simulator / Quick Testing Panel */
        .simulator-box {
            margin-top: 1.5rem;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1.25rem;
        }

        .simulator-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sim-btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .sim-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 0.5rem 0.85rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-family: 'JetBrains Mono', monospace;
        }

        .sim-btn:hover {
            background: var(--primary-indigo);
            border-color: var(--primary-indigo);
            color: white;
            transform: translateY(-1px);
        }

        .sim-btn.inactive-btn:hover {
            background: var(--error-color);
            border-color: var(--error-color);
        }

        /* Attendance Feed Table */
        .table-wrap {
            overflow-x: auto;
            max-height: 480px;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.875rem;
        }

        th {
            background: rgba(15, 23, 42, 0.9);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 0.85rem 1rem;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: var(--text-main);
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .reg-pill {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.06);
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .status-badge.PRESENT {
            background: var(--success-bg);
            color: var(--success-color);
            border: 1px solid var(--success-border);
        }

        .status-badge.REJECTED {
            background: var(--error-bg);
            color: var(--error-color);
            border: 1px solid var(--error-border);
        }

        /* Loading Spinner */
        .spinner {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <div class="brand-title">ScanGuard Attendance</div>
                <div class="brand-subtitle">Automated QR & Smart Card Attendance Gate</div>
            </div>
        </div>

        <div class="live-badge">
            <div class="pulse-dot"></div>
            SYSTEM LIVE & MONITORING
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="container">
        
        <!-- Left Column: Scanner Receiver & Status Screen -->
        <div>
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value" id="stat-total">{{ $stats['total_students'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Active Eligible</div>
                    <div class="stat-value" style="color: var(--success-color);" id="stat-active">{{ $stats['active_students'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Today Scans</div>
                    <div class="stat-value" style="color: var(--primary-indigo);" id="stat-today">{{ $stats['today_attendance'] }}</div>
                </div>
            </div>

            <!-- Scanner Receiver Box -->
            <div class="glass-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-barcode"></i> Automated Hardware Scanner Listener
                    </div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); font-family: 'JetBrains Mono', monospace;" id="clock-display">00:00:00 AM</span>
                </div>

                <div class="scanner-target-box focused" id="scanner-box" onclick="document.getElementById('scanner-input').focus()">
                    <div class="scanner-icon-wrap">
                        <i class="fa-solid fa-expand"></i>
                    </div>
                    <div class="scanner-status-text">SCAN CARD NOW</div>
                    <div class="scanner-sub-text">Point QR / barcode scanner here &mdash; auto-submits instantly on scan.</div>

                    <!-- Scanner input: completely invisible, always-focused, captures hardware scanner output -->
                    <form id="scan-form" onsubmit="return false;">
                        <input type="text"
                               id="scanner-input"
                               class="scanner-input"
                               autocomplete="off"
                               autofocus
                               spellcheck="false"
                               tabindex="-1"
                               aria-hidden="true">
                    </form>
                </div>

                <!-- Result Status Display Area -->
                <div class="result-card idle" id="result-card">
                    <div id="result-idle-view">
                        <i class="fa-solid fa-id-card-clip" style="font-size: 2.5rem; color: rgba(255,255,255,0.2); margin-bottom: 0.75rem;"></i>
                        <p style="color: var(--text-muted); font-weight: 500;">Awaiting scan input...</p>
                    </div>

                    <div id="result-active-view" style="display: none; width: 100%;">
                        <div class="result-status-badge">
                            <i class="fa-solid fa-circle-check"></i> USER ACTIVE
                        </div>
                        <p style="color: var(--success-color); font-weight: 600; margin-bottom: 0.5rem;" id="active-msg">Attendance Logged Successfully</p>
                        <p style="font-size:0.75rem; color: var(--text-muted); margin-bottom:0.5rem; font-family:'JetBrains Mono',monospace;" id="active-raw-match"></p>

                        <div class="student-profile">
                            <div class="student-avatar" id="active-avatar">AJ</div>
                            <div class="student-info">
                                <div class="student-name" id="active-name">Alex Johnson</div>
                                <div class="student-reg" id="active-reg">500</div>
                                <div class="student-dept" id="active-dept">Computer Science & Eng</div>
                            </div>
                        </div>
                    </div>

                    <div id="result-duplicate-view" style="display: none; width: 100%;">
                        <div class="result-status-badge">
                            <i class="fa-solid fa-clock-rotate-left"></i> ALREADY CHECKED IN
                        </div>
                        <p style="color: var(--info-color); font-weight: 600; margin-bottom: 0.5rem;" id="duplicate-msg">Already marked present today.</p>
                        <p style="font-size:0.75rem; color: var(--text-muted); margin-bottom:0.5rem; font-family:'JetBrains Mono',monospace;" id="duplicate-time"></p>

                        <div class="student-profile">
                            <div class="student-avatar" id="duplicate-avatar" style="background: var(--info-color); color:#000;">AJ</div>
                            <div class="student-info">
                                <div class="student-name" id="duplicate-name">Student Name</div>
                                <div class="student-reg" id="duplicate-reg">Reg No</div>
                                <div class="student-dept" id="duplicate-dept">Department</div>
                            </div>
                        </div>
                    </div>

                    <div id="result-inactive-view" style="display: none; width: 100%;">
                        <div class="result-status-badge">
                            <i class="fa-solid fa-circle-xmark"></i> USER NOT ACTIVE
                        </div>
                        <p style="color: var(--error-color); font-weight: 600; margin-bottom: 0.5rem;" id="inactive-msg">Account Status: Inactive / Suspended</p>
                        <p style="font-size:0.75rem; color: var(--text-muted); margin-bottom:0.5rem; font-family:'JetBrains Mono',monospace;" id="inactive-raw-match"></p>

                        <div class="student-profile" id="inactive-profile-box">
                            <div class="student-avatar" id="inactive-avatar" style="background: var(--error-color);">?</div>
                            <div class="student-info">
                                <div class="student-name" id="inactive-name">Michael Scott</div>
                                <div class="student-reg" id="inactive-reg">505</div>
                                <div class="student-dept" id="inactive-dept">Business Administration</div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <!-- Right Column: Live Attendance Log Feed -->
        <div>
            <div class="glass-card" style="height: 100%; display: flex; flex-direction: column;">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-clock-rotate-left"></i> Live Attendance Feed
                    </div>
                    <button class="sim-btn" onclick="fetchRecentLogs()" style="padding: 0.35rem 0.75rem;">
                        <i class="fa-solid fa-rotate-right"></i> Refresh
                    </button>
                </div>

                <!-- Date Banner -->
                <div style="
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    background: rgba(99, 102, 241, 0.08);
                    border: 1px solid rgba(99, 102, 241, 0.2);
                    border-radius: 12px;
                    padding: 0.75rem 1rem;
                    margin-bottom: 1rem;
                ">
                    <i class="fa-solid fa-calendar-day" style="color: var(--primary-indigo); font-size: 1.1rem;"></i>
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600;">Showing Records For</div>
                        <div style="font-size: 1rem; font-weight: 700; color: #ffffff;" id="feed-date-display">
                            {{ now()->format('l, d F Y') }}
                        </div>
                    </div>
                    <div style="margin-left: auto; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--primary-indigo); font-weight: 600;" id="feed-record-count">
                        {{ $recentAttendances->count() }} record(s)
                    </div>
                </div>

                <div class="table-wrap" style="flex: 1;">
                    <table>
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Reg No</th>
                                <th>Student Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="logs-tbody">
                            @forelse($recentAttendances as $log)
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 0.8rem;">{{ $log->scanned_at->format('h:i:s A') }}</td>
                                    <td><span class="reg-pill">{{ $log->register_number }}</span></td>
                                    <td>
                                        <div style="font-weight: 600;">{{ $log->student ? $log->student->name : 'Unknown User' }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $log->student ? $log->student->department : 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $log->status }}">
                                            @if($log->status === 'PRESENT')
                                                <i class="fa-solid fa-check"></i> PRESENT
                                            @else
                                                <i class="fa-solid fa-xmark"></i> REJECTED
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                        No attendance scans recorded today yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Report Generation Panel -->
                <div style="
                    margin-top: 1.25rem;
                    padding-top: 1.25rem;
                    border-top: 1px solid var(--border-color);
                ">
                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-file-arrow-down" style="color: #a78bfa;"></i>
                        GENERATE ATTENDANCE REPORT
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 160px;">
                            <label style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; display: block; margin-bottom: 0.35rem;">Select Date</label>
                            <input
                                type="date"
                                id="report-date-picker"
                                value="{{ now()->toDateString() }}"
                                max="{{ now()->toDateString() }}"
                                style="
                                    width: 100%;
                                    background: rgba(0,0,0,0.4);
                                    border: 1px solid rgba(167, 139, 250, 0.3);
                                    border-radius: 8px;
                                    color: #e2e8f0;
                                    padding: 0.5rem 0.75rem;
                                    font-size: 0.875rem;
                                    font-family: 'JetBrains Mono', monospace;
                                    outline: none;
                                    cursor: pointer;
                                    color-scheme: dark;
                                "
                                onfocus="this.style.borderColor='#a78bfa'"
                                onblur="this.style.borderColor='rgba(167,139,250,0.3)'"
                            >
                        </div>
                        <div style="padding-top: 1.35rem;">
                            <button
                                id="download-report-btn"
                                onclick="downloadReport()"
                                style="
                                    background: linear-gradient(135deg, #7c3aed, #4f46e5);
                                    border: none;
                                    color: white;
                                    padding: 0.5rem 1.1rem;
                                    border-radius: 8px;
                                    font-size: 0.85rem;
                                    font-weight: 700;
                                    cursor: pointer;
                                    display: flex;
                                    align-items: center;
                                    gap: 0.5rem;
                                    white-space: nowrap;
                                    transition: opacity 0.2s, transform 0.15s;
                                    box-shadow: 0 0 16px rgba(124, 58, 237, 0.35);
                                "
                                onmouseover="this.style.opacity='0.88'; this.style.transform='translateY(-1px)'"
                                onmouseout="this.style.opacity='1'; this.style.transform='translateY(0)'"
                            >
                                <i class="fa-solid fa-file-csv"></i> Download CSV
                            </button>
                        </div>
                        <div style="padding-top: 1.35rem;">
                            <button
                                id="download-pdf-btn"
                                onclick="downloadReportPdf()"
                                style="
                                    background: linear-gradient(135deg, #dc2626, #b91c1c);
                                    border: none;
                                    color: white;
                                    padding: 0.5rem 1.1rem;
                                    border-radius: 8px;
                                    font-size: 0.85rem;
                                    font-weight: 700;
                                    cursor: pointer;
                                    display: flex;
                                    align-items: center;
                                    gap: 0.5rem;
                                    white-space: nowrap;
                                    transition: opacity 0.2s, transform 0.15s;
                                    box-shadow: 0 0 16px rgba(220, 38, 38, 0.35);
                                "
                                onmouseover="this.style.opacity='0.88'; this.style.transform='translateY(-1px)'"
                                onmouseout="this.style.opacity='1'; this.style.transform='translateY(0)'"
                            >
                                <i class="fa-solid fa-file-pdf"></i> Download PDF
                            </button>
                        </div>
                    </div>
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.5rem;">
                        <i class="fa-solid fa-circle-info" style="margin-right:0.3rem;"></i>
                        Downloads all scan records (PRESENT &amp; REJECTED) for the selected date as CSV or PDF.
                    </p>
                </div>
            </div>
        </div>


    </div>

    <!-- JavaScript Logic for Hardware Scanner & Audio Synthesis -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const scannerInput = document.getElementById('scanner-input');
        const scannerBox = document.getElementById('scanner-box');
        const resultCard = document.getElementById('result-card');
        
        const resultIdleView = document.getElementById('result-idle-view');
        const resultActiveView = document.getElementById('result-active-view');
        const resultDuplicateView = document.getElementById('result-duplicate-view');
        const resultInactiveView = document.getElementById('result-inactive-view');

        let isProcessing = false;
        let resetTimer = null;
        let lastScannedValue = null;
        let lastScannedTime = 0;
        const DUPLICATE_GUARD_MS = 3000; // ignore same scan within 3 seconds

        // Web Audio API Synthesizer Sound Effects
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

        function playDuplicateSound() {
            try {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(440, audioCtx.currentTime);      // A4
                osc.frequency.setValueAtTime(440, audioCtx.currentTime + 0.12);
                osc.frequency.setValueAtTime(0,   audioCtx.currentTime + 0.18);
                osc.frequency.setValueAtTime(440, audioCtx.currentTime + 0.25); // second beep

                gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);

                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.5);
            } catch (e) {
                console.log('Audio error');
            }
        }

        function playSuccessSound() {
            try {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const osc1 = audioCtx.createOscillator();
                const osc2 = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc1.type = 'sine';
                osc2.type = 'sine';

                osc1.frequency.setValueAtTime(523.25, audioCtx.currentTime); // C5
                osc1.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.1); // E5
                osc1.frequency.setValueAtTime(783.99, audioCtx.currentTime + 0.2); // G5

                gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);

                osc1.connect(gain);
                gain.connect(audioCtx.destination);

                osc1.start();
                osc1.stop(audioCtx.currentTime + 0.5);
            } catch (e) {
                console.log('Audio playback prevented or unsupported');
            }
        }

        function playFailureSound() {
            try {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(180, audioCtx.currentTime);
                osc.frequency.setValueAtTime(140, audioCtx.currentTime + 0.15);

                gain.gain.setValueAtTime(0.4, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.4);

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                osc.start();
                osc.stop(audioCtx.currentTime + 0.4);
            } catch (e) {
                console.log('Audio error');
            }
        }

        // Automatic Input Focus Keeper
        function keepFocus() {
            if (document.activeElement !== scannerInput) {
                scannerInput.focus();
            }
        }

        window.addEventListener('click', keepFocus);
        document.addEventListener('keydown', (e) => {
            if (document.activeElement !== scannerInput) {
                scannerInput.focus();
            }
        });
        setInterval(keepFocus, 1000);

        // Real-time Clock Display
        function updateClock() {
            const now = new Date();
            document.getElementById('clock-display').innerText = now.toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Live char count + debounce auto-submit
        // Hardware scanners blast all chars in <100ms then stop.
        // We fire processScan 150ms after the last character received.
        let debounceTimer = null;
        const SCAN_DEBOUNCE_MS = 150;

        scannerInput.addEventListener('input', function () {
            const len = this.value.length;

            // Reset debounce timer on every keystroke
            if (debounceTimer) clearTimeout(debounceTimer);

            if (len > 0) {
                debounceTimer = setTimeout(() => {
                    const val = scannerInput.value.trim();
                    clearScanInput();
                    if (val.length > 0 && !isProcessing) {
                        processScan(val);
                    }
                }, SCAN_DEBOUNCE_MS);
            }
        });

        // Keep Enter as a manual fallback
        scannerInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (debounceTimer) clearTimeout(debounceTimer);
                const val = this.value.trim();
                clearScanInput();
                if (val.length > 0 && !isProcessing) {
                    processScan(val);
                }
            }
        });

        function clearScanInput() {
            scannerInput.value = '';
        }

        // Simulator button helper
        function simulateScan(regNo) {
            clearScanInput();
            processScan(regNo);
        }

        // Process Scan API Request
        async function processScan(regNo) {
            // Duplicate guard: block same value scanned within DUPLICATE_GUARD_MS
            const now = Date.now();
            if (regNo === lastScannedValue && (now - lastScannedTime) < DUPLICATE_GUARD_MS) {
                const remaining = Math.ceil((DUPLICATE_GUARD_MS - (now - lastScannedTime)) / 1000);
                console.log(`Duplicate scan blocked for [${regNo}] — wait ${remaining}s`);
                clearScanInput();
                return;
            }
            lastScannedValue = regNo;
            lastScannedTime = now;

            isProcessing = true;
            scannerBox.classList.add('focused');
            
            // Show scanning state in result card
            resultCard.className = 'result-card idle';
            resultIdleView.innerHTML = `
                <div class="spinner" style="margin-bottom: 0.75rem;"></div>
                <p style="color: #ffffff; font-weight: 600;">Verifying Eligibility for [${regNo}]...</p>
            `;
            resultIdleView.style.display = 'block';
            resultActiveView.style.display = 'none';
            resultDuplicateView.style.display = 'none';
            resultInactiveView.style.display = 'none';

            try {
                const response = await fetch('/api/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ register_number: regNo })
                });

                const data = await response.json();

                if (data.status === 'ALREADY_SCANNED') {
                    renderAlreadyScanned(data);
                } else if (data.eligible && data.success) {
                    renderActiveSuccess(data);
                } else {
                    renderInactiveFailure(data);
                }

                // Refresh table logs and stat counters
                fetchRecentLogs();

            } catch (err) {
                renderInactiveFailure({
                    title: 'User Not Active',
                    message: 'Server error or network timeout checking register number.',
                    student: null,
                    register_number: regNo
                });
            } finally {
                isProcessing = false;
                clearScanInput();
                keepFocus();

                // Auto reset display back to idle after 4 seconds
                if (resetTimer) clearTimeout(resetTimer);
                resetTimer = setTimeout(() => {
                    resetResultCard();
                    // Clear duplicate guard so a re-scan after reset is allowed
                    lastScannedValue = null;
                    lastScannedTime = 0;
                }, 4500);
            }
        }

        function renderActiveSuccess(data) {
            playSuccessSound();
            resultCard.className = 'result-card active';
            
            resultIdleView.style.display = 'none';
            resultDuplicateView.style.display = 'none';
            resultInactiveView.style.display = 'none';
            resultActiveView.style.display = 'block';

            document.getElementById('active-msg').innerText = data.message;
            document.getElementById('active-name').innerText = data.student.name;
            document.getElementById('active-reg').innerText = data.student.register_number;
            document.getElementById('active-dept').innerText = data.student.department;

            // Show raw → matched register number debug line
            const rawEl = document.getElementById('active-raw-match');
            if (data.raw_value && data.raw_value !== data.student.register_number) {
                rawEl.innerHTML = `<i class="fa-solid fa-arrow-right-arrow-left" style="margin-right:0.35rem;"></i>Scanned: "${data.raw_value}" → Matched: "${data.student.register_number}"`;
            } else {
                rawEl.innerHTML = `<i class="fa-solid fa-check-circle" style="margin-right:0.35rem;color:#10b981"></i>Matched register: "${data.student.register_number}"`;
            }

            // Generate Initials Avatar
            const initials = data.student.name.split(' ').map(n => n[0]).join('').substring(0, 2);
            const avatarEl = document.getElementById('active-avatar');
            avatarEl.innerText = initials;
            avatarEl.style.backgroundColor = data.student.avatar_color || '#10b981';
        }

        function renderAlreadyScanned(data) {
            playDuplicateSound();
            resultCard.className = 'result-card duplicate';

            resultIdleView.style.display = 'none';
            resultActiveView.style.display = 'none';
            resultInactiveView.style.display = 'none';
            resultDuplicateView.style.display = 'block';

            document.getElementById('duplicate-msg').innerText = data.message;
            document.getElementById('duplicate-time').innerHTML =
                `<i class="fa-solid fa-clock" style="margin-right:0.35rem;"></i>First check-in at: ${data.scanned_at}`;

            document.getElementById('duplicate-name').innerText = data.student.name;
            document.getElementById('duplicate-reg').innerText = data.student.register_number;
            document.getElementById('duplicate-dept').innerText = data.student.department;

            const initials = data.student.name.split(' ').map(n => n[0]).join('').substring(0, 2);
            const avatarEl = document.getElementById('duplicate-avatar');
            avatarEl.innerText = initials;
            avatarEl.style.backgroundColor = 'var(--info-color)';
            avatarEl.style.color = '#000';
        }

        function renderInactiveFailure(data) {
            playFailureSound();
            resultCard.className = 'result-card inactive';

            resultIdleView.style.display = 'none';
            resultActiveView.style.display = 'none';
            resultDuplicateView.style.display = 'none';
            resultInactiveView.style.display = 'block';

            document.getElementById('inactive-msg').innerText = data.message;

            // Show raw → matched register number debug line
            const rawEl = document.getElementById('inactive-raw-match');
            const matchedReg = data.student ? data.student.register_number : data.register_number;
            if (data.raw_value && data.raw_value !== matchedReg) {
                rawEl.innerHTML = `<i class="fa-solid fa-arrow-right-arrow-left" style="margin-right:0.35rem;"></i>Scanned: "${data.raw_value}" → Looked up: "${matchedReg || 'N/A'}"`;
            } else {
                rawEl.innerHTML = `<i class="fa-solid fa-magnifying-glass" style="margin-right:0.35rem;"></i>Looked up: "${matchedReg || data.raw_value || 'UNKNOWN'}"`;
            }

            const profileBox = document.getElementById('inactive-profile-box');
            if (data.student) {
                profileBox.style.display = 'flex';
                document.getElementById('inactive-name').innerText = data.student.name;
                document.getElementById('inactive-reg').innerText = data.student.register_number;
                document.getElementById('inactive-dept').innerText = data.student.department;
                
                const initials = data.student.name.split(' ').map(n => n[0]).join('').substring(0, 2);
                const avatarEl = document.getElementById('inactive-avatar');
                avatarEl.innerText = initials;
                avatarEl.style.backgroundColor = '#ef4444';
            } else {
                profileBox.style.display = 'flex';
                document.getElementById('inactive-name').innerText = 'Unregistered / Unknown Card';
                document.getElementById('inactive-reg').innerText = data.register_number || 'UNKNOWN';
                document.getElementById('inactive-dept').innerText = 'Access Denied';
                document.getElementById('inactive-avatar').innerText = '?';
                document.getElementById('inactive-avatar').style.backgroundColor = '#ef4444';
            }
        }

        function resetResultCard() {
            resultCard.className = 'result-card idle';
            resultIdleView.innerHTML = `
                <i class="fa-solid fa-id-card-clip" style="font-size: 2.5rem; color: rgba(255,255,255,0.2); margin-bottom: 0.75rem;"></i>
                <p style="color: var(--text-muted); font-weight: 500;">Awaiting scan input...</p>
            `;
            resultIdleView.style.display = 'block';
            resultActiveView.style.display = 'none';
            resultDuplicateView.style.display = 'none';
            resultInactiveView.style.display = 'none';
        }

        async function fetchRecentLogs() {
            try {
                const res = await fetch('/api/logs');
                const data = await res.json();
                
                // Update Stats
                if (data.stats) {
                    document.getElementById('stat-total').innerText = data.stats.total_students;
                    document.getElementById('stat-active').innerText = data.stats.active_students;
                    document.getElementById('stat-today').innerText = data.stats.today_attendance;
                }

                // Update Table
                const tbody = document.getElementById('logs-tbody');
                if (data.logs && data.logs.length > 0) {
                    tbody.innerHTML = data.logs.map(log => `
                        <tr>
                            <td style="color: var(--text-muted); font-size: 0.8rem;">${log.scanned_at.split(' ')[1] + ' ' + (log.scanned_at.split(' ')[2] || '')}</td>
                            <td><span class="reg-pill">${log.register_number}</span></td>
                            <td>
                                <div style="font-weight: 600;">${log.student_name}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">${log.department}</div>
                            </td>
                            <td>
                                <span class="status-badge ${log.status}">
                                    ${log.status === 'PRESENT' ? '<i class="fa-solid fa-check"></i> PRESENT' : '<i class="fa-solid fa-xmark"></i> REJECTED'}
                                </span>
                            </td>
                        </tr>
                    `).join('');

                    // Update record count in date banner
                    document.getElementById('feed-record-count').innerText = data.logs.length + ' record(s)';
                }
            } catch (e) {
                console.log('Error refreshing logs:', e);
            }
        }

        function downloadReport() {
            const date = document.getElementById('report-date-picker').value;
            if (!date) {
                alert('Please select a date first.');
                return;
            }
            const btn = document.getElementById('download-report-btn');
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;

            // Open as a download link
            const link = document.createElement('a');
            link.href = `/report/download?date=${date}`;
            link.download = `attendance_report_${date}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Restore button after short delay
            setTimeout(() => {
                btn.innerHTML = origHtml;
                btn.disabled = false;
            }, 1500);
        }

        function downloadReportPdf() {
            const date = document.getElementById('report-date-picker').value;
            if (!date) {
                alert('Please select a date first.');
                return;
            }
            const btn = document.getElementById('download-pdf-btn');
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;

            const link = document.createElement('a');
            link.href = `/report/download-pdf?date=${date}`;
            link.download = `attendance_report_${date}.pdf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            setTimeout(() => {
                btn.innerHTML = origHtml;
                btn.disabled = false;
            }, 2500);
        }

    </script>
</body>
</html>
