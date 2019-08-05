<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('jkb.database_table_name'), function (Blueprint $table) {
            $table->string('token', 200)->primary();
            $table->integer('role_id');
            $table->string('guard',100);
            $table->text('scope');
            $table->string('role_class', 100);
            $table->dateTime('expired_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(config('jkb.database_table_name'));
    }
}
