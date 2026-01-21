<?php

use App\Models\Training;
use App\TrainingStatus;

test('training status key maps to slugs', function (TrainingStatus $status, string $expected) {
    $training = new Training(['status' => $status]);

    expect($training->statusKey())->toBe($expected);
})->with([
    'planning' => [TrainingStatus::Planning, 'planning'],
    'scheduled' => [TrainingStatus::Scheduled, 'scheduled'],
    'canceled' => [TrainingStatus::Canceled, 'canceled'],
    'completed' => [TrainingStatus::Completed, 'completed'],
]);
