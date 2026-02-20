<?php

namespace App\Enums;

enum StpApproachStatus: string
{
    case Planned = 'planned';
    case Assigned = 'assigned';
    case Done = 'done';
    case Reviewed = 'reviewed';
}
