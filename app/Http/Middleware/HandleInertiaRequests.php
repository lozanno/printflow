<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // A plain first() rather than Shop::current() (which uses sole())
        // - this runs on every request, including before the shop is
        // seeded, and should degrade to null rather than throw.
        $shop = Shop::query()->first();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'shop' => $shop ? [
                'name' => $shop->name,
                'logo_url' => $shop->logo_url,
                'brand_color' => $shop->brand_color,
                'accent_color' => $shop->accent_color,
            ] : null,
            // Only PublicLayout reads this, but sharing it once here beats
            // re-querying it from every public controller action.
            'footer' => $shop ? [
                'categories' => $shop->categories()
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($category) => ['title' => $category->name, 'slug' => $category->slug]),
                'pages' => $shop->pages()
                    ->where('is_published', true)
                    ->orderBy('title')
                    ->get()
                    ->map(fn ($page) => ['title' => $page->title, 'slug' => $page->slug]),
                'contact' => [
                    'email' => $shop->contact_email,
                    'phone' => $shop->pickup_phone,
                    'facebook_url' => $shop->facebook_url,
                    'instagram_url' => $shop->instagram_url,
                    'whatsapp_url' => $shop->whatsapp_url,
                ],
            ] : null,
        ];
    }
}
