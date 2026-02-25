<?php

it('hides countdown when event time is not defined', function (): void {
    $view = $this->blade(
        '<x-web.events.bar-fixed-cta
            course_name="Treinamento"
            course_type="EE"
            date="2026-03-10"
            start_time=""
            end_time=""
            route="https://example.com"
        />',
    );

    $view->assertDontSee('data-countdown', false);
    $view->assertSee('Horário a definir');
});

it('shows countdown when event has valid time', function (): void {
    $view = $this->blade(
        '<x-web.events.bar-fixed-cta
            course_name="Treinamento"
            course_type="EE"
            date="2026-03-10"
            start_time="09:00:00"
            end_time="12:00:00"
            route="https://example.com"
        />',
    );

    $view->assertSee('data-countdown', false);
    $view->assertSee('Horário de Brasília');
});
