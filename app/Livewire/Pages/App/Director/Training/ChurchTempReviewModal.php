<?php

namespace App\Livewire\Pages\App\Director\Training;

use App\Livewire\Shared\Training\ChurchTempReviewModal as SharedChurchTempReviewModal;

class ChurchTempReviewModal extends SharedChurchTempReviewModal
{
    protected string $trainingContext = 'director';

    protected function viewTemplate(): string
    {
        return 'livewire.pages.app.director.training.church-temp-review-modal';
    }
}
