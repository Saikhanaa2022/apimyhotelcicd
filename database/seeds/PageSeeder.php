<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pages')->insert([
            [
                'name' => 'Бүх',
                'en_name' => 'All',
                'slug' => 'all',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Нүүр',
                'en_name' => 'Home',
                'slug' => 'home',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Календар',
                'en_name' => 'Calendar',
                'slug' => 'calendar',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Захиалга',
                'en_name' => 'Reservations',
                'slug' => 'reservations',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Урьдчилсан захиалга',
                'en_name' => 'Res requests',
                'slug' => 'res-requests',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Өрөөний хаалт',
                'en_name' => 'Room block',
                'slug' => 'blocks',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Цэвэрлэгээ',
                'en_name' => 'Cleaning',
                'slug' => 'house-keeping',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Зочны бүртгэл',
                'en_name' => 'Guests',
                'slug' => 'guests',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Үнэ тохируулах',
                'en_name' => 'Adjust the price',
                'slug' => 'rates',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Нэхэмжлэх',
                'en_name' => 'Invoices',
                'slug' => 'invoices',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Орлого',
                'en_name' => 'Incomes',
                'slug' => 'incomes',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Статистик тайлан',
                'en_name' => 'Statistic reports',
                'slug' => 'report',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Ерөнхий тохиргоо',
                'en_name' => 'General settings',
                'slug' => 'step-base',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Шинэ захиалга',
                'en_name' => 'New reservation',
                'slug' => 'new-reservation',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Өрөөний бүртгэл',
                'en_name' => 'Room registration',
                'slug' => 'room-types',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Валют',
                'en_name' => 'Currency',
                'slug' => 'currencies',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Үнийн төрөл',
                'en_name' => 'Price type',
                'slug' => 'rate-plans',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Татвар',
                'en_name' => 'Taxes',
                'slug' => 'taxes',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Үйлчилгээ',
                'en_name' => 'Services',
                'slug' => 'services',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Хэрэглэгчийн эрх',
                'en_name' => 'User roles',
                'slug' => 'roles',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Байгууллагууд',
                'en_name' => 'Partners',
                'slug' => 'partners',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Хэрэглэгч',
                'en_name' => 'Customers',
                'slug' => 'users',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Захиалгын суваг',
                'en_name' => 'Channel manager',
                'slug' => 'sources',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Нөхцөл - Бодлогууд',
                'en_name' => 'Terms - Conditions',
                'slug' => 'policies',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Төлбөрийн хэрэгсэл',
                'en_name' => 'Payment methods',
                'slug' => 'payment-methods',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Буудлын тохиргоо',
                'en_name' => 'Hotel settings',
                'slug' => 'edit-settings',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Бүртгэлтэй буудлууд',
                'en_name' => 'Properties',
                'slug' => 'hotels',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Шинэ буудал',
                'en_name' => 'New hotel',
                'slug' => 'new-hotel',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Бүртгэлтэй хэрэглэгчид',
                'en_name' => 'Registered users',
                'slug' => 'hotel-users',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
