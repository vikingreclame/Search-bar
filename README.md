# Workwear AJAX Search voor WordPress + WooCommerce + Elementor

Deze repository is nu direct uploadbaar als WordPress-plugin (ZIP), zodat je **geen fout “geen geldige plugin”** meer krijgt.

## Waarom die fout ontstond
WordPress accepteert een plugin-zip alleen als in de **hoofdmap van de zip** een pluginbestand staat met geldige plugin-header. Deze is nu toegevoegd als:

- `workwear-ajax-search.php` (in de root)

## Wat je krijgt
- Realtime AJAX zoekresultaten op WooCommerce producten terwijl de bezoeker typt.
- Werkt voor bezoekers en ingelogde gebruikers.
- In te voegen in Elementor via shortcode.
- Styling neemt automatisch huisstijl over via Elementor/thema CSS-variabelen.

## Installatie (2 manieren)

### Manier A — via WordPress ZIP upload (aanbevolen)
1. Maak van deze projectmap een zipbestand.
2. Ga in WordPress naar **Plugins → Nieuwe plugin → Plugin uploaden**.
3. Upload de zip.
4. Activeer **Workwear AJAX Product Search**.
5. Zorg dat WooCommerce actief is.

### Manier B — via FTP
1. Upload de volledige map naar `wp-content/plugins/`.
2. Controleer dat dit bestand bestaat:
   - `wp-content/plugins/<mapnaam>/workwear-ajax-search.php`
3. Activeer de plugin in WordPress.

## Elementor plaatsing

### Optie A: Shortcode widget
1. Open je pagina/template in Elementor.
2. Sleep de **Shortcode** widget naar de juiste positie.
3. Plaats:

```text
[workwear_ajax_search]
```

### Optie B: Met instellingen

```text
[workwear_ajax_search placeholder="Zoek bedrijfskleding..." max_results="10" show_price="yes" show_image="yes"]
```

- `placeholder`: tekst in het zoekveld
- `max_results`: 1-20
- `show_price`: `yes/no`
- `show_image`: `yes/no`

## Styling / huisstijl
De CSS gebruikt o.a.:
- `--e-global-color-primary`
- `--e-global-color-text`
- `--e-global-border-radius`

Daarmee sluit de zoekbalk automatisch aan op je bestaande design.

## Troubleshooting
Als je nog steeds “geen geldige plugin” ziet:
1. Open de zip lokaal en check of `workwear-ajax-search.php` direct in de root staat (niet 2 mappen diep).
2. Verwijder oude foutieve versies van dezelfde pluginmap op de server.
3. Upload opnieuw.
