<?php

namespace App\Http\Controllers;

use App\Models\GaRequest;
use App\Models\GaVendorQuotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Lampiran GA disimpan di disk privat (tidak bisa dibuka lewat URL langsung).
 * File hanya dikirim setelah dicek: yang meminta harus boleh melihat request GA-nya
 * (aturan yang sama dengan halaman detail: GaRequest::isVisibleTo).
 */
class GaAttachmentController extends Controller
{
    private const DISK = 'local';

    /** Tipe yang aman ditampilkan langsung di browser; selain itu dipaksa jadi unduhan. */
    private const INLINE_MIMES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'application/pdf'];

    public function lampiran(GaRequest $gaRequest, string $jenis): StreamedResponse
    {
        abort_unless($gaRequest->isVisibleTo(Auth::user()), 403);

        $path = match ($jenis) {
            'bukti' => $gaRequest->lampiran_bukti_kondisi,
            'rekomendasi' => $gaRequest->lampiran_rekomendasi_vendor,
            default => null,
        };

        return $this->send($path);
    }

    public function quotation(GaRequest $gaRequest, GaVendorQuotation $quotation): StreamedResponse
    {
        abort_unless($gaRequest->isVisibleTo(Auth::user()), 403);
        abort_unless($quotation->ga_request_id === $gaRequest->id, 404);

        return $this->send($quotation->file_path);
    }

    private function send(?string $path): StreamedResponse
    {
        abort_if(blank($path) || ! Storage::disk(self::DISK)->exists($path), 404);

        $inline = in_array(Storage::disk(self::DISK)->mimeType($path), self::INLINE_MIMES, true);

        return Storage::disk(self::DISK)->response($path, basename($path), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ], $inline ? 'inline' : 'attachment');
    }
}
