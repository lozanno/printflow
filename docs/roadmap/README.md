# Roadmap

## Current status (2026-08-28)

**Working end to end, for one shop:**

- Catalog, categories, static pages (with Google Maps embeds).
- Product Configurator + Quote Engine: per-unit tiered, per-area, and
  per-area-with-setup pricing, with option-level price modifiers.
- Checkout: contact info, pickup/ship, a *simulated* payment (no real
  gateway wired up) that marks the order `PAID` immediately.
- Admin panel: components, product templates, catalog products
  (pricing tiers, option modifiers, FAQs, reviews), categories, pages,
  shop branding/settings, role-based access, an order list and a
  per-order detail page with a production pipeline, quality gate, and
  bitácora (see Phases 1-3.1 below).
- 183 Pest tests (177 passing, 6 skipped).

**Known gaps, not yet started:**

- Single-tenant only - `Shop::current()` assumes exactly one shop row
  exists. This is the biggest blocker to reselling PrintFlow to other
  print shops, and is called out as a deliberate "seam for later" in
  `app/Models/Shop.php`.
- No customer accounts/login (the README lists "Customer Portal" as a
  core module; it doesn't exist yet - `Customer` is just a name/email
  record captured at checkout).
- No cart (checkout is one product at a time).
- No email/notifications anywhere in the app.
- No customer artwork upload.
- No real payment gateway.
- No discount/coupon mechanism.

## Next: the operations platform

See [docs/business/print-shop-operations.md](../business/print-shop-operations.md)
for the request in full and the reasoning behind it. Summary: today the
app stops caring about an order the moment it's paid. This phase makes
PrintFlow own the order through production, quality, and delivery too,
with each role (Ventas, Administrativo, Producción, Calidad) seeing only
what its job needs.

Proposed sequencing - each phase is usable on its own, and each one
depends on the phase(s) before it:

### Phase 1 - Roles & permissions (foundation) - DONE (2026-08-28)

**Decided: building this for real** - multiple people with distinct
roles will operate PrintFlow, not one person wearing several hats. A
lightweight "any admin can do anything" stopgap isn't worth building
first.

Shipped: a `UserRole` enum (Admin/Ventas/Administrativo/Producción/
Calidad) on `users.role`; a `role` route middleware gating every
`/admin` route (no args = any assigned role, e.g. `role:ADMIN` = that
role only); product/catalog configuration, shop settings, and staff
management stayed Admin-only, while every role can see Pedidos (the one
thing every job touches today); an Admin-only Usuarios screen to create
staff logins and assign roles; the sidebar nav is now role-aware
(non-admins only ever see Dashboard + Pedidos, since Phases 2-3 haven't
shipped yet); every user that existed before this shipped was backfilled
to Admin so nobody lost access; a freshly self-registered user gets no
role and is blocked from `/admin` entirely until an admin assigns one -
closing the gap where public registration used to hand out full access.
Guarded against locking everyone out: an Admin can't delete their own
account, and can't demote or delete the last remaining Admin.

**Follow-up hardening (2026-08-28):** disabled public self-registration
entirely (`Features::registration()` removed from `config/fortify.php`,
`/register` now 404s, the "Sign up" link is gone from the login page).
Staff accounts are created exclusively from `/admin/usuarios` by an
Admin now - there's no other way in.

### Phase 2 - Production pipeline - DONE (2026-08-28)

Shipped: a `ProductionStage` enum (Pending/InProduction/QualityCheck/
Ready/Delivered) on `orders.production_stage` - a separate axis from
`OrderStatus`, since payment and production are different questions. A
new `order_stage_changes` table records every transition (from, to, who,
when); `Order::advanceProductionStage()` writes both the new value and
its audit row in one call, and every order gets its first stage recorded
the moment checkout marks it `PAID`. Orders placed before this shipped
were backfilled to `PENDING` (no history for those, since nothing
recorded the transition). Only Admin and Producción can move a stage
(`PATCH admin/orders/{order}/production-stage`, gated by `role:ADMIN,
PRODUCCION` - blocked in the route, not just hidden in the UI); every
other role sees the current stage as a read-only badge instead of the
editable dropdown. The Pedidos list shows "who changed it, when" under
the stage control. Transitions are unrestricted for now (any stage to
any stage) - the one transition Phase 3 will actually gate is Calidad's
sign-off before `READY`.

### Phase 3 - Quality gate - DONE (2026-08-28)

Shipped: `orders.quality_checked_at` / `quality_checked_by_user_id` -
Calidad's sign-off, who gave it, and when. The gate itself lives in
`Order::advanceProductionStage()`, not the UI: moving to `READY` or
`DELIVERED` while `quality_checked_at` is null throws
`QualityCheckRequiredException`, which the controller turns into a
validation error - the same shape as `PriceCalculator` refusing to quote
a bad selection, so it can't be bypassed by a direct request any more
than a price can. `PATCH admin/orders/{order}/quality-check` is gated
`role:ADMIN,CALIDAD` only; Ventas, Administrativo, and Producción can't
reach it. Un-checking is allowed (passing `false` clears both fields) -
correcting a mistake here works the same as moving a stage backward.
Verified live: blocked a real order from reaching "Listo para entrega"
with the box unchecked (clear toast, dropdown stayed put), then checked
the box and the same transition went through.

### Phase 3.1 - Order detail page & bitácora - DONE (2026-08-28)

Not one of the original phases - added after Phase 3 shipped, once it
became clear the Pedidos list alone couldn't hold everything a shop
floor needs to track per order. Shipped: a dedicated `admin/orders/{id}`
page (any assigned role can view it) with a visual 5-step pipeline
(reusing the `ProductionStage` order), the same stage-select/quality-
checkbox controls as the list (role-gated identically), and a unified
bitácora - one chronological feed merging stage-change history, the
quality-check sign-off, and free-text notes. Notes are a new
`order_notes` table (`OrderNote` model, `body`/`user_id`/timestamps);
any assigned role can add one, not just Producción/Calidad - the same
reasoning as Pedidos itself being visible to every role. The merge
happens server-side (`Admin\OrderController::buildTimeline()`), so the
frontend just renders a typed `OrderTimelineEvent[]` discriminated union
newest-first, instead of stitching three data sources together in React.
Verified live: pipeline renders with the right stage highlighted, full
stage-change history for a well-traveled order displays correctly, and
a submitted note shows up at the top of the bitácora immediately.

### Phase 3.2 - Ventas-facing scheduling & color-coded status - DONE (2026-08-28)

Reworked the Pedidos list and detail page around what Ventas actually
needs day to day. Shipped: `orders.estimated_delivery_date` and
`orders.is_urgent`, editable only by Admin/Ventas from either the list
(inline) or the detail page's Resumen card; `orders.needs_sales_attention`,
a manual flag Ventas sets when an order can't move into production yet
(no automatic rule - only a human following up with a client knows
that). The Pedidos list dropped delivery type, product, total, and the
quality column - it's now ID, cliente, fecha de pedido, fecha de
entrega, and a color-coded Producción tag (view-only there; editing
still only happens on the detail page). Colors are semantic everywhere
they appear: amarillo = Pendiente, azul = En producción, morado =
Control de calidad, naranja = Listo para entrega, verde = Entregado,
and blanco overrides all of those while `needs_sales_attention` is on.
The detail page's "Detalles" card was renamed "Resumen" and dropped the
shipping address (kept in the payload, just not shown up top). Customer
management from the admin panel (for walk-in/in-house orders) was
explicitly scoped out of this round - noted for a future phase.

### Phase 4 - Printable ticket

A print-friendly view per order - a job ticket/packing slip. This is
comparatively cheap (an HTML view with print CSS is enough to start;
PDF export via a package like `spatie/laravel-pdf` or `barryvdh/laravel-dompdf`
can come later if a physical printer workflow needs it) and delivers
value to Producción immediately, so it doesn't have to wait for every
later phase.

### Phase 5 - File management

Let a customer attach artwork to an order (at checkout or after), and
let Producción/Calidad view/download it. Needs a decision on storage
(local `public` disk is fine for one shop; revisit if/when
multi-tenant, for per-shop quotas).

### Phase 6 - Invoicing

**Decided (2026-08-28): non-fiscal receipt only, for now.** Fiscal
invoicing (CFDI) is explicitly out of scope until this is revisited -
it's a SAT compliance integration (RFC data, XML generation, timbrado
through a PAC such as Facturama, Facturapi, or SW Sapien, ongoing
certificate/cost management), not a feature to fold into this roadmap.
When the shop is ready for it, it should be scoped as its own
initiative rather than a line item here.

For now: a PDF/print view of the order, generated in-house, no external
dependency - Administrativo can produce this for any paid order.
