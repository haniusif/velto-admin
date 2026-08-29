<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Support\BookingTime;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The day sheet: every booking scheduled for today, in the order it happens.
 *
 * The Dispatch Board answers "what state is each job in"; this answers "what
 * is happening today, and when" — the question asked at the start of a shift
 * and every time the phone rings. Neither the board nor the appointments list
 * answered it without filtering by hand.
 */
class TodayAppointments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.today-appointments';

    public static function getNavigationLabel(): string
    {
        return __('Today');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Operations');
    }

    public function getTitle(): string
    {
        return __("Today's bookings");
    }

    /** How many bookings are on today, so the badge reads before the page opens. */
    public static function getNavigationBadge(): ?string
    {
        $count = self::todaysBookings()->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * Today in Riyadh, not in UTC.
     *
     * scheduled_at holds Riyadh wall-clock digits stored naively, so the day
     * has to be taken from the business clock. Using today() would roll the
     * day over at 03:00 local time and show tomorrow's sheet to anyone working
     * late.
     */
    public static function todaysBookings(): Builder
    {
        return Appointment::query()
            ->whereDate('scheduled_at', BookingTime::nowWallClock()->toDateString());
    }

    /**
     * The day's figures, counted once and shared with the stats widget so
     * the header and the rows can never disagree.
     *
     * @return array<string,int|float>
     */
    public static function summary(): array
    {
        $rows = self::todaysBookings()->get(['status', 'total_price', 'worker_id']);

        return [
            'total' => $rows->count(),
            'upcoming' => $rows->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
            ])->count(),
            'in_progress' => $rows->whereIn('status', [
                Appointment::STATUS_ON_THE_WAY,
                Appointment::STATUS_ARRIVED,
                Appointment::STATUS_IN_PROGRESS,
            ])->count(),
            'completed' => $rows->where('status', Appointment::STATUS_COMPLETED)->count(),
            'cancelled' => $rows->where('status', Appointment::STATUS_CANCELLED)->count(),
            // The number that decides whether someone needs to start phoning
            // workers: booked, not cancelled, nobody assigned.
            'unassigned' => $rows->whereNull('worker_id')
                ->where('status', '!=', Appointment::STATUS_CANCELLED)
                ->count(),
            'revenue' => (float) $rows->where('status', Appointment::STATUS_COMPLETED)->sum('total_price'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => self::todaysBookings()->with(['customer', 'worker']))
            ->columns([
                TextColumn::make('scheduled_at')->timezone(config('app.timezone'))
                    ->label(__('Time'))
                    // Riyadh wall-clock digits printed as stored, in the
                    // 12-hour form both apps now use.
                    ->dateTime('g:i A')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->description(fn (Appointment $record): ?string => $record->customer?->phone)
                    ->searchable(),
                TextColumn::make('service_name')
                    ->label(__('Service'))
                    ->limit(28),
                TextColumn::make('vehicle_label')
                    ->label(__('Car'))
                    ->placeholder('—')
                    ->limit(22),
                TextColumn::make('worker.name')
                    ->label(__('Specialist'))
                    // Unassigned is the actionable state, so it is coloured
                    // rather than left as an empty cell.
                    ->placeholder(__('Unassigned'))
                    ->badge()
                    ->color(fn (Appointment $record): string => $record->worker_id ? 'success' : 'danger'),
                TextColumn::make('address_label')
                    ->label(__('Address'))
                    ->placeholder('—')
                    ->limit(24)
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Appointment::STATUS_COMPLETED => 'success',
                        Appointment::STATUS_CANCELLED => 'danger',
                        Appointment::STATUS_PENDING => 'warning',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => __(ucfirst(str_replace('_', ' ', $state)))),
                TextColumn::make('total_price')
                    ->label(__('Total'))
                    ->money()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label(__('Status'))->options([
                    Appointment::STATUS_PENDING => __('Pending'),
                    Appointment::STATUS_CONFIRMED => __('Confirmed'),
                    Appointment::STATUS_COMPLETED => __('Completed'),
                    Appointment::STATUS_CANCELLED => __('Cancelled'),
                ]),
            ])
            ->recordActions([
                Action::make('open')
                    ->label(__('Open'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Appointment $record): string => AppointmentResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('scheduled_at')
            ->emptyStateHeading(__('Nothing booked for today'))
            // A day sheet that needs a manual reload is a day sheet nobody
            // trusts; new bookings land here on their own.
            ->poll('30s');
    }
}
