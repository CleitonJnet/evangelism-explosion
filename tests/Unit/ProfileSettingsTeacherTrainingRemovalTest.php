<?php

it('removes profile training and teaching sections and related backend references', function (): void {
    $projectRoot = dirname(__DIR__, 2);
    $profilePageView = file_get_contents($projectRoot.'/resources/views/livewire/pages/app/settings/profile.blade.php');
    $profileComponentClass = file_get_contents($projectRoot.'/app/Livewire/Pages/App/Settings/Profile.php');
    $userModel = file_get_contents($projectRoot.'/app/Models/User.php');

    expect($profilePageView)->not->toContain('Treinamentos como professor titular');
    expect($profilePageView)->not->toContain('$user->trainingsAsTeacher');
    expect($profilePageView)->not->toContain('Funções e ensino');
    expect($profilePageView)->not->toContain('Cursos em que atua como professor');
    expect($profilePageView)->not->toContain('$user->courseAsTeacher');
    expect($profilePageView)->not->toContain('Treinamentos como aluno');
    expect($profilePageView)->not->toContain('$user->trainingsAsStudent');
    expect($profileComponentClass)->not->toContain('trainingsAsTeacher.course');
    expect($profileComponentClass)->not->toContain('trainingsAsTeacher.church');
    expect($profileComponentClass)->not->toContain('courseAsTeacher');
    expect($profileComponentClass)->not->toContain('trainingsAsStudent.course');
    expect($profileComponentClass)->not->toContain('trainingsAsStudent.church');
    expect($userModel)->not->toContain('function trainingsAsTeacher');
    expect($userModel)->not->toContain('function courseAsTeacher');
    expect($userModel)->not->toContain('function trainingsAsStudent');
});
