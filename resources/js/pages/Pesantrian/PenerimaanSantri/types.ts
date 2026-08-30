import type { Auth } from '@/types/auth';

export type AdmissionStatus =
    | 'draft'
    | 'submitted'
    | 'verified'
    | 'accepted'
    | 'rejected'
    | 'cancelled';

export type RegistrationFeeStatus =
    | 'not_required'
    | 'pending'
    | 'verified'
    | 'rejected';

export type CandidateGender = 'male' | 'female';

export type AdmissionChecklistItem = {
    type: string;
    status: 'not_submitted' | 'submitted' | 'verified' | 'rejected';
    notes: string;
};

export type AdmissionChecklistStatus = AdmissionChecklistItem['status'];

export type StudentAdmission = {
    id: string;
    registration_no: string;
    registration_period: string | null;
    candidate_name: string;
    candidate_gender: CandidateGender | null;
    candidate_birth_place: string | null;
    candidate_birth_date: string | null;
    previous_school: string | null;
    target_unit_id: string | null;
    guardian_name: string;
    guardian_phone: string | null;
    guardian_relation: string | null;
    registration_fee_required: boolean;
    registration_fee_amount: string | null;
    registration_fee_status: RegistrationFeeStatus;
    document_checklist: AdmissionChecklistItem[] | null;
    status: AdmissionStatus;
    registered_at: string | null;
    decided_at: string | null;
    decided_by: string | null;
    notes: string | null;
    created_at: string | null;
    updated_at: string | null;
};

export type AdmissionMutationPayload = {
    registration_period: string | null;
    candidate_name: string;
    candidate_gender: CandidateGender | null;
    candidate_birth_place: string | null;
    candidate_birth_date: string | null;
    previous_school: string | null;
    target_unit_id: string | null;
    guardian_name: string;
    guardian_phone: string | null;
    guardian_relation: string | null;
    registration_fee_required: boolean;
    registration_fee_amount: string | null;
    registration_fee_status: RegistrationFeeStatus;
    document_checklist: AdmissionChecklistItem[];
    status: 'draft' | 'submitted' | 'verified';
    notes: string | null;
};

export type AdmissionPage = {
    data: StudentAdmission[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type AdmissionTargetUnitOption = {
    id: string;
    code: string;
    name: string;
};

export type AdmissionFilters = {
    search?: string;
    filter?: {
        status?: AdmissionStatus;
        target_unit_id?: string;
        registration_fee_status?: RegistrationFeeStatus;
    };
    page?: number | string;
    per_page?: number | string;
    sort?:
        | 'created_at'
        | '-created_at'
        | 'registration_no'
        | '-registration_no'
        | 'candidate_name'
        | '-candidate_name'
        | 'registered_at'
        | '-registered_at';
};

export type AdmissionPageProps = {
    auth: Auth;
    admissions: AdmissionPage;
    filters: AdmissionFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    targetUnitOptions: AdmissionTargetUnitOption[];
    errors?: Record<string, string>;
};
