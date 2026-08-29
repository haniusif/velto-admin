<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moves the app's clock from UTC to Riyadh, for the data that needs moving.
 *
 * Most columns need nothing. MySQL keeps TIMESTAMP values in UTC internally and
 * converts them with the session timezone, so once the connection is pinned to
 * +03:00 every created_at, updated_at, expires_at and read_at in the database —
 * 104 columns — starts reading in Riyadh on its own, with the stored bytes
 * untouched. Nothing to rewrite and nothing to lose.
 *
 * DATETIME is the exception: it is stored literally and never converted. Seven
 * such columns exist, all on appointments, and they split two ways.
 *
 *   - accepted_at, started_at, arrived_at, work_started_at, completed_at and
 *     cancelled_at are instants written with now(). They hold UTC digits and
 *     must move forward three hours to keep meaning the same moment.
 *
 *   - scheduled_at is a Riyadh wall-clock time stored naively. It is already
 *     right, and shifting it would move every booking in the system three hours
 *     later. It is deliberately left alone.
 *
 * The old values are copied to appointment_event_times_utc_backup first, so the
 * change is reversible from the data rather than by arithmetic alone.
 */
return new class extends Migration
{
    private const BACKUP = 'appointment_event_times_utc_backup';

    /** Instants written with now(). scheduled_at is NOT one of these. */
    private const INSTANT_COLUMNS = [
        'accepted_at',
        'started_at',
        'arrived_at',
        'work_started_at',
        'completed_at',
        'cancelled_at',
    ];

    public function up(): void
    {
        if (! Schema::hasTable(self::BACKUP)) {
            $columns = implode(', ', self::INSTANT_COLUMNS);

            // A copy of exactly what is about to change, taken before it
            // changes. scheduled_at rides along untouched so a restore can
            // prove it was never modified.
            DB::statement(
                'CREATE TABLE '.self::BACKUP.
                ' AS SELECT id, scheduled_at, '.$columns.' FROM appointments'
            );
        }

        foreach (self::INSTANT_COLUMNS as $column) {
            DB::table('appointments')
                ->whereNotNull($column)
                ->update([$column => DB::raw($this->shift($column, forward: true))]);
        }
    }

    /**
     * Three hours of date arithmetic, in the dialect of whichever database is
     * running it. Production is MySQL; the test suite is SQLite, which does not
     * understand DATE_ADD and would fail every test that touches a migration.
     */
    private function shift(string $column, bool $forward): string
    {
        $hours = $forward ? '+3' : '-3';

        return DB::connection()->getDriverName() === 'sqlite'
            ? "datetime({$column}, '{$hours} hours')"
            : "DATE_ADD({$column}, INTERVAL {$hours} HOUR)";
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::BACKUP)) {
            // No backup to restore from, so undo the arithmetic instead.
            foreach (self::INSTANT_COLUMNS as $column) {
                DB::table('appointments')
                    ->whereNotNull($column)
                    ->update([$column => DB::raw($this->shift($column, forward: false))]);
            }

            return;
        }

        foreach (self::INSTANT_COLUMNS as $column) {
            // Correlated subquery rather than UPDATE…JOIN, which is MySQL-only.
            DB::statement(
                'UPDATE appointments SET '.$column.
                ' = (SELECT b.'.$column.' FROM '.self::BACKUP.' b WHERE b.id = appointments.id)'.
                ' WHERE id IN (SELECT id FROM '.self::BACKUP.')'
            );
        }

        Schema::dropIfExists(self::BACKUP);
    }
};
