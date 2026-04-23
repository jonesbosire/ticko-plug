<?php

namespace App\Filament\Organizer\Widgets;

use App\Enums\OrderStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrganizerStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $organizerId = auth()->id();

        $eventIds = Event::where('organizer_id', $organizerId)->pluck('id');

        $totalRevenue = Order::whereIn('event_id', $eventIds)
            ->where('status', OrderStatus::Paid)
            ->sum('organizer_amount');

        $totalTickets = Ticket::whereIn('event_id', $eventIds)
            ->whereIn('status', ['active', 'used'])
            ->count();

        $checkedIn = Ticket::whereIn('event_id', $eventIds)
            ->where('status', 'used')
            ->count();

        $upcomingEvents = Event::where('organizer_id', $organizerId)
            ->where('start_datetime', '>', now())
            ->whereIn('status', ['published'])
            ->count();

        $revenueThisMonth = Order::whereIn('event_id', $eventIds)
            ->where('status', OrderStatus::Paid)
            ->whereMonth('paid_at', now()->month)
            ->sum('organizer_amount');

        $ordersThisMonth = Order::whereIn('event_id', $eventIds)
            ->where('status', OrderStatus::Paid)
            ->whereMonth('paid_at', now()->month)
            ->count();

        return [
            Stat::make('Total Revenue', 'KES ' . number_format($totalRevenue))
                ->description('All-time organizer earnings')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('This Month', 'KES ' . number_format($revenueThisMonth))
                ->description($ordersThisMonth . ' orders this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Tickets Sold', number_format($totalTickets))
                ->description($checkedIn . ' checked in')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('warning'),

            Stat::make('Upcoming Events', $upcomingEvents)
                ->description('Live and published')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),
        ];
    }
}
