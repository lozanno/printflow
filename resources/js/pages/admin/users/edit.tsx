import { Form, Head, Link } from '@inertiajs/react';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index } from '@/routes/admin/users';
import type { AdminUser } from '@/types/admin';

export default function UsersEdit({ user }: { user: AdminUser }) {
    return (
        <>
            <Head title={`Editar ${user.name}`} />

            <div className="max-w-xl space-y-8 p-4">
                <Heading
                    title={user.name}
                    description="Edita los datos y el rol de este usuario."
                />

                <Form
                    {...UserController.update.form(user.id)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nombre</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    defaultValue={user.name}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Correo</Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    defaultValue={user.email}
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="role">Rol</Label>
                                <Select
                                    name="role"
                                    required
                                    defaultValue={user.role ?? undefined}
                                >
                                    <SelectTrigger id="role">
                                        <SelectValue placeholder="Selecciona un rol" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="ADMIN">
                                            Administrador
                                        </SelectItem>
                                        <SelectItem value="VENTAS">
                                            Ventas
                                        </SelectItem>
                                        <SelectItem value="ADMINISTRATIVO">
                                            Administrativo
                                        </SelectItem>
                                        <SelectItem value="PRODUCCION">
                                            Produccion
                                        </SelectItem>
                                        <SelectItem value="CALIDAD">
                                            Calidad
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.role} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    Nueva contrasena
                                </Label>
                                <Input
                                    id="password"
                                    name="password"
                                    type="password"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Dejala en blanco para mantener la contrasena
                                    actual.
                                </p>
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirmar nueva contrasena
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button disabled={processing}>
                                Guardar cambios
                            </Button>
                        </>
                    )}
                </Form>

                <Button variant="outline" asChild>
                    <Link href={index()}>Volver a usuarios</Link>
                </Button>
            </div>
        </>
    );
}

UsersEdit.layout = {
    breadcrumbs: [
        { title: 'Usuarios', href: index() },
        { title: 'Editar', href: '' },
    ],
};
