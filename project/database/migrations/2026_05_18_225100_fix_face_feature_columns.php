<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('face_feature', function (Blueprint $table) {
    
            $table->string('landmark_hash', 64)->nullable()->change();
            $table->jsonb('embedding')->nullable()->after('landmark_hash');
        });
    }

    public function down(): void
    {
        Schema::table('face_feature', function (Blueprint $table) {
            $table->jsonb('landmark_hash')->nullable()->change();
            $table->dropColumn('embedding');
        });
    }
};
