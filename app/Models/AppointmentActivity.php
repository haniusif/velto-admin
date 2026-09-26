<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per change to a booking, with who made it. Append-only; written by
 * Appointment's model hooks so no controller, command or panel action can
 * forget to record itself.
 */
class AppointmentActivity extends Model
{
    public const UPDATED_AT = null;

    public const EVENT_CREATED = 'created';
    public const EVENT_STATUS_CHANGED = 'status_changed';
    public const EVENT_UPDATED = 'updated';

    public const ACTOR_ADMIN = 'admin';
    public const ACTOR_CUSTOMER = 'customer';
    public const ACTOR_WORKER = 'worker';
    public const ACTOR_SYSTEM = 'system';

    /**
     * The fields worth telling a person about. Bookkeeping that moves on
     * every dispatch tick (attempt counters, offer ids, dispatch_state) and
     * the *_at stamps that merely accompany a status change are left out —
     * the status change itself is the event.
     */
    public const TRACKED = [
        'status', 'worker_id', 'scheduled_at', 'payment_status', 'payment_method',
        'customer_id', 'service_name', 'vehicle_label', 'address_label', 'notes',
        'base_price', 'addons_total', 'discount_total', 'total_price',
        'auto_dispatch', 'assignment_locked',
    ];

    protected $fillable = ['appointment_id', 'event', 'actor_type', 'actor_id', 'actor_name', 'changes', 'created_at'];

    protected $casts = ['changes' => 'array', 'created_at' => 'datetime'];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public static function recordCreated(Appointment $appointment): void
    {
        static::write($appointment, self::EVENT_CREATED, null);
    }

    public static function recordUpdated(Appointment $appointment): void
    {
        $changes = [];
        foreach (array_intersect_key($appointment->getChanges(), array_flip(self::TRACKED)) as $field => $new) {
            $old = $appointment->getOriginal($field);
            if (self::normalise($old) !== self::normalise($new)) {
                $changes[$field] = [self::normalise($old), self::normalise($new)];
            }
        }

        if ($changes === []) {
            return;
        }

        static::write($appointment, isset($changes['status']) ? self::EVENT_STATUS_CHANGED : self::EVENT_UPDATED, $changes);
    }

    private static function write(Appointment $appointment, string $event, ?array $changes): void
    {
        [$type, $id, $name] = self::currentActor();

        static::create([
            'appointment_id' => $appointment->id,
            'event' => $event,
            'actor_type' => $type,
            'actor_id' => $id,
            'actor_name' => $name,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }

    /**
     * Whoever the current request authenticated as. Each API route group sets
     * its own guard as the default (auth:customer / auth:worker) and the panel
     * uses web, so the default guard's user is the one acting. Nobody signed
     * in means the platform itself: a scheduled command, a queued job, or a
     * payment gateway calling back.
     *
     * @return array{0: string, 1: ?int, 2: ?string}
     */
    public static function currentActor(): array
    {
        $user = auth()->user();

        return match (true) {
            $user instanceof User => [self::ACTOR_ADMIN, $user->id, $user->name],
            $user instanceof Customer => [self::ACTOR_CUSTOMER, $user->id, $user->name],
            $user instanceof Worker => [self::ACTOR_WORKER, $user->id, $user->name],
            app()->runningInConsole() => [self::ACTOR_SYSTEM, null, self::consoleLabel($_SERVER['argv'][1] ?? null)],
            default => [self::ACTOR_SYSTEM, null, str_contains(request()->path(), 'pay') ? 'payment gateway' : request()->path()],
        };
    }

    /** What a scheduled command or queued job is, in words an operator reads. */
    private static function consoleLabel(?string $command): ?string
    {
        return match ($command) {
            'bookings:cancel-stale' => 'Unpaid booking auto-cancel',
            'queue:work', 'schedule:run', 'schedule:work' => 'Background task',
            null => null,
            default => $command,
        };
    }

    private static function normalise(mixed $value): mixed
    {
        return match (true) {
            $value instanceof \DateTimeInterface => $value->format('Y-m-d H:i:s'),
            is_bool($value) => $value,
            is_float($value), is_int($value) => (string) $value,
            default => $value,
        };
    }
}
