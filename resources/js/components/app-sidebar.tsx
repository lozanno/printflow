import { Link } from '@inertiajs/react';
import {
    BookOpen,
    FileText,
    FolderGit2,
    LayoutGrid,
    Package,
    Settings,
    ShoppingBag,
    SlidersHorizontal,
    Tag,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
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
import { index as pagesIndex } from '@/routes/admin/pages';
import { index as productTemplatesIndex } from '@/routes/admin/product-templates';
import { edit as shopSettingsEdit } from '@/routes/admin/settings';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
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
    {
        title: 'Catalogo',
        href: catalogProductsIndex(),
        icon: ShoppingBag,
    },
    {
        title: 'Categorias',
        href: categoriesIndex(),
        icon: Tag,
    },
    {
        title: 'Paginas',
        href: pagesIndex(),
        icon: FileText,
    },
    {
        title: 'Ajustes de la tienda',
        href: shopSettingsEdit(),
        icon: Settings,
    },
];

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
