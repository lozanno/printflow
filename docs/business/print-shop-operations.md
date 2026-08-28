# Print Shop Operations

## Requested by

Diego Lozano, 2026-08-28.

## The ask, in the owner's words

> Define productos y reglas de cotización. Recibe pedidos estructurados.
> Administra archivos y pagos. Envía trabajos a producción. Controla cada
> etapa. Supervisa calidad. Gestiona entrega.
>
> Una sola plataforma conecta todo el flujo comercial y operativo.

## What this means for PrintFlow

Today PrintFlow covers the first two sentences only: a customer configures
a product, gets a quote, and places an order (Catalog + Product
Configurator + Quote Engine, per the README's core modules). Everything
after "recibe pedidos" - files, payment reconciliation, production,
quality, delivery - currently has no owner inside the app. An order is
created, gets marked `PAID`, and then the system has nothing more to say
about it. `OrderStatus::Completed` and `::Cancelled` exist in the enum
but nothing in the codebase ever sets them.

This is not a rejection of [Vision](../architecture/Vision.md) or
[ADR-0001](../decisions/ADR-0001-customer-first.md) ("customer never sees
production") - that principle is about the *customer-facing* quoting
experience staying simple. What's being asked for here is the mirror
image: giving the *shop's own staff* a system to run the production
complexity that's intentionally hidden from the customer. Both can be
true at once.

## Roles implied

The request names four working roles, which don't exist as a concept in
the app yet (there's a single undifferentiated `User` model used for the
whole admin panel today):

- **Ventas** (sales) - takes orders, follows up with customers.
- **Administrativo** (admin/back-office) - reconciles payments, issues
  invoices (fiscal or non-fiscal).
- **Producción** (workstation operators / floor managers) - advance an
  order through production stages.
- **Calidad** (QA) - a checkpoint that must pass before an order ships.

## Concrete capabilities requested

1. Role-gated access to the admin panel and to the order list, scoped to
   what each role needs to see/do.
2. Operators/managers can set a production status on an order.
3. Administrative users can generate an invoice - fiscal or non-fiscal -
   for an order.
4. QA has a checklist/checkbox gate that must be completed before an
   order can be marked ready to ship.
5. A printable ticket can be produced for an order (e.g. a job ticket /
   packing slip that travels with the physical product through the shop
   floor, or hands to the customer at pickup).

See [docs/roadmap](../roadmap/README.md) for how this is sequenced.
