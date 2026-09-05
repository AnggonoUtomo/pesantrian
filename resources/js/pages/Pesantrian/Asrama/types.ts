import type { Auth } from '@/types/auth';

export type ReferenceOption = {
    id: string;
    code: string;
    name: string;
};

export type DormitoryStatus = 'active' | 'inactive' | 'archived';
export type DormitoryGenderPolicy = 'male' | 'female' | 'mixed' | 'unspecified';
export type DormitoryArchiveFilter = 'active' | 'archived';

export type DormitoryRoom = {
    id: string;
    code: string;
    name: string;
    capacity: number;
    occupied_count: number;
    available_capacity: number;
    status: DormitoryStatus;
    archived_at: string | null;
    created_at: string | null;
    updated_at: string | null;
};

export type StudentRoomPlacement = {
    id: string;
    student_id: string;
    student_no: string;
    student_name: string | null;
    room_id: string;
    room_code: string | null;
    started_at: string;
    ended_at: string | null;
    status: 'active' | 'moved' | 'inactive';
    reason: string | null;
};

export type DormitorySupervisor = {
    id: string;
    employee_id: string;
    employee_name: string;
    role: string;
    dormitory_id: string | null;
    dormitory_room_id: string | null;
    room_code: string | null;
    started_at: string;
    ended_at: string | null;
    status: 'active' | 'ended';
    reason: string | null;
};

export type Dormitory = {
    id: string;
    unit: ReferenceOption;
    code: string;
    name: string;
    gender_policy: DormitoryGenderPolicy;
    description: string | null;
    room_count: number;
    capacity: number;
    occupied_count: number;
    available_capacity: number;
    status: DormitoryStatus;
    archived_at: string | null;
    created_at: string | null;
    updated_at: string | null;
    rooms?: DormitoryRoom[];
    placements?: StudentRoomPlacement[];
    supervisors?: DormitorySupervisor[];
};

export type DormitoryPage = {
    data: Dormitory[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type DormitoryFilters = {
    search?: string;
    filter?: {
        unit_id?: string;
        gender_policy?: DormitoryGenderPolicy;
        status?: DormitoryStatus;
        archived?: DormitoryArchiveFilter;
    };
    page?: number | string;
    per_page?: number | string;
    sort?:
        | 'created_at'
        | '-created_at'
        | 'code'
        | '-code'
        | 'name'
        | '-name'
        | 'gender_policy'
        | '-gender_policy'
        | 'capacity'
        | '-capacity'
        | 'occupied_count'
        | '-occupied_count'
        | 'status'
        | '-status';
};

export type DormitoryIndexPageProps = {
    auth: Auth;
    dormitories: DormitoryPage;
    filters: DormitoryFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    options: {
        units: ReferenceOption[];
    };
    canManage: boolean;
    canPlacement: boolean;
    canSupervisor: boolean;
    canArchive: boolean;
    errors?: Record<string, string>;
};

export type DormitoryShowPageProps = {
    auth: Auth;
    dormitory: Dormitory;
    options: {
        students: ReferenceOption[];
        employees: ReferenceOption[];
    };
    canManage: boolean;
    canPlacement: boolean;
    canSupervisor: boolean;
    canArchive: boolean;
};

export type DormitoryMutationPayload = {
    unit_id: string;
    code: string;
    name: string;
    gender_policy: DormitoryGenderPolicy;
    description: string | null;
    status: Exclude<DormitoryStatus, 'archived'>;
};

export type DormitoryRoomPayload = {
    code: string;
    name: string;
    capacity: string;
    status: Exclude<DormitoryStatus, 'archived'>;
};

export type StudentPlacementPayload = {
    student_id: string;
    dormitory_room_id: string;
    started_at: string;
};

export type StudentTransferPayload = {
    target_room_id: string;
    started_at: string;
    reason: string;
};

export type EndPayload = {
    ended_at: string;
    reason: string;
};

export type SupervisorPayload = {
    employee_id: string;
    dormitory_room_id: string | null;
    role: 'musyrif' | 'pembina';
    started_at: string;
};

export type ArchivePayload = {
    reason: string;
};
