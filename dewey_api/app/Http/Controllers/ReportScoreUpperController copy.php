<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportScoreUpperController extends Controller
{
    public function viewUpper(Request $request)
    {
        $class_id = $request->class_id;
        $type = $request->type;
        $month_id = $request->month_id;

        $month_name = DB::table('month')
            ->where('month.id', $month_id)
            ->select('month.name_kh')
            ->get();

        $info = DB::table('classrooms')
            ->leftJoin('grades as g', 'classrooms.grade_id', '=', 'g.id')
            ->leftJoin('academicyears as year', 'classrooms.year_id', '=', 'year.id')
            ->where('classrooms.id', $class_id)
            ->select([
                'g.name as grade_name',
                'year.name'
            ])
            ->get();

        $studentClass = DB::table('student_class')
            ->where('student_class.class_id', $class_id)
            ->get();
        $allStudent = $studentClass->count();

        $student_female = DB::table('students as s')
            ->leftJoin('student_class as sc', 's.id', '=', 'sc.student_id')
            ->leftJoin('classrooms as c', 'sc.class_id', '=', 'c.id')
            ->where('c.id', $class_id)
            ->whereIn('s.gender', ['F', '2'])
            ->count();

        switch ($type) {
            case 'month':
                $data = $this->fetchMonthData($class_id, $month_id);
                return $this->processAndRespond($data, 'ខែ', 'successful month', $info, $month_name, $allStudent, $student_female);
            case 'semester1':
                $data = $this->getSemesterOne($class_id);
                return response()->json([
                    'type' => "ឆមាសទី១",
                    'data' => $data,
                    'info' => $info,
                    'allStudent' => $allStudent,
                    'student_female' => $student_female
                ]);

            case 'semester2':
                $data = $this->getSemester2($class_id);
                return response()->json([
                    'type' => "ឆមាសទី២",
                    'data' => $data,
                    'info' => $info,
                    'allStudent' => $allStudent,
                    'student_female' => $student_female
                ]);

            default:
                return $this->processFullYear($class_id, $info, $allStudent, $student_female);
        }
    }

    // private function fetchMonthData($class_id, $month_id)
    // {
    //     return DB::table('score_upper_cc as score')
    //         ->join('students as s', 'score.student_id', '=', 's.id')
    //         ->where('score.class_id', $class_id)
    //         ->where('score.month_id', $month_id)
    //         // ->groupBy('s.id')
    //         ->get()
    //         ->toArray();
    // }

    private function fetchMonthData($class_id, $month_id)
    {
        return DB::table(table: 'score_upper_cc as score')
            ->join('students as s', 'score.student_id', '=', 's.id')
            ->select(
                's.id as student_id',
                's.kh_name',
                's.en_name',
                's.gender',
                'score.class_id',
                's.photo_path',
                'score.khmer',
                'score.math',
                DB::raw('SUM(score.total_score) as total_score'),
                DB::raw('AVG(score.total_avg) as total_avg')
            )
            ->where('score.class_id', $class_id)
            ->where('score.month_id', $month_id)
            ->groupBy(
                's.id',
                's.kh_name',
                's.en_name',
                's.gender',
                'score.class_id',
                's.photo_path',
                'score.khmer',
                'score.math',
            )
            ->get()
            ->toArray();
    }

    private function processAndRespond($data, $type, $successMessage, $info, $month_name, $allStudent, $student_female)
    {
        if (empty($data)) {
            return response()->json(['status' => 1, 'message' => 'UnSuccesfully']);
        }

        $processed = $this->formatData($data, $type);
        $sorted = $this->sortAndRank($processed, 'avg');

        return response()->json([
            'status' => 200,
            'data' => $sorted,
            'message' => $successMessage,
            'type' => $type,
            'info' => $info,
            'month' => $month_name,
            'allStudent' => $allStudent,
            'student_female' => $student_female
        ]);
    }


    public function formatData($data, $type)
    {
        return array_map(function ($student) use ($type) {
            return [

                'student_id' => $student->student_id,
                'kh_name' => $student->kh_name,
                'en_name' => $student->en_name,
                'gender' => $student->gender,
                'class_id' => $student->class_id,
                'total_score' => number_format($student->total_score, 2, '.', ''),
                'avg' => number_format($student->total_avg, 2, '.', ''),
                'grade' => $this->getGradeUpper($student->total_avg),
                'photo_path' => $student->photo_path,
                "khmer" => $student->khmer,
                'math' => $student->math,

                // how to take subject score = fetchMondata(groupby function)
                'type' => $type,
            ];
        }, $data);
    }

    private function sortAndRank($data, $sortKey)
    {
        usort($data, fn($a, $b) => $b[$sortKey] <=> $a[$sortKey]);
        $rank = 1;
        foreach ($data as $index => &$item) {
            if ($index === 0) {
                $item['rank'] = $rank; // First item always gets rank 1
            } else if ($item[$sortKey] === $data[$index - 1][$sortKey]) {   //ប្រសិនបើតម្លៃថ្មី ស្មើរនឹងតម្លៃចាស់នោះ rank ស្មើរគ្នា
                $item['rank'] = $data[$index - 1]['rank']; // Same value, same rank
            } else {
                $item['rank'] = $index + 1; // Different value, rank = position in list
            }
        }
        return $data;
    }

    public function getGradeUpper($avg)
    {
        $grade = "";

        if ($avg >= 48.00 || $avg >= 50.00) {
            $grade = "ល្អប្រសើរ";
        } elseif ($avg >= 45.99 || $avg >= 47.99) {

            $grade = "ល្អណាស់";
        } elseif ($avg >= 40.00 || $avg >= 44.99) {

            $grade = "ល្អ";
        } elseif ($avg >= 32.50 || $avg >= 39.99) {

            $grade = "ល្អបង្គួរ";
        } elseif ($avg >= 25.00 || $avg >= 32.49) {

            $grade = "មធ្យម";
        } else {
            $grade = "ធ្លាក់";
        }

        return $grade;
    }

    private function getSemesterOne($class_id)
    {
        $months = [11, 12, 1, 2];
        $semester_data = [];

        //fetch and process all months
        foreach ($months as $m) {
            $month_data = DB::table('score_upper_cc as s_score')
                ->join('students as s', 's_score.student_id', '=', 's.id')
                ->where('class_id', $class_id)
                ->where('month_id', $m)
                ->get()
                ->toArray();

            if (empty($month_data)) {
                return "data not found";
            }

            foreach ($month_data as $student) {
                $id = $student->id;
                $avg = $student->total_avg;

                if (!isset($semester_data[$id])) {
                    $semester_data[$id] = (array)$student + [
                        'student_id' => $id,
                        'total_avg_3_month' => 0,
                        'total_semester_month' => 0
                    ];
                }
                if ($m == 2) {
                    $semester_data[$id]['total_semester_month'] = $avg;
                } else {
                    $semester_data[$id]['total_avg_3_month'] += $avg / 3;
                }
            }
        }

        // return response()->json([
        //     "semesterDATA" => $semester_data
        // ]);

        $result = array_map(function ($student) {
            $total_avg_year = number_format(($student['total_avg_3_month'] + $student['total_semester_month']) / 2, 2, '.', '');
            return [
                'student_id' => $student['student_id'],
                'kh_name' => $student['kh_name'],
                'en_name' => $student['en_name'],
                'gender' => $student['gender'],
                'photo_path' => $student['photo_path'],
                'average_3_month' => number_format($student['total_avg_3_month'], 2, '.', ''),
                'average_month_semester' => number_format($student['total_semester_month'], 2, '.', ''),
                'average_semester1' => $total_avg_year,
                'average_semester2' => 0,
                'grade' => $this->getGradeUpper($total_avg_year),
                'type' => 'Semester1'
            ];
        }, array_values(($semester_data)));

        usort($result, fn($a, $b) => $b['average_semester1'] <=> $a['average_semester1']);
        $rank = 1;
        foreach ($result as $i => &$student) {
            if ($i === 0) {
                $student['rank'] = $rank;
            } else if ($student['average_semester1'] === $result[$i - 1]['average_semester1']) {
                $student['rank'] = $result[$i - 1]['rank'];
            } else {
                $student['rank'] = $i + 1;
            }
        }
        return $result;
    }

    private function getSemester2($class_id)
    {
        $gradeId = DB::table('classrooms')->where('id', $class_id)->first()->grade_id ?? '';
        $gradeLevel = DB::table('grades')->where('id', $gradeId)->first()->grade_level_id ?? '';

        $months = $gradeLevel == 12 ? [3, 4, 5, 6] : [5, 6, 7, 8];
        $finalMonth = end($months);

        $semesterData = $this->processMonths($class_id, $months, $finalMonth);
        if ($semesterData === 0) return 0;
        $result = $this->formatResults($semesterData);
        $this->rankResults($result);

        return $result;
    }

    // private function processMonths($class_id, $months, $finalMonth)
    // {
    //     $semesterData = [];
    //     foreach ($months as $m) {
    //         $monthData = DB::table('score_upper_cc as s_score')
    //             ->join('students as s', 's_score.student_id', '=', 's.id')
    //             ->where('class_id', $class_id)
    //             ->where('month_id', $m)
    //             ->get()
    //             ->toArray();

    //         if (empty($monthData)) return 0;

    //         foreach ($monthData as $student) {
    //             $id = $student->id;
    //             $avg = $student->total_avg;
    //             // printf($avg);

    //             if (!isset($semesterData[$id])) {
    //                 $semesterData[$id] = (array) $student + [
    //                     'student_id' => $id,
    //                     'total_avg_3_month' => 0,
    //                     'total_semester_month' => 0,
    //                 ];
    //             }
    //             // $m == $finalMonth
    //             //     ? $semesterData[$id]['total_semester_month'] = $avg
    //             //     : $semesterData[$id]['total_avg_3_month'] += $avg / 3;

    //             // Add to final or 3-month total
    //             if ($m == $finalMonth) {
    //                 $semesterData[$id]['total_semester_month'] = $avg;
    //             } else {
    //                 $semesterData[$id]['total_avg_3_month'] += $avg / 3;
    //             }
    //         }
    //     }
    //     return $semesterData;
    // }

    private function processMonths($class_id, $months, $finalMonth)
    {
        $semesterData = [];

        foreach ($months as $m) {
            $monthData = DB::table('score_upper_cc as s_score')
                ->join('students as s', 's_score.student_id', '=', 's.id')
                ->where('class_id', $class_id)
                ->where('month_id', $m)
                ->get()
                ->toArray();

            if (empty($monthData)) return 0;

            // 👉 Track which students have already been added for this month
            $seenThisMonth = [];

            foreach ($monthData as $student) {
                $id = $student->id;
                $avg = $student->total_avg;

                // 👇 Only sum 1 time per student per month
                if (isset($seenThisMonth[$id])) {
                    continue; // skip if already added in this month
                }

                $seenThisMonth[$id] = true;

                if (!isset($semesterData[$id])) {
                    $semesterData[$id] = (array) $student + [
                        'student_id' => $id,
                        'total_avg_3_month' => 0,
                        'total_semester_month' => 0,
                    ];
                }

                if ($m == $finalMonth) {
                    $semesterData[$id]['total_semester_month'] = $avg;
                } else {
                    $semesterData[$id]['total_avg_3_month'] += $avg / 3;
                }
            }
        }

        return $semesterData;
    }


    private function formatResults($semesterData)
    {
        return array_map(function ($student) {
            $averageSemester = number_format(($student['total_avg_3_month'] + $student['total_semester_month']) / 2, 2, '.', '');
            return [
                'student_id' => $student['student_id'],
                'kh_name' => $student['kh_name'],
                'en_name' => $student['en_name'],
                'gender' => $student['gender'],
                'photo_path' => $student['photo_path'],
                'average_3_month' => number_format($student['total_avg_3_month'], 2, '.', ''),
                'average_month_semester' => number_format($student['total_semester_month'], 2, '.', ''),
                'average_semester2' => $averageSemester,
                'average_semester1' => 0,
                'grade' => $this->getGradeUpper($averageSemester),
                'type' => "semester2",
            ];
        }, array_values($semesterData));
    }

    private function rankResults(&$result)
    {
        usort($result, fn($a, $b) => $b['average_semester2'] <=> $a['average_semester2']);
        $rank = 1;
        foreach ($result as $i => &$student) {
            if ($i === 0) {
                $student['rank'] = $rank;
            } else if ($student['average_semester2'] === $result[$i - 1]['average_semester2']) {
                $student['rank'] = $result[$i - 1]['rank'];
            } else {
                $student['rank'] = $i + 1;
            }
        }
    }

    private function processFullYear($class_id, $info, $allStudent, $student_female)
    {
        $semester1 = $this->getSemesterOne($class_id);
        $semester2 = $this->getSemester2($class_id);

        if (empty($semester1) && empty($semester2)) {
            return response()->json(['status' => 1, 'message' => 'UnSuccesfully']);
        }
        $semester1 = is_array($semester1) ? $semester1 : (json_decode($semester1, true) ?? []);
        $semester2 = is_array($semester2) ? $semester2 : (json_decode($semester2, true) ?? []);
        $combined = array_merge($semester1, $semester2);


        $yearly = [];
        foreach ($combined as $student) {
            $id = $student['student_id'];
            if (!isset($yearly[$id])) {
                $yearly[$id] = [
                    'student_id' => $id,
                    'kh_name' => $student['kh_name'],
                    'en_name' => $student['en_name'],
                    'photo_path' => $student['photo_path'] ?? null,
                    'gender' => $student['gender'],
                    'average_semester1' => 0,
                    'average_semester2' => 0,
                ];
            }

            $yearly[$id]['average_semester1'] += $student['average_semester1'];
            $yearly[$id]['average_semester2'] += $student['average_semester2'];
        }

        $processed = array_map(function ($student) {
            $semester1 = number_format($student['average_semester1'], 2, '.', '');
            $semester2 = number_format($student['average_semester2'], 2, '.', '');
            $total_sum = number_format(($student['average_semester1'] + $student['average_semester2']) / 2, 2);
            return [
                'kh_name' => $student['kh_name'],
                'en_name' => $student['en_name'],
                'gender' => $student['gender'],
                'photo_path' => $student['photo_path'],
                'average_semester1' => $semester1,
                'average_semester2' => $semester2,
                'average_year' => $total_sum,
                'grade' => $this->getGradeUpper($total_sum),
                'type' => 'All'
            ];
        }, array_values($yearly));

        $sorted = $this->sortAndRank($processed, 'average_year');
        return response()->json([
            'status' => 0,
            'data' => $sorted,
            'message' => 'Succesfully for Year Result',
            'type' => 'ឆ្នាំ',
            'info' => $info,
            'allStudent' => $allStudent,
            'student_female' => $student_female
        ]);
    }
}
