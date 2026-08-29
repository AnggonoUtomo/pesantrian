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
        ->and($registry)->toContain("route('organization.units.index')")
        ->and($registry)->toContain("route('academic.periods.index')")
        ->and($registry)->toContain("'organization.view'")
        ->and($registry)->toContain("'organization.manage'")
        ->and($registry)->toContain("'academic_period.view'")
        ->and($registry)->toContain("'academic_period.manage'")
        ->and($sidebar)->toContain('buildNamespaceNavigation(auth)')
        ->and($sidebar)->toContain('<NavMain groups={mainNavGroups} />')
        ->and($navMain)->toContain('groups')
        ->and($navMain)->toContain('SidebarGroupLabel')
        ->and($navMain)->toContain('group.title');
});
