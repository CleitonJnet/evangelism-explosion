<?php

namespace App\Livewire\Pages\App\Director\Training;

use App\Livewire\Shared\Training\ApproveChurchTempModal as SharedApproveChurchTempModal;

class ApproveChurchTempModal extends SharedApproveChurchTempModal
{
    protected string $trainingContext = 'director';

    protected function viewTemplate(): string
    {
        return 'livewire.pages.app.director.training.approve-church-temp-modal';
    }
}
