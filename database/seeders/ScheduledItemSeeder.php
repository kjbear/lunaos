<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduledItem;
use Carbon\Carbon;

class ScheduledItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $startOfWeek = $now->copy()->startOfWeek();

        // Sample events for this week
        $events = [
            // Cron jobs (red)
            [
                'title' => 'Morning Digest',
                'source_type' => ScheduledItem::SOURCE_CRON,
                'starts_at' => $startOfWeek->copy()->addDays(0)->setTime(8, 0),
                'priority' => ScheduledItem::PRIORITY_NORMAL,
                'metadata' => ['schedule' => '0 8 * * *'],
            ],
            [
                'title' => 'Evening Summary',
                'source_type' => ScheduledItem::SOURCE_CRON,
                'starts_at' => $startOfWeek->copy()->addDays(0)->setTime(23, 0),
                'priority' => ScheduledItem::PRIORITY_NORMAL,
                'metadata' => ['schedule' => '0 23 * * *'],
            ],
            [
                'title' => 'Heartbeat Check',
                'source_type' => ScheduledItem::SOURCE_CRON,
                'starts_at' => $startOfWeek->copy()->addDays(1)->setTime(9, 0),
                'priority' => ScheduledItem::PRIORITY_LOW,
                'metadata' => ['schedule' => '0 */2 * * *'],
            ],

            // Reminders (orange)
            [
                'title' => 'Review LunaOS PR',
                'source_type' => ScheduledItem::SOURCE_REMINDER,
                'starts_at' => $startOfWeek->copy()->addDays(1)->setTime(14, 0),
                'priority' => ScheduledItem::PRIORITY_HIGH,
                'description' => 'Review and merge the UI design system PR',
            ],
            [
                'title' => 'Update MEMORY.md',
                'source_type' => ScheduledItem::SOURCE_REMINDER,
                'starts_at' => $startOfWeek->copy()->addDays(2)->setTime(20, 0),
                'priority' => ScheduledItem::PRIORITY_NORMAL,
            ],

            // Calendar events (green)
            [
                'title' => 'LunaOS Standup',
                'source_type' => ScheduledItem::SOURCE_CALENDAR,
                'starts_at' => $startOfWeek->copy()->addDays(3)->setTime(10, 0),
                'ends_at' => $startOfWeek->copy()->addDays(3)->setTime(10, 30),
                'priority' => ScheduledItem::PRIORITY_NORMAL,
                'description' => 'Weekly team standup for LunaOS development',
            ],
            [
                'title' => 'Architecture Review',
                'source_type' => ScheduledItem::SOURCE_CALENDAR,
                'starts_at' => $startOfWeek->copy()->addDays(4)->setTime(15, 0),
                'ends_at' => $startOfWeek->copy()->addDays(4)->setTime(16, 30),
                'priority' => ScheduledItem::PRIORITY_HIGH,
            ],

            // Email tasks (blue)
            [
                'title' => 'Weekly Digest Email',
                'source_type' => ScheduledItem::SOURCE_EMAIL,
                'starts_at' => $startOfWeek->copy()->addDays(6)->setTime(8, 0),
                'priority' => ScheduledItem::PRIORITY_NORMAL,
                'metadata' => ['template' => 'weekly-digest'],
            ],

            // Plane tasks (purple)
            [
                'title' => 'Complete Calendar UI',
                'source_type' => ScheduledItem::SOURCE_TASK,
                'starts_at' => $startOfWeek->copy()->addDays(2)->setTime(12, 0),
                'priority' => ScheduledItem::PRIORITY_HIGH,
                'description' => 'Finish Calendar week view implementation',
            ],
            [
                'title' => 'Write Activity Feed Tests',
                'source_type' => ScheduledItem::SOURCE_TASK,
                'starts_at' => $startOfWeek->copy()->addDays(5)->setTime(14, 0),
                'priority' => ScheduledItem::PRIORITY_NORMAL,
            ],
        ];

        foreach ($events as $event) {
            ScheduledItem::create($event);
        }
    }
}