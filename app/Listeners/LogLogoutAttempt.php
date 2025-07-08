<?php

namespace App\Listeners;

use App\Actions\SystemLogAction;
use App\Dtos\SystemLogData;
use Illuminate\Auth\Events\Logout;

class LogLogoutAttempt
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected SystemLogAction $systemLogAction,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $this->systemLogAction->execute(
            new SystemLogData(
                'User logged out',
                null,
                null,
                'logout',
                request()->ip(),
                null,
                null,
                request()->fullUrl(),
                $event->user->id
            )
        );
    }
}
