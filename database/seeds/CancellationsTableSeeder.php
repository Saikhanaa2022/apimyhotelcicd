<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CancellationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Times
        DB::table('cancellation_times')->insert([
            [
                'id' => 1,
                // Until 6:00 PM on the day of arrival
                'name' => 'Ирэх өдрийн 18:00 цаг хүртэл',
                'has_time' => true,
                'day' => 18,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                // Until 2:00 PM on the day of arrival
                'name' => 'Ирэх өдрийн 14:00 цаг хүртэл',
                'has_time' => true,
                'day' => 14,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                // until 1 day before arrival
                'name' => 'Ирэхээс 1 өдрийн өмнө',
                'has_time' => false,
                'day' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Ирэхээс 2 өдрийн өмнө',
                'has_time' => false,
                'day' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Ирэхээс 3 өдрийн өмнө',
                'has_time' => false,
                'day' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'name' => 'Ирэхээс 5 өдрийн өмнө',
                'has_time' => false,
                'day' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'name' => 'Ирэхээс 7 өдрийн өмнө',
                'has_time' => false,
                'day' => 7,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 8,
                'name' => 'Ирэхээс 14 өдрийн өмнө',
                'has_time' => false,
                'day' => 14,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 9,
                'name' => 'Ирэхээс 21 өдрийн өмнө',
                'has_time' => false,
                'day' => 21,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 10,
                'name' => 'Ирэхээс 30 өдрийн өмнө',
                'has_time' => false,
                'day' => 30,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 11,
                'name' => 'Ирэхээс 60 өдрийн өмнө',
                'has_time' => false,
                'day' => 60,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Percents
        DB::table('cancellation_percents')->insert([
            [
                'id' => 1,
                'name' => 'Эхний шөнийн үнэтэй ижилхэн',
                'is_first_night' => true,
                'percent' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Нийт үнийн 10%',
                'is_first_night' => false,
                'percent' => 10,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'Нийт үнийн 15%',
                'is_first_night' => false,
                'percent' => 15,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Нийт үнийн 20%',
                'is_first_night' => false,
                'percent' => 20,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Нийт үнийн 30%',
                'is_first_night' => false,
                'percent' => 30,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'name' => 'Нийт үнийн 40%',
                'is_first_night' => false,
                'percent' => 40,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'name' => 'Нийт үнийн 50%',
                'is_first_night' => false,
                'percent' => 50,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 8,
                'name' => 'Нийт үнийн 60%',
                'is_first_night' => false,
                'percent' => 60,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 9,
                'name' => 'Нийт үнийн 70%',
                'is_first_night' => false,
                'percent' => 70,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 10,
                'name' => 'Нийт үнийн 80%',
                'is_first_night' => false,
                'percent' => 80,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 11,
                'name' => 'Нийт үнийн 90%',
                'is_first_night' => false,
                'percent' => 90,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 12,
                'name' => 'Нийт үнийн 100%',
                'is_first_night' => false,
                'percent' => 100,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
