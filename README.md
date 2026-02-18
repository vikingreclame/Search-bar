# Workwear AJAX Search voor WordPress + WooCommerce + Elementor

Deze repository bevat een complete plugin voor een **realtime AJAX zoekbalk** die WooCommerce-producten toont terwijl de bezoeker typt.

## Wat je krijgt
- Live zoekresultaten op basis van producttitel/zoekterm.
- Werkt met WooCommerce producten (alleen `publish` + standaard op voorraad).
- In te voegen via Elementor met een shortcode.
- CSS is bewust "theme-first" gemaakt met CSS variabelen zodat de stijl van je website automatisch wordt overgenomen.

## Installatie
1. Upload de map `workwear-ajax-search` naar `wp-content/plugins/`.
2. Activeer de plugin in WordPress (`Plugins > Geïnstalleerde plugins`).
3. Zorg dat WooCommerce actief is.

## Elementor plaatsing
Je hebt twee makkelijke opties:

### Optie A: Shortcode widget (aanbevolen)
1. Open je pagina/template in Elementor.
2. Sleep de **Shortcode** widget naar de gewenste plek (header, hero, shoppagina, etc).
3. Plaats deze shortcode:

```text
[workwear_ajax_search]
```

### Optie B: Aangepaste instellingen
Gebruik shortcode-attributen:

```text
[workwear_ajax_search placeholder="Zoek bedrijfskleding..." max_results="10" show_price="yes" show_image="yes"]
```

- `placeholder`: tekst in het zoekveld.
- `max_results`: aantal resultaten (1-20).
- `show_price`: `yes/no`.
- `show_image`: `yes/no`.

## Styling / huisstijl overnemen
De zoekbalk gebruikt Elementor/thema variabelen zoals:
- `--e-global-color-primary`
- `--e-global-color-text`
- `--e-global-border-radius`

Daardoor neemt de component direct je huisstijl over. Wil je extra fijnafstemming doen, zet dan Custom CSS op je pagina of in je child theme, bijvoorbeeld:

```css
.wwas-search {
  --wwas-border-radius: 999px;
}
```

## Aanpak voor bedrijfskleding-webshops
Voor betere resultaten bij bedrijfskleding kun je:
1. Producttitels verrijken met termen als beroep/functie ("Horeca", "Bouw", "Logistiek").
2. SKU's consistent invullen voor snelle herkenning.
3. Categorieën logisch opbouwen (werkbroek, werkjas, veiligheidsschoenen).

## Belangrijk
- Deze plugin gebruikt `admin-ajax.php` met nonce-validatie.
- Resultaten sluiten producten met `exclude-from-search` uit.
- In de code is eenvoudig uit te breiden naar categorie/tags/SKU-zoeking op maat.
