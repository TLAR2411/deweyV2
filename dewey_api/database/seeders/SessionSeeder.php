<?php

namespace Database\Seeders;

use App\Models\Session;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            [
                'session_name' => "ព្រឹក",
                'time'=>"7:00 Am - 12:00 Pm"

            ],
            [
                'session_name' => "ថ្ងៃរសៀល",
                'time'=>"1:00 Pm - 5:00 Pm"
            ],
           
        ];

        foreach ($posts as $post) {
            Session::create($post);
        }
    }
}
