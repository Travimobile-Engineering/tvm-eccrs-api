<?php

namespace App\Enums\Enums;

enum WatchlistStatus: string
{
    const IN_CUSTODY = 'in-custody';
    const CLOSED = 'closed';
    const ACTIVE = 'active';
}
