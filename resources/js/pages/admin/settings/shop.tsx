import { Form, Head } from '@inertiajs/react';
import ShopController from '@/actions/App/Http/Controllers/Admin/ShopController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import type { Shop } from '@/types';

export default function ShopSettings({ shop }: { shop: Shop }) {
    return (
        <>
            <Head title="Ajustes de la tienda" />

            <div className="max-w-2xl space-y-8 p-4">
                <Heading
                    title="Ajustes de la tienda"
                    description="Marca, contacto y direccion de recoleccion que se muestran en el sitio publico."
                />

                <Form
                    {...ShopController.update.form()}
                    className="space-y-8"
                >
                    {({ processing, errors }) => (
                        <>
                            <Card>
                                <CardHeader>
                                    <CardTitle>General</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">
                                            Nombre de la tienda
                                        </Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            defaultValue={shop.name}
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="currency">
                                            Moneda
                                        </Label>
                                        <Input
                                            id="currency"
                                            name="currency"
                                            required
                                            maxLength={3}
                                            className="w-24 font-mono uppercase"
                                            defaultValue={shop.currency}
                                        />
                                        <InputError
                                            message={errors.currency}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Marca</CardTitle>
                                    <CardDescription>
                                        El logotipo y color aparecen en el
                                        encabezado del sitio publico.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    <div className="grid gap-2">
                                        <Label htmlFor="logo">
                                            Logotipo
                                        </Label>
                                        {shop.logo_url && (
                                            <img
                                                src={shop.logo_url}
                                                alt=""
                                                className="h-16 w-auto rounded border object-contain p-2"
                                            />
                                        )}
                                        <Input
                                            id="logo"
                                            name="logo"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            className="max-w-72"
                                        />
                                        <InputError message={errors.logo} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="brand_color">
                                            Color de marca
                                        </Label>
                                        <div className="flex items-center gap-3">
                                            <Input
                                                id="brand_color"
                                                name="brand_color"
                                                type="color"
                                                className="h-10 w-16 p-1"
                                                defaultValue={
                                                    shop.brand_color ??
                                                    '#18181b'
                                                }
                                            />
                                            <Input
                                                aria-label="Color de marca (hex)"
                                                className="w-32 font-mono"
                                                defaultValue={
                                                    shop.brand_color ??
                                                    '#18181b'
                                                }
                                                onChange={(event) => {
                                                    const colorInput =
                                                        document.getElementById(
                                                            'brand_color',
                                                        ) as HTMLInputElement | null;

                                                    if (
                                                        colorInput &&
                                                        /^#[0-9a-fA-F]{6}$/.test(
                                                            event.target
                                                                .value,
                                                        )
                                                    ) {
                                                        colorInput.value =
                                                            event.target.value;
                                                    }
                                                }}
                                            />
                                        </div>
                                        <InputError
                                            message={errors.brand_color}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Contacto y redes</CardTitle>
                                    <CardDescription>
                                        Se muestran en el pie de pagina del
                                        sitio publico.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    <div className="grid gap-2">
                                        <Label htmlFor="contact_email">
                                            Correo de contacto
                                        </Label>
                                        <Input
                                            id="contact_email"
                                            name="contact_email"
                                            type="email"
                                            defaultValue={
                                                shop.contact_email ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.contact_email}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="facebook_url">
                                            Facebook
                                        </Label>
                                        <Input
                                            id="facebook_url"
                                            name="facebook_url"
                                            type="url"
                                            placeholder="https://facebook.com/tutienda"
                                            defaultValue={
                                                shop.facebook_url ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.facebook_url}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="instagram_url">
                                            Instagram
                                        </Label>
                                        <Input
                                            id="instagram_url"
                                            name="instagram_url"
                                            type="url"
                                            placeholder="https://instagram.com/tutienda"
                                            defaultValue={
                                                shop.instagram_url ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.instagram_url}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="whatsapp_url">
                                            WhatsApp
                                        </Label>
                                        <Input
                                            id="whatsapp_url"
                                            name="whatsapp_url"
                                            type="url"
                                            placeholder="https://wa.me/5215512345678"
                                            defaultValue={
                                                shop.whatsapp_url ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.whatsapp_url}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        Direccion de recoleccion
                                    </CardTitle>
                                    <CardDescription>
                                        Se usa cuando el cliente elige recoger
                                        su pedido en tienda.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    <div className="grid gap-2">
                                        <Label htmlFor="pickup_line1">
                                            Calle y numero
                                        </Label>
                                        <Input
                                            id="pickup_line1"
                                            name="pickup_line1"
                                            defaultValue={
                                                shop.pickup_line1 ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.pickup_line1}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="pickup_line2">
                                            Colonia / referencia (opcional)
                                        </Label>
                                        <Input
                                            id="pickup_line2"
                                            name="pickup_line2"
                                            defaultValue={
                                                shop.pickup_line2 ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.pickup_line2}
                                        />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="pickup_city">
                                                Ciudad
                                            </Label>
                                            <Input
                                                id="pickup_city"
                                                name="pickup_city"
                                                defaultValue={
                                                    shop.pickup_city ?? ''
                                                }
                                            />
                                            <InputError
                                                message={errors.pickup_city}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="pickup_state">
                                                Estado
                                            </Label>
                                            <Input
                                                id="pickup_state"
                                                name="pickup_state"
                                                defaultValue={
                                                    shop.pickup_state ?? ''
                                                }
                                            />
                                            <InputError
                                                message={errors.pickup_state}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="pickup_postal_code">
                                                Codigo postal
                                            </Label>
                                            <Input
                                                id="pickup_postal_code"
                                                name="pickup_postal_code"
                                                defaultValue={
                                                    shop.pickup_postal_code ??
                                                    ''
                                                }
                                            />
                                            <InputError
                                                message={
                                                    errors.pickup_postal_code
                                                }
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="pickup_phone">
                                                Telefono
                                            </Label>
                                            <Input
                                                id="pickup_phone"
                                                name="pickup_phone"
                                                defaultValue={
                                                    shop.pickup_phone ?? ''
                                                }
                                            />
                                            <InputError
                                                message={errors.pickup_phone}
                                            />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Button disabled={processing}>
                                Guardar cambios
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

ShopSettings.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Ajustes de la tienda', href: '' },
    ],
};
