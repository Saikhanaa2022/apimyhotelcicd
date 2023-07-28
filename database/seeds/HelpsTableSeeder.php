<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class HelpsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('helps')->insert([
            [
                'name' => 'MyHotel cистемд бүртгүүлэх',
                'icon' => 'mdi-account',
                'url' => 'https://www.youtube.com/embed/pE1Cpqk1bDk',
                'content' => NULL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Ерөнхий тохиргоо',
                'icon' => 'mdi-settings',
                'url' => 'https://www.youtube.com/embed/3LnnPf3i9QQ',
                'content' => NULL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Нэмэлт тохиргоо',
                'icon' => 'mdi-settings',
                'url' => 'https://www.youtube.com/embed/bhPLfgRQcio',
                'content' => NULL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Хэрэглэгчийн эрх тохируулах',
                'icon' => 'mdi-account-multiple-check',
                'url' => 'https://www.youtube.com/embed/eH360xUkAUo',
                'content' => NULL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Нэмэлт үйлчилгээ бүртгэх',
                'icon' => 'mdi-room-service',
                'url' => 'https://www.youtube.com/embed/bhPLfgRQcio',
                'content' => NULL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Өрөө хаалт тохируулах',
                'icon' => 'mdi-lock',
                'url' => 'https://www.youtube.com/embed/Ek_eJc2JEVY',
                'content' => NULL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
