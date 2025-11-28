<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ReportSecondaryScoreController extends Controller
{
    public function viewSecondary(Request $request)
    {
        $class_id = $request->class_id;
        $type = $request->type;
        $month_id = $request->month_id;
        $campus_id = $request->campus_id;
        $year_id = $request->year_id;
        // $avg_mutil = $request->avg_m;

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

        $gradeId = DB::table('classrooms')->where('id', $class_id)->first()->grade_id ?? '';
        // dd($gradeId);
        $class = DB::table('grades')->where('id', $gradeId)->first();

        $allStudent = DB::table('student_class')->where('class_id', $class_id)->where('deleted', 0)->count();

        $student_female = DB::table('students as s')
            ->leftJoin('student_class as sc', 's.id', '=', 'sc.student_id')
            ->leftJoin('classrooms as c', 'sc.class_id', '=', 'c.id')
            ->where('c.id', $class_id)
            ->where('sc.deleted', 0)
            ->whereIn('s.gender', ['F', '2'])
            ->count();

        switch ($type) {
            case 'month':
                $data = $this->fetchMonthData($class_id, $month_id);
                return $this->processAndRespond($data, 'ខែ', 'successful month', $info, $month_name, $allStudent, $student_female);
            case 'semester1':
                $data = $this->getSemesterOne($class_id, $class->grade_level_id ?? '', $year_id, $campus_id);
                return response()->json([
                    'type' => "ឆមាសទី១",
                    'data' => $data,
                    'info' => $info,
                    'allStudent' => $allStudent,
                    'student_female' => $student_female
                ]);

            case 'semester2':
                $data = $this->getSemester2($class_id, $year_id, $campus_id);
                return response()->json([
                    'type' => "ឆមាសទី២",
                    'data' => $data,
                    'info' => $info,
                    'allStudent' => $allStudent,
                    'student_female' => $student_female,
                    // 'yearId' => $year_id
                ]);

            default:
                return $this->processFullYear($class_id, $info, $allStudent, $student_female, $year_id, $campus_id,  $class->grade_level_id ?? '');
        }
    }

    private function fetchMonthData($class_id, $month_id)
    {
        return DB::table('score_secondary_cc as score')
            ->join('students as s', 'score.student_id', '=', 's.id')
            ->select(
                's.id as student_id',
                's.kh_name',
                's.en_name',
                's.gender',
                's.photo_path',
                'score.class_id',
                'score.khmer',
                'score.essay',
                'score.writing',
                'score.morality',
                'score.history',
                'score.geography',
                'score.geology',
                'score.math',
                'score.physical',
                'score.chemistry',
                'score.biology',
                'score.english',
                'score.house_education',
                'score.pe',
                'score.computer',
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
                's.photo_path',
                'score.class_id',
                'score.khmer',
                'score.essay',
                'score.writing',
                'score.morality',
                'score.history',
                'score.geography',
                'score.geology',
                'score.math',
                'score.physical',
                'score.chemistry',
                'score.biology',
                'score.english',
                'score.house_education',
                'score.pe',
                'score.computer',
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
                'grade' => $this->getGradeSecondary(number_format($student->total_avg)),
                'photo_path' => $student->photo_path,
                "khmer" => $student->khmer,
                "writing" => $student->writing,
                "essay" => $student->essay,
                'math' => $student->math,
                'morality' => $student->morality,
                'history' => $student->history,
                'geography' => $student->geography,
                'geology' => $student->geology,
                'physical' => $student->physical,
                'chemistry' => $student->chemistry,
                'biology' => $student->biology,
                'english' => $student->english,
                'pe' => $student->pe,
                'computer' => $student->computer,
                'house_education' => $student->house_education,
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

    public function getGradeSecondary($avg)
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

    private function getSemesterOne($class_id, $grade_level, $year_id, $campus_id)
    {

        // $months = [11, 12, 1, 2];
        // $semester_data = [];

        // Get semester months configuration from database
        $semesterConfig = DB::table('setting_semester_list')
            // ->where('setting_semester_list.campus_id', $campus_id)
            ->where('setting_semester_list.year_id', $year_id)
            ->where('setting_semester_list.grade_level_id', $grade_level)
            ->first();

        if (!$semesterConfig) {
            return response()->json([
                'status' => 404,
                'message' => 'Semester configuration not found for this grade level',
                'level' => $grade_level
            ], 404);
        }

        // Get months for first 3 months and semester month
        $three_months = collect(explode(',', $semesterConfig->three_months_semester1))
            ->map(fn($m) => (int) trim($m))
            ->filter()
            ->values()
            ->toArray();

        $month_semester = (int) $semesterConfig->semester_month1;

        $all_months = array_merge($three_months, [$month_semester]);

        $semester_data = [];

        // return response()->json($all_months);

        //fetch and process all months

        foreach ($all_months as $m) {
            $month_data = DB::table('score_secondary_cc as s_score')
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
                if ($m ==  $month_semester) {
                    $semester_data[$id]['total_semester_month'] = $avg;
                    $semester_data[$id]['writing'] = $student->writing;
                    $semester_data[$id]['essay'] = $student->essay;
                    $semester_data[$id]['morality'] = $student->morality;
                    $semester_data[$id]['khmer'] = $student->khmer;
                    $semester_data[$id]['history'] = $student->history;
                    $semester_data[$id]['geography'] = $student->geography;
                    $semester_data[$id]['math'] = $student->math;
                    $semester_data[$id]['physical'] = $student->physical;
                    $semester_data[$id]['chemistry'] = $student->chemistry;
                    $semester_data[$id]['biology'] = $student->biology;
                    $semester_data[$id]['geology'] = $student->geology;
                    $semester_data[$id]['house_education'] = $student->house_education;
                    $semester_data[$id]['english'] = $student->english;
                    $semester_data[$id]['pe'] = $student->pe;
                    $semester_data[$id]['computer'] = $student->computer;
                    $semester_data[$id]['total_score'] = $student->total_score;
                } else {
                    $semester_data[$id]['total_avg_3_month'] += $avg / 3;
                }
            }
        }

        $result = array_map(function ($student) {
            $total_avg_year = number_format(($student['total_avg_3_month'] + $student['total_semester_month']) / 2, 2, '.', '');
            return [
                'student_id' => $student['student_id'],
                'kh_name' => $student['kh_name'],
                'en_name' => $student['en_name'],
                'photo_path' => $student['photo_path'],
                'gender' => $student['gender'],
                'average_3_month' => number_format($student['total_avg_3_month'], 2, '.', ''),
                'average_month_semester' => number_format($student['total_semester_month'], 2, '.', ''),
                'average_semester1' => $total_avg_year,
                'average_semester2' => 0,
                'grade' => $this->getGradeSecondary(avg: $total_avg_year),
                'type' => 'Semester1',
                'khmer' => $student['khmer'] ?? 0,
                'math' => $student['math'] ?? 0,
                'morality' => $student['morality'] ?? 0,
                'history' => $student['history'] ?? 0,
                'geography' => $student['geography'] ?? 0,
                'physical' => $student['physical'] ?? 0,
                'chemistry' => $student['chemistry'] ?? 0,
                'biology' => $student['biology'] ?? 0,
                'writing' => $student['writing'] ?? 0,
                'essay' => $student['essay'] ?? 0,
                'geology' => $student['geology'] ?? 0,
                'house_education' => $student['house_education'] ?? 0,
                'english' => $student['english'] ?? 0,
                'pe' => $student['pe'] ?? 0,
                'computer' => $student['computer'] ?? 0,
                'total_score' => $student['total_score'] ?? 0,

            ];
        }, array_values(($semester_data)));


        $result = $this->addRank($result, 'average_3_month', 'rank_3_month');
        $result = $this->addRank($result, 'average_month_semester', 'rank_month_semester');
        $result = $this->addRank($result, 'average_semester1', 'rank');
        $result = $this->addRank($result, 'khmer', 'rankKhmer');
        $result = $this->addRank($result, 'writing', 'rankWriting');
        $result = $this->addRank($result, 'essay', 'rankEssay');
        $result = $this->addRank($result, 'morality', 'rankMorality');
        $result = $this->addRank($result, 'history', 'rankHistory');
        $result = $this->addRank($result, 'geography', 'rankGeography');
        $result = $this->addRank($result, 'math', 'rankMath');
        $result = $this->addRank($result, 'physical', 'rankPhysic');
        $result = $this->addRank($result, 'chemistry', 'rankChemistry');
        $result = $this->addRank($result, 'biology', 'rankBiology');
        $result = $this->addRank($result, 'geology', 'rankGeology');
        $result = $this->addRank($result, 'pe', 'rankPe');
        $result = $this->addRank($result, 'english', 'rankEnglish');
        $result = $this->addRank($result, 'computer', 'rankComputer');
        $result = $this->addRank($result, 'house_education', 'rankHouseEducation');



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


    //find rank that have 2 rank like semester have 2 rank rank month semester and rank semester
    private function addRank(array $data, string $field, string $rankFieldName): array
    {
        // Sort a copy of the array by the given field (descending order)
        $sorted = $data;
        usort($sorted, fn($a, $b) => $b[$field] <=> $a[$field]);
        // Assign ranks
        $rank = 1;
        foreach ($sorted as $i => &$student) {
            if ($i === 0) {
                $student[$rankFieldName] = $rank;
            } else if ($student[$field] === $sorted[$i - 1][$field]) {
                $student[$rankFieldName] = $sorted[$i - 1][$rankFieldName];
            } else {
                $student[$rankFieldName] = $i + 1;
            }
        }

        // Map ranks back to the original array
        foreach ($data as &$student) {
            foreach ($sorted as $s) {
                if ($student['student_id'] === $s['student_id']) {
                    $student[$rankFieldName] = $s[$rankFieldName];
                    break;
                }
            }
        }

        return $data;
    }

    private function getSemester2($class_id, $year_id, $campus_id)
    {
        $gradeId = DB::table('classrooms')->where('id', $class_id)->first()->grade_id ?? '';
        $gradeLevel = DB::table('grades')->where('id', $gradeId)->first()->grade_level_id ?? '';



        // Get semester months configuration from database
        $semesterConfig = DB::table('setting_semester_list')
            // ->where('setting_semester_list.campus_id', $campus_id)
            ->where('setting_semester_list.year_id', $year_id)
            ->where('setting_semester_list.grade_level_id', $gradeLevel)
            ->first();

        if (!$semesterConfig) {
            return response()->json([
                'status' => 404,
                'message' => 'Semester configuration not found for this grade level',
                'level' => $gradeLevel
            ], 404);
        }

        // Get months for first 3 months and semester month
        $three_months = collect(explode(',', $semesterConfig->three_months_semester2))
            ->map(fn($m) => (int) trim($m))
            ->filter()
            ->values()
            ->toArray();

        $month_semester = (int) $semesterConfig->semester_month2;

        $months  = array_merge($three_months, [$month_semester]);


        // $months = $gradeLevel == 9 ? [3, 4, 5, 6] : [5, 6, 7, 8];

        $finalMonth = end($months);

        $semesterData = $this->processMonths($class_id, $months, $finalMonth);
        if ($semesterData === 0) return 0;
        $result = $this->formatResults($semesterData);
        $this->rankResults($result);

        $result = $this->addRank($result, 'average_3_month', 'rank_3_month');
        $result = $this->addRank($result, 'average_month_semester', 'rank_month_semester');
        $result = $this->addRank($result, 'average_semester2', 'rank');
        $result = $this->addRank($result, 'khmer', 'rankKhmer');
        $result = $this->addRank($result, 'writing', 'rankWriting');
        $result = $this->addRank($result, 'essay', 'rankEssay');
        $result = $this->addRank($result, 'morality', 'rankMorality');
        $result = $this->addRank($result, 'history', 'rankHistory');
        $result = $this->addRank($result, 'geography', 'rankGeography');
        $result = $this->addRank($result, 'math', 'rankMath');
        $result = $this->addRank($result, 'physical', 'rankPhysic');
        $result = $this->addRank($result, 'chemistry', 'rankChemistry');
        $result = $this->addRank($result, 'biology', 'rankBiology');
        $result = $this->addRank($result, 'geology', 'rankGeology');
        $result = $this->addRank($result, 'pe', 'rankPe');
        $result = $this->addRank($result, 'english', 'rankEnglish');
        $result = $this->addRank($result, 'computer', 'rankComputer');
        $result = $this->addRank($result, 'house_education', 'rankHouseEducation');

        return $result;
    }

    private function processMonths($class_id, $months, $finalMonth)
    {
        $semesterData = [];
        foreach ($months as $m) {
            $monthData = DB::table('score_secondary_cc as s_score')
                ->join('students as s', 's_score.student_id', '=', 's.id')
                ->where('class_id', $class_id)
                ->where('month_id', $m)
                ->get()
                ->toArray();

            if (empty($monthData)) return 0;

            foreach ($monthData as $student) {
                $id = $student->id;
                $avg = $student->total_avg;

                if (!isset($semesterData[$id])) {
                    $semesterData[$id] = (array) $student + [
                        'student_id' => $id,
                        'total_avg_3_month' => 0,
                        'total_semester_month' => 0,
                    ];
                }
                if ($m == $finalMonth) {
                    $semesterData[$id]['total_semester_month'] = $avg;
                    $semesterData[$id]['writing'] = $student->writing;
                    $semesterData[$id]['essay'] = $student->essay;
                    $semesterData[$id]['morality'] = $student->morality;
                    $semesterData[$id]['khmer'] = $student->khmer;
                    $semesterData[$id]['history'] = $student->history;
                    $semesterData[$id]['geography'] = $student->geography;
                    $semesterData[$id]['math'] = $student->math;
                    $semesterData[$id]['physical'] = $student->physical;
                    $semesterData[$id]['chemistry'] = $student->chemistry;
                    $semesterData[$id]['biology'] = $student->biology;
                    $semesterData[$id]['geology'] = $student->geology;
                    $semesterData[$id]['house_education'] = $student->house_education;
                    $semesterData[$id]['english'] = $student->english;
                    $semesterData[$id]['pe'] = $student->pe;
                    $semesterData[$id]['computer'] = $student->computer;
                    $semesterData[$id]['total_score'] = $student->total_score;
                } else {
                    $semesterData[$id]['total_avg_3_month'] += $avg / 3;
                }
                // $m == $finalMonth
                //     ? $semesterData[$id]['total_semester_month'] = $avg
                //     : $semesterData[$id]['total_avg_3_month'] += $avg / 3;
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
                'grade' => $this->getGradeSecondary($averageSemester),
                'type' => "semester2",
                'khmer' => $student['khmer'] ?? 0,
                'math' => $student['math'] ?? 0,
                'morality' => $student['morality'] ?? 0,
                'history' => $student['history'] ?? 0,
                'geography' => $student['geography'] ?? 0,
                'physical' => $student['physical'] ?? 0,
                'chemistry' => $student['chemistry'] ?? 0,
                'biology' => $student['biology'] ?? 0,
                'writing' => $student['writing'] ?? 0,
                'essay' => $student['essay'] ?? 0,
                'geology' => $student['geology'] ?? 0,
                'house_education' => $student['house_education'] ?? 0,
                'english' => $student['english'] ?? 0,
                'pe' => $student['pe'] ?? 0,
                'computer' => $student['computer'] ?? 0,
                'total_score' => $student['total_score'] ?? 0,

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

    private function processFullYear($class_id, $info, $allStudent, $student_female, $year_id, $campus_id, $grade_level,)
    {
        $semester1 = $this->getSemesterOne($class_id, $grade_level, $year_id, $campus_id);
        $semester2 = $this->getSemester2($class_id, $year_id, $campus_id,);

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
                'student_id' => $student['student_id'],
                'kh_name' => $student['kh_name'],
                'en_name' => $student['en_name'],
                'gender' => $student['gender'],
                'photo_path' => $student['photo_path'],
                'average_semester1' => $semester1,
                'average_semester2' => $semester2,
                'grade1' => $this->getGradeSecondary($semester1),
                'grade2' => $this->getGradeSecondary($semester2),
                'average_year' => $total_sum,
                // 'photo_path' => $student['photo_path'],
                'grade' => $this->getGradeSecondary($total_sum),
                'type' => 'All'
            ];
        }, array_values($yearly));

        $processed = $this->addRank($processed, 'average_semester1', 'rank1');
        $processed = $this->addRank($processed, 'average_semester2', 'rank2');

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
