<?php

namespace App\Listeners;

use App\Events\WorkerAssigned;
use App\Services\Notifications\NotificationDispatcher;

/** Turns the assignment event into the worker "assigned" notification. */
class SendWorkerAssignedNotification
{
    public function __construct(private readonly NotificationDispatcher $notifications) {}

    public function handle(WorkerAssigned $event): void
    {
        $this->notifications->workerAssigned($event->appointment);
    }
}
