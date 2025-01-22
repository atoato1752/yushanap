<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('phone', 11);
            $table->text('content');
            $table->enum('status', ['pending', 'processing', 'resolved'])->default('pending');
            $table->text('admin_remark')->nullable();
            $table->unsignedBigInteger('query_id')->nullable();
            $table->timestamps();

            $table->foreign('query_id')
                  ->references('id')
                  ->on('queries')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('complaints');
    }
}; 