import { Form, Head } from '@inertiajs/react';
import PageController from '@/actions/App/Http/Controllers/Admin/PageController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { RichTextEditor } from '@/components/rich-text-editor';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { create, index } from '@/routes/admin/pages';

export default function PagesCreate() {
    return (
        <>
            <Head title="Nueva pagina" />

            <div className="max-w-3xl space-y-6 p-4">
                <Heading
                    title="Nueva pagina"
                    description="Contenido estatico, por ejemplo 'Quienes somos' o 'Terminos y condiciones'."
                />

                <Form
                    {...PageController.store.form()}
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
                                    placeholder="Quienes somos"
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="slug">URL amigable</Label>
                                <Input
                                    id="slug"
                                    name="slug"
                                    required
                                    placeholder="quienes-somos"
                                    className="font-mono"
                                />
                                <InputError message={errors.slug} />
                                <p className="text-xs text-muted-foreground">
                                    Como se vera en la direccion:
                                    localhost/quienes-somos
                                </p>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="content">Contenido</Label>
                                <RichTextEditor name="content" />
                                <InputError message={errors.content} />
                            </div>

                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="is_published"
                                    name="is_published"
                                />
                                <Label
                                    htmlFor="is_published"
                                    className="font-normal"
                                >
                                    Publicada (visible en el sitio)
                                </Label>
                                <InputError message={errors.is_published} />
                            </div>

                            <Button
                                disabled={processing}
                                data-test="create-page-button"
                            >
                                Crear pagina
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

PagesCreate.layout = {
    breadcrumbs: [
        { title: 'Paginas', href: index() },
        { title: 'Nueva pagina', href: create() },
    ],
};
