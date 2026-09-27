<?php

namespace App\Jobs;

use App\Models\WhatsappUser;
use App\Models\ChatHistory;
use App\Services\Bot\BotFlowService;
use App\Services\Bot\SessionService;
use App\Services\Bot\LanguageService;
use App\Services\WhatsApp\WhatsAppService;
use App\Services\Payment\RazorpayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBotMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int    $tries   = 2;
    public int    $timeout = 60;
    public string $queue   = 'bot';

    public function __construct(
        public readonly string $phone,
        public readonly string $text,
        public readonly string $type,
        public readonly array  $rawPayload = []
    ) {}

    public function handle(): void
    {
        // Get or create user
        $user = WhatsappUser::firstOrCreate(
            ['phone' => $this->phone],
            ['preferred_lang' => 'en', 'session_state' => 'IDLE']
        );

        // Log inbound message to chat_history
        ChatHistory::create([
            'user_id'      => $user->id,
            'direction'    => 'inbound',
            'message_type' => $this->type,
            'body'         => $this->text,
        ]);

        // Process through bot flow
        $botFlow = new BotFlowService(
            new WhatsAppService(),
            new SessionService(),
            new LanguageService(),
            new RazorpayService()
        );

        $botFlow->handle($user, $this->text, $this->type);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessBotMessage job failed for phone: {$this->phone}", [
            'text'  => $this->text,
            'error' => $exception->getMessage(),
        ]);
    }
}