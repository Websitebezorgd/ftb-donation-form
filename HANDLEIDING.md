# FTB Donatieformulier — Handleiding

Handleiding voor Maarten en Anne Jan.

---

## Wat is dit?

Een op maat gebouwde WordPress-plugin voor donatieformulieren, specifiek voor For The Better. De plugin vervangt de oude Connect Op Maat plugin (en andere benodigde plugins) en Paytium en is volledig afgestemd op de huisstijl, AVG-richtlijnen en toegankelijkheidseisen (WCAG 2.2).

---

## Wat is er gebouwd?

### Kern
- Meerstaps donatieformulier (stap 1: bedrag en frequentie, stap 2: persoonsgegevens)
- Eenmalige en terugkerende betalingen via **Mollie** (iDEAL, SEPA-incasso)
- Webhook-endpoint dat Mollie-statusupdates verwerkt
- Donatiebeheer in het WordPress-dashboard (overzicht, zoeken, filteren, status wijzigen, verwijderen, CSV-export)
- E-mailnotificaties: bevestigingsmail naar donateur + melding naar beheerder
- Toegangsbeheer: bepaal of en welke redacteuren de plugin mogen beheren

### Technisch
- OOP PHP, WordPress Coding Standards, PHPStan level 5
- Database: eigen tabel `wp_ftb_donations` met donateur- en betalingsgegevens
- Mollie PHP SDK v3, betalingsstroom via redirect + webhook
- REST-endpoint `POST /wp-json/ftb/v1/webhook` — re-fetcht betaling van Mollie voor statusupdate
- Terugkerende betalingen: Mollie-klant aanmaken → eerste betaling met `sequenceType: first` → abonnement na webhook-bevestiging
- Nonces, sanitization, escaping, capability checks op elke admin-pagina
- Bedragen opgeslagen als integer cents (float-precisie vermijden)
- i18n-ready: `.pot`, `.po`, `.mo` en `.l10n.php` in `/languages`
- Shortcode: `[ftb_donation_form]`

### Toegankelijkheid
- WCAG 2.2 conform
- Volledig toetsenbordnavigeerbaar
- Getest met WAVE, Accessibility Checker, NVDA en Windows Narrator
- `aria-required`, `aria-invalid`, `aria-describedby` op alle invoervelden
- Foutenoverzicht met focusbeheer bij validatiefouten
- `fieldset`/`legend` voor radiogroepen

---

## Plugin installeren

### Optie 1 — Via WordPress-dashboard (aanbevolen)
1. Maak een zip van de plugin (zie [Plugin uploaden](#plugin-uploaden))
2. Ga naar **WP-admin → Plugins → Plugin toevoegen → Plugin uploaden**
3. Selecteer de zip → **Nu installeren** → **Plugin activeren**

### Optie 2 — Via FTP/SFTP
1. Upload de map `ftb-donation-form` naar `/wp-content/plugins/`
2. Activeer de plugin via **WP-admin → Plugins**

---

## Plugin uploaden

Een nieuwe versie van de plugin wordt automatisch gebuild via GitHub Actions. Dit zorgt voor een schone zip zonder ontwikkelbestanden, én met de Mollie PHP SDK erin.

### Stappen

1. Zorg dat alle wijzigingen naar GitHub zijn gepusht
2. Ga naar de GitHub-repository → tabblad **Actions** → workflow **Build & Release Plugin**
3. Klik op **Run workflow** → voer het versienummer in (bijv. `1.0.1`) → klik **Run workflow**
4. Na een minuut of twee staat de release klaar onder **Releases** (rechterkant van de repository-startpagina)
5. Download `ftb-donation-form.zip` uit de release

### Installeren op een WordPress-site

1. Ga naar **WP-admin → Plugins → Plugin toevoegen → Plugin uploaden**
2. Selecteer de gedownloade zip → **Nu installeren** → **Plugin activeren**

> Bij een update van een bestaande installatie: deactiveer de plugin eerst, verwijder hem, en installeer de nieuwe versie. Donaties en instellingen blijven bewaard, **tenzij** de optie *Alle gegevens wissen bij verwijderen* aan staat (zie [Toegang en beheer](#toegang-en-beheer)). Controleer dit vóór je de plugin verwijdert.

---

## Instellingen configureren

Ga naar **WP-admin → Donatieformulier → Instellingen**.

### Mollie
| Instelling | Uitleg |
|---|---|
| API sleutel | Vind je via Mollie-dashboard → Ontwikkelaars → API-sleutels. Gebruik de live sleutel voor echte betalingen. |
| Testmodus | Inschakelen tijdens het testen — gebruik dan de test-API-sleutel. |

> De sleutel wordt direct gevalideerd bij opslaan. Als de sleutel ongeldig is, verschijnt een foutmelding.

### Formulier
| Instelling | Uitleg |
|---|---|
| Titel | De koptekst boven het formulier. Standaard: "Doneer nu". |
| Terugkerende betalingen | Schakel in om maandelijkse en jaarlijkse donaties toe te staan. Vereist SEPA-incasso in het Mollie-dashboard. |

### Bedragopties
- Stel maximaal drie vaste bedragen in (minimaal €1 per optie)
- Schakel "Eigen bedrag toestaan" in als donateurs een vrij bedrag mogen invullen
- Stel het minimumbedrag in voor eigen bedragen

> Je moet minimaal één bedragoptie inschakelen.

### Formuliervelden
Naam en e-mailadres zijn altijd verplicht. Optionele velden:
- Telefoonnummer
- Straat + huisnummer
- Postcode + plaats

### Privacyverklaring
Voer de URL in naar de privacyverklaring van je website. De plugin toont een AVG-herinnering als dit veld leeg is. Als er een URL is ingevuld, verschijnt er een kant-en-klare privacyverklaringstekst die je kunt kopiëren.

### Na betaling
Kies wat er gebeurt nadat de donateur succesvol heeft betaald:
- **Bedankbericht** — toon een tekst op de formualierpagina (aanpasbaar)
- **Doorsturen naar een pagina** — voer een URL in

### E-mailnotificaties
| Instelling | Uitleg |
|---|---|
| Melding bij nieuwe donatie | Stuur automatisch een e-mail naar het ingestelde e-mailadres bij elke geslaagde betaling. Onderwerp en inhoud zijn vast. |
| Bevestigingsmail naar donateur | Stuur de donateur een bevestiging na geslaagde betaling. |
| E-mailadres voor meldingen en afzender | Ontvanger van de melding bij nieuwe donatie én afzender van de bevestigingsmail. Alleen zichtbaar als minstens één van de twee e-mails aan staat. |

De bevestigingsmail naar de donateur heeft een aanpasbaar onderwerp en berichttekst, met een live voorbeeld in de instellingen. De interne melding heeft een vaste inhoud.

**Vul bij voorkeur het e-mailadres van de klant in**: donateurs die op de bevestigingsmail antwoorden, mailen naar dit adres. Blijft het veld leeg, dan gebruikt de plugin het beheerdersadres van WordPress (**Instellingen → Algemeen**) als ontvanger en afzender. Een gele melding onder het veld laat zien welk adres dat is.

### Stijl
| Variant | Uitleg |
|---|---|
| Kaart | Formulier in een wit vlak met schaduw en afgeronde hoeken, in de stijl van de plugin. |
| Vlak | Zoals Kaart, maar zonder schaduw en afgeronde hoeken. |
| Thema | Het formulier neemt de stijl van het thema over: koppen, labels, invoervelden, links en knoppen. Waar het thema niets voor instelt, gebruikt de plugin zijn eigen stijl als fallback. De bedrag- en frequentiekeuzes blijven klikbare vakken in de tekstkleur van het thema, en de stappen staan als tekst ("Stap 1 van 2: Jouw donatie"). |

De **primaire kleur** bepaalt bij Kaart en Vlak de accentkleur. Bij Thema is het de fallback-kleur: die wordt alleen gebruikt als het thema zelf geen kleur geeft, bijvoorbeeld aan de knoppen. Focusranden en foutmeldingen zijn bij alle varianten van de plugin, vanwege de toegankelijkheid.

Bij **Kaart en Vlak** wordt de primaire kleur afgedwongen op de knoppen en het gekozen bedrag of de gekozen frequentie, ook als het thema eigen knopstijlen heeft.

**Contrast wordt automatisch bewaakt**: de plugin kiest zelf witte of zwarte tekst op de primaire kleur, en maakt de kleur donkerder waar hij als tekst op wit staat (de knop "Vorige" en de link naar de privacyverklaring). Daardoor is het contrast altijd minimaal 4,5:1 (WCAG). Een lichte kleur geeft dus zwarte tekst op de knop. Bij Thema bepaalt het thema de knoppen; let daar zelf op het contrast met de achtergrond van de pagina.

---

## Donaties beheren

Ga naar **WP-admin → Donatieformulier → Donaties**.

### Overzicht
Het overzicht toont alle donaties met: naam, e-mailadres, telefoonnummer, adres, bedrag, frequentie, betaalstatus en datum.

**Filteren en zoeken:**
- Gebruik de statustabs bovenaan (Alle / In afwachting / Betaald / Mislukt / Geannuleerd)
- Gebruik het zoekveld rechtsboven om te zoeken op naam of e-mailadres
- Kolommen zijn sorteerbaar via de kolomkoppen

### Status wijzigen
Klik op **Status wijzigen** onder de naam van een donateur om de betaalstatus handmatig aan te passen. Dit is handig als Mollie een statusupdate heeft gemist.

### Verwijderen
- **Individueel:** klik op **Verwijderen** onder de naam
- **Bulk:** vink meerdere donaties aan → selecteer "Verwijderen" in het bulkmenu → **Toepassen**

### CSV-exporteren
Klik op de knop **Exporteer CSV** rechtsboven op de donatiespagina. Het bestand is UTF-8 met BOM (correct leesbaar in Excel).

**Apostrof voor sommige waarden**: begint een door de donateur ingevulde waarde met `=`, `+`, `-` of `@` (bijvoorbeeld een telefoonnummer als `+31 6 12345678`), dan staat er in het exportbestand een apostrof voor: `'+31 6 12345678`. Dat is een beveiliging: zonder apostrof zou Excel zo'n waarde als formule uitvoeren, en via het openbare formulier kan iemand bewust een kwaadaardige formule invullen. In de donaties in WordPress zelf staat de waarde gewoon zonder apostrof.

Zie je `########` in een kolom, dan is die kolom te smal; maak hem breder.

---

## Toegang en beheer

Ga naar **WP-admin → Donatieformulier → Instellingen** (sectie Toegang en beheer, onderaan). Deze sectie is alleen zichtbaar voor beheerders.

### Formulierbeheerders

> Dit blok is alleen zichtbaar als er gebruikers met de redacteursrol aanwezig zijn op de site. Beheerders hebben altijd toegang.

| Optie | Uitleg |
|---|---|
| Alleen beheerders | Geen redacteuren hebben toegang (**standaard bij een nieuwe installatie**) |
| Alle redacteuren | Alle gebruikers met de redacteursrol hebben toegang |
| Specifieke redacteuren | Alleen aangevinkte redacteuren hebben toegang (zichtbaar bij 2+ redacteuren) |

De gekozen toegang blijft gelden als de plugin wordt gedeactiveerd en weer geactiveerd, of opnieuw wordt geïnstalleerd zonder de gegevens te wissen. Bestaande sites houden hun huidige keuze bij een update. Een specifieke redacteur die later een andere rol krijgt (bijvoorbeeld Auteur), verliest de toegang automatisch zodra de keuze opnieuw wordt opgeslagen of de plugin opnieuw wordt geactiveerd.

### Bij verwijderen van de plugin

Standaard blijven alle donaties en instellingen bewaard als de plugin wordt verwijderd. Deactiveren wist nooit gegevens.

Zet de schakelaar **Alle gegevens wissen bij verwijderen** aan als alles definitief gewist moet worden zodra de plugin wordt verwijderd (via het dashboard, wp-cli of een extern beheerdashboard). Er verschijnt dan een rode waarschuwing. Dit kan niet ongedaan worden gemaakt — exporteer eerst de donaties als je ze wilt bewaren.

---

## Shortcode

Plaats het formulier op een pagina met:

```
[ftb_donation_form]
```

---

## Vertalingen bijwerken

Na aanpassingen in de PHP-bronbestanden:

> De `wp`-commando's draai je in Local via rechtsklik op de site → **Open site shell**. Die opent in `app\public`; ga eerst naar de plugin-map met `cd wp-content\plugins\ftb-donation-form`.

1. Regenereer `.pot` (vanuit de plugin-map):
```
wp i18n make-pot . languages/ftb-donation-form.pot --domain=ftb-donation-form --exclude=vendor
```
2. Open `ftb-donation-form-en_US.po` in Poedit → **Catalogus → Bijwerken vanuit POT-bestand** → vertaal nieuwe strings → Opslaan
3. Verwijder verouderde strings: **Vertalingen → Verouderde vertalingen verwijderen** → Opslaan
4. Regenereer `.l10n.php`:
```
wp i18n make-php languages/ftb-donation-form-en_US.po
```
5. Voeg de ABSPATH-guard toe bovenaan het gegenereerde `.l10n.php` (direct na `<?php`):
```php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
```

---

## Nog te testen (live omgeving vereist)

De volgende functionaliteit werkt alleen op een server met HTTPS — Mollie kan geen lokale omgeving bereiken:

- [ ] Eenmalige betaling: formulier → Mollie-checkout → webhook → bedankbericht/doorstuur-URL
- [ ] Terugkerende betaling: mandaat aanmaken, abonnement aanmaken, vervolgbetalingen verwerken
- [ ] Bevestigingsmail naar donateur na geslaagde betaling
- [ ] Beheerdersmelding bij nieuwe donatie

---

## Voor ontwikkelaars

Meer technische details, open vragen en nog te nemen beslissingen staan in `README.md` in de plugin-map.
