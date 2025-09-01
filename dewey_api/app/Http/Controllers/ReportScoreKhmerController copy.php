<?php

namespace App\Http\Controllers;

use Dflydev\DotAccessData\Data;
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
                $data = $this->getSemesterOnePrimary($class_id, $avg_mutil);
                return response()->json([
                    'type' => "ឆមាសទី១",
                    'data' => $data,
                    'info' => $info,
                    'allStudent' => $allStudent,
                    'student_female' => $student_female

                ]);

            case 'semester2':
                $data = $this->getSemesterTwoPrimary($class_id, $avg_mutil);
                return response()->json([
                    'type' => "ឆមាសទី២",
                    'data' => $data,
                    'info' => $info,
                    'allStudent' => $allStudent,
                    'student_female' => $student_female
                ]);

            default:
                return $this->processFullYear($class_id, $avg_mutil, $info, $allStudent, $student_female);
        }
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
                'avg' => number_format($avg, 2, '.', ''),
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

    public function getSemesterOnePrimary($class_id, $avg_mutil)
    {
        $months = [12, 1, 2, 3];
        $semester_data_by_id = [];

        // Fetch and process all months
        foreach ($months as $month) {
            $month_data = DB::table('score_primary_cc')
                ->join('students', 'score_primary_cc.student_id', '=', 'students.id')
                ->where('class_id', $class_id)
                ->where('month_id', $month)
                ->get()
                ->toArray();
            if (empty($month_data)) {
                return 0;
            }

            // Process each student's data for the month
            foreach ($month_data as $student) {
                $id = $student->id;
                $avg = floor(($student->total_score / $avg_mutil) * 100) / 100;

                // If the student is new, initialize their data
                if (!isset($semester_data_by_id[$id])) {
                    $semester_data_by_id[$id] = (array) $student + [
                        'student_id' => $id,
                        'total_avg_3_month' => 0,
                        'total_semester_month' => 0,
                    ];
                }
                if ($month == 3) {
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
                    $semester_data_by_id[$id]['total_avg_3_month'] += $avg / 3;
                }
            }
        }

        $result = array_map(function ($student) {



            $khmer = $student['reading'] + $student['essay'] + $student['writing'];

            $khmer = number_format($khmer, 2);
            $averageKhmer = number_format($khmer / 3, 2, '.', '');

            $social_scient = $student['morality'] + $student['physical'] + $student['history'] + $student['word'] + $student['pe'] + $student['art'] + $student['steam'];
            $social_scient = number_format($social_scient, 2);

            $averageSocial  = number_format($social_scient / 7, 2, '.', '');

            $total_score_kcms = number_format(($averageKhmer + $student['chemistry'] + $averageSocial + $student['math']), 2);

            $average_kcms = number_format($total_score_kcms / 4, 2);

            $total_avg_year = number_format(($average_kcms + $student['total_avg_3_month']) / 2, 2, '.', '');

            return [
                'total_score_kcms' => $total_score_kcms,
                'average_kcms' => $average_kcms,
                'social_scient' => $social_scient,
                'averageSocial' => $averageSocial,
                'averageKhmer' => $averageKhmer,
                'khmer' => $khmer,
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
        }, array_values($semester_data_by_id));

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

    public function getSemesterTwoPrimary($class_id, $avg_mutil)
    {
        $months = [5, 6, 7, 8];

        $semester_data = [];

        foreach ($months as $m) {
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
                $avg = $student->total_score / $avg_mutil;

                // check and add to $semester_data if not yet but if it already have skip
                if (!isset($semester_data[$id])) {
                    $semester_data[$id] = (array) $student + [
                        'student_id' => $id,
                        'total_avg_3_month' => 0,
                        'total_semester_month' => 0,
                    ];
                }

                if ($m == 8) {
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
                    $semester_data[$id]['total_avg_3_month'] += $avg / 3;
                }
            }
        }

        $result = array_map(function ($student) {
            $averageSemester = number_format(($student['total_avg_3_month'] + $student['total_semester_month']) / 2, 2, '.', '');

            $khmer = $student['reading'] + $student['essay'] + $student['writing'];

            $khmer = number_format($khmer, 2);
            $averageKhmer = number_format($khmer / 3, 2, '.', '');

            $social_scient = $student['morality'] + $student['physical'] + $student['history'] + $student['word'] + $student['pe'] + $student['art'] + $student['steam'];
            $social_scient = number_format($social_scient, 2);

            $averageSocial  = number_format($social_scient / 7, 2, '.', '');

            $total_score_kcms = number_format(($averageKhmer + $student['chemistry'] + $averageSocial + $student['math']), 2);

            $average_kcms = number_format($total_score_kcms / 4, 2);

            $total_avg_year = number_format(($average_kcms + $student['total_avg_3_month']) / 2, 2, '.', '');


            return [
                'total_score_kcms' => $total_score_kcms,
                'average_kcms' => $average_kcms,
                'social_scient' => $social_scient,
                'averageSocial' => $averageSocial,
                'averageKhmer' => $averageKhmer,
                'khmer' => $khmer,
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

    public function processFullYear($class_id, $avg_mutil, $info, $allStudent, $student_female)
    {
        $semester1 = $this->getSemesterOnePrimary($class_id, $avg_mutil);
        $semester2 = $this->getSemesterTwoPrimary($class_id, $avg_mutil);


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
