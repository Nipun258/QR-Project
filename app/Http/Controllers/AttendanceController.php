<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display the main attendance capture dashboard.
     */
    public function index(): View
    {
        $students = Student::all();
        $recentAttendances = Attendance::with('student')
            ->orderBy('created_at', 'desc')
            //->take(15)
            ->get();

        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('is_active', true)->count(),
            'today_attendance' => Attendance::whereDate('scanned_at', now()->today())
                ->where('status', 'PRESENT')
                ->count(),
        ];

        return view('attendance', compact('students', 'recentAttendances', 'stats'));
    }

    /**
     * Auto scan endpoint called by QR/Barcode scanner hardware input.
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'register_number' => 'required|string|max:100',
        ]);

        // Normalize raw scanned value before DB lookup
        $rawValue = $request->input('register_number');
        $registerNumber = $this->normalizeRegisterNumber($rawValue);

        // Check Eligibility API logic
        $student = Student::where('register_number', $registerNumber)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'eligible' => false,
                'status' => 'NOT_FOUND',
                'title' => 'User Not Found',
                'message' => "Register Number [{$registerNumber}] is not registered in system.",
                'raw_value' => $rawValue,
                'register_number' => $registerNumber,
                'student' => null,
                'timestamp' => now()->format('h:i:s A'),
            ], 404);
        }

        if (!$student->is_active) {
            // Log failed scan attempt
            Attendance::create([
                'student_id' => $student->id,
                'register_number' => $student->register_number,
                'scanned_at' => now(),
                'status' => 'REJECTED',
                'remarks' => 'Access Denied: Inactive Status',
            ]);

            return response()->json([
                'success' => false,
                'eligible' => false,
                'status' => 'INACTIVE',
                'title' => 'User Not Active',
                'message' => "{$student->name} ({$student->register_number}) status is INACTIVE.",
                'raw_value' => $rawValue,
                'register_number' => $student->register_number,
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'department' => $student->department,
                    'email' => $student->email,
                    'register_number' => $student->register_number,
                    'avatar_color' => $student->avatar_color,
                ],
                'timestamp' => now()->format('h:i:s A'),
            ], 200);
        }

        // Check if student already scanned within the last 1 minute (duplicate guard)
        $existingToday = Attendance::where('student_id', $student->id)
            ->where('status', 'PRESENT')
            ->where('scanned_at', '>=', now()->subMinute())
            ->first();

        if ($existingToday) {
            return response()->json([
                'success' => true,
                'eligible' => true,
                'status' => 'ALREADY_SCANNED',
                'title' => 'Already Checked In',
                'message' => "{$student->name} already marked present today at {$existingToday->scanned_at->format('h:i:s A')}.",
                'raw_value' => $rawValue,
                'register_number' => $student->register_number,
                'scanned_at' => $existingToday->scanned_at->format('h:i:s A'),
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'department' => $student->department,
                    'email' => $student->email,
                    'register_number' => $student->register_number,
                    'avatar_color' => $student->avatar_color,
                ],
                'timestamp' => now()->format('h:i:s A'),
            ], 200);
        }

        // Student is ACTIVE & ELIGIBLE -> Record Attendance
        $attendance = Attendance::create([
            'student_id' => $student->id,
            'register_number' => $student->register_number,
            'scanned_at' => now(),
            'status' => 'PRESENT',
            'remarks' => 'Automated QR Scan Passed',
        ]);

        return response()->json([
            'success' => true,
            'eligible' => true,
            'status' => 'ACTIVE',
            'title' => 'User Active',
            'message' => "Welcome {$student->name}! Attendance recorded.",
            'raw_value' => $rawValue,
            'register_number' => $student->register_number,
            'scanned_at' => $attendance->scanned_at->format('h:i:s A'),
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'department' => $student->department,
                'email' => $student->email,
                'register_number' => $student->register_number,
                'avatar_color' => $student->avatar_color,
            ],
            'timestamp' => now()->format('h:i:s A'),
        ], 200);
    }

    /**
     * Normalize a raw scanned value to a clean register number for DB lookup.
     * Handles trimming, uppercasing, and stripping common QR/barcode prefixes.
     */
    private function normalizeRegisterNumber(string $raw): string
    {
        // 1. Trim whitespace and control characters (\r\n from scanner)
        $value = trim($raw);

        // 2. Uppercase
        $value = strtoupper($value);

        // 3. Strip common QR data prefixes (e.g. "REG:", "ID:", "STU:", "NO:")
        $prefixes = ['REG:', 'ID:', 'STU:', 'NO:', 'REGNO:', 'ROLLNO:', 'ROLL:'];
        foreach ($prefixes as $prefix) {
            if (str_starts_with($value, $prefix)) {
                $value = substr($value, strlen($prefix));
                break;
            }
        }

        // 4. Final trim after prefix removal
        return trim($value);
    }

    /**
     * Get recent logs formatted for AJAX refresh.
     */
    public function logs(): JsonResponse
    {
        $logs = Attendance::with('student')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'register_number' => $item->register_number,
                    'student_name' => $item->student ? $item->student->name : 'Unknown',
                    'department' => $item->student ? $item->student->department : 'N/A',
                    'scanned_at' => $item->scanned_at->format('Y-m-d h:i:s A'),
                    'status' => $item->status,
                    'remarks' => $item->remarks,
                    'avatar_color' => $item->student ? $item->student->avatar_color : '#6b7280',
                ];
            });

        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('is_active', true)->count(),
            'today_attendance' => Attendance::whereDate('scanned_at', now()->today())
                ->where('status', 'PRESENT')
                ->count(),
        ];

        return response()->json([
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }

    /**
     * Download attendance report as CSV for a given date (defaults to today).
     */
    public function report(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        // Validate date format
        try {
            $reportDate = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            $reportDate = now();
        }

        $records = Attendance::with('student')
            ->whereDate('scanned_at', $reportDate->toDateString())
            ->orderBy('scanned_at', 'asc')
            ->get();

        $filename = 'attendance_report_' . $reportDate->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($records, $reportDate) {
            $handle = fopen('php://output', 'w');

            // Report title row
            fputcsv($handle, ['Attendance Report — ' . $reportDate->format('l, d F Y')]);
            fputcsv($handle, ['Generated at: ' . now()->format('d/m/Y h:i:s A')]);
            fputcsv($handle, []); // blank row

            // Header columns
            fputcsv($handle, ['#', 'Time', 'Register Number', 'Student Name', 'Department', 'Status', 'Remarks']);

            $i = 1;
            foreach ($records as $record) {
                fputcsv($handle, [
                    $i++,
                    $record->scanned_at->format('h:i:s A'),
                    $record->register_number,
                    $record->student ? $record->student->name : 'Unknown',
                    $record->student ? $record->student->department : 'N/A',
                    $record->status,
                    $record->remarks ?? '',
                ]);
            }

            // Summary rows
            $presentCount = $records->where('status', 'PRESENT')->count();
            $rejectedCount = $records->where('status', 'REJECTED')->count();
            fputcsv($handle, []);
            fputcsv($handle, ['', '', '', '', 'Total PRESENT:', $presentCount]);
            fputcsv($handle, ['', '', '', '', 'Total REJECTED:', $rejectedCount]);
            fputcsv($handle, ['', '', '', '', 'Total Scans:', $records->count()]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download attendance report as a styled PDF for a given date (defaults to today).
     */
    public function reportPdf(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        try {
            $reportDate = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            $reportDate = now();
        }

        $records = Attendance::with('student')
            ->whereDate('scanned_at', $reportDate->toDateString())
            ->orderBy('scanned_at', 'asc')
            ->get();

        $presentCount = $records->where('status', 'PRESENT')->count();
        $rejectedCount = $records->where('status', 'REJECTED')->count();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.attendance_pdf', [
            'records' => $records,
            'reportDate' => $reportDate,
            'presentCount' => $presentCount,
            'rejectedCount' => $rejectedCount,
            'generatedAt' => now()->format('d/m/Y h:i:s A'),
        ])->setPaper('a4', 'portrait');

        $filename = 'attendance_report_' . $reportDate->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
