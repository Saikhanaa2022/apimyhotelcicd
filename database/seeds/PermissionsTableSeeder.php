<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->insert([
            [
                'code' => 'manage-settings',
                'name' => 'Ерөнхий тохиргоо',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'show-reservations',
                'name' => 'Захиалгын жагсаалт харах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-reservations',
                'name' => 'Захиалга удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'access-reports',
                'name' => 'Тайлан харах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-blocks',
                'name' => 'Өрөөний хаалт удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-room-cleans',
                'name' => 'Өрөөний цэвэрлэгээ удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-rooms',
                'name' => 'Өрөөний удирдлага',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-rates',
                'name' => 'Үнийн удирдлага',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-users',
                'name' => 'Хэрэглэгчийн бүртгэл удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-sources',
                'name' => 'Захиалгын суваг удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-partners',
                'name' => 'Гэрээт байгууллага удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-services',
                'name' => 'Нэмэлт үйлчилгээ удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-payment-methods',
                'name' => 'Төлбөрийн төрөл удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-currencies',
                'name' => 'Валют удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-taxes',
                'name' => 'Татвар удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-hotels',
                'name' => 'Буудал зохион байгуулах',
                'is_property' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-invoices',
                'name' => 'Нэхэмжлэх удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'manage-guests',
                'name' => 'Зочны бүртгэл удирдах',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'perform-night-audit',
                'name' => 'Өдөр өндөрлөх',
                'is_property' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
