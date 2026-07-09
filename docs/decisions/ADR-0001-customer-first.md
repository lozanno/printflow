# ADR-0001

## Title

Customer Never Sees Production

---

## Status

Accepted

---

## Date

2026-07-09

---

## Context

Customers purchase products, not production processes.

Showing technical production information increases friction during the quotation process.

---

## Decision

The customer interface will never expose production concepts such as:

- Tabloid sheets
- Paper cost
- Suppliers
- Machine time
- Internal production steps

These concepts belong exclusively to Quote Engine.

---

## Consequences

Positive

- Simpler UX
- Faster quotations
- Easier product catalog

Negative

- Production complexity moves to Blueprint.