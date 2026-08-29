import type { Dispatch, SetStateAction } from 'react';
import type { AcademicTerm, AcademicYear } from '../types';
import { AcademicTermFormDialog } from './AcademicTermFormDialog';
import { AcademicTermLifecycleDialog } from './AcademicTermLifecycleDialog';
import { AcademicYearFormDialog } from './AcademicYearFormDialog';

type AcademicPeriodMutationDialogsProps = {
    years: AcademicYear[];
    yearFormOpen: boolean;
    termFormOpen: boolean;
    editingYear: AcademicYear | null;
    editingTerm: AcademicTerm | null;
    activatingTerm: AcademicTerm | null;
    closingTerm: AcademicTerm | null;
    setYearFormOpen: Dispatch<SetStateAction<boolean>>;
    setTermFormOpen: Dispatch<SetStateAction<boolean>>;
    setActivatingTerm: Dispatch<SetStateAction<AcademicTerm | null>>;
    setClosingTerm: Dispatch<SetStateAction<AcademicTerm | null>>;
};

export function AcademicPeriodMutationDialogs({
    years,
    yearFormOpen,
    termFormOpen,
    editingYear,
    editingTerm,
    activatingTerm,
    closingTerm,
    setYearFormOpen,
    setTermFormOpen,
    setActivatingTerm,
    setClosingTerm,
}: AcademicPeriodMutationDialogsProps) {
    return (
        <>
            <AcademicYearFormDialog
                open={yearFormOpen}
                year={editingYear}
                onOpenChange={setYearFormOpen}
            />
            <AcademicTermFormDialog
                open={termFormOpen}
                term={editingTerm}
                years={years}
                onOpenChange={setTermFormOpen}
            />
            <AcademicTermLifecycleDialog
                action="activate"
                open={activatingTerm !== null}
                term={activatingTerm}
                onOpenChange={(open) => !open && setActivatingTerm(null)}
            />
            <AcademicTermLifecycleDialog
                action="close"
                open={closingTerm !== null}
                term={closingTerm}
                onOpenChange={(open) => !open && setClosingTerm(null)}
            />
        </>
    );
}
