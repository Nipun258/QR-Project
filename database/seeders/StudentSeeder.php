<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            [
                'register_number' => '500',
                'name' => 'Alex Johnson',
                'department' => 'Computer Science & Eng',
                'email' => 'alex.j@example.com',
                'is_active' => true,
                'avatar_color' => '#6366f1', // Indigo
            ],
            [
                'register_number' => '501',
                'name' => 'Sophia Martinez',
                'department' => 'Electrical & Electronics',
                'email' => 'sophia.m@example.com',
                'is_active' => true,
                'avatar_color' => '#10b981', // Emerald
            ],
            [
                'register_number' => '503',
                'name' => 'David Chen',
                'department' => 'Mechanical Engineering',
                'email' => 'david.c@example.com',
                'is_active' => true,
                'avatar_color' => '#3b82f6', // Blue
            ],
            [
                'register_number' => '504',
                'name' => 'Emma Watson',
                'department' => 'Information Technology',
                'email' => 'emma.w@example.com',
                'is_active' => true,
                'avatar_color' => '#ec4899', // Pink
            ],
            // INACTIVE / BLOCKED STUDENTS
            [
                'register_number' => '505',
                'name' => 'Michael Scott',
                'department' => 'Business Administration',
                'email' => 'michael.s@example.com',
                'is_active' => false,
                'avatar_color' => '#ef4444', // Red
            ],
            [
                'register_number' => '507',
                'name' => 'Sarah Connor',
                'department' => 'Robotics & Automation',
                'email' => 'sarah.c@example.com',
                'is_active' => false,
                'avatar_color' => '#f59e0b', // Amber
            ],
        ];

        foreach ($students as $student) {
            Student::updateOrCreate(
                ['register_number' => $student['register_number']],
                $student
            );
        }
    }
}
