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
            ->take(15)
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
        $rawValue        = $request->input('register_number');
        $registerNumber  = $this->normalizeRegisterNumber($rawValue);

        // Check Eligibility API logic
        $student = Student::where('register_number', $registerNumber)->first();

        if (!$student) {
            return response()->json([
                'success'           => false,
                'eligible'          => false,
                'status'            => 'NOT_FOUND',
                'title'             => 'User Not Found',
                'message'           => "Register Number [{$registerNumber}] is not registered in system.",
                'raw_value'         => $rawValue,
                'register_number'   => $registerNumber,
                'student'           => null,
                'timestamp'         => now()->format('h:i:s A'),
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
                'success'           => false,
                'eligible'          => false,
                'status'            => 'INACTIVE',
                'title'             => 'User Not Active',
                'message'           => "{$student->name} ({$student->register_number}) status is INACTIVE.",
                'raw_value'         => $rawValue,
                'register_number'   => $student->register_number,
                'student'           => [
                    'id'              => $student->id,
                    'name'            => $student->name,
                    'department'      => $student->department,
                    'email'           => $student->email,
                    'register_number' => $student->register_number,
                    'avatar_color'    => $student->avatar_color,
                ],
                'timestamp'         => now()->format('h:i:s A'),
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
            'success'           => true,
            'eligible'          => true,
            'status'            => 'ACTIVE',
            'title'             => 'User Active',
            'message'           => "Welcome {$student->name}! Attendance recorded.",
            'raw_value'         => $rawValue,
            'register_number'   => $student->register_number,
            'scanned_at'        => $attendance->scanned_at->format('h:i:s A'),
            'student'           => [
                'id'              => $student->id,
                'name'            => $student->name,
                'department'      => $student->department,
                'email'           => $student->email,
                'register_number' => $student->register_number,
                'avatar_color'    => $student->avatar_color,
            ],
            'timestamp'         => now()->format('h:i:s A'),
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
}
