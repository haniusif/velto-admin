<?php

namespace App\Jobs;

use App\Mail\AdminAlertMail;
use App\Services\JawalySMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Email and SMS for one admin alert. Each channel is tried on its own, so a
 * mailbox misconfiguration does not also swallow the SMS.
 */
class SendAdminAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /** Not retried: a retry after a partial send would SMS everyone twice. */
    public int $tries = 1;

    /**
     * @param  list<string>  $emails
     * @param  list<string>  $phones
     */
    public function __construct(
        public string $title,
        public string $body,
        public string $url,
        public array $emails,
        public array $phones,
    ) {}

    public function handle(JawalySMSService $sms): void
    {
        if ($this->emails !== []) {
            try {
                Mail::to($this->emails)->send(new AdminAlertMail($this->title, $this->body, $this->url));
            } catch (Throwable $e) {
                Log::error('[alerts] email failed', ['title' => $this->title, 'error' => $e->getMessage()]);
            }
        }

        if ($this->phones !== []) {
            $result = $sms->sendSMS($this->phones, $this->title."\n".$this->body);

            if (! ($result['success'] ?? false)) {
                Log::error('[alerts] sms failed', ['title' => $this->title, 'result' => $result]);
            }
        }
    }
}
