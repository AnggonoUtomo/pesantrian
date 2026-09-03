import type { ClassGroupIndexPageProps } from '../types';
import { ClassGroupFormDialog } from './ClassGroupFormDialog';
import { ClassLevelFormDialog } from './ClassLevelFormDialog';
import { CurriculumFormDialog } from './CurriculumFormDialog';

type Props = {
    options: ClassGroupIndexPageProps['options'];
    curriculumFormOpen: boolean;
    levelFormOpen: boolean;
    classGroupFormOpen: boolean;
    setCurriculumFormOpen: (open: boolean) => void;
    setLevelFormOpen: (open: boolean) => void;
    setClassGroupFormOpen: (open: boolean) => void;
};

export function KelasRombelMutationDialogs({
    options,
    curriculumFormOpen,
    levelFormOpen,
    classGroupFormOpen,
    setCurriculumFormOpen,
    setLevelFormOpen,
    setClassGroupFormOpen,
}: Props) {
    return (
        <>
            <CurriculumFormDialog
                open={curriculumFormOpen}
                onOpenChange={setCurriculumFormOpen}
            />
            <ClassLevelFormDialog
                open={levelFormOpen}
                units={options.units}
                onOpenChange={setLevelFormOpen}
            />
            <ClassGroupFormDialog
                open={classGroupFormOpen}
                options={options}
                onOpenChange={setClassGroupFormOpen}
            />
        </>
    );
}
