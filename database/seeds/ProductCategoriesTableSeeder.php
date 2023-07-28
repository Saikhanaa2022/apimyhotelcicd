<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProductCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_categories')->insert([
            [
                'id' => 1,
                'name' => 'Амралт, чөлөөт цагт зориулагдсан бусад төрлийн үйлчилгээ',
                'code' => '9699000',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Зочид буудал, замын буудлын үйлчилгээ',
                'code' => '6311110',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'Цэвэрлэгээний төрөлжсөн үйлчилгээ',
                'code' => '8534000',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Махаар хийсэн бэлэн хоол, хүнс',
                'code' => '2117600',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Соёолжоор бэлтгэсэн шар айраг',
                'code' => '2431000',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'name' => 'Цэвэр ус',
                'code' => '2441010',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'name' => 'Амтат ус, ундаа',
                'code' => '2449010',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 8,
                'name' => 'Оргилуун дарснаас бусад төрлийн усан үзмийн дарс',
                'code' => '2421200',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 9,
                'name' => '40 орчим хувийн хатуулгатай бусад төрлийн спирт, ликёр болон согтууруулах ундаа',
                'code' => '2413190',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
