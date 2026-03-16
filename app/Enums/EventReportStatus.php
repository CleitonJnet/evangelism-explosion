<?php

namespace App\Enums;

enum EventReportStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case NeedsRevision = 'needs_revision';
    case Reviewed = 'reviewed';
}
