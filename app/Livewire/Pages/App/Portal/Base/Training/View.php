<?php

namespace App\Livewire\Pages\App\Portal\Base\Training;

use App\Livewire\Shared\Training\ViewPage;

class View extends ViewPage
{
    protected string $trainingContext = 'base';

    protected function viewTemplate(): string
    {
        return 'livewire.pages.app.portal.base.training.view';
    }
}
