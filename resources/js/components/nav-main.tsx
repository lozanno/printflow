import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

export type NavMainEntry =
    | NavItem
    | { separator: true }
    | { text: true; title: string; icon?: LucideIcon };

export function NavMain({ items = [] }: { items: NavMainEntry[] }) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>Platform</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item, index) => {
                    if ('separator' in item) {
                        return (
                            <SidebarSeparator
                                key={`separator-${index}`}
                                className="my-2"
                            />
                        );
                    }

                    if ('text' in item) {
                        return (
                            <SidebarMenuItem key={item.title}>
                                <SidebarMenuButton disabled>
                                    {item.icon && <item.icon />}
                                    <span>{item.title}</span>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        );
                    }

                    return (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={isCurrentUrl(item.href)}
                                tooltip={{ children: item.title }}
                            >
                                <Link href={item.href} prefetch>
                                    {item.icon && <item.icon />}
                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    );
                })}
            </SidebarMenu>
        </SidebarGroup>
    );
}
