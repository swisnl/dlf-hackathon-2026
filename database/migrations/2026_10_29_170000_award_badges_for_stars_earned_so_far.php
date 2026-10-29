<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Badges were not awarded automatically before; give students the badges their stars already reach.
     */
    public function up(): void
    {
        $now = now();

        DB::table('badge_user')->insertOrIgnoreUsing(
            ['badge_id', 'user_id', 'awarded_at', 'created_at', 'updated_at'],
            DB::table('users')
                ->join('badges', 'badges.threshold', '<=', 'users.stars')
                ->select('badges.id', 'users.id')
                ->selectRaw('?, ?, ?', [$now, $now, $now]),
        );
    }

    public function down(): void
    {
        // Badges are never taken away.
    }
};
