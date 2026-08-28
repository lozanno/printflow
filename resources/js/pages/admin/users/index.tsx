import { Head, Link, router } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { create, destroy, edit, index } from '@/routes/admin/users';
import type { AdminUser } from '@/types/admin';
import type { UserRole } from '@/types/auth';

const roleLabels: Record<UserRole, string> = {
    ADMIN: 'Administrador',
    VENTAS: 'Ventas',
    ADMINISTRATIVO: 'Administrativo',
    PRODUCCION: 'Produccion',
    CALIDAD: 'Calidad',
};

function handleDelete(user: AdminUser) {
    if (
        !confirm(`Eliminar a "${user.name}"? Esta accion no se puede deshacer.`)
    ) {
        return;
    }

    router.delete(destroy(user.id).url);
}

export default function UsersIndex({ users }: { users: AdminUser[] }) {
    return (
        <>
            <Head title="Usuarios" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Usuarios"
                        description="Da acceso al panel a tu equipo y define que puede hacer cada quien."
                    />

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Nuevo usuario
                        </Link>
                    </Button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nombre</TableHead>
                            <TableHead>Correo</TableHead>
                            <TableHead>Rol</TableHead>
                            <TableHead className="text-right">
                                Acciones
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {users.map((user) => (
                            <TableRow key={user.id}>
                                <TableCell className="font-medium">
                                    {user.name}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {user.email}
                                </TableCell>
                                <TableCell>
                                    {user.role ? (
                                        <Badge variant="secondary">
                                            {roleLabels[user.role]}
                                        </Badge>
                                    ) : (
                                        <Badge variant="outline">
                                            Sin rol asignado
                                        </Badge>
                                    )}
                                </TableCell>
                                <TableCell className="text-right">
                                    <div className="flex justify-end gap-2">
                                        <Button
                                            asChild
                                            variant="outline"
                                            size="sm"
                                        >
                                            <Link href={edit(user.id)}>
                                                Editar
                                            </Link>
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            aria-label={`Eliminar ${user.name}`}
                                            onClick={() => handleDelete(user)}
                                        >
                                            <Trash2 />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [{ title: 'Usuarios', href: index() }],
};
