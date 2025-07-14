<?php

namespace App\Listeners;

use App\Actions\SystemLogAction;
use App\Dtos\SystemLogData;
use Illuminate\Auth\Events\Login;

class LogLoginAttempt
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
    public function handle(Login $event): void
    {
        $this->systemLogAction->execute(
            new SystemLogData(
                'User logged in',
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
