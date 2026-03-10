<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Livewire\Shared\Training\ViewPage;

class View extends ViewPage
{
    protected string $trainingContext = 'teacher';

    protected function viewTemplate(): string
    {
        return 'livewire.pages.app.teacher.training.view';
    }
}
