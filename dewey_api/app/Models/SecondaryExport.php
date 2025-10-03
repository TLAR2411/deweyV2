<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Borders;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class SecondaryExport implements FromView
{
    use Exportable;

    protected $allStudent;
    protected $info;
    protected $data;
    protected $message;
    protected $student_female;
    protected $month;
    protected $type;
    protected $level;

    public function __construct($allStudent, $info, $data, $message, $student_female, $month, $type, $level)
    {
        $this->allStudent = $allStudent;
        $this->info = $info;
        $this->data = $data;
        $this->message = $message;
        $this->student_female = $student_female;
        $this->month = $month;
        $this->type = $type;
        $this->level = $level;
    }

    public function view(): View
    {
        return view('primaryReport', [
            'allStudent' => $this->allStudent,
            'info' => $this->info,
            'data' => $this->data,
            'message' => $this->message,
            'student_female' => $this->student_female,
            'month' => $this->month,
            'type' => $this->type,
            'level' => $this->level,
        ]);
    }
}
