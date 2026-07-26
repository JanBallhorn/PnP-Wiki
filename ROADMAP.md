# Roadmap & Feature-Liste

Feature-Übersicht des Pen-&-Paper-Wikis: was umgesetzt ist und was geplant/offen ist.

> Deploy läuft über **Git Push-to-Deploy**: ein Push auf das `deploy`-Remote geht direkt live
> (Bare-Repo + `post-receive`-Hook auf dem Hetzner-Server, siehe `DEPLOY.md`). Ein Commit allein
> ändert nichts, ein Push auf `deploy` schon. Details zur lokalen Umgebung in `DOCKER.md`.
> Stand: 2026-07-26

---

## ✅ Umgesetzt

### Kategorie-Vorlagen *(live seit 2026-07-23)*
- **Infobox-Vorlagen pro Kategorie** – pro Kategorie definierbare Steckbrief-Felder (Gruppe + Feldname), die beim Anlegen eines Artikels per Checkbox `useTemplate` übernommen werden.
- **Abschnitts-Vorlagen pro Kategorie** – vordefinierte Abschnitts-Überschriften, die beim Anlegen als leere Stub-Abschnitte erzeugt werden.
- Mehrere Kategorien werden zusammengeführt (Duplikate nach Feldname/Überschrift übersprungen); Anwenden ist ein Snapshot beim Anlegen, danach frei editierbar.

### Artikel & Inhalte
- Artikel-Steckbriefe (Infobox) mit verschiebbaren, gruppierbaren Zeilen und Zeilenumbrüchen.
- Quellenangaben für Artikel.
- Verlinkung auf noch nicht existierende ("gewünschte") Artikel; leere Artikel werden als Stub gekennzeichnet.
- Löschen von Artikeln und einzelnen Steckbrief-Feldern.
- Bild-Upload mit erhöhtem Größenlimit.
- **Interne Links sind umgebungsunabhängig** *(2026-07-26)* – Links auf die eigene Domain werden beim Ausgeben relativ gemacht, fremde Domains bleiben absolut. Lokal bleibt man damit lokal, live bleibt man live.

### Projekte & Zusammenarbeit
- Mehrbenutzer-Zusammenarbeit an privaten Projekten/Artikeln (Autorisierungslisten).
- Filter- und Paginierungsfunktion für Projekt- und Kategorieseiten.

### Infrastruktur & Deployment *(2026-07-25/26)*
- **Git Push-to-Deploy** – Bare-Repo mit `post-receive`-Hook auf dem Hetzner-Server (SSH, Port 222); der Hook aktualisiert den Arbeitsbaum und erneuert den Composer-Autoload, fehlertolerant. Ersetzt das frühere PhpStorm-SFTP-Deployment. Anleitung: `DEPLOY.md`.
- **Lokale Docker-Entwicklungsumgebung** – Web (Port 8090), MariaDB (3307) und phpMyAdmin (8091); Projekt komplett gemountet, damit `DOCUMENT_ROOT` und die Pfade zu `.env`/`externalImages` der Produktion entsprechen. Inkl. OPcache, `mod_headers` und `no-store` für Assets sowie `display_errors` lokal über die `php.ini`. Anleitung: `DOCKER.md`.
- Abhängigkeiten auf PHP 8.4 aktualisiert.

### Sicherheit *(Audit-Zyklus abgeschlossen 2026-07-18)*
- SQL-Injection geschlossen (Suche, Ajax-Feld-/Tabellennamen, `ORDER BY`-Filter-Whitelist).
- Stored XSS in Artikeltext/Feldern serverseitig sanitisiert.
- Broken Access Control behoben (private Artikel & Projekte, Profil-Speichern, editable/Eigentümer-Prüfung serverseitig).
- Passwort-Hashing von unsalted SHA-256 auf `password_hash()` umgestellt.
- JWT-Signaturprüfung; JWT-Secret & Zugangsdaten in `.env` ausgelagert; `.htaccess` gehärtet.
- Datei-Upload-Validierung (echte Inhalts-/Typprüfung) und Path-Traversal über Artikel-Titel behoben.
- Login-Rate-Limiting (5 Versuche/15 min); Login-Cookie mit `SameSite=Lax`.

### Performance
- N+1-Queries beim Laden von Artikellisten und Projektbäumen behoben.
- Identity-Cache für User-/Kategorie-/Projekt-Lookups nach ID.

---

## 🚧 Geplant / Offen

### Angedacht, bewusst zurückgestellt
- **Vollständiger CSRF-Token-Schutz** über alle Formulare. Zurückgestellt (2026-07-18): `SameSite=Lax` gilt als ausreichend; voller Rollout würde ~20 Templates + JS betreffen. Nur bei Bedarf angehen.

### Suche
- [x] **Alternative Überschriften (Aliase) für Artikel** – Eingabe, Speicherung (`article_alt_headline`) und Suche waren bereits vorhanden. Ergänzt: Suchergebnisse zeigen bei Alias-Treffern „gefunden über: {Alias}", und der Artikel lässt sich unter dem Alias aufrufen (`?alias=…`, serverseitig validiert) mit Hinweis auf den Originaltitel.
- [x] **Such-Vorschläge per Ajax (Autocomplete)** – Dropdown ab 2 Zeichen (`type=suggest` in `Ajax.php`, `ArticleRepository::suggest()`), Präfix-Match auf Titel + Alias, private Artikel gefiltert. Auswahl springt direkt zum Artikel (Alias-Treffer inkl. `&alias=…`). Frontend: `searchSuggest.js`, global in `base.twig`.

### Artikel-Verknüpfung *(größere Features)*
- [ ] **Zeitstrahl** – Artikel entlang einer Zeitachse verknüpfen und darstellen. **Detailplan: `TIMELINE.md`** (Stand 2026-07-26, noch kein Code). Entschieden: volles Kalendersystem mit selbst definierten Monaten und Schaltregeln, Artikel-Bezug pro Ereignis optional, Zeiträume von Anfang an. Schema-Skelett (`timelines`, `timeline_events`, `calendars`, `calendar_*`) liegt bereits im Dump, ist aber leer und ohne jeden Codepfad.
- [ ] **Familienstammbäume** – Artikel als Personen in einem Stammbaum verknüpfen und darstellen.
- [x] **Sammel-/Begriffsklärungsseiten** – *Reine Konvention, kein Code (Entscheidung 2026-07-24).* Eine Begriffsklärung ist einfach ein normaler Artikel mit dem Begriff als Titel (z. B. „Objekt"), der manuell auf die zugehörigen Artikel („Sache", „Objekt (Grammatik)", …) verlinkt – genau wie Wikipedia es macht. Nutzt vorhandene Machinerie: eindeutige Titel → Suche findet die Seite als Top-Treffer, Link-Modal fürs Verlinken, Bearbeiten/Rechte/Aliase inklusive. Zum Sammeln/Erkennen dient eine eigene Kategorie „Begriffsklärung" (Übersichtsseite, Icon, Kategorie-Suche gratis). Automatische Gruppierung wurde verworfen: Aliase sind eindeutig und Wikipedia-Beispiele wie „Sache" lassen sich nur manuell zuordnen.

### Darstellung / UI
- [x] **Kategorie-Icons in Übersichten** – Icon **und** Name der Kategorie(n) rechtsbündig in allen Artikel-Übersichten (Artikelliste, Suche, Kategorie-Detail, Projekt-Detail, Startseite). Icons liegen außerhalb des Webroots und werden im Controller per `encodeArticleCategoryIcons()` als base64-Data-URI eingebettet. Fallback: Kategorien ohne Icon zeigen nur den Namen.

### Editor
- [x] **TinyMCE anpassen / eigene Elemente** *(2026-07-26)* – eigene Toolbar-Gruppe „Wiki" mit Spoiler, Spoiler-Block, Artikel-Link, Wunschartikel und Auto-Link; Skin folgt dem Dark-/Light-Umschalter der Seite (Editor wird dafür neu aufgebaut, weil ein Skin nicht live tauschbar ist); Überschriften-Auswahl ohne h1/h2, weil die dem Artikeltitel und den Abschnitten gehören.
- [x] **Spoiler & Platzhalter-Links überarbeiten** *(2026-07-26)* – Spoiler sind echte Elemente (`span.spoiler` inline, `div.spoiler` über mehrere Absätze) statt der alten `||`-Marker; Wunschartikel-Links tragen `.createNewArticle` und zeigen im Editor wie in der Ansicht auf `/article/create?name=…`.
- [x] **Verlinkungs-Hilfe beim Schreiben** *(2026-07-26)* – Auto-Link-Button prüft den Text gegen alle bekannten Titel und Aliase (`type=linkscan`) und verlinkt das erste Vorkommen je Artikel: ganzwortgenau, nie innerhalb eines bestehenden Links, nie der Artikel selbst. Der eingefügte Link trägt die echte Ziel-Überschrift als `title`, sodass ein Alias-Treffer beim Darüberfahren sein Ziel zeigt.
  - *Optionaler Ausbau (Backlog):* Vorschlag live beim Tippen statt auf Knopfdruck.
- [x] **Link-Felder zeigen gerenderte Links** *(2026-07-26)* – die Steckbrief-Felder und die Bildunterschriften zeigten das rohe `<a href=…>`-Markup im Eingabefeld. Jetzt spiegelt ein `contenteditable` den Inhalt mit gerendertem Link, während das versteckte Originalfeld unverändert den HTML-Wert zum Formular trägt (`linkField.js`). Das Link-Modal kann Wiki-Artikel suchen (`type=suggest`) und setzt `href` samt `title`; bestehende Links lassen sich anklicken, bearbeiten und entfernen. Der Zeichenzähler zählt die HTML-Länge gegen die `varchar`-Grenzen und sperrt bei Überschreitung, weil `maxlength` an einem `contenteditable` nicht greift.
- [ ] **Verweisliste-Element** – strukturiertes Editor-Element „Artikel + Kurzbeschreibung" statt manuellem Verlinken, vor allem für Begriffsklärungsseiten.

### Benachrichtigungen / Engagement
- [ ] **Erinnerungs-Newsletter** – Nutzer per E-Mail erinnern, einen Artikel zu schreiben, wenn sie über einen bestimmten Zeitraum keinen verfasst haben.

### Statistiken
- [x] **Statistiken v1** *(live)* – eigene Seite `/statistics` mit vier Auswertungen: Autoren-Rangliste (nicht-leere Artikel, aktueller Monat + gesamt, alle Nutzer), Wachstum seit Beginn (kumulierte Linie, mit/ohne leere Artikel, Inline-SVG), Artikel pro Kategorie und meistgenutzte Quellen (je Balken + Top-N-Donut mit „Sonstige"). Das Flag `users.test_user` blendet Test-Konten und deren Artikel aus (Spalte auf dem Server angelegt). Aggregate zählen private Artikel mit (keine Artikel namentlich).
- [ ] **Statistiken-Ausbau** – weitere Kennzahlen und Hover-Tooltips in den Diagrammen.
