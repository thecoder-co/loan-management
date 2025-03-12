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
        Schema::create('loans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained()->onDelete('cascade');
        $table->decimal('loan_amount', 10, 2);
        $table->decimal('interest_rate', 5, 2);
        $table->integer('repayment_plan');
        $table->date('start_date');
        $table->date('end_date');
        $table->decimal('total_amount_due', 10, 2);
        $table->decimal('monthly_installment', 10, 2);
        $table->decimal('remaining_balance', 10, 2);
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
        Schema::dropIfExists('loans');
    }
};
