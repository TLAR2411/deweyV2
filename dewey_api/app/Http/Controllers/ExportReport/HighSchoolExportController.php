<?php

namespace App\Http\Controllers\ExportReport;

use App\Exports\HighSchoolExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class HighSchoolExportController extends Controller
{
    /**
     * Main function to handle all types of score views:
     * - Monthly
     * - Semester 1
     * - Semester 2
     * - Full Year
     */
    public function viewUpper(Request $request)
    {
        $class_id = $request->class_id;
        $type = $request->type;
        $month_id = $request->month_id;
        $campus_id = $request->campus_id;
        $year_id = $request->year_id;

        // Get the selected month name
        $month_name = DB::table('month')
            ->where('month.id', $month_id)
            ->select('month.name_kh')
            ->get();

        // Get classroom info (grade and academic year)
        $info = DB::table('classrooms')
            ->leftJoin('grades as g', 'classrooms.grade_id', '=', 'g.id')
            ->leftJoin('academicyears as year', 'classrooms.year_id', '=', 'year.id')
            ->where('classrooms.id', $class_id)
            ->select([
                'g.name as grade_name',
                'year.name'
            ])
            ->get();

        // Count total students and female students
        $allStudent = DB::table('student_class')->where('class_id', $class_id)->where('deleted', 0)->count();

        $student_female = DB::table('students as s')
            ->leftJoin('student_class as sc', 's.id', '=', 'sc.student_id')
            ->where('sc.class_id', $class_id)
            ->where('sc.deleted', 0)
            ->whereIn('s.gender', ['F', '2']) // Khmer '2' = female
            ->count();

        $gradeId = DB::table('classrooms')->where('id', $class_id)->first()->grade_id ?? '';
        // dd($gradeId);
        $class = DB::table('grades')->where('id', $gradeId)->first();


        // Handle different report types
        switch ($type) {

            case 'month':
                $data = $this->fetchMonthData($class_id, $month_id);
                return $this->processAndRespond($data, 'ខែ', 'successful month', $info, $month_name, $allStudent, $student_female);

            case 'semester1':
                $data = $this->getSemesterOne($class_id, $class->grade_level_id ?? '', $year_id, $campus_id);

                $filename = 'ពិន្ទុប្រចាំ' . '.xlsx';
                $data = Excel::download(new HighSchoolExport(
                    $allStudent,
                    $info,
                    $data,
                    'ឆមាសទី១',
                    $student_female,
                    $month_name,
                    $type,
                    $class->grade_level_id ?? ''
                ), $filename);

                return $data;

                // return response()->json([
                //     'type' => "ឆមាសទី១",
                //     'data' => $data,
                //     'info' => $info,
                //     'allStudent' => $allStudent,
                //     'student_female' => $student_female
                // ]);

            case 'semester2':
                $data = $this->getSemester2($class_id,  $year_id, $campus_id);

                $filename = 'ពិន្ទុប្រចាំ' . '.xlsx';
                $data = Excel::download(new HighSchoolExport(
                    $allStudent,
                    $info,
                    $data,
                    'ឆមាសទី១',
                    $student_female,
                    $month_name,
                    $type,
                    $class->grade_level_id ?? ''
                ), $filename);

                return $data;
                // return response()->json([
                //     'type' => "ឆមាសទី២",
                //     'data' => $data,
                //     'info' => $info,
                //     'allStudent' => $allStudent,
                //     'student_female' => $student_female
                // ]);

            default: // Full Year
                return $this->processFullYear($class_id, $info, $allStudent, $student_female, $class->grade_level_id ?? '', $year_id, $campus_id);
        }
    }

    /**
     * Fetch scores for students in a specific month and group them
     * 
     * Why:
     * - Used to get score data of students for a given month
     * - It groups results by student for display
     * 
     * Example:
     * fetchMonthData(5, 1) => all student scores in class 5 for January
     */
    private function fetchMonthData($class_id, $month_id)
    {
        return DB::table('score_upper_cc as score')
            ->join('students as s', 'score.student_id', '=', 's.id')
            ->select(
                's.id as student_id',
                's.kh_name',
                's.en_name',
                's.gender',
                's.photo_path',
                'score.class_id',
                'score.khmer',
                'score.morality',
                'score.history',
                'score.geography',
                'score.math',
                'score.physical',
                'score.chemistry',
                'score.biology',
                'score.earth_science',
                'score.english',
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
                'score.morality',
                'score.history',
                'score.geography',
                'score.math',
                'score.physical',
                'score.chemistry',
                'score.biology',
                'score.earth_science',
                'score.english',
                'score.pe',
                'score.computer',
            )
            ->get()
            ->toArray();
    }

    /**
     * Format and respond with ranking, grade, and student details
     * 
     * Why:
     * - Used after fetching data to prepare response
     * - Adds rank and grade info
     * 
     * Example:
     * processAndRespond(data, 'ខែ', ...) => returns json with formatted results
     */
    private function processAndRespond($data, $type, $successMessage, $info, $month_name, $allStudent, $student_female)
    {
        if (empty($data)) {
            return response()->json(['status' => 1, 'message' => 'UnSuccessfully']);
        }

        $processed = $this->formatData($data, $type);
        $sorted = $this->sortAndRank($processed, 'avg');


        $filename = 'ពិន្ទុប្រចាំ' . '.xlsx';

        $data = Excel::download(new HighSchoolExport(
            $allStudent,
            $info,
            $sorted,
            $successMessage,
            $student_female,
            $month_name,
            $type,
            ''
        ), $filename);

        return $data;

        // return response()->json([
        //     'status' => 200,
        //     'data' => $sorted,
        //     'message' => $successMessage,
        //     'type' => $type,
        //     'info' => $info,
        //     'month' => $month_name,
        //     'allStudent' => $allStudent,
        //     'student_female' => $student_female
        // ]);
    }

    /**
     * Format raw score data to include grade and clean average numbers
     * 
     * Why:
     * - Helps front-end to show score with Khmer grade and cleaned values
     * 
     * Example:
     * formatData([{ avg: 45.66 }]) => adds grade: 'ល្អណាស់'
     */
    public function formatData($data, $type)
    {
        return array_map(function ($student) use ($type) {
            return [

                'student_id' => $student->student_id,
                'kh_name' => $student->kh_name,
                'en_name' => $student->en_name,
                'gender' => $student->gender,
                'class_id' => $student->class_id,
                'total_score' => $student->total_score,                // 'total_score' => number_format($student->total_score, 2, '.', ''),
                'avg' => number_format($student->total_avg, 2, '.', ''),
                'grade' => $this->getGradeUpper($student->total_avg),
                'photo_path' => $student->photo_path,
                "khmer" => $student->khmer,
                'math' => $student->math,
                'morality' => $student->morality,
                'history' => $student->history,
                'geography' => $student->geography,
                'physical' => $student->physical,
                'chemistry' => $student->chemistry,
                'biology' => $student->biology,
                'earth_science' => $student->earth_science,
                'english' => $student->english,
                'pe' => $student->pe,
                'computer' => $student->computer,
                // how to take subject score = fetchMondata(groupby function)
                'type' => $type,
            ];
        }, $data);
    }

    /**
     * Sorts students by score average and assigns rank
     * 
     * Why:
     * - So students can be ordered by performance (highest to lowest)
     * 
     * Example:
     * sortAndRank(data, 'avg') => adds 'rank' to each student
     */
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

    /**
     * Get scores for Semester 1 using 3-month avg and final month
     * 
     * Why:
     * - Semester 1 result = (Nov + Dec + Jan)/3 + Feb
     * - Final avg = (avg_3_months + final_month_avg) / 2
     * 
     * Example:
     * getSemesterOne(5) => all student avg in Semester 1 for class 5
     */
    public function getGradeUpper($avg)
    {
        if ($avg >= 48.00) return "ល្អប្រសើរ";
        elseif ($avg >= 46.00) return "ល្អណាស់";
        elseif ($avg >= 40.00) return "ល្អ";
        elseif ($avg >= 32.50) return "ល្អបង្គួរ";
        elseif ($avg >= 25.00) return "មធ្យម";
        return "ធ្លាក់";
    }

    /**
     * Get scores for Semester 1 using 3-month avg and final month
     * 
     * Why:
     * - Semester 1 result = (Nov + Dec + Jan)/3 + Feb(average)
     * - Final avg = (avg_3_months + semester_month_avg) / 2
     * 
     * Example:
     * getSemesterOne(5) => all student avg in Semester 1 for class 5
     */
    private function getSemesterOne($class_id, $grade_level, $year_id, $campus_id)
    {
        // $months = [11, 12, 1, 2]; // Nov-Feb
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

        //fetch and process all months
        foreach ($all_months as $m) {
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
                if ($m == $month_semester) {
                    $semester_data[$id]['total_semester_month'] = $avg;

                    // February: store semester month avg + subjects
                    $semester_data[$id]['total_semester_month'] = $avg;
                    $semester_data[$id]['khmer'] = $student->khmer;
                    $semester_data[$id]['morality'] = $student->morality;
                    $semester_data[$id]['history'] = $student->history;
                    $semester_data[$id]['geography'] = $student->geography;
                    $semester_data[$id]['math'] = $student->math;
                    $semester_data[$id]['physical'] = $student->physical;
                    $semester_data[$id]['chemistry'] = $student->chemistry;
                    $semester_data[$id]['biology'] = $student->biology;
                    $semester_data[$id]['earth_science'] = $student->earth_science;
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
                'gender' => $student['gender'],
                'photo_path' => $student['photo_path'],

                'average_3_month' => number_format($student['total_avg_3_month'], 2, '.', ''),
                'average_month_semester' => number_format($student['total_semester_month'], 2, '.', ''),
                'average_semester1' => $total_avg_year,
                'average_semester2' => 0,
                'grade' => $this->getGradeUpper($total_avg_year),
                'type' => 'Semester1',
                // Only from month 2 (February)
                'khmer' => $student['khmer'] ?? 0,
                'morality' => $student['morality'] ?? 0,
                'history' => $student['history'] ?? 0,
                'geography' => $student['geography'] ?? 0,
                'math' => $student['math'] ?? 0,
                'physical' => $student['physical'] ?? 0,
                'chemistry' => $student['chemistry'] ?? 0,
                'biology' => $student['biology'] ?? 0,
                'earth_science' => $student['earth_science'] ?? 0,
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
        $result = $this->addRank($result, 'morality', 'rankMorality');
        $result = $this->addRank($result, 'history', 'rankHistory');
        $result = $this->addRank($result, 'geography', 'rankGeography');
        $result = $this->addRank($result, 'math', 'rankMath');
        $result = $this->addRank($result, 'physical', 'rankPhysic');
        $result = $this->addRank($result, 'chemistry', 'rankChemistry');
        $result = $this->addRank($result, 'biology', 'rankBiology');
        $result = $this->addRank($result, 'earth_science', 'rankEarth');
        $result = $this->addRank($result, 'pe', 'rankPe');
        $result = $this->addRank($result, 'english', 'rankEnglish');
        $result = $this->addRank($result, 'computer', 'rankComputer');

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

    /**
     * Get scores for Semester 2 with 3 months avg + final month
     * 
     * Why:
     * - Same logic as semester1 but months vary by grade
     * - Grade 12 uses Mar-Jun, others use May-Aug
     * 
     * Example:
     * getSemester2(5) => semester 2 score for class 5
     */
    private function getSemester2($class_id, $year_id, $campus_id)
    {
        $gradeId = DB::table('classrooms')->where('id', $class_id)->first()->grade_id ?? '';
        $gradeLevel = DB::table('grades')->where('id', $gradeId)->first()->grade_level_id ?? '';

        // Get semester months configuration from database
        $semesterConfig = DB::table('setting_semester_list')
            // ->where('setting_semester_list.campus_id', $campus_id)
            ->where('setting_semester_list.year_id', $year_id)
            ->where('setting_semester_list.grade_level_id',  $gradeLevel)
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

        // $months = $gradeLevel == 12 ? [3, 4, 5, 6] : [5, 6, 7, 8];
        // $finalMonth = end($months);
        $finalMonth = $month_semester;

        // print_r($finalMonth);

        $semesterData = $this->processMonths($class_id, $months, $finalMonth);
        if ($semesterData === 0) return 0;
        $result = $this->formatResults($semesterData);
        // $this->rankResults($result);

        $result = $this->addRank($result, 'average_3_month', 'rank_3_month');
        $result = $this->addRank($result, 'average_month_semester', 'rank_month_semester');
        $result = $this->addRank($result, 'average_semester2', 'rank');
        $result = $this->addRank($result, 'khmer', 'rankKhmer');
        $result = $this->addRank($result, 'morality', 'rankMorality');
        $result = $this->addRank($result, 'history', 'rankHistory');
        $result = $this->addRank($result, 'geography', 'rankGeography');
        $result = $this->addRank($result, 'math', 'rankMath');
        $result = $this->addRank($result, 'physical', 'rankPhysic');
        $result = $this->addRank($result, 'chemistry', 'rankChemistry');
        $result = $this->addRank($result, 'biology', 'rankBiology');
        $result = $this->addRank($result, 'earth_science', 'rankEarth');
        $result = $this->addRank($result, 'pe', 'rankPe');
        $result = $this->addRank($result, 'english', 'rankEnglish');
        $result = $this->addRank($result, 'computer', 'rankComputer');

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


    /**
     * Build score data from given months for semester 2
     * 
     * Why:
     * - Helper for getSemester2()
     * - Adds up average scores for 3 months + final month
     *
     * Example:
     * processMonths(5, [5,6,7,8], 8)
     */
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
                    // $semester_data[$id]['total_semester_month'] = $avg;
                    $semesterData[$id]['khmer'] = $student->khmer;
                    $semesterData[$id]['morality'] = $student->morality;
                    $semesterData[$id]['history'] = $student->history;
                    $semesterData[$id]['geography'] = $student->geography;
                    $semesterData[$id]['math'] = $student->math;
                    $semesterData[$id]['physical'] = $student->physical;
                    $semesterData[$id]['chemistry'] = $student->chemistry;
                    $semesterData[$id]['biology'] = $student->biology;
                    $semesterData[$id]['earth_science'] = $student->earth_science;
                    $semesterData[$id]['english'] = $student->english;
                    $semesterData[$id]['pe'] = $student->pe;
                    $semesterData[$id]['computer'] = $student->computer;
                    $semesterData[$id]['total_score'] = $student->total_score;
                } else {
                    $semesterData[$id]['total_avg_3_month'] += $avg / 3;
                }
            }
        }

        return $semesterData;
    }

    /**
     * Format processed semester data into structured student info
     * 
     * Why:
     * - Used by getSemester2 to finalize response
     *
     * Example:
     * formatResults(semesterData) => returns student avg with grades
     */
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
                'khmer' => $student['khmer'] ?? 0,
                'morality' => $student['morality'] ?? 0,
                'history' => $student['history'] ?? 0,
                'geography' => $student['geography'] ?? 0,
                'math' => $student['math'] ?? 0,
                'physical' => $student['physical'] ?? 0,
                'chemistry' => $student['chemistry'] ?? 0,
                'biology' => $student['biology'] ?? 0,
                'earth_science' => $student['earth_science'] ?? 0,
                'english' => $student['english'] ?? 0,
                'pe' => $student['pe'] ?? 0,
                'computer' => $student['computer'] ?? 0,
                'total_score' => $student['total_score'] ?? 0,
            ];
        }, array_values($semesterData));
    }


    /**
     * Add rank based on semester 2 score
     * 
     * Why:
     * - Used after formatting to show student positions
     *
     * Example:
     * rankResults(results) => adds rank to each student
     */
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


    /**
     * Combine semester 1 + 2 for full year average and grade
     * 
     * Why:
     * - Final yearly result = (semester1 + semester2) / 2
     * - Adds grade and rank
     * 
     * Example:
     * processFullYear(5) => yearly score for all students in class 5
     */
    private function processFullYear($class_id, $info, $allStudent, $student_female, $grade_level, $year_id, $campus_id)
    {
        $semester1 = $this->getSemesterOne($class_id, $grade_level, $year_id, $campus_id);
        $semester2 = $this->getSemester2($class_id, $year_id, $campus_id);

        // return response()->json($semester2);

        if (empty($semester1) && empty($semester2)) {
            return response()->json(['status' => 1, 'message' => 'UnSuccesfully']);
        }
        $semester1 = is_array($semester1) ? $semester1 : (json_decode($semester1, true) ?? []);



        $semester2 = is_array($semester2) ? $semester2 : (json_decode($semester2, true) ?? []);
        $combined = array_merge($semester1, $semester2);

        // return response()->json($combined);


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

        // return response()->json($yearly);

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
        // return response()->json($sorted);
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
