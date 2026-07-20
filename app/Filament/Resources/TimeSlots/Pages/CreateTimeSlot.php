<?php

namespace App\Filament\Resources\TimeSlots\Pages;

use App\Filament\Resources\TimeSlots\TimeSlotResource;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateTimeSlot extends CreateRecord
{
    protected static string $resource = TimeSlotResource::class;

    /** Hard cap so a fat date range can't accidentally spawn thousands of rows. */
    private const MAX_SLOTS = 2000;

    protected int $createdCount = 0;

    protected int $skippedCount = 0;

    public function getTitle(): string
    {
        return __('Generate time slots');
    }

    /**
     * Bulk-generation form: pick a date range + a daily window + a slot length,
     * and every matching slot is created in one go. Far quicker than adding
     * them one at a time. (Editing still uses the single-slot resource form.)
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Date range'))
                    ->description(__('Slots are generated for every selected weekday between these two dates (inclusive).'))
                    ->columns(2)
                    ->components([
                        DatePicker::make('date_from')
                            ->label(__('From date'))
                            ->required()
                            ->native(false)
                            ->minDate(today())
                            ->default(today())
                            ->live(),

                        DatePicker::make('date_to')
                            ->label(__('To date'))
                            ->required()
                            ->native(false)
                            ->minDate(today())
                            ->default(today())
                            ->afterOrEqual('date_from')
                            ->live(),

                        CheckboxList::make('weekdays')
                            ->label(__('On these days'))
                            ->options([
                                '0' => __('Sunday'),
                                '1' => __('Monday'),
                                '2' => __('Tuesday'),
                                '3' => __('Wednesday'),
                                '4' => __('Thursday'),
                                '5' => __('Friday'),
                                '6' => __('Saturday'),
                            ])
                            ->default(['0', '1', '2', '3', '4', '5', '6'])
                            ->columns(4)
                            ->gridDirection('row')
                            ->helperText(__('Leave all checked to include every day in the range.'))
                            ->columnSpanFull()
                            ->live(),
                    ]),

                Section::make(__('Daily window'))
                    ->description(__('Each day is filled with back-to-back slots of the chosen length inside this window.'))
                    ->columns(2)
                    ->components([
                        TimePicker::make('window_start')
                            ->label(__('Day starts at'))
                            ->seconds(false)
                            ->native(false)
                            ->default('09:00')
                            ->required()
                            ->live(),

                        TimePicker::make('window_end')
                            ->label(__('Day ends at'))
                            ->seconds(false)
                            ->native(false)
                            ->default('18:00')
                            ->required()
                            ->after('window_start')
                            ->live(),

                        Select::make('slot_minutes')
                            ->label(__('Slot length'))
                            ->options([
                                30 => __(':n minutes', ['n' => 30]),
                                45 => __(':n minutes', ['n' => 45]),
                                60 => __('1 hour'),
                                90 => __('1.5 hours'),
                                120 => __('2 hours'),
                            ])
                            ->default(60)
                            ->native(false)
                            ->required()
                            ->live(),

                        TextInput::make('gap_minutes')
                            ->label(__('Gap between slots'))
                            ->helperText(__('Buffer in minutes after each slot (e.g. travel/cleanup time).'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix(__('min'))
                            ->live(),
                    ]),

                Section::make(__('Applied to every slot'))
                    ->columns(2)
                    ->components([
                        TextInput::make('capacity')
                            ->label(__('Capacity'))
                            ->helperText(__('How many parallel bookings each slot can hold.'))
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make(__('Preview'))
                    ->components([
                        Placeholder::make('slots_preview')
                            ->hiddenLabel()
                            ->content(fn (Get $get): string => static::previewText([
                                'date_from' => $get('date_from'),
                                'date_to' => $get('date_to'),
                                'window_start' => $get('window_start'),
                                'window_end' => $get('window_end'),
                                'slot_minutes' => $get('slot_minutes'),
                                'gap_minutes' => $get('gap_minutes'),
                                'weekdays' => $get('weekdays'),
                            ])),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Build the list of slots implied by the form state. Single source of truth
     * for both the live preview and the actual creation.
     *
     * @return array<int, array{date: string, start_time: string, end_time: string}>
     */
    private static function buildSlots(array $data): array
    {
        $slots = [];

        foreach (['date_from', 'date_to', 'window_start', 'window_end', 'slot_minutes'] as $key) {
            if (blank($data[$key] ?? null)) {
                return $slots;
            }
        }

        try {
            $from = Carbon::parse($data['date_from'])->startOfDay();
            $to = Carbon::parse($data['date_to'])->startOfDay();
            $windowStart = Carbon::parse($data['window_start']);
            $windowEnd = Carbon::parse($data['window_end']);
        } catch (\Throwable) {
            return $slots;
        }

        if ($to->lt($from)) {
            return $slots;
        }

        $slotMinutes = (int) $data['slot_minutes'];
        $gapMinutes = max(0, (int) ($data['gap_minutes'] ?? 0));
        if ($slotMinutes < 1) {
            return $slots;
        }

        $weekdays = array_map('strval', (array) ($data['weekdays'] ?? []));

        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            if (! empty($weekdays) && ! in_array((string) $day->dayOfWeek, $weekdays, true)) {
                continue;
            }

            $cursor = $day->copy()->setTime($windowStart->hour, $windowStart->minute);
            $dayEnd = $day->copy()->setTime($windowEnd->hour, $windowEnd->minute);

            while ($cursor->copy()->addMinutes($slotMinutes)->lte($dayEnd)) {
                $slots[] = [
                    'date' => $day->toDateString(),
                    'start_time' => $cursor->format('H:i:s'),
                    'end_time' => $cursor->copy()->addMinutes($slotMinutes)->format('H:i:s'),
                ];

                if (count($slots) >= self::MAX_SLOTS) {
                    return $slots;
                }

                $cursor->addMinutes($slotMinutes + $gapMinutes);
            }
        }

        return $slots;
    }

    /**
     * Split planned slots into the ones we'd actually create versus those that
     * clash with a slot already in the DB. A clash is any time-range overlap on
     * the same day — not just an identical start time — so a 1-hour slot won't
     * be dropped inside an existing 2-hour one. Planned slots never overlap each
     * other (they're back-to-back with a non-negative gap), so we only compare
     * against existing rows.
     *
     * @param  array<int, array{date: string, start_time: string, end_time: string}>  $planned
     * @return array{new: array<int, array{date: string, start_time: string, end_time: string}>, skipped: int}
     */
    private static function partitionSlots(array $planned): array
    {
        if (empty($planned)) {
            return ['new' => [], 'skipped' => 0];
        }

        $dates = array_values(array_unique(array_column($planned, 'date')));

        $existingByDate = TimeSlot::whereIn('date', $dates)
            ->get(['date', 'start_time', 'end_time'])
            ->groupBy(fn (TimeSlot $slot): string => $slot->date->toDateString());

        $new = [];
        $skipped = 0;

        foreach ($planned as $slot) {
            $overlaps = ($existingByDate[$slot['date']] ?? collect())
                ->contains(function (TimeSlot $existing) use ($slot): bool {
                    $existingStart = substr((string) $existing->start_time, 0, 8);
                    $existingEnd = substr((string) $existing->end_time, 0, 8);

                    // Standard interval overlap; touching edges (end == start) don't count.
                    return $existingStart < $slot['end_time'] && $existingEnd > $slot['start_time'];
                });

            if ($overlaps) {
                $skipped++;

                continue;
            }

            $new[] = $slot;
        }

        return ['new' => $new, 'skipped' => $skipped];
    }

    private static function previewText(array $data): string
    {
        $planned = static::buildSlots($data);

        if (empty($planned)) {
            return __('No slots yet — pick a date range and a daily window that fits at least one slot.');
        }

        ['new' => $new, 'skipped' => $skipped] = static::partitionSlots($planned);
        $capped = count($planned) >= self::MAX_SLOTS ? ' '.__('(capped — narrow the range to see the rest)') : '';

        if (empty($new)) {
            return __('Every slot in this range already exists or overlaps — nothing new to create.').$capped;
        }

        $newCount = count($new);
        $days = count(array_unique(array_column($new, 'date')));

        $text = __('This will create :count :slots across :days :days_word.', [
            'count' => $newCount,
            'slots' => trans_choice('{1}slot|[2,*]slots', $newCount),
            'days' => $days,
            'days_word' => trans_choice('{1}day|[2,*]days', $days),
        ]);

        if ($skipped > 0) {
            $text .= ' '.__(':count already exist or overlap and will be skipped.', ['count' => $skipped]);
        }

        return $text.$capped;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $planned = static::buildSlots($data);

        if (empty($planned)) {
            Notification::make()
                ->warning()
                ->title(__('Nothing to generate'))
                ->body(__('Check the date range and daily window — no slot fits.'))
                ->send();

            throw new Halt;
        }

        ['new' => $new, 'skipped' => $this->skippedCount] = static::partitionSlots($planned);

        if (empty($new)) {
            Notification::make()
                ->warning()
                ->title(__('Nothing new to create'))
                ->body(__('Every slot in this range already exists or overlaps.'))
                ->send();

            throw new Halt;
        }

        $capacity = (int) $data['capacity'];
        $isActive = (bool) ($data['is_active'] ?? true);

        $first = null;

        foreach ($new as $slot) {
            // firstOrCreate keeps the unique(date, start_time) index as a final
            // guard against a race, though partitionSlots already excluded clashes.
            $record = TimeSlot::firstOrCreate(
                ['date' => $slot['date'], 'start_time' => $slot['start_time']],
                [
                    'end_time' => $slot['end_time'],
                    'capacity' => $capacity,
                    'booked_count' => 0,
                    'is_active' => $isActive,
                ],
            );

            $first ??= $record;
            $record->wasRecentlyCreated ? $this->createdCount++ : $this->skippedCount++;
        }

        return $first;
    }

    protected function getCreatedNotification(): ?Notification
    {
        $notification = Notification::make()
            ->success()
            ->title(__(':count time slots created', ['count' => $this->createdCount]));

        if ($this->skippedCount > 0) {
            $notification->body(__(':count already existed or overlapped and were skipped.', ['count' => $this->skippedCount]));
        }

        return $notification;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
