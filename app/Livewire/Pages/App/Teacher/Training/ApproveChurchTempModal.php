<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Livewire\Shared\Training\ApproveChurchTempModal as SharedApproveChurchTempModal;

class ApproveChurchTempModal extends SharedApproveChurchTempModal
{
    protected function viewTemplate(): string
    {
        return 'livewire.pages.app.teacher.training.approve-church-temp-modal';
    }
}
