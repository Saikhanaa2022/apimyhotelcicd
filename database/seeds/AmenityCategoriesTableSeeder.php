<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AmenityCategoriesTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('amenity_categories')->insert([
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
                'name' => 'Өрөөний онцлох',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 3,
                'sync_id' => 3,
                'name' => 'Хэвлэл мэдээлэл, технологи',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 4,
                'sync_id' => 4,
                'name' => 'Хоолны нэмэлт',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 5,
                'sync_id' => 5,
                'name' => 'Үйлчилгээ ба нэмэлт',
                'is_default' => 0,
                'is_most' => 0,
            ],
            [
                'id' => 6,
                'sync_id' => 6,
                'name' => 'Гаднах/Үзэгдэх орчин',
                'is_default' => 0,
                'is_most' => 0,
            ],  
        ]);

        DB::table('amenity_category_translations')->insert([
            [
                'id' => 1,
                'sync_id' => 1,
                'locale' => 'en',
                'translation_id' => 1,
                'name' => 'Top Amenities',
            ],
            [
                'id' => 2,
                'sync_id' => 2,
                'locale' => 'en',
                'translation_id' => 2,
                'name' => 'Room amenities',
            ],
            [
                'id' => 3,
                'sync_id' => 3,
                'locale' => 'en',
                'translation_id' => 3,
                'name' => 'Media & Technology',
            ],
            [
                'id' => 4,
                'sync_id' => 4,
                'locale' => 'en',
                'translation_id' => 4,
                'name' => 'Food & Drink',
            ],
            [
                'id' => 5,
                'sync_id' => 5,
                'locale' => 'en',
                'translation_id' => 5,
                'name' => 'Services & Extras',
            ],
            [
                'id' => 6,
                'sync_id' => 6,
                'locale' => 'en',
                'translation_id' => 6,
                'name' => 'Outdoor & View',
            ],
        ]);
    }
}
