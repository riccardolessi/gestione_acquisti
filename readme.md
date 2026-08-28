# Purchases dashboard — interface redesign

Redesign of the [Inventario](../../tree/main) interface: same database, new UI. Turns the browsing tables into an analytics dashboard, with monthly spending trends and a per-product view showing average price, quantity, and purchase history.

> **Separate branch, not merged into `main`.**
> This branch contains only the presentation layer: it reads the same database as `main` but **does not include XML invoice import**, which stays on the main branch. The refactor was never completed and the two branches were never unified — the limitations are documented below.

---

## What it contains

**Dashboard** — month-by-month spending trend chart, with a year selector populated from the years actually present in the database and the annual total highlighted.

**Product search** — search by description or item code, with the associated supplier.

**Product page** — total quantity purchased, weighted average price, total spend, and full purchase history, filterable by date range.

---

## Relationship to the main branch

| | `main` | this branch |
|---|---|---|
| XML invoice import | yes | no |
| Data browsing | Bootstrap tables | dashboard with charts |
| DB connection | `config.ini` | credentials in `db.php` |
| Database schema | same (`schema.sql` on `main`) | |

To have data to display, you first need to run the import from the `main` branch: this branch is read-only.

---

## Stack

- Plain PHP, no framework or Composer dependencies
- MySQL 8
- [Chart.js](https://www.chartjs.org/) from CDN for the chart
- Hand-written CSS on design tokens (`:root` with 13 variables), dark theme, glass effect on the header
- Font Awesome and Inter from CDN

No build step: it just opens and works.

---

## Structure

```
├── index.php              dashboard and yearly chart
├── search.php             product search
├── product_details.php    product page and purchase history
├── db.php                 database connection
├── includes/              shared header and footer
└── css/style.css          design system (289 lines)
```

---

## Installation

Load the schema and import the invoices from the `main` branch, then configure the credentials in `db.php` and open `index.php`.

---

## Known limitations and what I'd redo today

### The most important limitation: comparing the same product across suppliers

In the product page, the "Supplier" column **does not come from the individual purchase**: it's copied from the product (product_details.php). The join movements → documents → suppliers, which would tell you who you bought *that particular time* from, is never done.

The result on screen is correct, but only for an indirect reason: during import, products are deduplicated by description + item code + supplier, so each product already belongs to a single supplier. And that's exactly the underlying problem: **the same item bought from three suppliers becomes three distinct rows in products**, and the dashboard can't compare their prices — which is the very reason the application exists. This shows up clearly in the real data: roughly 1,900 invoices generated over 23,000 products.

Fixing this requires changing the data model, not the UI: separating the product master data from the supplier relationship, and reading the supplier from the movement's document.

The limitation of reading products from electronic invoices is that the same product purchased from multiple suppliers has different product codes and descriptions (often the descriptions are very different), so they can't be merged automatically. The only way to verify that the same product was purchased from multiple suppliers is via barcode, which however isn't reported on the invoice by all suppliers and isn't handled by this app.

### The CSS is only half applied

The stylesheet defines a coherent design system — color tokens, spacing, `.card`, `.stat-card`, `.dashboard-grid` classes — but **31 inline `style="..."` attributes** remain in the pages, written while fixing up the layout and never moved into the CSS.

Most importantly: **there is no media query at all**, so the layout doesn't adapt on narrow screens. For a dashboard this is the most impactful flaw, and also the fastest to fix (the app only ran on my own computer, so this flaw didn't matter at the time).

### Other known flaws

- `db.php` has credentials hardcoded: a step backward compared to the `config.ini` on the main branch, and needs to be aligned.
- The chart is configured as `type: 'bar'` but the dataset still carries `tension`, `fill`, and `pointRadius`, options from line charts that are ignored on a bar chart: leftovers from a chart-type change that was never cleaned up.
- The footer says "All rights reserved" on an interface that's entirely in Italian.
- In `product_details.php`, the "no movements" row uses `colspan="6"` on a five-column table.
- **No authentication**: like the main branch, the application was built for personal use on `localhost`. Exposed on a network, it would show the entire purchase history to anyone.
- The dashboard totals are only as accurate as the import: the price-conversion bugs documented on `main` carry over here, since this branch only reads the data.

### What I'd change structurally

- **Queries live inside the pages.** Every file mixes data access, calculations, and HTML. The main branch had already introduced repositories: this UI should have been built on top of those, not alongside them.
- **No pagination**: search is capped at a fixed `LIMIT 50`, with no way to see further results.
- **No error handling**: if the database doesn't respond, the page dies with a system error instead of a readable message.

---

## Unimplemented ideas

- Comparing prices of the same product across different suppliers (requires the data model change described above)
- Flagging price increases compared to the previous purchase
- CSV export of the filtered history
- Unifying with the `main` branch into a single application

---

## License

MIT

