<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SupportTicketResource;
use App\Models\Appointment;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    /** GET /api/v1/me/support/tickets */
    public function index(Request $request): JsonResponse
    {
        $tickets = $request->user()->supportTickets()->latest('id')->get();

        return response()->json(['data' => SupportTicketResource::collection($tickets)]);
    }

    /** POST /api/v1/me/support/tickets */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(SupportTicket::TYPES)],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:4000'],
            'appointment_id' => ['nullable', 'integer'],
        ]);

        $appointmentId = null;
        if (! empty($data['appointment_id'])) {
            // Only the customer's own booking can be attached; anyone else's
            // id is treated as "none" rather than leaking that it exists.
            $appointmentId = Appointment::query()
                ->whereKey($data['appointment_id'])
                ->where('customer_id', $request->user()->id)
                ->value('id');
        }

        $ticket = $request->user()->supportTickets()->create([
            'type' => $data['type'],
            'subject' => trim($data['subject']),
            'message' => trim($data['message']),
            'appointment_id' => $appointmentId,
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return response()->json(['data' => new SupportTicketResource($ticket)], 201);
    }

    /** GET /api/v1/me/support/tickets/{ticket} */
    public function show(Request $request, SupportTicket $ticket): JsonResponse
    {
        abort_unless($ticket->customer_id === $request->user()?->id, 404);

        return response()->json(['data' => new SupportTicketResource($ticket)]);
    }
}
