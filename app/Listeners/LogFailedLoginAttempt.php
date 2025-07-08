<?php

namespace App\Listeners;

use App\Actions\SystemLogAction;
use App\Dtos\SystemLogData;
use Illuminate\Auth\Events\Failed;

class LogFailedLoginAttempt
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
    public function handle(Failed $event): void
    {
        $this->systemLogAction->execute(
            new SystemLogData(
                'Failed login attempt',
                null,
                null,
                'login',
                request()->ip(),
                null,
                null,
                request()->fullUrl(),
                $event->user->id
            )
        );
    }
}
