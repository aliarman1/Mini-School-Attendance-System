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
        Schema::table('attendances', function (Blueprint $table) {
            // Add user_id foreign key
            $table->foreignId('user_id')->nullable()->after('student_id')->constrained()->onDelete('set null');
            
            // Change recorded_by to nullable (will be replaced by user relationship)
            $table->string('recorded_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->string('recorded_by')->nullable(false)->change();
        });
    }
};
