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
       Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        // $table->foreignId('customer_id')->constrained()->onDelete('cascade');
        $table->enum('direction', ['debit', 'credit']);
        $table->string('type');
        $table->decimal('amount', 12, 2);
        $table->string('description')->nullable();
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
       Schema::dropIfExists('transactions');
    }
};
