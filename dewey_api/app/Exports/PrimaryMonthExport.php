<?php

namespace App\Exports;

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

class PrimaryMonthExport implements FromView, WithStyles, WithEvents
{
    use Exportable;


    protected $allStudent; // Property to hold the studentInClass data
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

        if ($this->type != 'ខែ' && $this->level == 6) {
            return [
                AfterSheet::class => function (AfterSheet $event) use ($lastRow, $startRow) {
                    $lastRow = $lastRow + 1;
                    $sheet = $event->sheet->getDelegate();
                    $gradeCells = ['E', 'G', 'I', 'K', 'M', 'O', 'Q', 'S', 'U', 'W', 'Y', 'AA', 'AC', 'AE', 'AG', 'AI', 'AK', 'AN', 'AQ', 'AT', 'AY', 'BA'];

                    $kcmsCells = ['P', 'R', 'AL', 'AM', 'AO', 'AP'];

                    $semesterCells = ['AR', 'AS', 'AU', 'AV', 'AX', 'AZ', 'BB'];

                    foreach ($kcmsCells as $cell) {
                        // header rank
                        $sheet->getStyle("{$cell}6")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}6")->getFill()->getStartColor()->setARGB('FFED29');

                        // body
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->getStartColor()->setARGB('FFEE8C');
                    }


                    foreach ($gradeCells as $cell) {

                        // header rank
                        $sheet->getStyle("{$cell}6")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}6")->getFill()->getStartColor()->setARGB('FF9933');

                        // body
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->getStartColor()->setARGB('FFCC99');
                    }

                    foreach ($semesterCells as $cell) {
                        // header rank
                        $sheet->getStyle("{$cell}6")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}6")->getFill()->getStartColor()->setARGB('00CC00');

                        // body
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->getStartColor()->setARGB('66FF66');
                    }
                    // header rank
                    $sheet->getStyle("AW7")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("AW7")->getFill()->getStartColor()->setARGB('FF9933');

                    $sheet->getStyle("AU7")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("AU7")->getFill()->getStartColor()->setARGB('00CC00');

                    $sheet->getStyle("AV7")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("AV7")->getFill()->getStartColor()->setARGB('00CC00');

                    // body
                    $sheet->getStyle("AW8:AW{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("AW8:AW{$lastRow}")->getFill()->getStartColor()->setARGB('FFCC99');


                    // border

                    $cellRange = "A6:BB{$lastRow}";
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

                    $grade = 'BB';
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
        } else if ($this->type != 'ខែ') {
            return [
                AfterSheet::class => function (AfterSheet $event) use ($lastRow, $startRow) {
                    $sheet = $event->sheet->getDelegate();
                    $gradeCells = ['E', 'G', 'I', 'K', 'M', 'O', 'Q', 'S', 'U', 'W', 'Y', 'AA', 'AC', 'AE', 'AG', 'AI', 'AK', 'AN', 'AP', 'AR'];
                    $semesterCells = ['AL', 'AM', 'AO', 'AQ', 'AS'];

                    foreach ($gradeCells as $cell) {


                        // header rank
                        $sheet->getStyle("{$cell}6")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}6")->getFill()->getStartColor()->setARGB('FF9933');

                        // body
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->getStartColor()->setARGB('FFCC99');
                    }

                    foreach ($semesterCells as $cell) {
                        // header rank
                        $sheet->getStyle("{$cell}6")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}6")->getFill()->getStartColor()->setARGB('00CC00');

                        // body
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->getStartColor()->setARGB('66FF66');
                    }


                    // border

                    $cellRange = "A6:AS{$lastRow}";
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



                    $grade = 'AS';
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




                    // $imageUrl = 'https://app.disreportcard.com/assets/DIS-BFWF5DQ5.png';
                    // $tempFile = tempnam(sys_get_temp_dir(), 'excel_img'); // create temp file
                    // file_put_contents($tempFile, file_get_contents($imageUrl)); // download image

                    // $drawing = new Drawing();
                    // $drawing->setName('Logo');
                    // $drawing->setDescription('School Logo');
                    // $drawing->setPath($tempFile); // use temp file
                    // $drawing->setHeight(50); // desired height
                    // $drawing->setWidth(50);  // optional width
                    // $drawing->setCoordinates('A3');
                    // $drawing->setOffsetX(100);
                    // $drawing->setOffsetY(-40);
                    // $drawing->setWorksheet($sheet);
                }


            ];
        } else {
            return [
                AfterSheet::class => function (AfterSheet $event) use ($lastRow, $startRow) {
                    $sheet = $event->sheet->getDelegate();
                    $semesterCells = ['U', 'V', 'X'];

                    // header rank
                    $sheet->getStyle(cellCoordinate: "W6")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("W6")->getFill()->getStartColor()->setARGB('FF9933');

                    // body
                    $sheet->getStyle("W7:W{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("W7:W{$lastRow}")->getFill()->getStartColor()->setARGB('FFCC99');

                    foreach ($semesterCells as $cell) {
                        // header rank
                        $sheet->getStyle("{$cell}6")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}6")->getFill()->getStartColor()->setARGB('00CC00');

                        // body
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle("{$cell}7:{$cell}{$lastRow}")->getFill()->getStartColor()->setARGB('66FF66');
                    }


                    // border

                    $cellRange = "A6:X{$lastRow}";
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => '000000'], // black
                            ],
                        ],
                    ]);



                    for ($row = $startRow; $row <= $lastRow; $row++) {
                        $cell = "W{$row}"; // rank column W
                        $value = $sheet->getCell($cell)->getValue();

                        if (in_array((int) $value, [1, 2, 3])) {
                            // Make text red + bold
                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB(Color::COLOR_RED);
                            $sheet->getStyle($cell)->getFont()->setBold(true);
                        }
                    }


                    // និទ្ទេសន៍
                    $grade = 'X';
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
