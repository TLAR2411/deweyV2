<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class StudentClassExport implements FromView
{
    use Exportable;

    protected $students; // Property to hold the studentInClass data
    protected $classroom;
    protected $total_student;
    protected $female_student;

    // Constructor to receive the studentInClass data
    public function __construct($students, $classroom, $total_student, $female_student)
    {
        $this->students = $students;
        $this->classroom = $classroom;
        $this->total_student = $total_student;
        $this->female_student = $female_student;
    }

    // Pass the data to the Blade view
    public function view(): View
    {
        return view('studentClass', [
            'students' => $this->students,
            'classroom' => $this->classroom,
            'total_student' => $this->total_student,
            'female_student' => $this->female_student
        ]);
    }
}
