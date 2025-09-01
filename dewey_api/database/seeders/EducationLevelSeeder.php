<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EducationLevel;

class EducationLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            ['edu_name' => "ថ្នាក់បឋម"],
            ['edu_name' => "ថ្នាក់អនុវិទ្យាល័យ"],
            ['edu_name' => "ថ្នាក់វិទ្យាល័យ"]
        ];

        foreach ($posts as $post) {
            EducationLevel::create($post);
        }
    }
}
