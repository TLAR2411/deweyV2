<?php

namespace App\Http\Controllers;

use Dflydev\DotAccessData\Data;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportScoreKhmerController extends Controller
{
    public function viewPrimary(Request $request)
    {
        $class_id = $request->class_id;
        $type = $request->type;
        $month_id = $request->month_id;
        $campus_id = $request->campus_id;
        $year_id = $request->year_id;

        $month_name = DB::table('month')
            ->where('month.id', $month_id)
            ->select('month.name_kh')
            ->get();

        $gradeId = DB::table('classrooms')->where('id', $class_id)->first()->grade_id ?? '';
        // dd($gradeId);
        $class = DB::table('grades')->where('id', $gradeId)->first();

        $avg_mutil = $this->getAvgMutil($class->grade_level_id ?? '');

        $info = DB::table('classrooms')
            ->leftJoin('grades as g', 'classrooms.grade_id', '=', 'g.id')
            ->leftJoin('academicyears as year', 'classrooms.year_id', '=', 'year.id')
            ->where('classrooms.id', $class_id)

            ->select([
                'g.name as grade_name',
                'year.name'
            ])
            ->get();

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
                return $this->processAndRespond($data, $avg_mutil, 'ខែ', 'Succesfully Month', $info, $month_name, $allStudent, $student_female);
            case 'semester1':
                $data = $this->getSemesterOnePrimary($class_id, $avg_mutil, $class->grade_level_id ?? '', $year_id, $campus_id);

                return response()->json([
                    'type' => "ឆមាសទី១",
                    'data' => $data,
                    'info' => $info,
                    'allStudent' => $allStudent,
                    'student_female' => $student_female,

                ]);

            case 'semester2':
                $data = $this->getSemesterTwoPrimary($class_id, $avg_mutil, $class->grade_level_id ?? '', $year_id, $campus_id);
                return response()->json([
                    'type' => "ឆមាសទី២",
                    'data' => $data,
                    'info' => $info,
                    'allStudent' => $allStudent,
                    'student_female' => $student_female
                ]);

            default:
                return $this->processFullYear($class_id, $avg_mutil, $info, $allStudent, $student_female, $class->grade_level_id ?? '', $year_id, $campus_id);
        }
    }
    private function getAvgMutil($grade)
    {
        if (str_contains($grade, '1') || str_contains($grade, '2'))
            return 13;
        if (str_contains($grade, '3') || str_contains($grade, '4'))
            return 15;
        return 17;
    }

    // Helper: Fetch month data
    private function fetchMonthData($class_id, $month_id)
    {
        return DB::table('score_primary_cc as score')
            ->join('students as s', 'score.student_id', '=', 's.id')
            ->select(
                's.id as student_id',
                's.kh_name',
                's.en_name',
                's.gender',
                's.photo_path',
                'score.class_id',
                'score.listent',
                'score.speaking',
                'score.writing',
                'score.reading',
                'score.essay',
                'score.grammar',
                'score.math',
                'score.chemistry',
                'score.physical',
                'score.history',
                'score.morality',
                'score.art',
                'score.word',
                'score.pe',
                'score.homework',
                'score.healthy',
                'score.steam',
                'score.total_avg',
                DB::raw('SUM(score.total_score) as total_score'),
            )
            ->where('score.class_id', $class_id)
            ->where(
                'score.month_id',
                $month_id
            )
            ->groupBy(
                's.id',
                's.kh_name',
                's.en_name',
                's.gender',
                's.photo_path',
                'score.class_id',
                'score.listent',
                'score.speaking',
                'score.writing',
                'score.reading',
                'score.essay',
                'score.grammar',
                'score.math',
                'score.chemistry',
                'score.physical',
                'score.history',
                'score.morality',
                'score.art',
                'score.word',
                'score.pe',
                'score.homework',
                'score.healthy',
                'score.steam',
                'score.total_avg',
            )
            ->get()
            ->toArray();
    }

    // Helper: Process data and return response
    private function processAndRespond($data, $avg_mutil, $type, $successMessage, $info, $month_name, $allStudent, $student_female)
    {
        if (empty($data)) {
            return response()->json(['status' => 1, 'message' => 'UnSuccesfully']);
        }
        $processed = $this->formatData($data, $avg_mutil, $type);
        $sorted = $this->sortAndRank($processed, 'avg');

        return response()->json([
            'status' => 0,
            'data' => $sorted,
            'message' => $successMessage,
            'type' => $type,
            'info' => $info,
            'month' => $month_name,
            'allStudent' => $allStudent,
            'student_female' => $student_female
        ]);
    }
    // Helper: Format data
    private function formatData($data, $avg_mutil, $type)
    {
        return array_map(function ($student) use ($avg_mutil, $type) {
            // $avg = number_format($student->total_score / $avg_mutil, 2, '.', '');
            $avg = floor(($student->total_score / $avg_mutil) * 100) / 100;
            return [
                'student_id' => $student->student_id,
                'kh_name' => $student->kh_name,
                'en_name' => $student->en_name,
                'gender' => $student->gender,
                'class_id' => $student->class_id,
                'total_score' => number_format($student->total_score, 2, '.', ''),
                // 'avg' => number_format($avg, 2, '.', ''),
                'avg' => number_format($student->total_avg, 2, '.', ''),
                'grade' => $this->getGradePrimary($avg),
                'photo_path' => $student->photo_path,
                // 'listent' => $student->listent,
                'listent' => number_format($student->listent, 2, '.', ''),
                'speaking' => number_format($student->speaking, 2, '.', ''),
                'writing' => number_format($student->writing, 2, '.', ''),
                'reading' => number_format($student->reading, 2, '.', ''),
                'essay' => number_format($student->essay, 2, '.', ''),
                'grammar' => number_format($student->grammar, 2, '.', ''),
                'math' => number_format($student->math, 2, '.', ''),
                'chemistry' => number_format($student->chemistry, 2, '.', ''),
                'physical' => number_format($student->physical, 2, '.', ''),
                'history' => number_format($student->history, 2, '.', ''),
                'morality' => number_format($student->morality, 2, '.', ''),
                'art' => number_format($student->art, 2, '.', ''),
                'word' => number_format($student->word, 2, '.', ''),
                'pe' => number_format($student->pe, 2, '.', ''),
                'homework' => number_format($student->homework, 2, '.', ''),
                'healthy' => number_format($student->healthy, 2, '.', ''),
                'steam' => number_format($student->steam, 2, '.', ''),
                'type' => $type,

            ];
        }, $data);
    }

    // Helper: Sort and rank
    private function sortAndRank($data, $sortKey)
    {
        usort($data, fn($a, $b): int => $b[$sortKey] <=> $a[$sortKey]);
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
    public function getGradePrimary($avg)
    {
        $grade = "";

        if ($avg >= 8.00 || $avg >= 10.00) {
            $grade = "ល្អ";
        } elseif ($avg >= 6.40 || $avg >= 7.99) {
            $grade = "ល្អបង្គួរ";
        } elseif ($avg >= 5.00 || $avg >= 6.49) {
            $grade = "មធ្យម";
        } else {
            $grade = "ធ្លាក់";
        }
        return $grade;
    }

    // private function getMonths($grade_level){
    //     $months = DB::table('month')->pluck('name_kh', 'id');
    //     $dataMonthSemester = DB

    // }
    public function getRankOne($data, $sortKey)
    {
        print_r($data);
        usort($data, callback: fn($a, $b): int => $b[$sortKey] <=> $a[$sortKey]);
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

        return $rank;
    }
    public function getSemesterOnePrimary($class_id, $avg_mutil, $grade_level, $year_id, $campus_id)
    {

        // Get semester months configuration from database
        $semesterConfig = DB::table('setting_semester_list')
            // ->where('setting_semester_list.campus_id', $campus_id)
            ->where('setting_semester_list.year_id', $year_id)
            ->where('setting_semester_list.grade_level_id', $grade_level)
            ->first();

        // print_r($semesterConfig);

        if (!$semesterConfig) {
            return response()->json([
                'status' => 404,
                'message' => 'Semester configuration not found for this grade level',
                'level' => $grade_level,
                'year_id' => $year_id,
                'campus_id' => $campus_id
            ], 404);
        }


        // Get months for first 3 months and semester month
        $three_months = collect(explode(',', $semesterConfig->three_months_semester1))
            ->map(fn($m) => (int) trim($m))
            ->filter()
            ->values()
            ->toArray();

        // return response()->json($three_months);
        // $three_month = [12,1,2] work well

        $month_semester = (int) $semesterConfig->semester_month1;

        // Combine all months: first 3 months + semester month
        $all_months = array_merge($three_months, [$month_semester]);

        // return response()->json($all_months);
        // work well

        $semester_data_by_id = [];

        $monthly_scores = []; // Store all students' scores for each month



        // Fetch and process all months
        foreach ($all_months as $month) {

            $month_data = DB::table('score_primary_cc')
                ->join('students', 'score_primary_cc.student_id', '=', 'students.id')
                ->where('class_id', $class_id)
                ->where('month_id', $month)
                ->get()
                ->toArray();

            if (empty($month_data)) {
                return response()->json([
                    'status' => 404,
                    'message' => "No data found for month $month"
                ], 404);
            }

            // Process each student's data for the month
            foreach ($month_data as $student) {
                $id = $student->id;
                $avg = number_format($student->total_avg, 2, '.', '');

                // If the student is new, initialize their data
                if (!isset($semester_data_by_id[$id])) {
                    $semester_data_by_id[$id] = (array) $student + [
                        'student_id' => $id,
                        'total_avg_3_month' => 0,
                        'month_count' => 0,
                        'total_semester_month' => 0,
                        'months' => []   // 🔥 new: store per-month data
                    ];
                }

                $semester_data_by_id[$id]['months']["month_$month"] = (float) $avg;

                // ✅ also keep track for ranking
                $monthly_scores[$month][$id] = (float) $avg;



                // Check if this month is the semester month (last month)
                if ($month == $month_semester) {
                    $semester_data_by_id[$id]['total_semester_month'] = $avg;
                    $semester_data_by_id[$id]['listent'] = $student->listent;
                    $semester_data_by_id[$id]['speaking'] = $student->speaking;
                    $semester_data_by_id[$id]['reading'] = $student->reading;
                    $semester_data_by_id[$id]['writing'] = $student->writing;
                    $semester_data_by_id[$id]['essay'] = $student->essay;
                    $semester_data_by_id[$id]['grammar'] = $student->grammar;
                    $semester_data_by_id[$id]['math'] = $student->math;
                    $semester_data_by_id[$id]['chemistry'] = $student->chemistry;
                    $semester_data_by_id[$id]['physical'] = $student->physical;
                    $semester_data_by_id[$id]['history'] = $student->history;
                    $semester_data_by_id[$id]['morality'] = $student->morality;
                    $semester_data_by_id[$id]['art'] = $student->art;
                    $semester_data_by_id[$id]['word'] = $student->word;
                    $semester_data_by_id[$id]['pe'] = $student->pe;
                    $semester_data_by_id[$id]['homework'] = $student->homework;
                    $semester_data_by_id[$id]['healthy'] = $student->healthy;
                    $semester_data_by_id[$id]['steam'] = $student->steam;
                    $semester_data_by_id[$id]['total_score'] = $student->total_score;
                } else {
                    // Accumulate average for first 3 months
                    $semester_data_by_id[$id]['total_avg_3_month'] += (float) $avg;
                    $semester_data_by_id[$id]['month_count']++;
                }
            }
        }



        // Calculate the actual average for the first 3 months
        foreach ($semester_data_by_id as &$student) {

            if ($student['month_count'] > 0) {
                $student['total_avg_3_month'] = $student['total_avg_3_month'] / $student['month_count'];
            }

            // print_r($student['month_count']);
        }




        // Calculate rankings for each month
        $monthly_rankings = [];
        foreach ($three_months as $month) {
            foreach ($monthly_scores as $month => $scores) {
                // print_r($scores);
                $monthly_rankings[$month] = $this->calculateMonthlyRankings($scores);
            }
        }


        // print_r($monthly_rankings);
        // echo ($monthly_rankings);

        $result = array_map(function ($student) use ($grade_level, $three_months, $month_semester, $monthly_rankings) {
            if ($grade_level == 6) {
                $khmer = $student['reading'] + $student['essay'] + $student['writing'];
                $khmer = number_format($khmer, 2);
                $averageKhmer = number_format($khmer / 3, 2, '.', '');

                $social_scient = $student['morality'] + $student['physical'] + $student['history'] + $student['word'] + $student['pe'] + $student['art'] + $student['steam'];
                $social_scient = number_format($social_scient, 2);
                $averageSocial = number_format($social_scient / 7, 2, '.', '');

                $total_score_kcms = number_format(($averageKhmer + $student['chemistry'] + $averageSocial + $student['math']), 2);
                $average_kcms = number_format($total_score_kcms / 4, 2);

                $total_avg_year = number_format(($average_kcms + $student['total_avg_3_month']) / 2, 2, '.', '');
            } else {
                $total_avg_year = number_format(($student['total_avg_3_month'] + $student['total_semester_month']) / 2, 2, '.', '');
            }

            $monthNames = [
                1 => 'មករា',
                2 => 'កុម្ភះ',
                3 => 'មិនា',
                4 => 'មេសា',
                5 => 'ឧសភា',
                6 => 'មិថុនា',
                7 => 'កក្កដា',
                8 => 'សីហា',
                9 => 'កញ្ញា',
                10 => 'តុលា',
                11 => 'វិច្ចិកា',
                12 => 'ធ្នូ'
            ];
            $months_with_rank = [];
            foreach ($three_months as $month) {  // preserve order
                $month_key = "month_$month";
                $avg = $student['months'][$month_key] ?? null;
                $rank = $monthly_rankings[$month][$student['student_id']] ?? null;

                $months_with_rank[$monthNames[$month]] = [   // use month name here
                    "average" => $avg,
                    "rank" => $rank
                ];
            }


            $three_months_name = array_map(function ($m) use ($monthNames) {
                return $monthNames[$m];
            }, $three_months);





            return array_merge([
                'months1' => $months_with_rank,
                'total_score_kcms' => $total_score_kcms ?? null,
                'average_kcms' => $average_kcms ?? null,
                'social_scient' => $social_scient ?? null,
                'averageSocial' => $averageSocial ?? null,
                'averageKhmer' => $averageKhmer ?? null,
                'khmer' => $khmer ?? null,
                'student_id' => $student['student_id'],
                'kh_name' => $student['kh_name'],
                'en_name' => $student['en_name'],
                'gender' => $student['gender'],
                'photo_path' => $student['photo_path'],
                'average_3_month' => number_format($student['total_avg_3_month'], 2, '.', ''),
                'average_month_semester' => number_format($student['total_semester_month'], 2, '.', ''),
                'average_semester1' => $total_avg_year,
                'average_semester2' => 0,
                'type' => 'Semester1',
                'months_config1' => [
                    'three_months' => $three_months,
                    'semester_month' => $month_semester,
                    'monthName' => $three_months_name
                ],
                'listent' => $student['listent'] ?? 0,
                'speaking' => $student['speaking'] ?? 0,
                'reading' => $student['reading'] ?? 0,
                'writing' => $student['writing'] ?? 0,
                'essay' => $student['essay'] ?? 0,
                'grammar' => $student['grammar'] ?? 0,
                'math' => number_format($student['math'] ?? 0, 2, '.', ''),
                'chemistry' => number_format($student['chemistry'] ?? 0, 2, '.', ''),
                'physical' => $student['physical'] ?? 0,
                'history' => $student['history'] ?? 0,
                'morality' => $student['morality'] ?? 0,
                'art' => $student['art'] ?? 0,
                'word' => $student['word'] ?? 0,
                'pe' => $student['pe'] ?? 0,
                'homework' => $student['homework'] ?? 0,
                'healthy' => $student['healthy'] ?? 0,
                'steam' => $student['steam'] ?? 0,
                'grade' => $this->getGradePrimary(avg: $total_avg_year),
                'total_score' => $student['total_score'] ?? 0,
            ]);
        }, array_values($semester_data_by_id));






        // Add ranking logic (your existing addRank calls)


        // $result = $this->addRank($result, 'months1', 'rank_3_month123');

        $result = $this->addRank($result, 'average_3_month', 'rank_3_month');
        $result = $this->addRank($result, 'average_month_semester', 'rank_month_semester');
        // $result = $this->sortAndRank($result, 'average_semester1');
        $result = $this->addRank($result, 'average_semester1', 'rank');
        $result = $this->addRank($result, 'listent', 'rankListent');
        $result = $this->addRank($result, 'speaking', 'rankSpeaking');
        $result = $this->addRank($result, 'reading', 'rankReading');
        $result = $this->addRank($result, 'writing', 'rankWriting');
        $result = $this->addRank($result, 'essay', 'rankEssay');
        $result = $this->addRank($result, 'grammar', 'rankGrammar');
        $result = $this->addRank($result, 'math', 'rankMath');
        $result = $this->addRank($result, 'chemistry', 'rankChemistry');
        $result = $this->addRank($result, 'physical', 'rankPhysical');
        $result = $this->addRank($result, 'history', 'rankHistory');
        $result = $this->addRank($result, 'morality', 'rankMorality');
        $result = $this->addRank($result, 'art', 'rankArt');
        $result = $this->addRank($result, 'word', 'rankWord');
        $result = $this->addRank($result, 'pe', 'rankPe');
        $result = $this->addRank($result, 'homework', 'rankHomework');
        $result = $this->addRank($result, 'healthy', 'rankHealthy');
        $result = $this->addRank($result, 'steam', 'rankSteam');
        $result = $this->addRank($result, 'averageKhmer', 'rankKhmer');
        $result = $this->addRank($result, 'averageSocial', 'rankSocial');
        $result = $this->addRank($result, 'average_kcms', 'rankKcms');


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

    private function calculateMonthlyRankings(array $scores): array
    {
        // Sort scores in descending order
        arsort($scores);

        $rankings = [];
        $rank = 1;
        $previous_score = null;
        $previous_rank = 1;

        foreach ($scores as $student_id => $score) {
            if ($previous_score === null) {
                // First student
                $rankings[$student_id] = $rank;
            } elseif ($score == $previous_score) {
                // Tie with previous student
                $rankings[$student_id] = $previous_rank;
            } else {
                // Different score, increment rank
                $rankings[$student_id] = $rank;
            }

            $previous_score = $score;
            $previous_rank = $rankings[$student_id];
            $rank++;
        }

        return $rankings;
    }


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

    public function getSemesterTwoPrimary($class_id, $avg_mutil, $grade_level, $year_id, $campus_id)
    {
        // $months = [5, 6, 7, 8];

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
        //  else {
        //     return response()->json($semesterConfig);
        // }

        // Get months for first 3 months and semester month
        $three_months = collect(explode(',', $semesterConfig->three_months_semester2))
            ->map(fn($m) => (int) trim($m))
            ->filter()
            ->values()
            ->toArray();

        // return response()->json($three_months);
        // $three_month = [12,1,2] work well

        $month_semester = (int) $semesterConfig->semester_month2;

        // Combine all months: first 3 months + semester month
        $all_months = array_merge($three_months, [$month_semester]);


        // return response()->json($all_months);
        // work well

        $semester_data_by_id = [];

        $semester_data = [];

        $monthly_scores = []; // Store all students' scores for each month

        foreach ($all_months as $m) {
            $monthData = DB::table('score_primary_cc as spc')
                ->join('students as s', 'spc.student_id', '=', 's.id')
                ->where('class_id', $class_id)
                ->where('month_id', $m)
                ->get()
                ->toArray();

            if (empty($monthData)) {
                return 0;
            }

            // dd($monthData);

            foreach ($monthData as $student) {
                $id = $student->id;
                // $avg = $student->total_score / $avg_mutil;
                $avg = number_format($student->total_avg, 2, '.', '');





                // check and add to $semester_data if not yet but if it already have skip
                if (!isset($semester_data[$id])) {
                    $semester_data[$id] = (array) $student + [
                        'student_id' => $id,
                        'total_avg_3_month' => 0,
                        'month_count' => 0,
                        'total_semester_month' => 0,
                    ];
                }

                $semester_data[$id]['months']["month_$m"] = (float) $avg;       // store average
                // $semester_data_by_id[$id]['months']["rank_month_$month"] = null;

                // ✅ also keep track for ranking
                $monthly_scores[$m][$id] = (float) $avg;

                if ($m == $month_semester) {
                    $semester_data[$id]['total_semester_month'] = $avg;
                    $semester_data[$id]['listent'] = $student->listent;
                    $semester_data[$id]['speaking'] = $student->speaking;
                    $semester_data[$id]['reading'] = $student->reading;
                    $semester_data[$id]['writing'] = $student->writing;
                    $semester_data[$id]['essay'] = $student->essay;
                    $semester_data[$id]['grammar'] = $student->grammar;
                    $semester_data[$id]['math'] = $student->math;
                    $semester_data[$id]['chemistry'] = $student->chemistry;
                    $semester_data[$id]['physical'] = $student->physical;
                    $semester_data[$id]['history'] = $student->history;
                    $semester_data[$id]['morality'] = $student->morality;
                    $semester_data[$id]['art'] = $student->art;
                    $semester_data[$id]['word'] = $student->word;
                    $semester_data[$id]['pe'] = $student->pe;
                    $semester_data[$id]['homework'] = $student->homework;
                    $semester_data[$id]['healthy'] = $student->healthy;
                    $semester_data[$id]['steam'] = $student->steam;
                    $semester_data[$id]['total_score'] = $student->total_score;
                } else {
                    // $semester_data[$id]['total_avg_3_month'] += $avg / 3;
                    $semester_data[$id]['total_avg_3_month'] += $avg;
                    $semester_data[$id]['month_count']++;
                }
            }
        }

        // Calculate the actual average for the first 3 months
        foreach ($semester_data as &$student) {
            if ($student['month_count'] > 0) {
                $student['total_avg_3_month'] = $student['total_avg_3_month'] / $student['month_count'];
            }
        }

        // Calculate rankings for each month
        $monthly_rankings = [];
        foreach ($three_months as $month) {
            foreach ($monthly_scores as $month => $scores) {
                // print_r($scores);
                $monthly_rankings[$month] = $this->calculateMonthlyRankings($scores);
            }
        }

        $result = array_map(function ($student) use ($grade_level, $three_months, $month_semester, $monthly_rankings) {
            if ($grade_level == 6) {
                $khmer = $student['reading'] + $student['essay'] + $student['writing'];
                $khmer = number_format($khmer, 2);
                $averageKhmer = number_format($khmer / 3, 2, '.', '');

                $social_scient = $student['morality'] + $student['physical'] + $student['history'] + $student['word'] + $student['pe'] + $student['art'] + $student['steam'];
                $social_scient = number_format($social_scient, 2);
                $averageSocial = number_format($social_scient / 7, 2, '.', '');

                $total_score_kcms = number_format(($averageKhmer + $student['chemistry'] + $averageSocial + $student['math']), 2);
                $average_kcms = number_format($total_score_kcms / 4, 2);

                $total_avg_year = number_format(($average_kcms + $student['total_avg_3_month']) / 2, 2, '.', '');
            } else {
                $total_avg_year = number_format(($student['total_avg_3_month'] + $student['total_semester_month']) / 2, 2, '.', '');
            }

            $monthNames = [
                1 => 'មករា',
                2 => 'កុម្ភះ',
                3 => 'មិនា',
                4 => 'មេសា',
                5 => 'ឧសភា',
                6 => 'មិថុនា',
                7 => 'កក្កដា',
                8 => 'សីហា',
                9 => 'កញ្ញា',
                10 => 'តុលា',
                11 => 'វិច្ចិកា',
                12 => 'ធ្នូ'
            ];
            $months_with_rank = [];
            foreach ($three_months as $month) {  // preserve order
                $month_key = "month_$month";
                $avg = $student['months'][$month_key] ?? null;
                $rank = $monthly_rankings[$month][$student['student_id']] ?? null;

                $months_with_rank[$monthNames[$month]] = [   // use month name here
                    "average" => $avg,
                    "rank" => $rank
                ];
            }


            $three_months_name = array_map(function ($m) use ($monthNames) {
                return $monthNames[$m];
            }, $three_months);

            return [
                'months2' => $months_with_rank,
                'total_score_kcms' => $total_score_kcms ?? null,
                'average_kcms' => $average_kcms ?? null,
                'social_scient' => $social_scient ?? null,
                'averageSocial' => $averageSocial ?? null,
                'averageKhmer' => $averageKhmer ?? null,
                'khmer' => $khmer ?? null,
                'student_id' => $student['student_id'],
                'kh_name' => $student['kh_name'],
                'en_name' => $student['en_name'],
                'gender' => $student['gender'],
                'photo_path' => $student['photo_path'],
                'average_3_month' => number_format($student['total_avg_3_month'], 2, '.', ''),
                'average_month_semester' => number_format($student['total_semester_month'], 2, '.', ''),
                'average_semester1' => 0,
                'average_semester2' => $total_avg_year,
                'type' => 'Semester1',
                'months_config2' => [
                    'three_months' => $three_months,
                    'semester_month' => $month_semester,
                    'monthName' => $three_months_name
                ],
                'listent' => $student['listent'] ?? 0,
                'speaking' => $student['speaking'] ?? 0,
                'reading' => $student['reading'] ?? 0,
                'writing' => $student['writing'] ?? 0,
                'essay' => $student['essay'] ?? 0,
                'grammar' => $student['grammar'] ?? 0,
                'math' => number_format($student['math'] ?? 0, 2, '.', ''),
                'chemistry' => number_format($student['chemistry'] ?? 0, 2, '.', ''),
                'physical' => $student['physical'] ?? 0,
                'history' => $student['history'] ?? 0,
                'morality' => $student['morality'] ?? 0,
                'art' => $student['art'] ?? 0,
                'word' => $student['word'] ?? 0,
                'pe' => $student['pe'] ?? 0,
                'homework' => $student['homework'] ?? 0,
                'healthy' => $student['healthy'] ?? 0,
                'steam' => $student['steam'] ?? 0,
                'grade' => $this->getGradePrimary(avg: $total_avg_year),
                'total_score' => $student['total_score'] ?? 0,
            ];
        }, array_values($semester_data));

        $result = $this->addRank($result, 'average_3_month', 'rank_3_month');
        $result = $this->addRank($result, 'average_month_semester', 'rank_month_semester');
        $result = $this->addRank($result, 'average_semester2', 'rank');
        $result = $this->addRank($result, 'listent', 'rankListent');
        $result = $this->addRank($result, 'speaking', 'rankSpeaking');
        $result = $this->addRank($result, 'reading', 'rankReading');
        $result = $this->addRank($result, 'writing', 'rankWriting');
        $result = $this->addRank($result, 'essay', 'rankEssay');
        $result = $this->addRank($result, 'grammar', 'rankGrammar');
        $result = $this->addRank($result, 'math', 'rankMath');
        $result = $this->addRank($result, 'chemistry', 'rankChemistry');
        $result = $this->addRank($result, 'physical', 'rankPhysical');
        $result = $this->addRank($result, 'history', 'rankHistory');
        $result = $this->addRank($result, 'morality', 'rankMorality');
        $result = $this->addRank($result, 'art', 'rankArt');
        $result = $this->addRank($result, 'word', 'rankWord');
        $result = $this->addRank($result, 'pe', 'rankPe');
        $result = $this->addRank($result, 'homework', 'rankHomework');
        $result = $this->addRank($result, 'healthy', 'rankHealthy');
        $result = $this->addRank($result, 'steam', 'rankSteam');
        $result = $this->addRank($result, 'averageKhmer', 'rankKhmer');
        $result = $this->addRank($result, 'averageSocial', 'rankSocial');
        $result = $this->addRank($result, 'average_kcms', 'rankKcms');

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

        return $result;
    }

    public function processFullYear($class_id, $avg_mutil, $info, $allStudent, $student_female, $gradeLevel, $campus_id, $year_id)
    {
        // Get semester results
        $semester1 = $this->getSemesterOnePrimary($class_id, $avg_mutil, $gradeLevel, $campus_id, $year_id);
        $semester2 = $this->getSemesterTwoPrimary($class_id, $avg_mutil, $gradeLevel, $campus_id, $year_id);

        // If both empty
        if (empty($semester1) && empty($semester2)) {
            return response()->json(['status' => 1, 'message' => 'UnSuccessfully']);
        }

        // Decode if response is JSON
        $semester1 = is_array($semester1) ? $semester1 : (json_decode($semester1, true) ?? []);
        $semester2 = is_array($semester2) ? $semester2 : (json_decode($semester2, true) ?? []);

        // Index results by student_id for easy merging
        $yearly = [];

        foreach ($semester1 as $student) {
            $id = $student['student_id'];
            $yearly[$id] = [
                'student_id' => $id,
                'kh_name' => $student['kh_name'],
                'en_name' => $student['en_name'],
                'gender' => $student['gender'],
                'photo_path' => $student['photo_path'] ?? null,
                'average_semester1' => $student['average_semester1'] ?? 0,
                'average_semester2' => 0,
                'months_config1' => $student['months_config1'] ?? [],
                'months_config2' => [],
                'months1'          => $student['months1'] ?? [],
                'months2'          => [],
                'math1' => $student['math'] ?? null,
                'math2' => null,
                'averageKhmer1' => $student['averageKhmer'] ?? null,
                'averageKhmer2' => null,
                'chemistry1' => $student['chemistry'] ?? null,
                'chemistry2' => null,
                'social1' => $student['averageSocial'] ?? null,
                'social2' => null,
                'rank1' => $student['rank'] ?? null,
                'rank2' => null,
                'rankMath1' => $student['rankMath'] ?? null,
                'rankMath2' => null,
                'rankKhmer1' => $student['rankKhmer'] ?? null,
                'rankKhmer2' => null,
                'rankChemistry1' => $student['rankChemistry'] ?? null,
                'rankChemistry2' => null,
                'rankSocial1' => $student['rankSocial'] ?? null,
                'rankSocial2' => null,
                'total_score_kcms1' => $student['total_score_kcms'] ?? null,
                'total_score_kcms2' => null,
                'rankKcms1' => $student['rankKcms'] ?? null,
                'rankKcms2' => null,
                'average_kcms1' => $student['average_kcms'] ?? null,
                'average_kcms2' => null
            ];
        }

        foreach ($semester2 as $student) {
            $id = $student['student_id'];
            if (!isset($yearly[$id])) {
                // If student exists only in semester2
                $yearly[$id] = [
                    'student_id' => $id,
                    'kh_name' => $student['kh_name'],
                    'en_name' => $student['en_name'],
                    'gender' => $student['gender'],
                    'photo_path' => $student['photo_path'] ?? null,
                    'average_semester1' => 0,
                    'average_semester2' => $student['average_semester2'] ?? 0,
                    'months_config1' => [],
                    'months_config2' => $student['months_config2'] ?? [],
                    'months1'          => [],
                    'months2'          => $student['months2'] ?? [],
                    'math2' => $student['math'] ?? null,
                    'math1' => null,
                    'averageKhmer2' => $student['averageKhmer'] ?? null,
                    'averageKhmer1' => null,
                    'chemistry2' => $student['chemistry'] ?? null,
                    'chemistry1' => null,
                    'social2' => $student['averageSocial'] ?? null,
                    'social1' => null,
                    'rank2' => $student['rank'] ?? null,
                    'rank1' => null,
                    'rankMath2' => $student['rankMath'] ?? null,
                    'rankMath1' => null,
                    'rankKhmer2' => $student['rankKhmer'] ?? null,
                    'rankKhmer1' => null,
                    'rankChemistry2' => $student['rankChemistry'] ?? null,
                    'rankChemistry1' => null,
                    'rankSocial2' => $student['rankSocial'] ?? null,
                    'rankSocial1' => null,
                    'total_score_kcms2' => $student['total_score_kcms'] ?? null,
                    'total_score_kcms1' => null,
                    'rankKcms2' => $student['rankKcms'] ?? null,
                    'rankKcms1' => null,
                    'average_kcms2' => $student['average_kcms'] ?? null,
                    'average_kcms1' => null
                ];
            } else {
                // Merge semester2 data
                $yearly[$id]['average_semester2'] = $student['average_semester2'] ?? 0;
                $yearly[$id]['months_config2'] = $student['months_config2'] ?? [];
                $yearly[$id]['months2'] = $student['months2'] ?? [];
                $yearly[$id]['math2'] = $student['math'] ?? null;
                $yearly[$id]['averageKhmer2'] = $student['averageKhmer'] ?? null;
                $yearly[$id]['chemistry2'] = $student['chemistry'] ?? null;
                $yearly[$id]['social2'] = $student['averageSocial'] ?? null;
                $yearly[$id]['rank2'] = $student['rank'] ?? 0;
                $yearly[$id]['rankMath2'] = $student['rankMath'] ?? 0;
                $yearly[$id]['rankKhmer2'] = $student['rankKhmer'] ?? 0;
                $yearly[$id]['rankChemistry2'] = $student['rankChemistry'] ?? 0;
                $yearly[$id]['rankSocial2'] = $student['rankSocial'] ?? 0;
                $yearly[$id]['total_score_kcms2'] = $student['total_score_kcms'] ?? 0;
                $yearly[$id]['rankKcms2'] = $student['rankKcms'] ?? 0;
                $yearly[$id]['average_kcms2'] = $student['average_kcms'] ?? 0;
            }
        }

        // Process yearly average
        $processed = array_map(function ($student) use ($gradeLevel, $semester1) {

            $allMonths =  array_merge($student['months1'], $student['months2']);

            $monthCount = count($allMonths);

            $sumMonths = 0;
            foreach ($allMonths as $monthName => $monthData) {
                $sumMonths += $monthData['average'] ?? 0;
            }

            // Divide by 6 (fixed number of months) if you always want 6 months
            $allMonthAvg = $monthCount > 0
                ? number_format($sumMonths / $monthCount, 2, '.', '')
                : 0;

            $semester1 = number_format($student['average_semester1'], 2, '.', '');
            $semester2 = number_format($student['average_semester2'], 2, '.', '');

            // If one semester is missing, average only the available one

            if ($gradeLevel == 6) {
                if ($student['average_semester1'] > 0 && $student['average_semester2'] > 0) {
                    $total_sum = number_format(($student['average_kcms1'] + $student['average_kcms2'] + $allMonthAvg) / 3, 2);
                } else {
                    $total_sum = number_format(max($student['average_kcms1'], $student['average_kcms2']), 2);
                }
            } else {
                if ($student['average_semester1'] > 0 && $student['average_semester2'] > 0) {
                    $total_sum = number_format(($student['average_semester1'] + $student['average_semester2']) / 2, 2);
                } else {
                    $total_sum = number_format(max($student['average_semester1'], $student['average_semester2']), 2);
                }
            }




            return [
                'student_id' => $student['student_id'],
                'kh_name' => $student['kh_name'],
                'en_name' => $student['en_name'],
                'gender' => $student['gender'],
                'photo_path' => $student['photo_path'],
                'average_semester1' => $semester1,

                'average_semester2' => $semester2,
                'average_year' => $total_sum,
                'grade' => $this->getGradePrimary($total_sum),
                'type' => 'All',
                // 👇 keep months configs in final data
                'months_config1' => $student['months_config1'],
                'months_config2' => $student['months_config2'],
                'months1' => $student['months1'],
                'months2' => $student['months2'],
                'rank1' => $student['rank1'],
                'rank2' => $student['rank2'],
                'grade1' => $this->getGradePrimary($semester1),
                'grade2' => $this->getGradePrimary($semester2),
                'math1'             => $student['math1'] ?? number_format(0, 2, '.', ''),
                'math2'             => $student['math2'] ?? number_format(0, 2, '.', ''),
                'rankMath1'             => $student['rankMath1'],
                'rankMath2'             => $student['rankMath2'],
                'khmer1'             => $student['averageKhmer1'] ?? number_format(0, 2, '.', ''),
                'khmer2'             => $student['averageKhmer2'] ?? number_format(0, 2, '.', ''),
                'rankKhmer1'             => $student['rankKhmer1'],
                'rankKhmer2'             => $student['rankKhmer2'],
                'chemistry1'             => $student['chemistry1'] ?? number_format(0, 2, '.', ''),
                'chemistry2'             => $student['chemistry2'] ?? number_format(0, 2, '.', ''),
                'rankChemistry1'             => $student['rankChemistry1'],
                'rankChemistry2'             => $student['rankChemistry2'],
                'social1'             => $student['social1'] ?? number_format(0, 2, '.', ''),
                'social2'             => $student['social2'] ?? number_format(0, 2, '.', ''),
                'rankSocial1'             => $student['rankSocial1'],
                'rankSocial2'             => $student['rankSocial2'],
                'total_score_kcms2'             => $student['total_score_kcms2'],
                'total_score_kcms1'             => $student['total_score_kcms1'],
                'rankKcms1'             => $student['rankKcms1'],
                'rankKcms2'             => $student['rankKcms2'],
                'average_kcms1'             => $student['average_kcms1'],
                'average_kcms2'             => $student['average_kcms2'],
                "allMonths" => $allMonths,
                "monthCount" => $monthCount,
                "sumMonths" => $sumMonths,
                "allMonthAvg" => $allMonthAvg,


            ];
        }, array_values($yearly));

        $processed = $this->addRank($processed, 'allMonthAvg', 'rankAllMonth');

        // Sort & Rank
        $sorted = $this->sortAndRank($processed, 'average_year');

        return response()->json([
            'status' => 0,
            'data' => $sorted,
            'message' => 'Successfully for Year Result',
            'type' => 'ឆ្នាំ',
            'info' => $info,
            'allStudent' => $allStudent,
            'student_female' => $student_female,
            'semester1' => $semester1,
            'semester2' => $semester2
        ]);
    }
}
