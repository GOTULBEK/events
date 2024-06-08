<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('events')->insert([
            [
                'title' => 'Event 3',
                'description' => 'Description of Event 3',
                'image' => 'event3.jpg',
                'start_date' => now(),
                'end_date' => now()->addDays(3), // Example: event lasts for 3 days
                'user_id' => 1, // Assuming this user exists in the database
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
