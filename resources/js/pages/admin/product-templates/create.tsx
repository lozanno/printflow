import { Form, Head } from '@inertiajs/react';
import ProductTemplateController from '@/actions/App/Http/Controllers/Admin/ProductTemplateController';
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
import { create, index } from '@/routes/admin/product-templates';

export default function ProductTemplatesCreate() {
    return (
        <>
            <Head title="Nueva plantilla de producto" />

            <div className="max-w-xl space-y-6 p-4">
                <Heading
                    title="Nueva plantilla de producto"
                    description="Un tipo de producto reusable, por ejemplo 'Tarjetas de presentacion'."
                />

                <Form
                    {...ProductTemplateController.store.form()}
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
                                    placeholder="Tarjetas de presentacion"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="code">Codigo interno</Label>
                                <Input
                                    id="code"
                                    name="code"
                                    required
                                    placeholder="business_cards"
                                    className="font-mono"
                                />
                                <InputError message={errors.code} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="pricing_strategy">
                                    Estrategia de precio
                                </Label>
                                <Select name="pricing_strategy" required>
                                    <SelectTrigger id="pricing_strategy">
                                        <SelectValue placeholder="Selecciona una estrategia" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="PER_UNIT_TIERED">
                                            Por pieza (escalonado por cantidad)
                                        </SelectItem>
                                        <SelectItem value="PER_AREA">
                                            Por area
                                        </SelectItem>
                                        <SelectItem value="PER_AREA_WITH_SETUP">
                                            Por area + costo fijo
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.pricing_strategy} />
                                <p className="text-xs text-muted-foreground">
                                    Define como se calcula el precio de este
                                    tipo de producto. Evita cambiarla despues de
                                    tener productos activos.
                                </p>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="create-product-template-button"
                                >
                                    Crear plantilla
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

ProductTemplatesCreate.layout = {
    breadcrumbs: [
        { title: 'Plantillas de producto', href: index() },
        { title: 'Nueva plantilla', href: create() },
    ],
};
