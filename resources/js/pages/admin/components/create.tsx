import { Form, Head } from '@inertiajs/react';
import ComponentController from '@/actions/App/Http/Controllers/Admin/ComponentController';
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
import { create, index } from '@/routes/admin/components';

export default function ComponentsCreate() {
    return (
        <>
            <Head title="Nuevo componente" />

            <div className="max-w-xl space-y-6 p-4">
                <Heading
                    title="Nuevo componente"
                    description="Un campo reusable de configuracion, por ejemplo 'Cantidad' o 'Acabado'."
                />

                <Form
                    {...ComponentController.store.form()}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="label">Etiqueta</Label>
                                <Input
                                    id="label"
                                    name="label"
                                    required
                                    placeholder="Cantidad"
                                />
                                <InputError message={errors.label} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="code">Codigo interno</Label>
                                <Input
                                    id="code"
                                    name="code"
                                    required
                                    placeholder="quantity"
                                    className="font-mono"
                                />
                                <InputError message={errors.code} />
                                <p className="text-xs text-muted-foreground">
                                    Identificador unico, sin espacios (usa
                                    guiones o guion bajo).
                                </p>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="input_type">
                                    Tipo de campo
                                </Label>
                                <Select name="input_type" required>
                                    <SelectTrigger id="input_type">
                                        <SelectValue placeholder="Selecciona un tipo" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="CHOICE">
                                            Opciones (elegir una)
                                        </SelectItem>
                                        <SelectItem value="NUMBER">
                                            Numero
                                        </SelectItem>
                                        <SelectItem value="DIMENSIONS">
                                            Dimensiones (ancho x alto)
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.input_type} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="create-component-button"
                                >
                                    Crear componente
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

ComponentsCreate.layout = {
    breadcrumbs: [
        { title: 'Componentes', href: index() },
        { title: 'Nuevo componente', href: create() },
    ],
};
