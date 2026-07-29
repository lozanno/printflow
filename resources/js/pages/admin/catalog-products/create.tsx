import { Form, Head } from '@inertiajs/react';
import CatalogProductController from '@/actions/App/Http/Controllers/Admin/CatalogProductController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index } from '@/routes/admin/catalog-products';
import type { ProductTemplate } from '@/types';

export default function CatalogProductsCreate({
    availableProductTemplates,
}: {
    availableProductTemplates: ProductTemplate[];
}) {
    return (
        <>
            <Head title="Nuevo producto de catalogo" />

            <div className="max-w-xl space-y-6 p-4">
                <Heading
                    title="Nuevo producto de catalogo"
                    description="Activa una plantilla de producto para tu tienda."
                />

                {availableProductTemplates.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Ya activaste todas las plantillas disponibles. Crea una
                        plantilla de producto nueva primero.
                    </p>
                ) : (
                    <Form
                        {...CatalogProductController.store.form()}
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="product_template_id">
                                        Plantilla de producto
                                    </Label>
                                    <Select name="product_template_id" required>
                                        <SelectTrigger id="product_template_id">
                                            <SelectValue placeholder="Selecciona una plantilla" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {availableProductTemplates.map(
                                                (template) => (
                                                    <SelectItem
                                                        key={template.id}
                                                        value={template.id.toString()}
                                                    >
                                                        {template.name}
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        message={errors.product_template_id}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="name_override">
                                        Nombre para el cliente (opcional)
                                    </Label>
                                    <Input
                                        id="name_override"
                                        name="name_override"
                                        placeholder="Deja vacio para usar el nombre de la plantilla"
                                    />
                                    <InputError
                                        message={errors.name_override}
                                    />
                                </div>

                                <div className="flex flex-col gap-2">
                                    <div className="flex items-center gap-2">
                                        <Checkbox
                                            id="is_active"
                                            name="is_active"
                                            defaultChecked
                                        />
                                        <Label
                                            htmlFor="is_active"
                                            className="font-normal"
                                        >
                                            Activo (visible en el catalogo)
                                        </Label>
                                    </div>
                                    <InputError message={errors.is_active} />
                                </div>

                                <Button
                                    disabled={processing}
                                    data-test="create-catalog-product-button"
                                >
                                    Crear producto
                                </Button>
                            </>
                        )}
                    </Form>
                )}
            </div>
        </>
    );
}

CatalogProductsCreate.layout = {
    breadcrumbs: [
        { title: 'Catalogo', href: index() },
        { title: 'Nuevo producto', href: '' },
    ],
};
