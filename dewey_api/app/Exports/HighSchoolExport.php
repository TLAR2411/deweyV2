<?php

namespace App\Exports;

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

class HighSchoolExport implements FromView, WithStyles, WithEvents
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
        return view('upperReport', [
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

    public function styles(Worksheet $sheet)
    {
        $startRow = 7;
        $d = $this->data;
        $lastRow = count($d) + $startRow - 1;

        // Rotate Listen and Reading headers (D1 and E1)
        $sheet->getStyle('D6:AT6')->getAlignment()->setTextRotation(90);

        // Center all headers
        $sheet->getStyle('A6:BB6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6:BB6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


        // Rotate Listen and Reading headers (D1 and E1)
        $sheet->getStyle('AX6:BB6')->getAlignment()->setTextRotation(90);

        // // Center all headers
        $sheet->getStyle("C7:BB" . ($lastRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C7:BB" . ($lastRow + 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    public function registerEvents(): array
    {
        $startRow = 7;
        $d = $this->data;
        $lastRow = count($d) + $startRow - 1;

        if ($this->type == 'ខែ' || $this->type == 'month') {
            return [
                AfterSheet::class => function (AfterSheet $event) use ($lastRow, $startRow) {
                    // $lastRow += 1;
                    $sheet = $event->sheet->getDelegate();

                    $gradeCells = ['P', 'Q', 'S'];

                    foreach ($gradeCells as $cell) {
                        // header rank
                        $sheet->getStyle("{$cell}6")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}6")->getFill()->getStartColor()->setARGB('00CC00');

                        // body
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->getStartColor()->setARGB('66FF66');
                    }

                    $cellRange = "A6:S{$lastRow}";
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => '000000'], // black
                            ],
                        ],
                    ]);

                    // header rank
                    $sheet->getStyle("R6")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("R6")->getFill()->getStartColor()->setARGB('FF9933');

                    // body
                    $sheet->getStyle("R7:R{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("R7:R{$lastRow}")->getFill()->getStartColor()->setARGB('FFCC99');


                    for ($row = $startRow; $row <= $lastRow; $row++) {
                        $cell = 'R' . $row;
                        $value = $sheet->getCell($cell)->getValue();

                        if (in_array((int) $value, [1, 2, 3])) {
                            // Make text red + bold
                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB(Color::COLOR_RED);
                            $sheet->getStyle($cell)->getFont()->setBold(true);
                        }
                    }

                    for ($row = $startRow; $row <= $lastRow; $row++) {
                        $cell = 'V' . $row;
                        $value = $sheet->getCell($cell)->getValue();

                        // check if the cell contains the word "ធ្លាក់"
                        if (trim($value) === 'ធ្លាក់') {
                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB(Color::COLOR_RED);
                            $sheet->getStyle($cell)->getFont()->setBold(true);
                        }
                    }
                }
            ];
        } else {
            return [
                AfterSheet::class => function (AfterSheet $event) use ($lastRow, $startRow) {
                    // $lastRow = $lastRow + 1;
                    $sheet = $event->sheet->getDelegate();
                    $gradeCells = [
                        'E',
                        'G',
                        'I',
                        'K',
                        'M',
                        'O',
                        'Q',
                        'S',
                        'U',
                        'W',
                        'Y',
                        'AA',
                        'AD',
                        'AF',
                        'AH'

                    ];

                    $semesterCell = ['AB', 'AC', 'AE', 'AG', 'AI'];

                    foreach ($gradeCells as $cell) {

                        // header rank
                        $sheet->getStyle("{$cell}6")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}6")->getFill()->getStartColor()->setARGB('FF9933');

                        // body
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->getStartColor()->setARGB('FFCC99');
                    }

                    foreach ($semesterCell as $cell) {
                        // header rank
                        $sheet->getStyle("{$cell}6")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}6")->getFill()->getStartColor()->setARGB('00CC00');

                        // body
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->getStartColor()->setARGB('66FF66');
                    }

                    $cellRange = "A6:AI{$lastRow}";
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => '000000'], // black
                            ],
                        ],
                    ]);


                    // number rank 123
                    foreach ($gradeCells as $col) {
                        for ($row = $startRow; $row <= $lastRow; $row++) {
                            $cell = $col . $row;
                            $value = $sheet->getCell($cell)->getValue();

                            if (in_array((int) $value, [1, 2, 3])) {
                                // Make text red + bold
                                $sheet->getStyle($cell)->getFont()->getColor()->setARGB(Color::COLOR_RED);
                                $sheet->getStyle($cell)->getFont()->setBold(true);
                            }
                        }
                    }

                    $grade = 'AI';
                    // number rank 123

                    for ($row = $startRow; $row <= $lastRow; $row++) {
                        $cell = $grade . $row;
                        $value = $sheet->getCell($cell)->getValue();

                        // check if the cell contains the word "ធ្លាក់"
                        if (trim($value) === 'ធ្លាក់') {
                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB(Color::COLOR_RED);
                            $sheet->getStyle($cell)->getFont()->setBold(true);
                        }
                    }
                }
            ];
        }
    }
}
