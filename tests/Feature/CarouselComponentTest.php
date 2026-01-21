<?php

use Illuminate\Support\Facades\File;

test('carousel script forces single slide per view when only one slide exists', function () {
    $contents = File::get(resource_path('views/components/src/carousel.blade.php'));

    expect($contents)->toContain('const isSingleSlide = slidesCount <= 1;');
    expect($contents)->toContain('slidesPerView: isSingleSlide ? 1 : 3');
    expect($contents)->toContain('breakpoints: isSingleSlide');
});
