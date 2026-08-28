# Purchasing dashboard — interface redesign

Interface redesign of Inventario: same database, new UI. It turns the
consultation tables into an analysis dashboard, with monthly spending trends and
a per-product page showing average price, quantity and purchase history.

Separate branch, not merged into main. This branch contains only the
presentation layer: it reads the same database as main, but does not include the
XML invoice import, which stays on the main branch. The refactoring was never
finished and the two branches were never unified — the limitations are
documented below.

## What's in here

**Dashboard** — month-by-month spending chart, with a year selector populated
from the years actually present in the database, and the annual total
highlighted.

**Product search** — search by description or item code, with the associated
supplier.

**Product page** — total quantity purchased, weighted average price, total
spending and the full purchase history, filterable by date range.

## Relationship to the main branch

| | main | this branch |
| --- | --- | --- |
| XML invoice import | yes | no |
| Data consultation | Bootstrap tables | dashboard with charts |
| DB connection | `config.ini` | credentials in `db.php` |
| Database schema | the same (`schema.sql` on main) | |

To have any data to display, you first need to run the import from the main
branch: this branch is read-only.

## Stack

- Plain PHP, no framework and no Composer dependencies
- MySQL 8
- Chart.js from a CDN for the chart
- Hand-written CSS on design tokens (`:root` with 13 variables), dark theme,
  glass effect on the header
- Font Awesome and Inter from a CDN

No build step: open it and it works.

## Layout

```
├── index.php              dashboard and yearly chart
├── search.php             product search
├── product_details.php    product page and purchase history
├── db.php                 database connection
├── includes/              shared header and footer
└── css/style.css          design system (289 lines)
```

## Installation

Load the schema and import the invoices from the main branch, then configure the
credentials in `db.php` and open `index.php`.

## Known limitations and what I would do differently today

### The biggest limitation: comparing the same product across suppliers

On the product page, the "Supplier" column does not come from the individual
purchase: it is copied from the product itself (`product_details.php`). The join
`movimenti` → `documenti` → `fornitori` (movements → documents → suppliers),
which would tell you who you bought from on that occasion, is never performed.

What ends up on screen is correct, but only for an indirect reason: during
import, products are deduplicated on description + item code + supplier, so each
product already belongs to a single supplier. And that is exactly where the
underlying problem lies: the same item bought from three suppliers becomes three
separate rows in `prodotti`, and the dashboard cannot compare their prices —
which is the whole reason the application exists. It shows up clearly in the
real data: roughly 1,900 invoices produced more than 23,000 products.

Fixing it means changing the data model, not the UI: separating the product
record from its link to the supplier, and reading the supplier from the
movement's document.

The limitation of reading products from electronic invoices is that the same
product bought from different suppliers comes with different product codes and
descriptions (often the descriptions differ a great deal), so they cannot be
merged automatically. The only way to verify that the same product was bought
from more than one supplier is the barcode, which not every supplier includes on
the invoice and which this app does not handle.

### The CSS is only half applied

The stylesheet defines a coherent design system — colour tokens, spacing,
`.card`, `.stat-card` and `.dashboard-grid` classes — but 31 inline
`style="..."` attributes remain in the pages, written while I was sorting out
the layout and never moved into the CSS.

Above all: there is not a single media query, so the layout does not adapt on
narrow screens. For a dashboard that is the most damaging flaw, and also the
quickest to fix. (The app only ran on my own computer, so this flaw didn't
matter at the time.)

### Other known flaws

- `db.php` has the credentials hard-coded: a step backwards from the
  `config.ini` on the main branch, and it needs to be brought into line.
- The chart is configured as `type: 'bar'`, but the dataset still carries
  `tension`, `fill` and `pointRadius` — line-chart options that a bar chart
  ignores: leftovers from a change of chart type that was never cleaned up.
- The footer says "All rights reserved" in an interface that is entirely in
  Italian.
- In `product_details.php`, the "no movements" row uses `colspan="6"` on a
  five-column table.
- No authentication: like the main branch, the application was built for
  personal use on localhost. Exposed on a network, it would show the entire
  purchase history to anyone.
- The dashboard totals are only as accurate as the import: the price conversion
  bugs documented on main are reflected here, because this branch only reads.

### What I would change structurally

- The queries live inside the pages. Every file mixes data access, calculations
  and HTML. The main branch had already introduced repositories: this UI should
  have been built on top of those, not alongside them.
- No pagination: search stops at a fixed `LIMIT 50`, with no way to see the
  results beyond that.
- No error handling: if the database doesn't respond, the page dies with a
  system error instead of a comprehensible message.

## Ideas never developed

- Price comparison for the same product across different suppliers (requires the
  data model change described above)
- Flagging price increases relative to the previous purchase
- CSV export of the filtered history
- Unification with the main branch into a single application

## License

MIT
