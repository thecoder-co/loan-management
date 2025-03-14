<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->unsignedBigInteger('customer_id')->after('id'); // Adjust position as needed
        $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropForeign(['customer_id']);
        $table->dropColumn('customer_id');
    });
}

};
