<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $stats = Cache::remember('admin:stats', 120, function () {
            $totalRevenue    = Order::where('status', 'paid')->sum('total');
            $todayRevenue    = Order::where('status', 'paid')->whereDate('paid_at', today())->sum('total');
            $totalOrders     = Order::where('status', 'paid')->count();
            $totalTickets    = Ticket::whereIn('status', ['active', 'used'])->count();
            $checkedIn       = Ticket::where('status', 'used')->count();
            $activeEvents    = Event::where('status', 'published')->where('start_datetime', '>', now())->count();
            $totalUsers      = User::count();
            $newUsersToday   = User::whereDate('created_at', today())->count();

            return compact(
                'totalRevenue', 'todayRevenue', 'totalOrders',
                'totalTickets', 'checkedIn', 'activeEvents',
                'totalUsers', 'newUsersToday'
            );
        });

        return [
            Stat::make('Total Revenue', 'KES ' . number_format($stats['totalRevenue'], 2))
                ->description('KES ' . number_format($stats['todayRevenue'], 2) . ' today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Paid Orders', number_format($stats['totalOrders']))
                ->description('All time confirmed orders')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Tickets Sold', number_format($stats['totalTickets']))
                ->description(number_format($stats['checkedIn']) . ' checked in')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make('Live Events', number_format($stats['activeEvents']))
                ->description('Upcoming published events')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Total Users', number_format($stats['totalUsers']))
                ->description('+' . $stats['newUsersToday'] . ' today')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
