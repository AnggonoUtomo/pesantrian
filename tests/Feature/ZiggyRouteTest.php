<?php

use Tighten\Ziggy\Ziggy;

it('membagikan route yang dibutuhkan frontend', function () {
    $routes = (new Ziggy)->toArray()['routes'];

    expect($routes)->toHaveKeys([
        'home',
        'login',
        'dashboard',
        'profile.edit',
        'access-control.roles.store',
        'access-control.roles.destroy',
        'system.users.restore',
        'system.users.invitations.store',
        'system.users.force-delete',
        'system.audit-logs.index',
        'system.audit-logs.show',
        'organization.units.index',
        'organization.units.store',
        'organization.units.update',
        'organization.units.archive',
        'organization.units.restore',
        'pesantrian.admissions.index',
        'api.v1.pesantrian.admissions.store',
        'api.v1.pesantrian.admissions.update',
        'api.v1.pesantrian.admissions.verify',
        'api.v1.pesantrian.admissions.accept',
        'api.v1.pesantrian.admissions.reject',
        'api.v1.pesantrian.admissions.cancel',
        'academic.periods.index',
        'academic.periods.years.store',
        'academic.periods.years.update',
        'academic.periods.terms.store',
        'academic.periods.terms.update',
        'academic.periods.terms.activate',
        'academic.periods.terms.close',
        'human-resource.employees.index',
        'two-factor.qr-code',
        'passkey.confirm',
    ]);
});

it('tidak membagikan route tanpa nama atau route internal yang tidak diizinkan', function () {
    $routes = (new Ziggy)->toArray()['routes'];

    expect($routes)->not->toHaveKeys([
        'storage.local',
        'storage.local.upload',
        'up',
    ]);
});
