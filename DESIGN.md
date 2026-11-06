---
version: alpha
colors:
    primary: '#2563EB'
    secondary: '#020713'
    background: '#09090B'
    surface: '#18181B'
typography:
    sans:
        fontFamily: 'Instrument Sans, ui-sans-serif, system-ui, sans-serif'
    display:
        fontFamily: 'ui-serif, Georgia, Cambria, Times New Roman, serif'
rounded:
    authCard: '0.75rem'
    authControl: '0.5rem'
spacing:
    authControlHeight: '2.5rem'
components:
    authCard: {}
    authPrimaryAction: {}
---

# SEO Impact design context

## Brand character

SEO Impact combines a premium dark-tech public presence with calm, highly legible operational interfaces. Electric blue is the primary accent. Motion supports hierarchy and comprehension; it must not compete with the content.

## Surface modes

### Public website

- Midnight navy background with restrained electric-blue light effects.
- Large editorial serif display type paired with Instrument Sans for interface copy.
- The hero is the primary visual moment; supporting sections should be quieter.

### Authentication and admin

- Use the admin login's dark mode as the canonical authentication visual language: a near-black page with one centered zinc card, restrained border, compact controls and minimal elevation.
- Client authentication keeps a small `Client portal` identifier and client-specific footer, but does not use a separate split-screen illustration. When available, passkey sign-in is presented as a restrained dark secondary action above the email form.
- Use white and zinc text for headings, labels and controls with the same electric-blue primary action as the admin login.
- Keep authentication forms focused and utilitarian: one card, one primary action and an obvious path back to the site.
- Preserve a visible keyboard focus ring and at least 44 px touch targets where practical.
- The client login should match the admin login's density, shape, border, typography and blue action hierarchy without copying Filament-specific markup.

## Shared rules

- Prefer explicit foreground/background pairs at boundaries between public, auth, portal and admin surfaces.
- Avoid `transition-all`; animate only the properties that are expected to change.
- Respect `prefers-reduced-motion` and provide static fallbacks for decorative media.
- Keep localized controls available on both desktop and mobile.
- Use the default arrow cursor over display text on public, portal and admin
  surfaces. Reserve the text cursor for editable fields and editors, and the
  pointer cursor for enabled interactive controls.
- Validate modified screens at desktop and mobile widths, including keyboard focus and overflow.
