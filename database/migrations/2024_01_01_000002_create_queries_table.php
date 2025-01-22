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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('id_card');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->decimal('amount', 10, 2);
            $table->json('result')->nullable();
            $table->string('report_path')->nullable();
            $table->timestamp('report_generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('queries');
    }
}; 