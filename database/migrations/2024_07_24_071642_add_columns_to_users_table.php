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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('password');
            $table->string('city')->nullable()->after('phone');
            $table->enum('role',['A','C','M','E'])->default('C')->comment('A : Admin , C : Customer , M : Manager' , 'Employee')->after('city');
            $table->enum('status',['I','A','P'])->default('P')->comment('I : In Active , A : Active , P : Pendding')->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('city');
            $table->dropColumn('role');
            $table->dropColumn('status');
        });
    }
};
