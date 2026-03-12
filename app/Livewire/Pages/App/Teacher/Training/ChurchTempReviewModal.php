<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Livewire\Shared\Training\ChurchTempReviewModal as SharedChurchTempReviewModal;

class ChurchTempReviewModal extends SharedChurchTempReviewModal
{
    protected string $trainingContext = 'teacher';

    protected function viewTemplate(): string
    {
        return 'livewire.pages.app.teacher.training.church-temp-review-modal';
    }
}
