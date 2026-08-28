import { Node, mergeAttributes } from '@tiptap/core';

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        iframe: {
            setIframe: (options: { src: string }) => ReturnType;
        };
    }
}

/**
 * Embeds (currently just Google Maps) rendered raw on the storefront.
 * HtmlSanitizer only lets the src through when it matches the Google Maps
 * embed URL pattern - see App\Support\HtmlSanitizer - so this stays a
 * narrow "map embed" tool rather than a general iframe inserter.
 */
export const Iframe = Node.create({
    name: 'iframe',
    group: 'block',
    atom: true,

    addAttributes() {
        return {
            src: { default: null },
        };
    },

    parseHTML() {
        return [{ tag: 'iframe' }];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'iframe',
            mergeAttributes(HTMLAttributes, {
                width: '100%',
                height: '320',
                style: 'border:0',
            }),
        ];
    },

    addCommands() {
        return {
            setIframe:
                (options) =>
                ({ commands }) =>
                    commands.insertContent({
                        type: this.name,
                        attrs: options,
                    }),
        };
    },
});
