import { Form, Head } from '@inertiajs/react';
import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { create, index } from '@/routes/admin/categories';

export default function CategoriesCreate() {
    return (
        <>
            <Head title="Nueva categoria" />

            <div className="max-w-xl space-y-6 p-4">
                <Heading
                    title="Nueva categoria"
                    description="Agrupa productos del catalogo, por ejemplo 'Impresos promocionales'."
                />

                <Form
                    {...CategoryController.store.form()}
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
                                    placeholder="Impresos promocionales"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="slug">URL amigable</Label>
                                <Input
                                    id="slug"
                                    name="slug"
                                    required
                                    placeholder="impresos-promocionales"
                                    className="font-mono"
                                />
                                <InputError message={errors.slug} />
                                <p className="text-xs text-muted-foreground">
                                    Como se vera en la direccion:
                                    localhost/impresos-promocionales
                                </p>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="create-category-button"
                                >
                                    Crear categoria
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

CategoriesCreate.layout = {
    breadcrumbs: [
        { title: 'Categorias', href: index() },
        { title: 'Nueva categoria', href: create() },
    ],
};
