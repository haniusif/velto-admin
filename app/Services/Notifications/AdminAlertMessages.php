<?php

namespace App\Services\Notifications;

/**
 * Title and body for each admin alert. Kept short: the same words become an
 * SMS, and every 70 Arabic characters is another billed segment.
 */
class AdminAlertMessages
{
    /** How each event is named on the Admin alerts page. */
    public const LABELS = [
        'booking_confirmed' => 'New booking (paid / confirmed)',
        'needs_worker' => 'Booking needs a worker',
        'booking_cancelled' => 'Booking cancelled',
        'booking_rescheduled' => 'Booking rescheduled',
        'worker_assigned' => 'Worker assigned',
        'worker_on_the_way' => 'Worker on the way',
        'worker_arrived' => 'Worker arrived',
        'job_started' => 'Work started',
        'job_completed' => 'Job completed',
        'customer_registered' => 'New customer signed up',
    ];

    /** event => [title, body] — English source strings, translated via lang/ar.json. */
    public const TEMPLATES = [
        'booking_confirmed' => ['New booking #:id', ':customer · :service · :when · :total'],
        'needs_worker' => ['Order #:id needs a worker', ':reason · :service · :when'],
        'booking_cancelled' => ['Order #:id cancelled', 'By :actor · :service · :when'],
        'booking_rescheduled' => ['Order #:id rescheduled', ':old → :when · by :actor'],
        'worker_assigned' => ['Order #:id assigned to :worker', ':service · :when · by :actor'],
        'worker_on_the_way' => [':worker is on the way — order #:id', ':customer · :address'],
        'worker_arrived' => [':worker arrived — order #:id', ':customer · :address'],
        'job_started' => [':worker started order #:id', ':service'],
        'job_completed' => ['Order #:id completed by :worker', ':service · :total'],
        'customer_registered' => ['New customer: :name', ':phone'],
    ];

    /**
     * @param  array<string,string>  $vars
     * @return array{0: string, 1: string}
     */
    public static function render(string $event, array $vars, string $lang): array
    {
        [$title, $body] = self::TEMPLATES[$event];

        return [__($title, $vars, $lang), __($body, $vars, $lang)];
    }
}
