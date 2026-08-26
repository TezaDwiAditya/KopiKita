---
name: Artisanal Brew & Botanical
colors:
  surface: '#fafaf5'
  surface-dim: '#dadad5'
  surface-bright: '#fafaf5'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f4f4ef'
  surface-container: '#eeeee9'
  surface-container-high: '#e8e8e3'
  surface-container-highest: '#e3e3de'
  on-surface: '#1a1c19'
  on-surface-variant: '#4f4540'
  inverse-surface: '#2f312e'
  inverse-on-surface: '#f1f1ec'
  outline: '#81756f'
  outline-variant: '#d3c3bd'
  surface-tint: '#705a4f'
  primary: '#25160e'
  on-primary: '#ffffff'
  primary-container: '#3c2a21'
  on-primary-container: '#aa9084'
  inverse-primary: '#dec1b3'
  secondary: '#466649'
  on-secondary: '#ffffff'
  secondary-container: '#c5e9c5'
  on-secondary-container: '#4a6a4d'
  tertiary: '#645f3c'
  on-tertiary: '#ffffff'
  tertiary-container: '#b2ac83'
  on-tertiary-container: '#444020'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#fbdcce'
  primary-fixed-dim: '#dec1b3'
  on-primary-fixed: '#281810'
  on-primary-fixed-variant: '#574238'
  secondary-fixed: '#c8ecc8'
  secondary-fixed-dim: '#acd0ad'
  on-secondary-fixed: '#03210b'
  on-secondary-fixed-variant: '#2f4e33'
  tertiary-fixed: '#ebe4b7'
  tertiary-fixed-dim: '#cec79d'
  on-tertiary-fixed: '#1f1c02'
  on-tertiary-fixed-variant: '#4c4827'
  background: '#fafaf5'
  on-background: '#1a1c19'
  surface-variant: '#e3e3de'
typography:
  display-lg:
    fontFamily: Literata
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Literata
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-md:
    fontFamily: Literata
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.3'
  headline-md-mobile:
    fontFamily: Literata
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.3'
  title-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: '1.5'
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '500'
    lineHeight: '1.4'
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 8px
  container-max: 1280px
  gutter: 24px
  margin-desktop: 64px
  margin-mobile: 20px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 32px
---

## Brand & Style
The design system is built on a "Warm Organic" aesthetic, designed to evoke the sensory experience of a premium coffee house merged with a lush botanical garden. The target audience is discerning coffee enthusiasts and remote professionals who value comfort, quality, and a connection to nature.

The style leverages **Modern Minimalism** with **Tactile** influences. It avoids clinical sharpness in favor of soft, organic edges and high-quality photography that highlights steam, texture, and foliage. The goal is to make the user feel "settled" and unhurried while maintaining the professional efficiency required for a digital ordering platform.

## Colors
The palette is derived from natural elements found in a coffee-botanical environment.

- **Primary (Espresso):** A deep, rich brown used for primary text, branding, and high-emphasis interaction states.
- **Secondary (Forest Green):** A grounded botanical green used for success states, sustainability callouts, and secondary actions.
- **Tertiary (Sage/Latte):** Soft, desaturated tones used for decorative accents and subtle component backgrounds.
- **Neutral (Parchment):** A warm, off-white base that prevents the "digital chill" of pure white, providing a paper-like quality to the UI.
- **Accent (Mocha/Matcha):** Functional variations used for hover states and subtle differentiations in information density.

## Typography
This design system employs a sophisticated pairing of **Literata** for editorial storytelling and **Plus Jakarta Sans** for functional interface elements.

- **Headlines:** Literata provides an artisanal, "printed" feel. It should be used for product names, section headers, and promotional banners.
- **Interface & Body:** Plus Jakarta Sans offers a modern, friendly, and highly readable counterpoint. Its soft curves mirror the organic shapes of the UI.
- **Scale:** On mobile, display sizes are reduced significantly to ensure the serif fonts remain elegant and do not overwhelm the smaller viewport.

## Layout & Spacing
The layout follows a **Fluid Grid** model with generous white space to evoke a calm, "slow coffee" atmosphere.

- **Desktop:** A 12-column grid with a 1280px max-width. Use wide margins (64px) to create an inset, premium feel.
- **Mobile:** A 4-column grid with 20px margins. Content should be stacked vertically with increased padding between different product categories.
- **Rhythm:** Use an 8px base unit. Spacing between cards and list items should be loose (minimum 24px) to avoid visual clutter and maintain the "airy" botanical aesthetic.

## Elevation & Depth
Depth in the design system is communicated through **Tonal Layers** and **Ambient Shadows** rather than stark borders.

- **Surface Levels:** The primary background uses the neutral "Parchment" color. Elevated elements like cards use pure white (#FFFFFF) to pop subtly.
- **Shadows:** Use extremely soft, diffused shadows with a slight warm tint (`rgba(60, 42, 33, 0.08)`) to mimic natural light in a physical space. Shadows should have a large blur radius (20px+) and low offset.
- **Interactive Depth:** On hover, elements should lift slightly (decreasing shadow blur and increasing offset) to provide a tactile "squishy" feel.

## Shapes
The shape language is purposefully **Rounded** to reflect organic forms like coffee beans, leaves, and ceramic mugs.

- **Standard Components:** Buttons and input fields use a 0.5rem (8px) radius.
- **Containers:** Large cards and image containers use `rounded-xl` (1.5rem / 24px) to create a soft, inviting frame for content.
- **Organic Accents:** Use "blob" shapes or asymmetrical radii for background decorative elements to reinforce the botanical theme.

## Components
- **Buttons:** Primary buttons are Espresso with white text. Secondary buttons use a Forest Green ghost style (border only). All buttons feature a 0.5rem radius and a slight scale-down effect on click for a tactile feel.
- **Chips/Tags:** Used for dietary labels (e.g., "Vegan", "Oat Milk"). These should have a Pill-shaped radius (Section 6, Level 3) and use Tertiary (Sage) backgrounds with dark green text.
- **Input Fields:** Soft Parchment backgrounds with a bottom-only border or a very light Espresso outline. Focus states should transition the border to Forest Green.
- **Cards:** Product cards must feature a 24px corner radius. Images within cards should have a slight zoom-in effect on hover. Use the ambient shadow defined in Section 5.
- **Lists:** Order history or menu lists should use generous vertical padding (16px+) and thin, Mocha-tinted separators to maintain a clean, organized look.
- **Selection Controls:** Checkboxes and Radio buttons should be circular or heavily rounded, using Forest Green for the "selected" state to symbolize growth and freshness.