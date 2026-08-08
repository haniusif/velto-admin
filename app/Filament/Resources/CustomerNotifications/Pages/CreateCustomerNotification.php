<?php

namespace App\Filament\Resources\CustomerNotifications\Pages;

use App\Filament\Resources\CustomerNotifications\CustomerNotificationResource;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\PushSender;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerNotification extends CreateRecord
{
    protected static string $resource = CustomerNotificationResource::class;

    /**
     * Writing the row only fills the in-app inbox, which the customer sees on
     * their next refresh. Push it too, so sending from the admin means the same
     * thing here as it does everywhere else in the app.
     */
    protected function afterCreate(): void
    {
        $pushed = app(NotificationDispatcher::class)
            ->pushCustomerNotification($this->record);

        if ($pushed) {
            return;
        }

        // Saying nothing would read as "delivered". Name the reason instead.
        Notification::make()
            ->title(__('Saved to the inbox, but not pushed'))
            ->body(app(PushSender::class)->configured(PushSender::AUDIENCE_CUSTOMER)
                ? __('This customer has no device registered — they will see it in the app.')
                : __('Push is not configured on this server.'))
            ->warning()
            ->send();
    }
}
