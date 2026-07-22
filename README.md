# KUPE DEE MINING — Official Website

High-end marketing website for **KUPE DEE MINING**, a Ghana-based mining and
construction enterprise. Built as a fast, dependency-free static site (HTML,
CSS, vanilla JS) with a dark-primary / gold-accent design system.

## Features

- **Dark / Light theme toggle** with persisted preference (`localStorage`) and
  system-preference detection, driven by CSS custom properties.
- **Responsive header** with a top utility bar (phone, email, socials, theme
  switcher) and a main navigation bar with multi-level dropdown menus.
- **Floating WhatsApp button** linking to `https://wa.me/233209996996` with a
  pre-filled enquiry message.
- **Scroll-to-top button** that fades in after scrolling.
- **Video showcase** with click-to-load (lazy) YouTube embeds.
- **Reveal-on-scroll** animations via `IntersectionObserver`.

## Structure

```
├── index.html        # HOME page (canonical)
├── HOME.html         # redirect → index.html
├── assets/img/       # logo, favicon, media
├── css/styles.css    # design system + components
└── js/main.js        # theme toggle, nav, dropdowns, scroll-to-top, video
```

### Planned pages (nav structure)

About Us · Products & Services (Cable Anchors, Stressing Equipment, Grout
Equipment, Portable Drilling Equipment, Mining Consumables, Vula Drilling,
Construction, Training) · Public Relations (Exhibitions, Bursaries) · Our Team
· Careers · Contact.

## Design tokens

| Role            | Dark          | Accent Gold       |
| --------------- | ------------- | ----------------- |
| Backgrounds     | `#06090e` `#0d1117` `#161b22` `#21262d` | — |
| Borders         | `#30363d`     | — |
| Text            | `#ffffff` `#f3f4f6` | — |
| Accent          | —             | `#f59e0b` `#d97706` |

## Local development

No build step. Serve the folder with any static server:

```bash
python3 -m http.server 8000
# open http://localhost:8000
```
