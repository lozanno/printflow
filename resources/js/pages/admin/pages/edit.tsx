import { Form, Head, Link } from '@inertiajs/react';
import PageController from '@/actions/App/Http/Controllers/Admin/PageController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { RichTextEditor } from '@/components/rich-text-editor';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/admin/pages';
import type { Page } from '@/types';

export default function PagesEdit({ page }: { page: Page }) {
    return (
        <>
            <Head title={`Editar ${page.title}`} />

            <div className="max-w-3xl space-y-8 p-4">
                <Heading
                    title={page.title}
                    description="Edita el titulo, la URL y el contenido de la pagina."
                />

                <Form
                    {...PageController.update.form(page.id)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="title">Titulo</Label>
                                <Input
                                    id="title"
                                    name="title"
                                    required
                                    defaultValue={page.title}
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="slug">URL amigable</Label>
                                <Input
                                    id="slug"
                                    name="slug"
                                    required
                                    defaultValue={page.slug}
                                    className="font-mono"
                                />
                                <InputError message={errors.slug} />
                                <p className="text-xs text-muted-foreground">
                                    Como se vera en la direccion: localhost/
                                    {page.slug}
                                </p>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="content">Contenido</Label>
                                <RichTextEditor
                                    name="content"
                                    defaultValue={page.content}
                                />
                                <InputError message={errors.content} />
                            </div>

                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="is_published"
                                    name="is_published"
                                    defaultChecked={page.is_published}
                                />
                                <Label
                                    htmlFor="is_published"
                                    className="font-normal"
                                >
                                    Publicada (visible en el sitio)
                                </Label>
                                <InputError message={errors.is_published} />
                            </div>

                            <Button disabled={processing}>
                                Guardar cambios
                            </Button>
                        </>
                    )}
                </Form>

                <Button variant="outline" asChild>
                    <Link href={index()}>Volver a paginas</Link>
                </Button>
            </div>
        </>
    );
}

PagesEdit.layout = {
    breadcrumbs: [
        { title: 'Paginas', href: index() },
        { title: 'Editar', href: '' },
    ],
};
