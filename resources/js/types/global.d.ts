import type { Auth } from '@/types/auth';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

type SharedShopBranding = {
    name: string;
    logo_url: string | null;
    brand_color: string | null;
};

type SharedFooterLink = {
    title: string;
    slug: string;
};

type SharedFooterData = {
    categories: SharedFooterLink[];
    pages: SharedFooterLink[];
    contact: {
        email: string | null;
        phone: string | null;
        facebook_url: string | null;
        instagram_url: string | null;
        whatsapp_url: string | null;
    };
};

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            shop: SharedShopBranding | null;
            footer: SharedFooterData | null;
            [key: string]: unknown;
        };
    }
}
