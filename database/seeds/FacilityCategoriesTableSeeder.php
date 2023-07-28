<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FacilityCategoriesTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('facility_categories')->insert([
            [
                'id' => 1,
                'sync_id' => 1,
                'name' => 'Топ Онцлох',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 2,
                'sync_id' => 2,
                'name' => 'Үйл ажиллагаа / Үйл ажиллагааны хөтөлбөр',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 3,
                'sync_id' => 3,
                'name' => 'Хоол болон уух зүйлс',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 4,
                'sync_id' => 4,
                'name' => 'Усан сан болон СПА',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 5,
                'sync_id' => 5,
                'name' => 'Тээвэрлэлт',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 6,
                'sync_id' => 6,
                'name' => 'Угтан авах үйлчилгээ',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 7,
                'sync_id' => 7,
                'name' => 'Олон нийтийн орчин',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 8,
                'sync_id' => 8,
                'name' => 'ENTERTAINMENT & FAMILY SERVICE ',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 9,
                'sync_id' => 9,
                'name' => 'Цэвэрлэх үйлчилгээ',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 10,
                'sync_id' => 10,
                'name' => 'Бизнес онцлог',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 11,
                'sync_id' => 11,
                'name' => 'Дэлгүүр',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 12,
                'sync_id' => 12,
                'name' => 'Бусад',
                'is_default' => 0,
                'is_most' => 0,
            ],
        ]);

        DB::table('facility_category_translations')->insert([
            [
                'id' => 1,
                'sync_id' => 1,
                'locale' => 'en',
                'translation_id' => 1,
                'name' => 'Топ Facility',
            ],
            [
                'id' => 2,
                'sync_id' => 2,
                'locale' => 'en',
                'translation_id' => 2,
                'name' => 'Activities',
            ],
            [
                'id' => 3,
                'sync_id' => 3,
                'locale' => 'en',
                'translation_id' => 3,
                'name' => 'FOOD AND DRINK',
            ],
            [
                'id' => 4,
                'sync_id' => 4,
                'locale' => 'en',
                'translation_id' => 4,
                'name' => 'Pool and Spa',
            ],
            [
                'id' => 5,
                'sync_id' => 5,
                'locale' => 'en',
                'translation_id' => 5,
                'name' => 'TRANSPORTATION',
            ],
            [
                'id' => 6,
                'sync_id' => 6,
                'locale' => 'en',
                'translation_id' => 6,
                'name' => 'FRONT DESK SERVICE',
            ],
            [
                'id' => 7,
                'sync_id' => 7,
                'locale' => 'en',
                'translation_id' => 7,
                'name' => 'COMMON AREAS',
            ],
            [
                'id' => 8,
                'sync_id' => 8,
                'locale' => 'en',
                'translation_id' => 8,
                'name' => 'ENTERTAINMENT & FAMILY SERVICE',
            ],[
                'id' => 9,
                'sync_id' => 9,
                'locale' => 'en',
                'translation_id' => 9,
                'name' => 'CLEANING SERVICE',
            ],
            [
                'id' => 10,
                'sync_id' => 10,
                'locale' => 'en',
                'translation_id' => 10,
                'name' => 'BUSINESS FACILITIES',
            ],
            [
                'id' => 11,
                'sync_id' => 11,
                'locale' => 'en',
                'translation_id' => 11,
                'name' => 'SHOPS',
            ],
            [
                'id' => 12,
                'sync_id' => 12,
                'locale' => 'en',
                'translation_id' => 12,
                'name' => 'MISCELLANEOUS',
            ],
        ]);
    }
}
