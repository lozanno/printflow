<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\File;

/**
 * Shared by any model that stores admin-authored HTML rendered raw on the
 * storefront (Page::content, CatalogProduct::details_content) - sanitized on
 * the way in so it stays safe even if an admin account is ever compromised,
 * or the app grows into multi-tenant with less-trusted per-shop staff.
 */
class HtmlSanitizer
{
    public static function sanitize(?string $html): ?string
    {
        return $html !== null ? self::purifier()->purify($html) : null;
    }

    private static function purifier(): HTMLPurifier
    {
        static $purifier = null;

        if ($purifier === null) {
            $cachePath = storage_path('framework/cache/htmlpurifier');
            File::ensureDirectoryExists($cachePath);

            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'p,br,strong,em,h2,h3,ul,ol,li,a[href],img[src|alt],blockquote,code,pre,hr,details[open],summary,div');
            $config->set('AutoFormat.RemoveEmpty', true);
            $config->set('Cache.SerializerPath', $cachePath);

            // HTMLPurifier's bundled HTML definition predates HTML5, so
            // <details>/<summary> (used for collapsible sections in
            // CatalogProduct::details_content) must be registered manually
            // via the raw-definition API rather than just HTML.Allowed.
            // The Details extension's content div loses its
            // data-type="detailsContent" attribute here (HTMLPurifier won't
            // preserve custom data-* attributes even via addAttribute() on
            // a pre-existing core element) - RichTextEditor's DetailsContent
            // config is extended to also parse a bare <div> back on reload,
            // so this is a non-issue rather than something to fight here.
            // A DefinitionID/Rev is required so HTMLPurifier caches this
            // customized definition separately from the stock one.
            $config->set('HTML.DefinitionID', 'printflow-html-with-details');
            $config->set('HTML.DefinitionRev', 3);

            $definition = $config->maybeGetRawHTMLDefinition();

            if ($definition !== null) {
                $definition->addElement('details', 'Block', 'Flow', 'Common', ['open' => 'Bool#open']);
                $definition->addElement('summary', 'Inline', 'Inline', 'Common', []);
            }

            $purifier = new HTMLPurifier($config);
        }

        return $purifier;
    }
}
