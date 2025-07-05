<?php

namespace App\Enums\Enums;

enum WatchlistStatus: string
{
    case IN_CUSTODY = 'in-custody';
    case CLOSED = 'closed';
    case ACTIVE = 'active';
}
