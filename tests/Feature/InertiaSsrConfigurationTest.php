<?php

test('SSR Inertia tidak aktif pada test tanpa server SSR', function () {
    expect(config('inertia.ssr.enabled'))->toBeFalse();
});

test('contoh environment menjadikan SSR sebagai fitur opt-in', function () {
    $example = file_get_contents(base_path('.env.example'));

    expect($example)
        ->toBeString()
        ->toContain('INERTIA_SSR_ENABLED=false')
        ->toContain('INERTIA_SSR_URL=http://127.0.0.1:13714');
});
