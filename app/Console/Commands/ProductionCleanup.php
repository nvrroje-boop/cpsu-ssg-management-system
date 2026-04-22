<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;

class ProductionCleanup extends Command
{
    protected $signature = 'system:production-cleanup';
    protected $description = 'Clean up demo data and prepare for production';

    public function handle()
    {
        $this->info('Starting production cleanup...');

        // Step 1: Show current stats
        $this->info("\n📊 Current Database State:");
        $this->line("Users: " . User::count());
        $this->line("Announcements: " . Announcement::count());
        $this->line("Notifications: " . Notification::count());

        // Step 2: Confirm before deletion
        if (!$this->confirm('This will remove all demo data. Do you want to continue?')) {
            $this->warn('Cleanup cancelled.');
            return;
        }

        // Step 3: Keep only admin user
        $adminUser = User::where('role', 'admin')->first();
        if (!$adminUser) {
            $this->error('❌ No admin user found! Creating default admin...');
            $adminUser = User::create([
                'name' => 'Administrator',
                'email' => 'admin@ssg.local',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
            $this->line("✓ Default admin created: {$adminUser->email}");
        } else {
            $this->line("✓ Admin user found: {$adminUser->email}");
        }

        // Step 4: Delete non-admin users
        $deletedUsers = User::where('role', '!=', 'admin')->delete();
        $this->info("✓ Deleted $deletedUsers non-admin users");

        // Step 5: Delete all announcements and notifications
        $deletedNotifications = Notification::truncate();
        $this->info("✓ Cleared notifications table");

        $deletedAnnouncements = Announcement::truncate();
        $this->info("✓ Cleared announcements table");

        // Step 6: Clear failed jobs
        \DB::table('failed_jobs')->truncate();
        $this->info("✓ Cleared failed jobs");

        // Step 7: Show final stats
        $this->info("\n✨ Cleanup Complete:");
        $this->line("Users: " . User::count());
        $this->line("Announcements: " . Announcement::count());
        $this->line("Notifications: " . Notification::count());

        $this->success('System is now production-ready!');
    }
}
