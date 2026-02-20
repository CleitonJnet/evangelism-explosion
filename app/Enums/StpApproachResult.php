<?php

namespace App\Enums;

enum StpApproachResult: string
{
    case Decision = 'decision';
    case NoDecisionInterested = 'no_decision_interested';
    case Rejection = 'rejection';
    case AlreadyChristian = 'already_christian';
}
