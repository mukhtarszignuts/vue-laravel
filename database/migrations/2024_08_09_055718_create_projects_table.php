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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->double('total_budget')->nullable();
            $table->double('budget')->nullable();
            $table->unsignedBigInteger('client_id')->nullable()->comment('FK Users Table');
            $table->unsignedBigInteger('company_id')->nullable()->comment('FK companies Table');
            $table->enum('status',['I','P','C','R','H'])->default('P')->comment('In Progress ,In Planing , Completed , Rejected','On Hold');
            $table->enum('type',['S','M','L'])->default('S')->comment('Small Meduiem Large');
            $table->string('thumbnil')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign key
            $table->foreign('client_id')->references('id')->on('users');
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
