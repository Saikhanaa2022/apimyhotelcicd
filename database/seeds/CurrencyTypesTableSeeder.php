<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CurrencyTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currency_types')->insert([
            [
                'id' => 1,
                'name' => 'Төгрөг',
                'short_name' => 'MNT',
                'symbol' => '₮',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Америк доллар',
                'short_name' => 'USD',
                'symbol' => '$',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'Хятадын юань',
                'short_name' => 'CNY',
                'symbol' => '元',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Eвро',
                'short_name' => 'EUR',
                'symbol' => '€',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Японы иен',
                'short_name' => 'JPY',
                'symbol' => '¥',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'name' => 'Вон',
                'short_name' => 'KRW',
                'symbol' => '₩',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'name' => 'Австрали доллар',
                'short_name' => 'AUD',
                'symbol' => '$',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 8,
                'name' => 'Оросын рубль',
                'short_name' => 'RUB',
                'symbol' => '₽',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
