# House Rental Danang Project Rules

## Homepage Localization Quality Gate

- Supported homepage locales are `en`, `vi`, `ko`, `ja`, `ru`, and `zh`.
- Homepage copy may come from RealHomes post meta, child-theme PHP, shortcodes, widgets, or partials; never assume editing page content alone localizes the rendered page.
- When adding or changing a homepage section, add and review every locale mapping for its title, description, CTA labels, FAQ, footer labels, links, and machine-readable metadata.
- Before committing or deploying homepage/localization changes, run:

  ```bash
  php scripts/check-homepage-localization.php
  ```

- Do not commit or deploy if the guard fails. Add the missing locale mapping or update the required-key list when a deliberately new homepage field is introduced.
- After live changes, render all six homepage URLs and check for source-language leaks, missing sections, incorrect links, canonical tags, and `index, follow` robots directives.
- Keep the guard script and the live/local child-theme copies synchronized.
