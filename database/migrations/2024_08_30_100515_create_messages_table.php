<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->comment('FK users table');
            $table->unsignedBigInteger('receiver_id')->comment('FK users table');
            $table->text('message');
            $table->integer('unseenMsgs')->nullable();
            $table->json('feedback')->nullable();
            $table->boolean('is_sent')->default(0);
            $table->boolean('is_delivered')->default(0);
            $table->boolean('is_seen')->default(0);
            $table->timestamps();
            $table->softDeletes();

            //Foreign Key 
            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('receiver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
