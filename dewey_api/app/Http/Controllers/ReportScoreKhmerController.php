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
                $data = $this->fetchMonthData($class_id, $month_id, $info);
                return $this->processAndRespond($data, $avg_mutil, 'ខែ', 'Succesfully Month', $info, $month_name, $allStudent, $student_female);
            case 'semester1':
                $data = $this->getSemesterOnePrimary($class_id, $avg_mutil, $class->grade_level_id ?? '');
                //    print_r($data);
                $data_rank = [];
                $checkwith_month_config = [];
                $i = 0;
                foreach ($data as $row) {

                    array_push($data_rank, $row['months']);
                }

                // $rankedDataWithCollection = $this->addRankingsToData($data_rank);
                // echo "Sample ranked data:\n";
                // foreach (array_slice($rankedDataWithCollection, 0, 3) as $index => $item) {
                //     echo "Record " . ($index + 1) . ":\n";
                //     foreach ($item as $key => $value) {
                //         echo "  $key: $value\n";
                //     }
                //     echo "\n";
                // }



                return response()->json([
                    'type' => "ឆមាសទី១",
                    'data' => $data,
                    'info' => $info,
                    'allStudent' => $allStudent,
                    'student_female' => $student_female,
                    "data_rank" => $data_rank

                ]);

            case 'semester2':
                $data = $this->getSemesterTwoPrimary($class_id, $avg_mutil, $class->grade_level_id ?? '');
                return response()->json([
                    'type' => "ឆមាសទី២",
                    'data' => $data,
                    'info' => $info,
                    'allStudent' => $allStudent,
                    'student_female' => $student_female
                ]);

            default:
                return $this->processFullYear($class_id, $avg_mutil, $info, $allStudent, $student_female, $class->grade_level_id ?? '');
        }
    }

    // Function to add rankings to the data
    function addRankingsToData($data)
    {
        $months = ['month_1', 'month_2', 'month_3', 'month_12'];

        // Filter out incomplete records
        $completeData = array_filter($data, function ($item) use ($months) {
            foreach ($months as $month) {
                if (!isset($item[$month]) || $item[$month] === 0) {
                    return false;
                }
            }
            return true;
        });

        $rankedData = [];

        foreach ($months as $month) {
            // Extract values for this month with their original indices
            $monthValues = [];
            foreach ($completeData as $index => $item) {
                $monthValues[] = [
                    'index' => $index,
                    'value' => $item[$month]
                ];
            }

            // Sort by value in descending order (highest first)
            usort($monthValues, function ($a, $b) {
                return $b['value'] <=> $a['value'];
            });

            // Assign rankings
            foreach ($monthValues as $rank => $item) {
                $rankedData[$item['index']][$month . '_rank'] = $rank + 1;
            }
        }

        // Add rankings to original data
        foreach ($completeData as $index => $item) {
            $completeData[$index] = array_merge($item, $rankedData[$index]);
        }

        return array_values($completeData);
    }

    // Using Laravel Collection (Alternative approach)
    function addRankingsWithCollection($data)
    {
        $collection = collect($data);
        $months = ['month_1', 'month_2', 'month_3', 'month_12'];

        // Filter out incomplete records
        $completeData = $collection->filter(function ($item) use ($months) {
            foreach ($months as $month) {
                if (!isset($item[$month]) || $item[$month] === 0) {
                    return false;
                }
            }
            return true;
        });

        foreach ($months as $month) {
            // Sort by month value and add ranking
            $sorted = $completeData->sortByDesc($month)->values();

            $completeData = $completeData->map(function ($item) use ($sorted, $month) {
                $rank = $sorted->search(function ($sortedItem) use ($item, $month) {
                    return $sortedItem[$month] === $item[$month];
                });

                $item[$month . '_rank'] = $rank + 1;
                return $item;
            });
        }

        return $completeData->values()->all();
    }
    private function getAvgMutil($grade)
    {
        if (str_contains($grade, '1') || str_contains($grade, '2')) return 13;
        if (str_contains($grade, '3') || str_contains($grade, '4')) return 15;
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
                'listent' => $student->listent,
                'speaking' => $student->speaking,
                'writing' => $student->writing,
                'reading' => $student->reading,
                'essay' => $student->essay,
                'grammar' => $student->grammar,
                'math' => $student->math,
                'chemistry' => $student->chemistry,
                'physical' => $student->physical,
                'history' => $student->history,
                'morality' => $student->morality,
                'art' => $student->art,
                'word' => $student->word,
                'pe' => $student->pe,
                'homework' => $student->homework,
                'healthy' => $student->healthy,
                'steam' => $student->steam,
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
    public function getSemesterOnePrimary($class_id, $avg_mutil, $grade_level)
    {

        // Get semester months configuration from database
        $semesterConfig = DB::table('setting_semester_list')

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

                $semester_data_by_id[$id]['months']["month_$month"] = (float) $avg;       // store average
                // $semester_data_by_id[$id]['months']["rank_month_$month"] = null;

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
                    $semester_data_by_id[$id]['total_avg_3_month'] += $avg;
                    $semester_data_by_id[$id]['month_count']++;
                }
            }
        }



        // Calculate the actual average for the first 3 months
        foreach ($semester_data_by_id as &$student) {

            if ($student['month_count'] > 0) {
                $student['total_avg_3_month'] =  $student['total_avg_3_month'] / $student['month_count'];
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
                1  => 'មករា',
                2  => 'កុម្ភះ',
                3  => 'មិនា',
                4  => 'មេសា',
                5  => 'ឧសភា',
                6  => 'មិថុនា',
                7  => 'កក្កដា',
                8  => 'សីហា',
                9  => 'កញ្ញា',
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
                'months' => $months_with_rank,
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
                'months_config' => [
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
                'math' => $student['math'] ?? 0,
                'chemistry' => $student['chemistry'] ?? 0,
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


        $result = $this->addRank($result, 'months', 'rank_3_month123');

        $result = $this->addRank($result, 'average_3_month', 'rank_3_month');
        $result = $this->addRank($result, 'average_month_semester', 'rank_month_semester');
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

    public function getSemesterTwoPrimary($class_id, $avg_mutil, $grade_level)
    {
        // $months = [5, 6, 7, 8];

        // Get semester months configuration from database
        $semesterConfig = DB::table('setting_semester_list')

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
                1  => 'មករា',
                2  => 'កុម្ភះ',
                3  => 'មិនា',
                4  => 'មេសា',
                5  => 'ឧសភា',
                6  => 'មិថុនា',
                7  => 'កក្កដា',
                8  => 'សីហា',
                9  => 'កញ្ញា',
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
                'months' => $months_with_rank,
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
                'months_config' => [
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
                'math' => $student['math'] ?? 0,
                'chemistry' => $student['chemistry'] ?? 0,
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

        // $result = array_map(function ($student) {
        //     $averageSemester = number_format(($student['total_avg_3_month'] + $student['total_semester_month']) / 2, 2, '.', '');

        //     $khmer = $student['reading'] + $student['essay'] + $student['writing'];

        //     $khmer = number_format($khmer, 2);
        //     $averageKhmer = number_format($khmer / 3, 2, '.', '');

        //     $social_scient = $student['morality'] + $student['physical'] + $student['history'] + $student['word'] + $student['pe'] + $student['art'] + $student['steam'];
        //     $social_scient = number_format($social_scient, 2);

        //     $averageSocial  = number_format($social_scient / 7, 2, '.', '');

        //     $total_score_kcms = number_format(($averageKhmer + $student['chemistry'] + $averageSocial + $student['math']), 2);

        //     $average_kcms = number_format($total_score_kcms / 4, 2);

        //     $total_avg_year = number_format(($average_kcms + $student['total_avg_3_month']) / 2, 2, '.', '');


        //     return [
        //         'total_score_kcms' => $total_score_kcms,
        //         'average_kcms' => $average_kcms,
        //         'social_scient' => $social_scient,
        //         'averageSocial' => $averageSocial,
        //         'averageKhmer' => $averageKhmer,
        //         'khmer' => $khmer,
        //         'student_id' => $student['student_id'],
        //         'kh_name' => $student['kh_name'],
        //         'en_name' => $student['en_name'],
        //         'gender' => $student['gender'],
        //         'photo_path' => $student['photo_path'],
        //         'average_3_month' => number_format($student['total_avg_3_month'], 2, '.', ''),
        //         'average_month_semester' => number_format($student['total_semester_month'], 2, '.', ''),
        //         'average_semester1' => $total_avg_year,
        //         'average_semester2' => 0,
        //         'type' => 'Semester1',
        //         'listent' => $student['listent'] ?? 0,
        //         'speaking' => $student['speaking'] ?? 0,
        //         'reading' => $student['reading'] ?? 0,
        //         'writing' => $student['writing'] ?? 0,
        //         'essay' => $student['essay'] ?? 0,
        //         'grammar' => $student['grammar'] ?? 0,
        //         'math' => $student['math'] ?? 0,
        //         'chemistry' => $student['chemistry'] ?? 0,
        //         'physical' => $student['physical'] ?? 0,
        //         'history' => $student['history'] ?? 0,
        //         'morality' => $student['morality'] ?? 0,
        //         'art' => $student['art'] ?? 0,
        //         'word' => $student['word'] ?? 0,
        //         'pe' => $student['pe'] ?? 0,
        //         'homework' => $student['homework'] ?? 0,
        //         'healthy' => $student['healthy'] ?? 0,
        //         'steam' => $student['steam'] ?? 0,
        //         'grade' => $this->getGradePrimary(avg: $total_avg_year),
        //         'total_score' => $student['total_score'] ?? 0,

        //     ];
        // }, array_values($semester_data));

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

        return $result;
    }

    public function processFullYear($class_id, $avg_mutil, $info, $allStudent, $student_female, $gradeLevel)
    {
        $semester1 = $this->getSemesterOnePrimary($class_id, $avg_mutil, $gradeLevel);
        $semester2 = $this->getSemesterTwoPrimary($class_id, $avg_mutil, $gradeLevel);


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
                    'gender' => $student['gender'],
                    'photo_path' => $student['photo_path'] ?? null,
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
                'grade' => $this->getGradePrimary($total_sum),
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
 // $months = [12, 1, 2, 3];
        // $semester_data_by_id = [];

        // // Fetch and process all months
        // foreach ($months as $month) {
        //     $month_data = DB::table('score_primary_cc')
        //         ->join('students', 'score_primary_cc.student_id', '=', 'students.id')
        //         ->where('class_id', $class_id)
        //         ->where('month_id', $month)
        //         ->get()
        //         ->toArray();
        //     if (empty($month_data)) {
        //         return 0;
        //     }

        //     // Process each student's data for the month
        //     foreach ($month_data as $student) {
        //         $id = $student->id;
        //         // $avg = floor(($student->total_score / $avg_mutil) * 100) / 100;
        //         $avg = number_format($student->total_avg, 2, '.', '');

        //         // If the student is new, initialize their data
        //         if (!isset($semester_data_by_id[$id])) {
        //             $semester_data_by_id[$id] = (array) $student + [
        //                 'student_id' => $id,
        //                 'total_avg_3_month' => 0,
        //                 'month_count' => 0, // Add counter for months
        //                 'total_semester_month' => 0,
        //             ];
        //         }

        //         if ($month == 3) {
        //             $semester_data_by_id[$id]['total_semester_month'] = $avg;
        //             $semester_data_by_id[$id]['listent'] = $student->listent;
        //             $semester_data_by_id[$id]['speaking'] = $student->speaking;
        //             $semester_data_by_id[$id]['reading'] = $student->reading;
        //             $semester_data_by_id[$id]['writing'] = $student->writing;
        //             $semester_data_by_id[$id]['essay'] = $student->essay;
        //             $semester_data_by_id[$id]['grammar'] = $student->grammar;
        //             $semester_data_by_id[$id]['math'] = $student->math;
        //             $semester_data_by_id[$id]['chemistry'] = $student->chemistry;
        //             $semester_data_by_id[$id]['physical'] = $student->physical;
        //             $semester_data_by_id[$id]['history'] = $student->history;
        //             $semester_data_by_id[$id]['morality'] = $student->morality;
        //             $semester_data_by_id[$id]['art'] = $student->art;
        //             $semester_data_by_id[$id]['word'] = $student->word;
        //             $semester_data_by_id[$id]['pe'] = $student->pe;
        //             $semester_data_by_id[$id]['homework'] = $student->homework;
        //             $semester_data_by_id[$id]['healthy'] = $student->healthy;
        //             $semester_data_by_id[$id]['steam'] = $student->steam;
        //             $semester_data_by_id[$id]['total_score'] = $student->total_score;
        //         } else {
        //             // $semester_data_by_id[$id]['total_avg_3_month'] += $student->total_avg / 3;
        //             // Accumulate average for first 3 months
        //             $semester_data_by_id[$id]['total_avg_3_month'] += $avg;
        //             $semester_data_by_id[$id]['month_count']++;
        //         }
        //     }
        // }

        // // Calculate the actual average for the first 3 months
        // foreach ($semester_data_by_id as &$student) {
        //     if ($student['month_count'] > 0) {
        //         $student['total_avg_3_month'] = $student['total_avg_3_month'] / $student['month_count'];
        //     }
        // }

        // $result = array_map(function ($student) use ($grade_level) {


        //     // $total_avg_year = number_format(($average_kcms + $student['total_avg_3_month']) / 2, 2, '.', '');

        //     if ($grade_level == 6) {

        //         $khmer = $student['reading'] + $student['essay'] + $student['writing'];

        //         $khmer = number_format($khmer, 2);
        //         $averageKhmer = number_format($khmer / 3, 2, '.', '');

        //         $social_scient = $student['morality'] + $student['physical'] + $student['history'] + $student['word'] + $student['pe'] + $student['art'] + $student['steam'];

        //         $social_scient = number_format($social_scient, 2);

        //         $averageSocial  = number_format($social_scient / 7, 2, '.', '');

        //         $total_score_kcms = number_format(($averageKhmer + $student['chemistry'] + $averageSocial + $student['math']), 2);

        //         $average_kcms = number_format($total_score_kcms / 4, 2);

        //         $total_avg_year = number_format(($average_kcms + $student['total_avg_3_month']) / 2, 2, '.', '');
        //     } else {
        //         $total_avg_year = number_format(($student['total_avg_3_month'] + $student['total_semester_month']) / 2, 2, '.', '');
        //     }

        //     return [

        //         'total_score_kcms' => $total_score_kcms ?? null,
        //         'average_kcms' => $average_kcms ?? null,
        //         'social_scient' => $social_scient ?? null,
        //         'averageSocial' => $averageSocial ?? null,
        //         'averageKhmer' => $averageKhmer ?? null,
        //         'khmer' => $khmer ?? null,
        //         'student_id' => $student['student_id'],
        //         'kh_name' => $student['kh_name'],
        //         'en_name' => $student['en_name'],
        //         'gender' => $student['gender'],
        //         'photo_path' => $student['photo_path'],
        //         'average_3_month' => number_format($student['total_avg_3_month'], 2, '.', ''),
        //         'average_month_semester' => number_format($student['total_semester_month'], 2, '.', ''),
        //         'average_semester1' => $total_avg_year,
        //         'average_semester2' => 0,
        //         'type' => 'Semester1',
        //         'listent' => $student['listent'] ?? 0,
        //         'speaking' => $student['speaking'] ?? 0,
        //         'reading' => $student['reading'] ?? 0,
        //         'writing' => $student['writing'] ?? 0,
        //         'essay' => $student['essay'] ?? 0,
        //         'grammar' => $student['grammar'] ?? 0,
        //         'math' => $student['math'] ?? 0,
        //         'chemistry' => $student['chemistry'] ?? 0,
        //         'physical' => $student['physical'] ?? 0,
        //         'history' => $student['history'] ?? 0,
        //         'morality' => $student['morality'] ?? 0,
        //         'art' => $student['art'] ?? 0,
        //         'word' => $student['word'] ?? 0,
        //         'pe' => $student['pe'] ?? 0,
        //         'homework' => $student['homework'] ?? 0,
        //         'healthy' => $student['healthy'] ?? 0,
        //         'steam' => $student['steam'] ?? 0,
        //         'grade' => $this->getGradePrimary(avg: $total_avg_year),
        //         'total_score' => $student['total_score'] ?? 0,
        //     ];
        // }, array_values($semester_data_by_id));

        // $result = $this->addRank($result, 'average_3_month', 'rank_3_month');
        // $result = $this->addRank($result, 'average_month_semester', 'rank_month_semester');
        // $result = $this->addRank($result, 'average_semester1', 'rank');
        // $result = $this->addRank($result, 'listent', 'rankListent');
        // $result = $this->addRank($result, 'speaking', 'rankSpeaking');
        // $result = $this->addRank($result, 'reading', 'rankReading');
        // $result = $this->addRank($result, 'writing', 'rankWriting');
        // $result = $this->addRank($result, 'essay', 'rankEssay');
        // $result = $this->addRank($result, 'grammar', 'rankGrammar');
        // $result = $this->addRank($result, 'math', 'rankMath');
        // $result = $this->addRank($result, 'chemistry', 'rankChemistry');
        // $result = $this->addRank($result, 'physical', 'rankPhysical');
        // $result = $this->addRank($result, 'history', 'rankHistory');
        // $result = $this->addRank($result, 'morality', 'rankMorality');
        // $result = $this->addRank($result, 'art', 'rankArt');
        // $result = $this->addRank($result, 'word', 'rankWord');
        // $result = $this->addRank($result, 'pe', 'rankPe');
        // $result = $this->addRank($result, 'homework', 'rankHomework');
        // $result = $this->addRank($result, 'healthy', 'rankHealthy');
        // $result = $this->addRank($result, 'steam', 'rankSteam');
        // $result = $this->addRank($result, 'averageKhmer', 'rankKhmer');
        // $result = $this->addRank($result, 'averageSocial', 'rankSocial');
        // $result = $this->addRank($result, 'average_kcms', 'rankKcms');


        // return $result;