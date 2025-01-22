<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agent_earnings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('query_id');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'settled'])->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')
                  ->references('id')
                  ->on('agents')
                  ->onDelete('cascade');

            $table->foreign('query_id')
                  ->references('id')
                  ->on('queries')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_earnings');
    }
}; 