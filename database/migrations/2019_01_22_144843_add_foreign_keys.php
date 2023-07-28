<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });

        Schema::table('user_clones', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('set null');
        });

        Schema::table('permission_user', function (Blueprint $table) {
            $table->foreign('permission_id')
                ->references('id')->on('permissions')
                ->onDelete('cascade');
                
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });

        Schema::table('sources', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });

        Schema::table('source_clones', function (Blueprint $table) {
            $table->foreign('source_id')
                ->references('id')->on('sources')
                ->onDelete('set null');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });

        Schema::table('currency_clones', function (Blueprint $table) {
            $table->foreign('currency_id')
                ->references('id')->on('currencies')
                ->onDelete('set null');
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });

        Schema::table('guest_clones', function (Blueprint $table) {
            $table->foreign('guest_id')
                ->references('id')->on('guests')
                ->onDelete('set null');
                
            $table->foreign('reservation_id')
                ->references('id')->on('reservations')
                ->onDelete('cascade');
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });

        Schema::table('partner_clones', function (Blueprint $table) {
            $table->foreign('partner_id')
                ->references('id')->on('partners')
                ->onDelete('set null');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });

        Schema::table('payment_method_clones', function (Blueprint $table) {
            $table->foreign('payment_method_id')
                ->references('id')->on('payment_methods')
                ->onDelete('set null');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->foreign('room_type_id')
                ->references('id')->on('room_types')
                ->onDelete('set null');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->foreign('room_type_id')
                ->references('id')->on('room_types')
                ->onDelete('cascade');
        });

        Schema::table('room_clones', function (Blueprint $table) {
            $table->foreign('room_id')
                ->references('id')->on('rooms')
                ->onDelete('set null');
        });

        Schema::table('blocks', function (Blueprint $table) {
            $table->foreign('room_id')
                ->references('id')->on('rooms')
                ->onDelete('cascade');
        });

        Schema::table('rate_plans', function (Blueprint $table) {
            $table->foreign('room_type_id')
                ->references('id')->on('room_types')
                ->onDelete('cascade');
        });

        Schema::table('rate_plan_clones', function (Blueprint $table) {
            $table->foreign('rate_plan_id')
                ->references('id')->on('rate_plans')
                ->onDelete('set null');
        });

        Schema::table('partner_rate_plan', function (Blueprint $table) {
            $table->foreign('partner_id')
                ->references('id')->on('partners')
                ->onDelete('cascade');
                
            $table->foreign('rate_plan_id')
                ->references('id')->on('rate_plans')
                ->onDelete('cascade');
        });

        Schema::table('intervals', function (Blueprint $table) {
            $table->foreign('rate_plan_id')
                ->references('id')->on('rate_plans')
                ->onDelete('cascade');
        });

        Schema::table('daily_rates', function (Blueprint $table) {
            $table->foreign('rate_plan_id')
                ->references('id')->on('rate_plans')
                ->onDelete('cascade');
        });

        Schema::table('rates', function (Blueprint $table) {
            $table->foreign('interval_id')
                ->references('id')->on('intervals')
                ->onDelete('cascade');
        });
        
        Schema::table('service_categories', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });

        Schema::table('service_category_clones', function (Blueprint $table) {
            $table->foreign('service_category_id')
                ->references('id')->on('service_categories')
                ->onDelete('set null');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreign('service_category_id')
                ->references('id')->on('service_categories')
                ->onDelete('cascade');
        });

        Schema::table('service_clones', function (Blueprint $table) {
            $table->foreign('service_id')
                ->references('id')->on('services')
                ->onDelete('set null');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });

        Schema::table('tax_clones', function (Blueprint $table) {
            $table->foreign('tax_id')
                ->references('id')->on('taxes')
                ->onDelete('set null');
            
            $table->foreign('reservation_id')
                ->references('id')->on('reservations')
                ->onDelete('cascade');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
            
            $table->foreign('group_id')
                ->references('id')->on('groups')
                ->onDelete('cascade');
            
            $table->foreign('user_clone_id')
                ->references('id')->on('user_clones')
                ->onDelete('cascade');
            
            $table->foreign('source_clone_id')
                ->references('id')->on('source_clones')
                ->onDelete('cascade');
            
            $table->foreign('partner_clone_id')
                ->references('id')->on('partner_clones')
                ->onDelete('set null');
            
            $table->foreign('rate_plan_clone_id')
                ->references('id')->on('rate_plan_clones')
                ->onDelete('set null');
            
            $table->foreign('room_type_clone_id')
                ->references('id')->on('room_type_clones')
                ->onDelete('cascade');
            
            $table->foreign('room_clone_id')
                ->references('id')->on('room_clones')
                ->onDelete('set null');
        });

        Schema::table('day_rates', function (Blueprint $table) {
            $table->foreign('reservation_id')
                ->references('id')->on('reservations')
                ->onDelete('cascade');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('payment_method_clone_id')
                ->references('id')->on('payment_method_clones')
                ->onDelete('cascade');
            
            $table->foreign('currency_clone_id')
                ->references('id')->on('currency_clones')
                ->onDelete('cascade');
            
            $table->foreign('user_clone_id')
                ->references('id')->on('user_clones')
                ->onDelete('cascade');

            $table->foreign('reservation_id')
                ->references('id')->on('reservations')
                ->onDelete('cascade');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreign('service_category_clone_id')
                ->references('id')->on('service_category_clones')
                ->onDelete('cascade');

            $table->foreign('service_clone_id')
                ->references('id')->on('service_clones')
                ->onDelete('cascade');
            
            $table->foreign('user_clone_id')
                ->references('id')->on('user_clones')
                ->onDelete('cascade');

            $table->foreign('reservation_id')
                ->references('id')->on('reservations')
                ->onDelete('cascade');
        });

        Schema::table('charges', function (Blueprint $table) {
            $table->foreign('user_clone_id')
                ->references('id')->on('user_clones')
                ->onDelete('cascade');

            $table->foreign('reservation_id')
                ->references('id')->on('reservations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
