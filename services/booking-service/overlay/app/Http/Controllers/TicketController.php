<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /** Un participant ne voit que ses propres billets (404 sinon). */
    private function mine(Request $r)
    {
        $uid = $r->attributes->get('auth')['id'];
        return Ticket::with('booking')->whereHas('booking', fn ($q) => $q->where('participant_id', $uid));
    }

    public function index(Request $r)
    {
        return $this->mine($r)->orderByDesc('created_at')->get();
    }

    public function show(Request $r, string $id)
    {
        return $this->mine($r)->findOrFail($id);
    }

    public function download(Request $r, string $id)
    {
        $t = $this->mine($r)->findOrFail($id);
        abort_unless($t->status === 'VALID', 409, 'Ce billet n\'est plus valide.');
        $qr = Builder::create()->writer(new PngWriter())->data($t->qr_token)->size(240)->margin(4)->build()->getDataUri();
        return Pdf::loadView('ticket', ['ticket' => $t, 'booking' => $t->booking, 'qr' => $qr])
            ->download($t->ticket_reference.'.pdf');
    }
}
