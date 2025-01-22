<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('query_id');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_type', ['wechat', 'alipay', 'auth_code']);
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->json('response')->nullable();
            $table->timestamps();

            $table->foreign('query_id')
                  ->references('id')
                  ->on('queries')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}; 