<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(HotelTypesTableSeeder::class);
        // $this->call(PermissionsTableSeeder::class);
        // $this->call(MealsTableSeeder::class);
        // $this->call(CurrencyTypesTableSeeder::class);
        // $this->call(ProductCategoriesTableSeeder::class);
        // $this->call(CancellationsTableSeeder::class);
        // $this->call(HelpsTableSeeder::class);
        // $this->call(ChannelsTableSeeder::class);
        // $this->call(UpdatedTablesSeeder::class);
        // $this->call(FacilityCategoriesTablesSeeder::class);
        // $this->call(FacilitiesTableSeeder::class);
        // $this->call(AmenityCategoriesTablesSeeder::class);
        // $this->call(AmenitiesTableSeeder::class);
        $this->call(SysRolesTableSeeder::class);
    }
}
