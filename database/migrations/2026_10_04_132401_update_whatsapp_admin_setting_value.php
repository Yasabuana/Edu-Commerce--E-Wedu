<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'whatsapp_admin')
            ->where('value', '6281234567890')
            ->update(['value' => '6288225435927']);

        Setting::flushCache();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'whatsapp_admin')
            ->where('value', '6288225435927')
            ->update(['value' => '6281234567890']);
    }
};
