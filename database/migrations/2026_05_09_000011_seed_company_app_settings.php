<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $settings = [
            'name' => 'KwikShift Movers & Relocators',
            'email' => 'info@kwikshiftmovers.co.ke',
            'phone' => '+254 112587581 / +254111330980',
            'address_line_1' => 'Londiani Road, off Likoni Road',
            'address_line_2' => 'Industrial Area, Nairobi, 00200, KE',
            'logo_path' => 'images/logo-dark.png',
        ];

        foreach ($settings as $key => $value) {
            $exists = DB::table('app_settings')
                ->where('group', 'company')
                ->where('key', $key)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('app_settings')->insert([
                'group' => 'company',
                'key' => $key,
                'value' => $value,
                'is_secret' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('app_settings')
            ->where('group', 'company')
            ->whereIn('key', [
                'name',
                'email',
                'phone',
                'address_line_1',
                'address_line_2',
                'logo_path',
            ])
            ->delete();
    }
};
