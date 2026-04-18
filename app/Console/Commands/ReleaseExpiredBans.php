<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\BanHistory;
use Carbon\Carbon;

class ReleaseExpiredBans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-expired-bans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically restore accounts with expired temporary bans.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired bans...');

        $bannedStatus = UserStatus::where('name', 'Banned')->first();
        $activeStatus = UserStatus::where('name', 'Active')->first();

        if (!$bannedStatus || !$activeStatus) {
            $this->error('Banned or Active status not found.');
            return;
        }

        $expiredUsers = User::where('status_id', $bannedStatus->id)
            ->whereNotNull('ban_expires_at')
            ->where('ban_expires_at', '<=', Carbon::now())
            ->get();

        if ($expiredUsers->isEmpty()) {
            $this->info('No expired bans found.');
            return;
        }

        foreach ($expiredUsers as $user) {
            $this->info("Restoring access for user: {$user->email}");

            // 1. Log restoration
            BanHistory::create([
                'user_id' => $user->id,
                'admin_id' => null, // System action
                'type' => 'temporary',
                'action' => 'restored',
                'reason' => 'Temporary ban period has expired. System auto-restore.',
            ]);

            // 2. Update status
            $user->update([
                'status_id' => $activeStatus->id,
                'ban_expires_at' => null,
            ]);
        }

        $this->info('Restoration complete.');
    }
}
