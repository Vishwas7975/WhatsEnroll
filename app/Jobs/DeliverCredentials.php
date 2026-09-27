<?php

namespace App\Jobs;

use App\Models\WhatsappUser;
use App\Services\Credential\CredentialService;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeliverCredentials implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int    $tries   = 3;
    public int    $backoff  = 60;
    public int    $timeout  = 120;
    public string $queue    = 'credentials';

    public function __construct(
        public readonly WhatsappUser $user,
        public readonly int $courseId,
        public readonly float $amount,
        public readonly string $utrNumber,
        public readonly string $paymentType = 'full'
    ) {}

    public function handle(CredentialService $credentialService): void
    {
        Log::info("DeliverCredentials job started", [
            'user_id'   => $this->user->id,
            'course_id' => $this->courseId,
            'utr'       => $this->utrNumber,
        ]);

        $success = $credentialService->enrollAndDeliver(
            $this->user,
            $this->courseId,
            $this->amount,
            $this->utrNumber,
            $this->paymentType
        );

        if (!$success) {
            Log::warning("DeliverCredentials: enrollAndDeliver returned false, will retry", [
                'user_id' => $this->user->id,
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("DeliverCredentials job FAILED permanently", [
            'user_id'   => $this->user->id,
            'course_id' => $this->courseId,
            'error'     => $exception->getMessage(),
        ]);

        try {
            $whatsapp = app(WhatsAppService::class);
            $whatsapp->sendText(
                $this->user->phone,
                "⚠️ We confirmed your enrollment but had trouble sending credentials automatically.\n\n"
                . "Our team will send them manually within 2 hours.\n"
                . "Support: " . config('app.support_email', 'support@example.com') . " 🙏"
            );
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp failure notice: " . $e->getMessage());
        }
    }
}