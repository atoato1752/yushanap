<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('queries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('report_id')->nullable();
            $table->enum('payment_type', ['wechat', 'alipay', 'auth_code'])->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->json('report_content')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('agent_id')
                  ->references('id')
                  ->on('agents')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('queries');
    }
}; 