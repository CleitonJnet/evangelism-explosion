<?php

namespace App\Enums;

enum EventReportReviewOutcome: string
{
    case Commented = 'commented';
    case ChangesRequested = 'changes_requested';
    case Approved = 'approved';
}
