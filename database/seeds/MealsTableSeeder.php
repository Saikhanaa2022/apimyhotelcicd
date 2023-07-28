<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MealsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('meals')->insert([
            [
                'name' => 'Өглөөний цай',
                'code' => 'breakfast',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Өдрийн хоол',
                'code' => 'lunch',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Оройн хоол',
                'code' => 'dinner',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
