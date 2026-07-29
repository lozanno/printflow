import { Form, Head, Link } from '@inertiajs/react';
import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/admin/categories';
import type { Category } from '@/types';

export default function CategoriesEdit({ category }: { category: Category }) {
    return (
        <>
            <Head title={`Editar ${category.name}`} />

            <div className="max-w-xl space-y-8 p-4">
                <Heading
                    title={category.name}
                    description="Edita el nombre y la URL amigable de la categoria."
                />

                <Form
                    {...CategoryController.update.form(category.id)}
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
                                    defaultValue={category.name}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="slug">URL amigable</Label>
                                <Input
                                    id="slug"
                                    name="slug"
                                    required
                                    defaultValue={category.slug}
                                    className="font-mono"
                                />
                                <InputError message={errors.slug} />
                                <p className="text-xs text-muted-foreground">
                                    Como se vera en la direccion: localhost/
                                    {category.slug}
                                </p>
                            </div>

                            <Button disabled={processing}>
                                Guardar cambios
                            </Button>
                        </>
                    )}
                </Form>

                <Button variant="outline" asChild>
                    <Link href={index()}>Volver a categorias</Link>
                </Button>
            </div>
        </>
    );
}

CategoriesEdit.layout = {
    breadcrumbs: [
        { title: 'Categorias', href: index() },
        { title: 'Editar', href: '' },
    ],
};
