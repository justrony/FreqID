<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('face_feature')->update([
            'embedding'     => null,
            'landmark_hash' => null,
        ]);
    }

    public function down(): void
    {
    }
};
