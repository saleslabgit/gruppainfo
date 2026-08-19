# Gruppa Info — Design System Specification

Implementation-ready specification extracted from the approved visual UI Kit in `uikit/index.html`. This document is the source of truth for visual numeric values and design implementation rules. Every value below is a single fixed number — no ranges, no "or", no "approximately". Where the source UI Kit contains no component or pattern to derive a value from at all, the item is listed in **Unresolved design values** at the end with no implementation rule attached — do not invent a value for it.

The files in `uikit/` are reference artifacts, not Laravel runtime code or dependencies. This document governs visual and design decisions only; it does not override project-wide architecture, security, stack, runtime, or asset-delivery constraints.

Font: **Montserrat** (400, 500, 600, 700) — the only font in the system. Icon library: **Lucide** only.

---

## 1. Foundations

### 1.1 Colors

| Token | Hex | Usage |
|---|---|---|
| `color-primary` | `#FF714A` | Primary buttons, active nav item, active tab indicator, focus ring border, links |
| `color-primary-hover` | `#E85F3A` | Primary button hover |
| `color-primary-active` | `#D14B29` | Primary button active/pressed, link hover, link icon color |
| `color-primary-subtle` | `#FFE1D3` | Focus halo, selected chip background, highlighted card background, active filter chip background |
| `color-text-primary` | `#3C3834` | Default body/heading text, default icon color |
| `color-text-secondary` | `#71695F` | Secondary text, descriptions |
| `color-text-muted` | `#9C948A` | Helper text, captions, placeholders, nav section labels, timestamps |
| `color-text-disabled` | `#C4BEB3` | Disabled text/icons |
| `color-text-inverse` | `#FFFFFF` | Text on primary/dark backgrounds |
| `color-background-page` | `#F5F4F0` | App background |
| `color-background-surface` | `#FFFFFF` | Cards, inputs, sidebar, modals, dropdowns |
| `color-background-subtle` | `#EBE9E2` | Secondary surfaces, table header row, dividers, borders |
| `color-background-hover` | `#F5F4F0` | Hover background for rows/list items/menu items |
| `color-background-disabled` | `#E4E1D8` | Disabled control background |
| `color-border-default` | `#EBE9E2` | Default 1px borders |
| `color-border-strong` | `#D6D2C8` | Hover borders |
| `color-border-focus` | `#FF714A` | Effective 2px focus edge |
| `color-success` | `#2E9960` | Success icon/text/badge text, success progress fill |
| `color-success-subtle` | `#E2F2E8` | Success badge/alert background |
| `color-warning` | `#C97A17` | Warning icon/text/badge text |
| `color-warning-subtle` | `#FAEAD2` | Warning badge/alert background |
| `color-danger` | `#D14545` | Danger button, error border/text, danger badge text |
| `color-danger-subtle` | `#FAE2E2` | Danger badge/alert background |
| `color-danger-hover` | `#B93A3A` | Danger button hover |
| `color-danger-disabled-bg` | `#FAE2E2` | Danger button disabled background |
| `color-danger-disabled-text` | `#E9A6A6` | Danger button disabled text |
| `color-info` | `#3D77AD` | Info icon/text/badge text |
| `color-info-subtle` | `#E4EDF5` | Info badge/alert background |
| `color-sidebar-bg` | `#3C3834` | Sidebar background (exclusive to Sidebar) |
| `color-sidebar-text` | `#D8D3CB` | Sidebar inactive item text (exclusive to Sidebar) |
| `color-sidebar-divider` | `#524D46` | Sidebar divider (exclusive to Sidebar) |
| `color-sidebar-footer-bg` | `#4A453F` | Sidebar user block background (exclusive to Sidebar) |
| `color-alert-info-text` | `#2C5A82` | Info alert title text (exclusive to Alert) |
| `color-alert-success-title` | `#1F7245` | Success alert title text (exclusive to Alert) |
| `color-alert-success-body` | `#2E7A4E` | Success alert body text (exclusive to Alert) |
| `color-alert-warning-text` | `#8A5A10` | Warning alert title and body text (exclusive to Alert) |
| `color-alert-danger-text` | `#9C2E2E` | Danger alert title text (exclusive to Alert) |
| `color-toast-success-icon` | `#6FCB93` | Success icon on toast (dark bg only) |
| `color-toast-info-icon` | `#8FBDE6` | Info icon on toast (dark bg only) |
| `color-toast-warning-icon` | `#E8B356` | Warning icon on toast (dark bg only) |
| `color-toast-danger-icon` | `#E88585` | Danger icon on toast (dark bg only) |
| `color-highlight-card-bg` | `#FFE1D3` | Highlighted Card background |
| `color-highlight-card-border` | `#FFD3BC` | Highlighted Card border |
| `color-highlight-card-title` | `#D14B29` | Highlighted Card title text |
| `color-highlight-card-body` | `#8A4A32` | Highlighted Card body text |

Every focusable element uses one clean `color-border-focus` edge with an effective visual thickness of `2px` **and** a `3px color-primary-subtle` halo. The implementation must preserve the resting border geometry: keep the control's `1px` border, change it to `color-border-focus`, then add a `1px` outer primary ring and the halo. Focus is never color-only and never changes layout or creates an inner/nested ring.

### 1.2 Typography

Font-family for every style: `'Montserrat', sans-serif`.

| Style token | Size (desktop, ≥1200px) | Size (tablet, 768–991px) | Size (mobile, <576px) | Weight | Line-height | Letter-spacing | Color |
|---|---|---|---|---|---|---|---|
| `text-display-h1` | 34px | 30px | 26px | 600 | 1.2 | −0.01em | text-primary |
| `text-h2` | 26px | 24px | 21px | 600 | 1.25 | 0 | text-primary |
| `text-h3` | 21px | 20px | 18px | 600 | 1.3 | 0 | text-primary |
| `text-h4` | 18px | 18px | 18px | 600 | 1.35 | 0 | text-primary |
| `text-body-lg` | 18px | 18px | 18px | 400 | 1.5 | 0 | text-primary |
| `text-body` | 16px | 16px | 16px | 400 | 1.5 | 0 | text-primary |
| `text-body-medium` | 16px | 16px | 16px | 500 | 1.5 | 0 | text-primary |
| `text-small` | 14px | 14px | 14px | 500 | 1.4 | 0 | text-secondary |
| `text-caption` | 12.5px | 12.5px | 12.5px | 500 | 1.4 | 0.02em, uppercase | text-muted |
| `text-button-lg` | 15px | 15px | 15px | 600 | 1 | 0 | context |
| `text-button-md` | 14px | 14px | 14px | 600 | 1 | 0 | context |
| `text-button-sm` | 13px | 13px | 13px | 600 | 1 | 0 | context |
| `text-label` | 14px | 14px | 14px | 500 | 1.4 | 0 | text-primary (text-disabled if disabled) |
| `text-helper` | 12px | 12px | 12px | 400 | 1.4 | 0 | text-muted |
| `text-error` | 12px | 12px | 12px | 500 | 1.4 | 0 | color-danger |
| `text-table-header` | 13px | 13px | 13px | 600 | 1.3 | 0 | text-secondary |
| `text-table-body` | 14px | 14px | 14px | 400 | 1.4 | 0 | text-primary |
| `text-badge` | 13px | 13px | 13px | 600 | 1 | 0 | semantic color |

### 1.3 Spacing scale

Fixed scale in px: `4, 8, 12, 16, 24, 32, 40, 48, 64`. No other spacing value is permitted anywhere in the product.

| Value | Rule |
|---|---|
| 8 | Label → control; gap between two closely related inline fields |
| 12 | Control → helper/error text |
| 16 | Between form fields; internal Card padding (Card variant default) |
| 24 | Between field groups; title → subtitle; PageHeader → content |
| 32 | Between cards in a list |
| 48 | Reserved — not used by any current component (kept in scale for future use) |
| 64 | Between page sections; container padding at desktop |

Container padding — one fixed value per breakpoint:
- Mobile (<576px): `16px`
- Tablet (768–991px): `32px`
- Desktop (≥1200px): `64px` horizontal / `56px` top / `140px` bottom (matches the application shell's Main region)

### 1.4 Sizes (controls)

| Size | Height | Font size |
|---|---|---|
| Small | 32px | 13px |
| Default | 40px | 14px |
| Large | 48px | 15px |

Horizontal padding and icon size are fixed **per component**, not shared across components:

| Component | Small | Default | Large |
|---|---|---|---|
| Button padding-x | 12px | 16px | 20px |
| Button icon size | 16px | 18px | 20px |
| Icon Button box | 32×32px | 40×40px | 48×48px |
| Icon Button icon size | 16px | 18px | 20px |
| Input/Select/Textarea padding-x | — (no Small variant, see Unresolved) | 14px | — (no Large variant, see Unresolved) |

Input, Select, and Textarea have exactly one size (Default, 40px height) — there is no Small or Large form-control variant in this system.

### 1.5 Border radius

One fixed radius value per component — no shared ambiguous "md/lg" range:

| Token | Value | Applies to |
|---|---|---|
| `radius-sm` | 8px | Small Button, Small Icon Button |
| `radius-control` | 10px | Default/Large Button, Input, Select, Textarea, Dropdown Menu item, List Item avatar container corner (n/a, avatars are pill) |
| `radius-panel` | 12px | Dropdown Menu surface, Select option panel, Popover, Alert, Toast |
| `radius-card` | 14px | Card, Choice Card, Empty State, Confirmation Pattern, Metric |
| `radius-shell` | 16px | Modal, Drawer, page-section demo container, Sidebar container |
| `radius-pill` | 999px | Badge, Chip, Avatar, Switch, Progress track, Pagination page buttons rendered as circular |

### 1.6 Borders

- Width: `1px` for all resting control borders. Focus keeps that geometry and creates an effective `2px` primary edge with one additional outer pixel; no state changes the control's box dimensions.
- Colors: default `#EBE9E2`; hover `#D6D2C8`; focus `#FF714A` (effective 2px visual edge, always paired with the halo below); error `#D14545` (1px, no halo); disabled `#EBE9E2`.
- Focus halo: after the effective 2px primary edge, render `3px #FFE1D3`; in CSS this is `box-shadow: 0 0 0 1px #FF714A, 0 0 0 4px #FFE1D3` on a control that retains its 1px border.

### 1.7 Shadows / elevation

| Token | CSS value | Applies to |
|---|---|---|
| `shadow-none` | none | Card, List Item, Sidebar, Table, Metric, Badge, Chip |
| `shadow-sm` | `0 1px 2px rgba(60,56,52,0.08), 0 1px 1px rgba(60,56,52,0.04)` | Interactive Card (hover state only) |
| `shadow-md` | `0 12px 28px rgba(60,56,52,0.16)` | Dropdown Menu, Select option panel, Popover |
| `shadow-modal` | `0 20px 48px rgba(60,56,52,0.22)` | Modal, Drawer |
| `shadow-toast` | `0 12px 28px rgba(60,56,52,0.2)` | Toast |

### 1.8 Motion

- Tokens: `motion-duration-fast:160ms`; `motion-ease-standard:ease-out`.
- Apply them consistently to visual micro-interaction properties on interactive components: `color`, `background-color`, `border-color`, `box-shadow`, and `opacity` where applicable.
- Never animate width, height, padding, margin, position, or other layout geometry.
- Under `prefers-reduced-motion:reduce`, effectively disable nonessential project-defined transitions and animations. Do not replace Bootstrap's Modal/Offcanvas transition mechanism.

### 1.9 Icons

- Library: Lucide only. In production it must be delivered locally in accordance with the project architecture; the delivery mechanism is not defined by this design specification. No other icon library is permitted.
- Stroke-width: `1.75` for all standalone and inline icons. `2` for spinner icons (`loader-2`). `2.5` for the checkmark glyph inside 20×20px Checkbox and inside the 13px table-row checkbox.
- Icon size is fixed per usage context — the full set of sizes used in this system is: `13, 14, 15, 16, 17, 18, 19, 20, 22, 24, 26, 28, 30` px. Each component section in §4 states its exact icon size; do not pick a size not already assigned to that component.
- Icon + text gap: `8px` inside buttons; `6px` inside all other inline contexts (chips, tabs badges, list metadata, breadcrumbs, dropdown items, filters, toolbar).
- Icon color: `color-text-primary` by default; semantic color (`color-success`/`color-warning`/`color-danger`/`color-info`) when representing a status.

### 1.10 Focus system

Every focusable element (button, input, select, checkbox, radio, switch, link, tab, menu item) receives one visually identical, layout-stable treatment on keyboard focus:
```
border-color: #FF714A; /* retain the resting 1px border width */
box-shadow: 0 0 0 1px #FF714A, 0 0 0 4px #FFE1D3;
```
Composite controls apply this treatment only to their outer visual shell via `:focus-within`; their nested native input must not render a second focus ring. No other focus treatment exists in this system.

---

## 2. Layout system

### Application shell
```
display: flex; min-height: 100vh; background: #F5F4F0;
```

**Sidebar**: `position: fixed; width: 264px; height: 100vh; padding: 28px 18px 60px; background: #FFFFFF; border-right: 1px solid #EBE9E2; overflow-y: auto`.

**Main content**: `margin-left: 264px; flex: 1; padding: 56px 64px 140px; max-width: 1180px`.

**Sidebar nav group**: `margin-top: 8px` between groups. Group label: `padding: 14px 10px 6px; font-size: 11px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #9C948A`. Nav item: `padding: 6px 10px; border-radius: 8px; font-size: 14px`; adjacent nav items have a `4px` vertical gap in both the desktop Sidebar and mobile Drawer so active and hovered rounded surfaces never merge.

### Page header
- Bottom margin before first section: `56px`.
- Eyebrow label → title: `10px`.
- Title → description: `12px`.
- Section `h2` → description paragraph: `6px`.
- Description → content card: `24px`.
- Section-to-section vertical gap: `64px`.

### Cards / surfaces
Component demo surface: `background: #FFFFFF; border: 1px solid #EBE9E2; border-radius: 16px; padding: 28px`. Product Card component: see §4.16 (uses `radius-card`, 14px).

### Grids
- Form grid: CSS grid, `gap: 16px` (row and column). Columns: `1fr` (single field), `1fr 1fr` (two fields), `1fr 1fr 1fr` (three fields) — exactly these three patterns exist.
- Table row grid: `grid-template-columns: 36px 2fr 1.2fr 1fr 1fr 60px; gap: 12px`. This exact template applies to every table in the system (checkbox / primary column / status column / numeric column / date column / action column). A table needing more or fewer columns must add or remove `1fr` tracks while keeping the `36px` checkbox and `60px` action tracks fixed.

---

## 3. Responsive behavior

Breakpoints:
```
Mobile:  < 576px
Tablet:  768–991px
Desktop: ≥ 1200px
```
(576–767px and 992–1199px inherit the nearest lower breakpoint's rules unless stated otherwise.)

### 3.1 Typography & spacing by breakpoint

| Token | Mobile (<576) | Tablet (768–991) | Desktop (≥1200) |
|---|---|---|---|
| Display/H1 | 26px / 600 | 30px / 600 | 34px / 600 |
| H2 | 21px / 600 | 24px / 600 | 26px / 600 |
| H3 | 18px / 600 | 20px / 600 | 21px / 600 |
| Body | 16px / 400 | 16px / 400 | 16px / 400 |
| Container padding | 16px | 32px | 64px |
| Spacing between sections | 32px | 40px | 64px |
| Touch target minimum | 44×44px | 40×40px | 32×32px |

Control height (Input/Button) never changes across breakpoints — always the values in §1.4. Only the hit-area padding around a small icon target grows to meet the touch-target minimum in this table; the icon's own size does not change.

### 3.2 Components by breakpoint

| Component | Mobile (<576) | Tablet (768–991) | Desktop (≥1200) |
|---|---|---|---|
| Button | `width: 100%` inside forms/modals; a button group stacks vertically with the primary button first | `width: auto`; a button group renders as a single row | `width: auto`; a button group renders as a single row |
| Icon Button | Visual box per §1.4; hit-area padded to 44×44px | Visual box per §1.4; hit-area padded to 40×40px | Visual box per §1.4 (32/40/48px), no added padding |
| Input / Select / Textarea | `width: 100%`, height fixed at 40px | `width: 100%` of its form-grid column | `width: 100%` of its form-grid column |
| Badge / Chip | Unchanged size/padding; a Badge/Chip group wraps to a new line when it overflows | Unchanged | Unchanged |
| Card | `padding: 16px`; no outer container margin below 400px width | `padding: 20px` | `padding: 24px` |
| Table | `overflow-x: auto` with the first grid column (`36px`) sticky | `overflow-x: auto` with the first grid column (`36px`) sticky | No horizontal scroll; full width |
| Dropdown / Popover / Select panel | Renders as a full-width bottom sheet fixed to the viewport bottom | Renders at its default anchored position (§4.6/§4.21/§4.26) | Renders at its default anchored position |
| Modal | `width: calc(100% - 32px)`, centered, `margin: 0 16px` | Fixed width per §4.27 size, centered | Fixed width per §4.27 size, centered |
| Sidebar | Replaced by Drawer, opened by the `menu` icon in Topbar | Replaced by Drawer, opened by the `menu` icon in Topbar | Persistent, `width: 264px` |
| Tabs | `overflow-x: auto`, no wrap | `overflow-x: auto`, no wrap | No scroll unless content overflows the container |
| Stepper | Renders as the compact "Шаг N из M" text only, no step circles | Full horizontal Stepper, step labels shown | Full horizontal Stepper, step labels shown |
| Page header actions | Render on a new row below the title, `width: 100%` | Render on a new row below the title if the action count exceeds 2 | Render inline with the title, right-aligned |
| Form grid | Every row collapses to `1fr` (single column) | A 3-column row (`1fr 1fr 1fr`) becomes `1fr 1fr` with the third field wrapping to a new row; a 2-column row stays `1fr 1fr` | Column count exactly as authored (1, 2, or 3) |

---

## 4. Components

### 4.1 Button

**Anatomy**: optional leading icon → label → optional trailing icon. Icon-only variant omits the label.
**Dimensions**: see §1.4 for height/padding/icon-size per size tier. Icon-to-label gap: `8px`. Border-radius: Small = `radius-sm` (8px); Default/Large = `radius-control` (10px).
**Typography**: `text-button-sm` / `text-button-md` / `text-button-lg` matching the size tier, weight 600.

**Primary**: default `background:#FF714A` `color:#FFFFFF` → hover `background:#E85F3A` → active `background:#D14B29` → focus `background:#FF714A` + the layout-stable focus treatment from §1.10 → disabled `background:#E4E1D8` `color:#C4BEB3` → loading: background unchanged, `opacity:0.85`, an 16px `loader-2` spinner is prepended at `gap:8px`, button width and height do not change.
**Secondary**: default `border:1px solid #EBE9E2` `background:#FFFFFF` `color:#3C3834` → hover `border:#D6D2C8` `background:#F5F4F0` → active `background:#EBE9E2` → disabled `border:#EBE9E2` `color:#C4BEB3`.
**Ghost**: default `background:transparent` `color:#3C3834` → hover `background:#EBE9E2` → disabled `color:#C4BEB3`.
**Danger**: default `background:#D14545` `color:#FFFFFF` → hover `background:#B93A3A` → disabled `background:#FAE2E2` `color:#E9A6A6`.
**Text**: default `color:#FF714A`, no background/border, `padding:0 4px`, `border-radius:6px` → hover `color:#D14B29` + underline.

**Variants**: Primary, Secondary, Ghost, Danger, Text, Icon Button (§4.2).
**Behavior**: `Full width` sets `width:100%`. A button carries exactly one leading OR one trailing icon, never both (Icon Button excepted).
**Forbidden**: no gradients; no page-specific button colors; no height outside §1.4; no shadow on any Button state.

### 4.2 Icon Button

**Dimensions**: box size and icon size per §1.4 (Small 32×32/16px icon, Default 40×40/18px icon, Large 48×48/20px icon).
**Colors**: default `border:1px solid #EBE9E2` `background:#FFFFFF` `color:#3C3834`; filled variant `background:#F5F4F0`; circular variant `border-radius:999px` `background:#FF714A` `color:#FFFFFF` (used only for a single primary floating add action); disabled `opacity:0.5` `color:#9C948A`; focus uses §1.10 without changing dimensions.

### 4.3 Links

Default `color:#FF714A` underlined → hover `color:#D14B29` underlined → focus uses the layout-stable treatment from §1.10 with `border-radius:4px` → disabled `color:#9C948A` `opacity:0.8` no underline → destructive link `color:#D14545` underlined. Icon+text link: icon `14px`, `gap:6px`.

### 4.4 Input

**Anatomy**: Label → (required marker) → Control (prefix → value → suffix) → Helper or Error (never both at once).
**Dimensions**: `height:40px`; `padding:0 14px`; `border-radius:10px`; label `margin-bottom:8px`; helper/error `margin-top:12px`; `width:100%` of its container.
**States**: default/empty `border:1px solid #EBE9E2` `background:#FFFFFF`; hover `border:#D6D2C8`; focus uses the layout-stable primary edge + halo from §1.10; filled `border:#EBE9E2` `color:#3C3834`; error `border:1px solid #D14545`, error text below with a 13px `alert-circle` icon at `gap:5px`; disabled `background:#F5F4F0` `border:#EBE9E2` `color:#C4BEB3`; readonly `background:#F5F4F0` `border:#EBE9E2` `color:#71695F`.
**Prefix**: attached block, `height:40px`, `padding:0 12px`, `background:#F5F4F0`, `border-radius:10px 0 0 10px`, joined to the control which becomes `border-radius:0 10px 10px 0`.
**Suffix icon**: `18px`, positioned `right:12px`.
**Types**: text, email, phone, number, password render identically; password adds a trailing `eye` icon at `right:12px`, size `18px`. No other type-specific visual variant exists.
**Forbidden**: placeholder never substitutes for Label; height is always 40px.

### 4.5 Textarea

Same border/color/state rules as Input. `height:96px` fixed (not `min-height`); `padding:10px 14px`; `resize:none`. Optional character counter renders inline with the helper row, right-aligned, `text-helper` style.

### 4.6 Select

**Anatomy**: Label → trigger (value or placeholder + icon) → option panel.
**Trigger**: `height:40px`, `padding:0 14px`, `border-radius:10px`, trailing icon `chevron-down` (closed) or `chevron-up` (open), `16px`.
**Panel**: `padding:6px`, `border-radius:12px`, `box-shadow:shadow-md`, positioned directly below the trigger with `4px` vertical gap. Options form a vertical stack with `4px` between adjacent items. Option: `padding:9px 10px`, `border-radius:8px`, `font-size:14px`.
**States**: placeholder `color:#9C948A`; selected value `color:#3C3834`; while open, the trigger retains the layout-stable focus treatment from §1.10 even when keyboard focus moves into an option; hover/focused unselected option `background:#F5F4F0`; selected option remains `background:#FF714A` `color:#FFFFFF` with a trailing `14px` check icon and is not replaced by hover styling; disabled option `color:#C4BEB3`; error trigger `border:1px solid #D14545`.
**Forbidden**: no native `<select>` rendering — always this custom trigger+panel.

### 4.7 Checkbox

`20×20px` box, `border-radius:5px`, label `gap:10px` (the `<label>` wraps both, making the label part of the clickable area).
Unchecked `border:1px solid #D6D2C8` `background:#FFFFFF`; checked `background:#FF714A` with a `13px` white check icon (`stroke-width:2.5`); indeterminate `background:#FF714A` with a `10×2px` white bar; hover `border:#D6D2C8` `background:#F5F4F0`; focus uses §1.10 without changing dimensions; disabled unchecked `border:#EBE9E2` `background:#F5F4F0`; disabled checked `background:#E4E1D8` with white check icon.

### 4.8 Radio

`20×20px` circle (`border-radius:999px`), label `gap:10px`.
Unchecked `border:1px solid #D6D2C8`; selected `border:6px solid #FF714A` `background:#FFFFFF`; hover `border:#D6D2C8` `background:#F5F4F0`; focus uses §1.10 without changing dimensions; disabled `border:1px solid #EBE9E2` `background:#F5F4F0`.

### 4.9 Switch

Track `44×26px`, `border-radius:999px`. Thumb `20×20px`, inset `3px` from the track edge (canonical size; see Unresolved for the source's one 18px outlier in the Focus demo, resolved here to 20px for consistency with every other Switch instance).
Off `background:#EBE9E2`, thumb at `left:3px`; on `background:#FF714A`, thumb at `right:3px`; focus uses §1.10 without changing dimensions; disabled `background:#E4E1D8`, thumb `opacity:0.7`.
Optional label `15px/500` at `gap:10px` from the track; optional description line below the label, `13px` `color:#9C948A`, `margin-top:2px`.

### 4.10 Date / Time / Money

Date and Time inputs use the Input shell exactly (§4.4: `height:40px`, `border-radius:10px`) with a trailing `calendar` or `clock` icon, `16px`, `color:#9C948A`, replacing the suffix slot.
Date range: two Input shells (`flex:1` each) separated by an em dash `—` (`color:#9C948A`), `gap:10px`.
Money input: Input shell with the numeric value left-aligned and the currency code (e.g. `BYN`) right-aligned inside the same row (`display:flex; justify-content:space-between`), `color:#9C948A` for the currency code. Errors use the standard Input error state (§4.4).

### 4.11 File Upload

**Dropzone**: `width:100%` of its container, `height:120px`, `border:1.5px dashed #D6D2C8`, `border-radius:12px`.
Drag-over: `border:1.5px dashed #FF714A`, `background:#FFE1D3`, icon/text `color:#D14B29`.
Uploading: dropzone shell unchanged, containing a `160px`-wide, `6px`-tall progress bar (`background:#EBE9E2` track, `#FF714A` fill) plus a percentage caption below at `gap:10px`.
**Uploaded file item**: `padding:10px 14px`, `border:1px solid #EBE9E2`, `border-radius:10px`, `gap:12px`: file-type icon (`20px`) → filename (`14px/500`) + meta line (`12px` `#9C948A`) → remove icon (`16px` `x`).
Error item: `border:1px solid #D14545`, icon `file-x` `color:#D14545`, meta text `color:#D14545`.

### 4.12 Form Layout

Label→control: `8px`. Control→helper/error: `12px`. Between fields in the same row or column: `16px`. Between field groups: `24px`. Two-field row: `grid-template-columns:1fr 1fr; gap:16px`. Three-field row: `grid-template-columns:1fr 1fr 1fr; gap:16px`. Checkbox/radio group: items stacked, `gap:10px`. Actions row: `border-top:1px solid #EBE9E2; padding-top:16px`, buttons right-aligned, `gap:12px`, secondary button left of primary button.

### 4.13 Labels, Helper, Error

Label: `14px/500`, `margin-bottom:8px`, `color:#3C3834` (`#C4BEB3` when disabled). Required marker: `*` in `color:#D14545`, immediately after the label text with no added spacing. Helper: `12px`, `margin-top:12px`, `color:#9C948A`. Error: `12px/500`, `margin-top:12px`, `color:#D14545`, optional `13px` `alert-circle` icon at `gap:5px`. Helper and Error never render at the same time.

### 4.14 Badge / Status

`padding:5px 12px`, `border-radius:999px`, `font-size:13px`, `font-weight:600`, no border, no icon, no background other than the variant's own.
Neutral `background:#EBE9E2` `color:#71695F`; Info `background:#E4EDF5` `color:#3D77AD`; Success `background:#E2F2E8` `color:#2E9960`; Warning `background:#FAEAD2` `color:#C97A17`; Danger `background:#FAE2E2` `color:#D14545`.
**Forbidden**: a status is never rendered without its text label; every new status maps to exactly one of these 5 variants.

### 4.15 Chips / Tags

`padding:7px 14px`, `border-radius:999px`, `font-size:14px`. Default `border:1px solid #EBE9E2` `color:#3C3834`. Selected `background:#FFE1D3` `border:1px solid #FF714A` `color:#D14B29` `font-weight:600`. Removable: trailing `13px` `x` icon at `gap:8px`. Disabled: `color:#C4BEB3`.

### 4.16 Card

**Anatomy**: optional Header → Body → optional Footer.
**Basic**: `background:#F5F4F0`, no border, `padding:20px`, `border-radius:14px`, `shadow-none`.
**Interactive**: `background:#FFFFFF`, `border:1px solid #EBE9E2`, `padding:20px`, `border-radius:14px`, `cursor:pointer`; hover applies `shadow-sm`.
**Highlighted**: `background:#FFE1D3`, `border:1px solid #FFD3BC`, `padding:20px`, `border-radius:14px`, title `color:#D14B29`, body `color:#8A4A32`.
**With Header/Footer**: `border:1px solid #EBE9E2`, `border-radius:14px`, `overflow:hidden`. Header `padding:16px 20px` + `border-bottom:1px solid #EBE9E2`, title `15px/600`. Body `padding:20px`, `14px` `color:#71695F`. Footer `padding:14px 20px` + `border-top:1px solid #EBE9E2`, actions right-aligned. The final child in Header, Body, and Footer has no trailing bottom margin; component padding alone defines the final inset.
**Forbidden**: no page-specific card name — every card is one of these 4 patterns. No shadow above `shadow-sm` on any Card.

### 4.17 Divider

Horizontal: `height:1px; background:#EBE9E2`. Labeled: two `flex:1` 1px lines around a centered `13px` `color:#9C948A` label, `gap:14px`. Vertical: `width:1px; height:100%; background:#EBE9E2`, used inline with `gap:14px`.

### 4.18 Table

**Anatomy**: Toolbar → Header row → Body rows (checkbox / primary / status / numeric / date / action cells) → Pagination.
**Container**: `border:1px solid #EBE9E2`, `border-radius:16px`, `overflow:hidden`.
**Grid**: `grid-template-columns:36px 2fr 1.2fr 1fr 1fr 60px; gap:12px` (§2).
**Header row**: `padding:12px 20px`, `background:#F5F4F0`, `text-table-header` style.
**Body row**: `padding:14px 20px`, `border-bottom:1px solid #EBE9E2`, `text-table-body` style.
**Row states**: default `background:#FFFFFF`; hover `background:#F5F4F0`; selected `background:#FFF7F3`; disabled-action row `opacity:0.6` with a `16px` `lock` icon (`color:#C4BEB3`) replacing the `more-horizontal` action icon; empty `padding:40px 20px`, centered, `14px` `color:#9C948A`.
**Numeric cell**: right-aligned, `font-family:monospace`.
**Sortable header**: appends a `13px` `chevron-down` icon at `gap:6px`.
**Density**: one density only — `14px` row text, `14px 20px` row padding. There is no compact density (see Unresolved).
**Mobile/Tablet**: `overflow-x:auto` with the `36px` checkbox column `position:sticky; left:0`. Desktop: no scroll.
**Forbidden**: the header row background is always `#F5F4F0`; no borders between individual cells, only the row-level bottom border.

### 4.19 Table Toolbar

`padding:20px`, `display:flex; align-items:center; gap:12px; flex-wrap:wrap`. Search field: `min-width:200px`, `height:38px`. Filter trigger: `height:38px`. Active filter: rendered as a Chip in its selected state (§4.15). Result count: `13px` `color:#9C948A`, pushed to the right edge via a `flex:1` spacer preceding it. Primary action: `height:38px`, rightmost element. Gap from the Table below it: `16px`.

### 4.20 Pagination

`padding:20px`, `display:flex; justify-content:space-between; align-items:center`. Result-range text: `13px` `color:#9C948A`. Page control buttons: `32×32px`, `border-radius:8px`, `gap:6px`; current page `background:#FF714A` `color:#FFFFFF`; other pages `border:1px solid #EBE9E2` `background:#FFFFFF`; ellipsis: plain `…` text `color:#9C948A`; prev/next: chevron icon (`15px`), disabled state `opacity:0.4`.

### 4.21 Dropdown Menu

`width:220px`, `padding:8px`, `border-radius:12px`, `box-shadow:shadow-md`, `border:1px solid #EBE9E2`. Item: `padding:9px 12px`, `border-radius:8px`, `font-size:14px`, icon `16px` at `gap:10px`; hover `background:#F5F4F0`; disabled `color:#C4BEB3`; destructive item `color:#D14545`. Divider: `height:1px; background:#EBE9E2; margin:6px 8px`.

### 4.22 Search Input

Input shell (§4.4). Leading `search` icon, `17px`, `color:#9C948A`, `gap:10px`. Active/typed state adds a trailing `x` icon, `15px`. Loading state replaces the leading icon with a spinning `loader-2`, `17px`, `stroke-width:2`.

### 4.23 Filters

Filter button / select filter: `height:36px`, `padding:0 12px`, `border-radius:9px`, `border:1px solid #EBE9E2`, `font-size:13.5px`, icon `14px` at `gap:6px`. Active filter chip: `background:#FFE1D3` `color:#D14B29`, `padding:6px 12px`, `border-radius:999px`. "Clear all": plain `13.5px` `color:#9C948A` text.
**Filter Panel**: `width:320px`, `border:1px solid #EBE9E2`, `border-radius:14px`, `padding:20px`, `gap:16px` between fields. Footer: `border-top:1px solid #EBE9E2; padding-top:16px`, reset as text left, Apply as Default Primary Button right. On mobile the Filter Panel always renders inside a Drawer (§4.28), full width.

### 4.24 Alert

**Anatomy**: icon (`19px`) → title (`14.5px/600`) + optional body (`13.5px`) → optional trailing action button or close icon.
`padding:16px 18px`, `border-radius:12px`, no border, `gap:12px` icon-to-content.
Info `background:#E4EDF5` title `#2C5A82`; Success `background:#E2F2E8` title `#1F7245` body `#2E7A4E`; Warning `background:#FAEAD2` title and body `#8A5A10`; Danger `background:#FAE2E2` title `#9C2E2E`.
**Compact**: icon + title only. **Full**: icon + title + body, optionally + action button or close icon.

### 4.25 Toast

`background:#3C3834` (always, for every variant), `padding:14px 16px`, `border-radius:12px`, `box-shadow:shadow-toast`, `width:340px`, `gap:10px`. Message: `14px` `color:#F5F4F0`. Variant is carried only by the leading icon (Success `#6FCB93`, Info `#8FBDE6`, Warning `#E8B356`, Danger `#E88585`) — the toast background never changes. Optional trailing close icon `14px` `color:#9C948A`.

### 4.26 Tooltip / Popover

**Tooltip**: `background:#3C3834`, `color:#F5F4F0`, `font-size:12.5px`, `padding:6px 10px`, `border-radius:7px`.
**Popover**: `width:260px`, `background:#FFFFFF`, `border:1px solid #EBE9E2`, `border-radius:12px`, `box-shadow:shadow-md`. Optional header: `padding:12px 16px` + `border-bottom:1px solid #EBE9E2`, `14px/600`. Body: `padding:16px`, `13.5px` `color:#71695F`.
**Forbidden**: Tooltip never carries primary information — only icon clarification or truncated-value disclosure.

### 4.27 Modal

**Sizes** (fixed width, not max-width): Small `360px`; Medium `480px`; Large `640px`.
**Anatomy**: Header (title `17px/600` + `18px` close icon aligned to the right edge with automatic intervening space) → Body (`14.5px` `color:#71695F`) → Footer (actions right-aligned, `gap:10px`).
`border-radius:16px`, `box-shadow:shadow-modal`. Header `padding:18px 20px` + `border-bottom:1px solid #EBE9E2`. Body `padding:20px`. Footer `padding:16px 20px` + `border-top:1px solid #EBE9E2`.
**Usage patterns** (same visual shell): informational (body + single action), form (body holds form fields), confirmation (short body + two actions), destructive confirmation (primary action rendered in Danger Button color).
**Mobile**: `width:calc(100% - 32px)`, `margin:0 16px`.

### 4.28 Drawer / Off-canvas

`width:320px`, `background:#FFFFFF`, `border-radius:16px`, `box-shadow:shadow-modal`. Header `padding:16px 18px` + `border-bottom:1px solid #EBE9E2`, title `16px/600` + `17px` close icon. Body `padding:18px`, `gap:12px` between fields. Footer `padding:14px 18px` + `border-top:1px solid #EBE9E2`, primary action `width:100%`.
**Usage**: mobile Sidebar replacement, mobile Filter Panel, secondary actions panel.

### 4.29 Empty State

**Anatomy**: icon (`30px`, `color:#C4BEB3`, `stroke-width:1.5`) → title (`15px/600`) → text (`13.5px` `color:#9C948A`) → optional CTA button (`height:36px`).
`padding:36px 24px`, `border:1px solid #EBE9E2`, `border-radius:14px`, content centered, `gap:10px`, `width:260px` (fixed — this component is always placed inside a `260px`-wide slot; it does not stretch).
**Variants**: empty data, no search results, permission-restricted (adds CTA).

### 4.30 Loading

**Spinner**: `loader-2` icon, `color:#FF714A`. Two fixed sizes: `16px` (inline/small) and `24px` (standalone/medium).
**Skeleton**: `background:#EBE9E2`. Text line: `height:14px`, `border-radius:6px`, three lines per block at `70%`, `100%`, `85%` width, `gap:8px`. Card block: `220×120px`, `border-radius:12px`. Table row skeleton: `height:16px` bars, one per column, `border-radius:6px`, `gap:8px`.
**Button loading**: `16px` spinner prepended at `gap:8px`, `opacity:0.85`, button width/height unchanged from its resting state.

### 4.31 Error State

**Inline field error**: identical to §4.13 Error.
**Block error**: `background:#FAE2E2`, `border-radius:10px`, `padding:14px 16px`, `font-size:14px`, `color:#9C2E2E`.
**Full failed-load state**: `border:1px dashed #EBE9E2`, `border-radius:12px`, `padding:32px`, centered, icon `server-crash` `28px` `color:#C4BEB3`, title `15px/600`, retry button (Secondary, `height:36px`).

### 4.32 Confirmation Pattern

`width:340px`, `border:1px solid #EBE9E2`, `border-radius:14px`, `padding:20px`, `gap:14px`. Title `16px/600`. Body `14px` `color:#71695F`. Actions right-aligned, `gap:10px`; the cancel/secondary action is always leftmost, the primary or destructive action is always rightmost.
**Neutral confirmation**: primary action uses the Primary Button color.
**Destructive confirmation**: the rightmost action uses the Danger Button color; the leftmost action uses the Secondary Button color.

### 4.33 Stepper

Step circle: `28px`. Connecting line: `height:2px`, `flex:1`, `margin:0 4px 22px`.
Completed: `background:#2E9960`, `14px` white check icon. Current: `background:#FF714A`, white step number, `13px/600`. Upcoming: `border:1px solid #D6D2C8`, `color:#9C948A`, `13px`. Error: `border:1px solid #D14545`, `color:#D14545`, `13px`.
Label: `12.5px`, below the circle, `gap:6px`.
Compact form: "Шаг N из M", `13.5px` `color:#9C948A` — used only per §3.2 mobile rule.

### 4.34 Choice Card

`width:200px` (fixed — always placed inside a `200px` slot), `border-radius:14px`, `padding:18px`, `gap:10px`.
**Anatomy**: optional icon (`20px`) → title (`15px/600`) → description (`13px` `color:#9C948A`) → radio marker (`18×18px` circle).
Default: `border:1px solid #EBE9E2`. Selected: `border:2px solid #FF714A` + halo, `background:#FFF7F3`, title `color:#D14B29`, description `color:#8A4A32`, radio marker `border:6px solid #FF714A`. Disabled: `opacity:0.5`.

### 4.35 Timeline

**Anatomy**: marker (`12px` circle) + connecting line (`1px`, `#EBE9E2`) → title (`14px/500`) → date/actor line (`13px` `color:#9C948A`) → optional comment block (`background:#F5F4F0`, `padding:10px 12px`, `border-radius:8px`, `14px`).
Marker color by variant: neutral `#9C948A`; success `#2E9960`; warning `#C97A17`; danger `#D14545`; info `#3D77AD`.
`gap:16px` marker-to-content; `padding-bottom:24px` between entries (omitted on the last entry).

### 4.36 List Item

**Anatomy**: leading avatar/icon (`36px` circle) → title (`15px/500`) + secondary text (`13px` `color:#9C948A`) → metadata (`12px` `color:#9C948A`) → trailing chevron or action.
`padding:14px 20px`, `border-bottom:1px solid #EBE9E2` (omitted on the last item), `gap:14px`.
Default `background:#FFFFFF`; hover/selected `background:#F5F4F0`; disabled `opacity:0.5`, no trailing action.

### 4.37 File / Document Item

A List Item (§4.36) specialization: leading icon sits inside a `36px` tinted circle (`background:#E4EDF5` `color:#3D77AD` for generic documents), filename as the title, file metadata as the secondary text.

### 4.38 Key-Value / Description List

**Vertical**: `display:flex; flex-direction:column; gap:12px`; each row `display:flex; justify-content:space-between`; label `14px` `color:#9C948A`; value `14px/500`.
**Two-column**: `display:grid; grid-template-columns:max-content max-content; justify-content:start; gap:8px 16px`; same typography as Vertical.

### 4.39 Avatar

Sizes and their fixed fallback-icon size: Small `28px` box / `13px` icon / `11px` initials font; Medium `40px` box / `18px` icon / `14px` initials font; Large `56px` box / `26px` icon / `18px` initials font. `border-radius:999px` always.
Initials variant: `background:#FFE1D3` `color:#D14B29`, `font-weight:600`. Icon-fallback variant: `background:#EBE9E2` `color:#9C948A`, icon = `user`. Empty/unassigned placeholder: `background:linear-gradient(135deg,#EBE9E2,#F5F4F0)`, `border:1px dashed #D6D2C8` — used only for a slot with no assigned user.

### 4.40 Metric / Counter

`background:#FFFFFF`, `border:1px solid #EBE9E2`, `border-radius:14px`, `padding:20px`, `width:180px` (fixed — always placed inside a `180px` slot), `gap:6px`. Label row: icon `15px` + text `13px` `color:#9C948A`, `gap:8px`. Value: `28px/700`.

### 4.41 Progress

`height:8px`, `border-radius:999px`. Track `background:#EBE9E2`. Fill `background:#FF714A` (default) or `background:#2E9960` (success/complete state), width set to the exact percentage value. Label row above the bar: `13px` `color:#9C948A`, `display:flex; justify-content:space-between` with the percentage value on the right.

---

## 5. Form composition

```
Label — 8px — Control (Prefix — Value — Suffix) — 12px — Helper OR Error
```
- Between fields in the same row/column: `16px`.
- Between field groups: `24px`.
- Row column counts: exactly `1`, `2`, or `3` (§2 Grids). No form row uses more than 3 columns.
- Actions row: `border-top:1px solid #EBE9E2`, `padding-top:16px`, right-aligned, `gap:12px`, Secondary button left of Primary button.
- Required indicator: `*` in `color:#D14545` directly after the label text, no added spacing.
- Validation: an error replaces the helper text (never shown together) and applies the Input/Select/Textarea error border.

---

## 6. Page composition patterns

### List page
```
AppShell
└── Main
    ├── PageHeader
    ├── TableToolbar         (gap to PageHeader: 24px)
    ├── Table                (gap to Toolbar: 16px)
    └── Pagination           (rendered as the Table's own footer row; gap: 0px)
```

### Form page
```
AppShell
└── Main
    ├── PageHeader           (gap to Card: 24px)
    └── Card
        ├── SectionHeader    (optional)
        ├── Form fields      (§5 rhythm)
        └── Actions row      (§5 rhythm)
```

### Detail page
```
AppShell
└── Main
    ├── PageHeader           (gap to content: 24px)
    ├── Key-Value block      (gap to Timeline: 24px)
    └── Timeline or List Item feed
```

No other page pattern exists. If a new screen does not fit one of these three, report the gap — do not invent a fourth pattern.

---

## 7. Component API recommendation

```
Button: variant(primary|secondary|ghost|danger|text), size(sm|md|lg), disabled, loading, leadingIcon, trailingIcon, iconOnly, fullWidth
IconButton: size(sm|md|lg), variant(default|filled|circular), disabled, icon
Input: type(text|email|phone|number|password), state(default|error|disabled|readonly), label, required, helperText, errorText, prefix, suffix
Textarea: state(default|error|disabled), label, helperText, errorText, maxLength, showCounter
Select: state(default|error|disabled|open), placeholder, value, options[]
Checkbox: checked, indeterminate, disabled, label
Radio: checked, disabled, label, name
Switch: checked, disabled, label, description
Badge: variant(neutral|info|success|warning|danger), label
Chip: state(default|selected|disabled), removable, onRemove, label
Card: variant(basic|interactive|highlighted|withHeaderFooter), header?, footer?, onClick?
Table: columns[], rows[], selectable, sortable, loading, emptyMessage
TableToolbar: search?, onSearch, filters?, resultCount, primaryAction?
Pagination: page, pageSize, total, onPageChange
DropdownMenu: items[], trigger
Tabs: items[], activeKey, onChange, disabledKeys?
Modal: size(sm|md|lg), title, onClose, footerActions[]
Drawer: side(left|right), title, onClose, footerAction?
Alert: variant(info|success|warning|danger), density(compact|full), title, body?, action?, dismissible
Toast: variant(success|info|warning|danger), message, dismissible, duration
Tooltip: content, placement
Popover: header?, body, placement
EmptyState: icon, title, text, action?
Stepper: steps[], currentIndex, displayMode(full|compact)
ChoiceCard: icon?, title, description, selected, disabled
Timeline: entries[] (variant, title, datetime, actor?, comment?)
ListItem: leading, title, secondaryText, metadata?, trailingAction?, selected, disabled
Avatar: size(sm|md|lg), src?, initials?, fallbackIcon?
Metric: icon?, label, value
Progress: value(0-100), variant(default|success)
```

---

## 8. Rules for AI / developer

1. Do not invent new colors — use only tokens in §1.1.
2. Do not invent new spacing values — use only `4/8/12/16/24/32/40/48/64` (§1.3).
3. Do not invent new border-radius values — use only the 6 tokens in §1.5.
4. Do not change typography — use only the tokens in §1.2.
5. Do not create a local/one-off copy of an existing component.
6. Do not create a new visual variant of a component without updating this document first.
7. Do not use arbitrary inline values outside the framework's token/style system for this codebase.
8. Do not use an arbitrary CSS value when a named token in this document already covers the case.
9. Do not change layout based on personal preference — follow §2 and §6 exactly.
10. Do not "improve" or restyle a component while implementing it.
11. If no pattern in this document covers a needed screen, report that the Design System has no answer — do not invent one.
12. The reference UI Kit (`uikit/index.html`) takes priority over developer assumption in any visual conflict this document does not already resolve.
13. This document takes priority over the UI Kit file for every numeric value and implementation rule.

---

## 9. Visual invariants

- Every Button/Danger/Secondary/Ghost button of the same size shares exactly the same height, padding, and radius.
- Every Input/Select/Textarea shares exactly the same 40px height and 10px radius — there is only one size.
- Every Card variant uses `radius-card` (14px); padding follows the breakpoint values in §3.2 exactly.
- Every Page Header uses the same vertical rhythm (title→description 12px, description→content 24px).
- Every Table uses the same row rhythm (14px vertical / 20px horizontal padding) and the same header background (`#F5F4F0`).
- Every Badge of the same semantic meaning uses the same bg/text pair regardless of the entity it describes.
- Every focus ring in the system is visually identical and layout-stable: an effective 2px `#FF714A` edge + 3px `#FFE1D3` halo, with composite controls focusing only their outer shell.
- Every disabled element uses `#C4BEB3` text and `#F5F4F0`/`#E4E1D8` surface — never opacity alone.
- Only Modal, Drawer, Dropdown Menu, Select panel, Popover, and Toast use an elevation shadow; no other component uses any shadow above `shadow-sm`.
- Every icon is Lucide, `stroke-width:1.75` except the two documented exceptions in §1.9.

---

## 10. Forbidden patterns

- Any `box-shadow` value not listed in §1.7.
- Any color not listed in §1.1.
- Any `font-size` not listed in §1.2.
- Two same-size instances of the same component with different heights.
- Two visually-equivalent components with different border-radius values.
- Any icon library other than Lucide.
- Local or duplicated implementations of Button, Input, Table, Card, Badge, or any other component in §4.
- Any spacing value not in `4/8/12/16/24/32/40/48/64`.
- Status communicated by color without accompanying text.
- Placeholder text used in place of a Label.
- Page-specific component names (e.g. "GroupCard", "PaymentTable") — always compose from §4's generic components.

---

## Unresolved design values

The following do not exist anywhere in the source UI Kit (`uikit/index.html`) — there is no value to fix, and none is assigned. Confirm with the designer before building:

1. **Input / Select / Textarea size variants** — no Small or Large form-control exists in the source; every field is 40px. If a denser or larger control is needed, it must be designed first.
2. **Table compact density** — only one row density (14px text, 14px/20px padding) exists; no compact row spec exists.
3. **Calendar / date-picker popover UI** — only the closed text-field representation of a date (§4.10) exists; no calendar grid, month navigation, or range-picker panel exists.
4. **Sidebar collapsed (icon-only) state** — only the expanded 264px Sidebar and its mobile Drawer replacement exist; no collapsed/rail state exists.
