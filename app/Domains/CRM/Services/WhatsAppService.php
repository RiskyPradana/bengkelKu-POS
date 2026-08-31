<?php

namespace App\Domains\CRM\Services;

use App\Domains\CRM\Models\WhatsappLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $provider;
    private string $apiUrl;
    private string $apiKey;
    private string $sender;

    public function __construct()
    {
        $this->provider = (string) config('whatsapp.provider', 'sandbox');
        $this->apiUrl   = (string) config('whatsapp.url', '');
        $this->apiKey   = (string) config('whatsapp.key', '');
        $this->sender   = (string) config('whatsapp.sender', '');

        // Kalau URL kosong tapi provider dikenal, pakai preset endpoint.
        if ($this->apiUrl === '') {
            $this->apiUrl = (string) config('whatsapp.providers.' . $this->provider . '.url', '');
        }
    }

    /**
     * Kirim pesan WhatsApp dan catat ke whatsapp_logs.
     */
    public function send(
        string $phone,
        string $message,
        string $type = 'custom',
        ?string $referenceId = null,
        ?string $customerId = null,
    ): bool {
        $log = WhatsappLog::create([
            'customer_id'  => $customerId,
            'phone_number' => $this->normalizePhone($phone),
            'type'         => $type,
            'message'      => $message,
            'status'       => 'queued',
            'reference_id' => $referenceId,
        ]);

        try {
            if ($this->isSandbox()) {
                Log::channel('daily')->info('WhatsApp [SANDBOX]', [
                    'provider' => $this->provider,
                    'to'       => $log->phone_number,
                    'type'     => $type,
                    'message'  => $message,
                ]);
                $log->update(['status' => 'sent', 'sent_at' => now()]);
                return true;
            }

            $response = Http::timeout(20)
                ->withHeaders($this->buildHeaders())
                ->post($this->apiUrl, $this->buildPayload($log->phone_number, $message));

            if ($response->successful()) {
                $log->update([
                    'status'   => 'sent',
                    'sent_at'  => now(),
                    'response' => mb_substr($response->body(), 0, 1000),
                ]);
                return true;
            }

            $log->update([
                'status'        => 'failed',
                'error_message' => mb_substr($response->body(), 0, 1000),
            ]);
            return false;
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error('WhatsApp send failed: ' . $e->getMessage());
            return false;
        }
    }

    private function isSandbox(): bool
    {
        return $this->provider === 'sandbox'
            || $this->apiUrl === ''
            || $this->apiKey === '';
    }

    private function buildHeaders(): array
    {
        $authType = (string) config('whatsapp.providers.' . $this->provider . '.auth_type', 'bearer');

        return match ($authType) {
            'raw'    => ['Authorization' => $this->apiKey],
            'bearer' => ['Authorization' => 'Bearer ' . $this->apiKey],
            default  => [],
        };
    }

    private function buildPayload(string $phone, string $message): array
    {
        // Meta WhatsApp Cloud API punya bentuk payload khusus.
        if ($this->provider === 'meta') {
            return [
                'messaging_product' => 'whatsapp',
                'to'                => $phone,
                'type'              => 'text',
                'text'              => ['body' => $message],
            ];
        }

        $fields  = (array) config('whatsapp.providers.' . $this->provider . '.fields', []);
        $payload = [];

        foreach ($fields as $apiField => $meaning) {
            $payload[$apiField] = $meaning === 'phone' ? $phone : $message;
        }

        if ($payload === []) {
            $payload = ['number' => $phone, 'message' => $message];
        }

        if ((string) config('whatsapp.providers.' . $this->provider . '.auth_type') === 'body') {
            $payload['api_key'] = $this->apiKey;
            $payload['number_key'] = $this->sender;
        }

        return $payload;
    }

    /**
     * Template: Struk digital setelah pembayaran.
     */
    public function sendInvoice(string $phone, string $customerName, string $invoiceNumber, float $total, ?string $customerId = null): bool
    {
        $amount  = 'Rp ' . number_format($total, 0, ',', '.');
        $message = "*BENGKELKU - Struk Digital*\n\n"
            . "Yth. {$customerName},\n"
            . "Terima kasih telah mempercayakan kendaraan Anda kepada kami.\n\n"
            . "No. Invoice : *{$invoiceNumber}*\n"
            . "Total       : *{$amount}*\n"
            . 'Tanggal     : ' . now()->format('d/m/Y H:i') . "\n\n"
            . '_Simpan pesan ini sebagai bukti pembayaran._';

        return $this->send($phone, $message, 'invoice', $invoiceNumber, $customerId);
    }

    /**
     * Template: Pengingat servis berkala.
     */
    public function sendServiceReminder(string $phone, string $customerName, string $plateNumber, string $dueDate, ?string $customerId = null): bool
    {
        $message = "*BENGKELKU - Pengingat Servis*\n\n"
            . "Yth. {$customerName},\n"
            . "Kendaraan Anda (*{$plateNumber}*) sudah waktunya servis berkala.\n\n"
            . "Jatuh tempo : *{$dueDate}*\n\n"
            . "Segera hubungi kami untuk membuat janji servis.\n"
            . 'Terima kasih!';

        return $this->send($phone, $message, 'reminder', $plateNumber, $customerId);
    }

    /**
     * Template: Kendaraan siap diambil.
     */
    public function sendReadyForPickup(string $phone, string $customerName, string $plateNumber, ?string $woNumber = null, ?string $customerId = null): bool
    {
        $message = "*BENGKELKU - Kendaraan Siap Diambil*\n\n"
            . "Yth. {$customerName},\n"
            . "Kendaraan Anda (*{$plateNumber}*) sudah selesai dikerjakan dan siap diambil.\n\n"
            . ($woNumber ? "No. WO : *{$woNumber}*\n\n" : "\n")
            . 'Silakan datang pada jam operasional bengkel. Terima kasih!';

        return $this->send($phone, $message, 'pickup', $woNumber, $customerId);
    }

    /**
     * Tes koneksi ke provider tanpa menyimpan log pelanggan.
     */
    public function testConnection(string $phone): array
    {
        if ($this->isSandbox()) {
            return [
                'ok'      => true,
                'mode'    => 'sandbox',
                'message' => 'Mode sandbox aktif. Pesan hanya masuk ke storage/logs/laravel.log.',
            ];
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders($this->buildHeaders())
                ->post($this->apiUrl, $this->buildPayload(
                    $this->normalizePhone($phone),
                    'Tes koneksi WhatsApp Gateway BengkelKu-POS. Kalau pesan ini masuk, konfigurasi sudah benar.'
                ));

            return [
                'ok'      => $response->successful(),
                'mode'    => $this->provider,
                'status'  => $response->status(),
                'message' => mb_substr($response->body(), 0, 500),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'mode' => $this->provider, 'message' => $e->getMessage()];
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        if (! str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
