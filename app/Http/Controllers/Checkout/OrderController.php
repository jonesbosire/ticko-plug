<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Services\Ticket\TicketService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly TicketService $ticketService) {}

    public function index(Request $request): View
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['event.venue', 'orderItems.ticketType'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_if($order->user_id !== auth()->id() && ! auth()->user()?->hasRole(['admin', 'super_admin']), 403);

        $order->load(['event.venue', 'orderItems.ticketType', 'tickets']);

        return view('orders.show', compact('order'));
    }

    public function confirmation(Order $order): View
    {
        abort_if($order->user_id !== auth()->id() && ! auth()->user()?->hasRole(['admin', 'super_admin']), 403);

        $order->load(['event.venue', 'tickets.ticketType', 'orderItems.ticketType', 'event.media']);

        return view('orders.confirmation', compact('order'));
    }

    public function downloadTicket(Order $order, Ticket $ticket): Response
    {
        abort_if($ticket->order_id !== $order->id, 404);
        abort_if($order->user_id !== auth()->id() && ! auth()->user()?->hasRole(['admin', 'super_admin']), 403);

        return $this->ticketService->downloadPdf($ticket);
    }
}
