import type { Auth } from '@/types/auth';

export type OrganizationUnit = {
    id: string;
    parent_id: string | null;
    code: string;
    name: string;
    type: OrganizationUnitType;
    status: OrganizationUnitStatus;
    location_name: string | null;
    created_at: string | null;
    updated_at: string | null;
};

export type OrganizationUnitStatus = 'active' | 'inactive';

export type OrganizationUnitType =
    | 'foundation'
    | 'pesantren'
    | 'education_unit'
    | 'operational_unit'
    | 'dormitory'
    | 'other';

export type OrganizationUnitPage = {
    data: OrganizationUnit[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type OrganizationUnitFilters = {
    search?: string;
    filter?: {
        status?: OrganizationUnitStatus;
        type?: OrganizationUnitType;
    };
    page?: number | string;
    per_page?: number | string;
    sort?: 'created_at' | '-created_at' | 'name' | '-name' | 'code' | '-code';
};

export type OrganizationUnitPageProps = {
    auth: Auth;
    units: OrganizationUnitPage;
    filters: OrganizationUnitFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    errors?: Record<string, string>;
};
