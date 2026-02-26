<?php

it('removes profile training and teaching sections and related backend references', function (): void {
    $projectRoot = dirname(__DIR__, 2);
    $profileComponentView = file_get_contents($projectRoot.'/resources/views/components/app/settings/⚡profile.blade.php');
    $profileVoltComponent = file_get_contents($projectRoot.'/resources/views/livewire/pages/app/settings/profile.blade.php');
    $userModel = file_get_contents($projectRoot.'/app/Models/User.php');

    expect($profileComponentView)->not->toContain('Treinamentos como professor titular');
    expect($profileComponentView)->not->toContain('$user->trainingsAsTeacher');
    expect($profileComponentView)->not->toContain('Funções e ensino');
    expect($profileComponentView)->not->toContain('Cursos em que atua como professor');
    expect($profileComponentView)->not->toContain('$user->courseAsTeacher');
    expect($profileComponentView)->not->toContain('Treinamentos como aluno');
    expect($profileComponentView)->not->toContain('$user->trainingsAsStudent');
    expect($profileVoltComponent)->not->toContain('trainingsAsTeacher.course');
    expect($profileVoltComponent)->not->toContain('trainingsAsTeacher.church');
    expect($profileVoltComponent)->not->toContain('courseAsTeacher');
    expect($profileVoltComponent)->not->toContain('trainingsAsStudent.course');
    expect($profileVoltComponent)->not->toContain('trainingsAsStudent.church');
    expect($userModel)->not->toContain('function trainingsAsTeacher');
    expect($userModel)->not->toContain('function courseAsTeacher');
    expect($userModel)->not->toContain('function trainingsAsStudent');
});
