<?php

declare(strict_types=1);

it('mengelompokkan menu sidebar berdasarkan namespace module', function (): void {
    $registry = file_get_contents(resource_path('js/lib/navigation.ts'));
    $sidebar = file_get_contents(resource_path('js/components/app-sidebar.tsx'));
    $navMain = file_get_contents(resource_path('js/components/nav-main.tsx'));

    expect($registry)->toContain('buildNamespaceNavigation')
        ->and($registry)->toContain("title: 'System'")
        ->and($registry)->toContain("title: 'Organization'")
        ->and($registry)->toContain("route('organization.units.index')")
        ->and($registry)->toContain("'organization.view'")
        ->and($registry)->toContain("'organization.manage'")
        ->and($sidebar)->toContain('buildNamespaceNavigation(auth)')
        ->and($sidebar)->toContain('<NavMain groups={mainNavGroups} />')
        ->and($navMain)->toContain('groups')
        ->and($navMain)->toContain('SidebarGroupLabel')
        ->and($navMain)->toContain('group.title');
});
