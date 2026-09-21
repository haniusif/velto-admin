<?php

namespace App\Filament\Resources\SupportTickets\Pages;

use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Services\Notifications\NotificationDispatcher;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSupportTicket extends EditRecord
{
    protected static string $resource = SupportTicketResource::class;

    private bool $replyChanged = false;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $reply = trim((string) ($data['admin_reply'] ?? ''));
        $data['admin_reply'] = $reply === '' ? null : $reply;

        $this->replyChanged = $data['admin_reply'] !== null
            && $data['admin_reply'] !== $this->record->admin_reply;

        if ($this->replyChanged) {
            $data['replied_at'] = now();
            // Answering an untouched ticket is what "in progress" means; don't
            // silently drop a status the agent already set past that.
            if (($data['status'] ?? null) === $this->record::STATUS_OPEN) {
                $data['status'] = $this->record::STATUS_IN_PROGRESS;
            }
        }

        return $data;
    }

    /** A reply the customer never hears about is no reply. */
    protected function afterSave(): void
    {
        if ($this->replyChanged) {
            app(NotificationDispatcher::class)->supportTicketReplied($this->record->fresh());
        }
    }
}
