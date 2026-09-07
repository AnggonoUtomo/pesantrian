<?php

declare(strict_types=1);

it('mengelompokkan menu sidebar berdasarkan namespace module', function (): void {
    $registry = file_get_contents(resource_path('js/lib/navigation.ts'));
    $sidebar = file_get_contents(resource_path('js/components/app-sidebar.tsx'));
    $navMain = file_get_contents(resource_path('js/components/nav-main.tsx'));

    expect($registry)->toContain('buildNamespaceNavigation')
        ->and($registry)->toContain("title: 'System'")
        ->and($registry)->toContain("title: 'Organization'")
        ->and($registry)->toContain("title: 'Academic'")
        ->and($registry)->toContain("title: 'HumanResource'")
        ->and($registry)->toContain("title: 'Pesantrian'")
        ->and($registry)->toContain("route('organization.units.index')")
        ->and($registry)->toContain("route('academic.periods.index')")
        ->and($registry)->toContain("route('human-resource.employees.index')")
        ->and($registry)->toContain('routeOr(')
        ->and($registry)->toContain("'/pesantrian/admissions'")
        ->and($registry)->toContain("'pesantrian.admissions.index'")
        ->and($registry)->toContain("'/pesantrian/student-permits'")
        ->and($registry)->toContain("'pesantrian.student-permits.index'")
        ->and($registry)->toContain("'organization.view'")
        ->and($registry)->toContain("'organization.manage'")
        ->and($registry)->toContain("'academic_period.view'")
        ->and($registry)->toContain("'academic_period.manage'")
        ->and($registry)->toContain("'human_resource.view'")
        ->and($registry)->toContain("'human_resource.manage'")
        ->and($registry)->toContain("'penerimaan_santri.view'")
        ->and($registry)->toContain("'penerimaan_santri.manage'")
        ->and($registry)->toContain("'penerimaan_santri.decide'")
        ->and($registry)->toContain("'perizinan_santri.view'")
        ->and($registry)->toContain("'perizinan_santri.checkout'")
        ->and($registry)->toContain("'perizinan_santri.return'")
        ->and($sidebar)->toContain('buildNamespaceNavigation(auth)')
        ->and($sidebar)->toContain('<NavMain groups={mainNavGroups} />')
        ->and($navMain)->toContain('groups')
        ->and($navMain)->toContain('SidebarGroupLabel')
        ->and($navMain)->toContain('group.title');
});
