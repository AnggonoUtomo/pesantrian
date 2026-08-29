import type { Employee } from '../types';
import { employeeTypeLabels } from './employeeDisplay';
import { EmployeeStatusBadge } from './EmployeeStatusBadge';

type Props = {
    employees: Employee[];
};

export function EmployeeList({ employees }: Props) {
    return (
        <div className="overflow-hidden rounded-xl border">
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted/50 text-xs text-foreground/65 uppercase">
                        <tr>
                            <th scope="col" className="px-4 py-3">
                                Employee
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Tipe kerja
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Unit utama
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Status
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Bergabung
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {employees.map((employee) => (
                            <tr key={employee.id} className="bg-background">
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {employee.name}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {employee.employee_no}
                                        {employee.preferred_name
                                            ? ` · ${employee.preferred_name}`
                                            : ''}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <div>
                                        {
                                            employeeTypeLabels[
                                                employee.employment_type
                                            ]
                                        }
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {employee.position ?? 'Jabatan belum diisi'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    <PrimaryUnit employee={employee} />
                                </td>
                                <td className="px-4 py-3">
                                    <EmployeeStatusBadge
                                        status={employee.status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {employee.joined_on ?? 'Belum diisi'}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="divide-y md:hidden">
                {employees.map((employee) => (
                    <article
                        key={employee.id}
                        className="space-y-3 bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">
                                    {employee.name}
                                </h3>
                                <p className="text-xs text-foreground/60">
                                    {employee.employee_no} ·{' '}
                                    {
                                        employeeTypeLabels[
                                            employee.employment_type
                                        ]
                                    }
                                </p>
                            </div>
                            <EmployeeStatusBadge status={employee.status} />
                        </div>
                        <div className="grid gap-2 text-sm text-foreground/70">
                            <p>{employee.position ?? 'Jabatan belum diisi'}</p>
                            <p>
                                Unit: <PrimaryUnit employee={employee} />
                            </p>
                            <p>Bergabung: {employee.joined_on ?? 'Belum diisi'}</p>
                        </div>
                    </article>
                ))}
            </div>
        </div>
    );
}

function PrimaryUnit({ employee }: { employee: Employee }) {
    if (!employee.primary_unit) {
        return <>Belum ditempatkan</>;
    }

    return (
        <>
            {employee.primary_unit.name} ({employee.primary_unit.code})
        </>
    );
}
