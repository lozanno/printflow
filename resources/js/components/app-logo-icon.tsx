import type { SVGAttributes } from 'react';

/**
 * PrintFlow's mark: three flow bars, the same shape used in the shop's
 * uploaded storefront logo - drawn here as plain currentColor rects so it
 * can be recolored per usage (sidebar, auth pages, light/dark) like the
 * icon it replaced.
 */
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect x="8" y="10" width="16" height="5" rx="2.5" />
            <rect x="8" y="17.5" width="24" height="5" rx="2.5" />
            <rect x="8" y="25" width="12" height="5" rx="2.5" />
        </svg>
    );
}
