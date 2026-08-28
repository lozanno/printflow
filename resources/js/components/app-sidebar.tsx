import { Link, usePage } from '@inertiajs/react';
import {
    Banknote,
    BarChart3,
    Contact,
    FileText,
    Package,
    Receipt,
    Settings,
    ShoppingBag,
    SlidersHorizontal,
    Tag,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import type { NavMainEntry } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as catalogProductsIndex } from '@/routes/admin/catalog-products';
import { index as categoriesIndex } from '@/routes/admin/categories';
import { index as componentsIndex } from '@/routes/admin/components';
import { index as ordersIndex } from '@/routes/admin/orders';
import { index as pagesIndex } from '@/routes/admin/pages';
import { index as productTemplatesIndex } from '@/routes/admin/product-templates';
import { edit as shopSettingsEdit } from '@/routes/admin/settings';
import { index as usersIndex } from '@/routes/admin/users';
import type { UserRole } from '@/types';

// Product/catalog configuration and staff management are admin-only.
// Every other assigned role only ever needs the shared Clientes/Pedidos
// block - there's nothing else for them to do here yet (production
// stages and the quality gate come in later phases).
function buildNavItems(role: UserRole | null): NavMainEntry[] {
    if (role === null) {
        return [];
    }

    const items: NavMainEntry[] = [
        { text: true, title: 'Clientes', icon: Contact },
        { title: 'Pedidos', href: ordersIndex(), icon: Receipt },
        { text: true, title: 'Facturacion', icon: Banknote },
        { text: true, title: 'Estadisticas', icon: BarChart3 },
        { separator: true },
    ];

    if (role === 'ADMIN') {
        items.push(
            {
                title: 'Catalogo de ventas',
                href: catalogProductsIndex(),
                icon: ShoppingBag,
            },
            {
                title: 'Componentes',
                href: componentsIndex(),
                icon: SlidersHorizontal,
            },
            {
                title: 'Plantillas de producto',
                href: productTemplatesIndex(),
                icon: Package,
            },
            { title: 'Categorias', href: categoriesIndex(), icon: Tag },
            { separator: true },
            { title: 'Paginas publicas', href: pagesIndex(), icon: FileText },
            { separator: true },
            { title: 'Usuarios', href: usersIndex(), icon: Users },
            {
                title: 'Ajustes globales',
                href: shopSettingsEdit(),
                icon: Settings,
            },
        );
    }

    return items;
}

export function AppSidebar() {
    const { auth } = usePage().props;
    const mainNavItems = buildNavItems(auth.user.role);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
