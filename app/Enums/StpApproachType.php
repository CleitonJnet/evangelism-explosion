<?php

namespace App\Enums;

enum StpApproachType: string
{
    case Visitor = 'visitor';
    case SecurityQuestionnaire = 'security_questionnaire';
    case Indication = 'indication';
    case Lifestyle = 'lifestyle';
}
