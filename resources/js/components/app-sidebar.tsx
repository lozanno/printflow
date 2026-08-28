import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    FileText,
    FolderGit2,
    LayoutGrid,
    Package,
    Receipt,
    Settings,
    ShoppingBag,
    SlidersHorizontal,
    Tag,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
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
import type { NavItem, UserRole } from '@/types';

// Product/catalog configuration and staff management are admin-only.
// Every other assigned role only ever needs the shared Pedidos view -
// there's nothing else for them to do here yet (production stages and
// the quality gate come in later phases).
function buildNavItems(role: UserRole | null): NavMainEntry[] {
    const items: NavMainEntry[] = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    ];

    if (role === null) {
        return items;
    }

    if (role === 'ADMIN') {
        items.push(
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
            {
                title: 'Catalogo',
                href: catalogProductsIndex(),
                icon: ShoppingBag,
            },
            { title: 'Categorias', href: categoriesIndex(), icon: Tag },
            { separator: true },
        );
    }

    items.push(
        { title: 'Pedidos', href: ordersIndex(), icon: Receipt },
        { separator: true },
    );

    if (role === 'ADMIN') {
        items.push(
            { title: 'Paginas', href: pagesIndex(), icon: FileText },
            { separator: true },
            { title: 'Usuarios', href: usersIndex(), icon: Users },
            {
                title: 'Ajustes de la tienda',
                href: shopSettingsEdit(),
                icon: Settings,
            },
        );
    }

    return items;
}

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

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
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
