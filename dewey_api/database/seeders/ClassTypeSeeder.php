<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Classtype;

class ClassTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            ['type' => "វិទ្យាសាស្រ្តពិត"],
            ['type' => "វិទ្យាសាស្រ្តសង្គម"],
            ['type' => "ធម្មតា"],
            ['type' => "ភាសាអង់គ្លេស"],
        ];

        foreach ($posts as $post) {
            Classtype::create($post);
        }
    }
}
