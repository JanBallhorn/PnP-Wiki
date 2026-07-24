# Roadmap & Feature-Liste

Feature-Übersicht des Pen-&-Paper-Wikis: was umgesetzt ist und was geplant/offen ist.

> Deploy läuft über PhpStorm-SFTP, **nicht** über Git. Ein Commit/Push bedeutet noch keinen Live-Stand.
> Stand: 2026-07-23

---

## ✅ Umgesetzt

### Kategorie-Vorlagen *(live seit 2026-07-23)*
- **Infobox-Vorlagen pro Kategorie** – pro Kategorie definierbare Steckbrief-Felder (Gruppe + Feldname), die beim Anlegen eines Artikels per Checkbox `useTemplate` übernommen werden.
- **Abschnitts-Vorlagen pro Kategorie** – vordefinierte Abschnitts-Überschriften, die beim Anlegen als leere Stub-Abschnitte erzeugt werden.
- Mehrere Kategorien werden zusammengeführt (Duplikate nach Feldname/Überschrift übersprungen); Anwenden ist ein Snapshot beim Anlegen, danach frei editierbar.

### Artikel & Inhalte
- Artikel-Steckbriefe (Infobox) mit verschiebbaren, gruppierbaren Zeilen und Zeilenumbrüchen.
- Quellenangaben für Artikel.
- Link-Einbettung in Texten per Modal; Verlinkung auf noch nicht existierende ("gewünschte") Artikel; leere Artikel werden als Stub gekennzeichnet.
- Löschen von Artikeln und einzelnen Steckbrief-Feldern.
- Bild-Upload mit erhöhtem Größenlimit.

### Projekte & Zusammenarbeit
- Mehrbenutzer-Zusammenarbeit an privaten Projekten/Artikeln (Autorisierungslisten).
- Filter- und Paginierungsfunktion für Projekt- und Kategorieseiten.

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
- [ ] **Zeitstrahl** – Artikel entlang einer Zeitachse verknüpfen und darstellen.
- [ ] **Familienstammbäume** – Artikel als Personen in einem Stammbaum verknüpfen und darstellen.
- [x] **Sammel-/Begriffsklärungsseiten** – *Reine Konvention, kein Code (Entscheidung 2026-07-24).* Eine Begriffsklärung ist einfach ein normaler Artikel mit dem Begriff als Titel (z. B. „Objekt"), der manuell auf die zugehörigen Artikel („Sache", „Objekt (Grammatik)", …) verlinkt – genau wie Wikipedia es macht. Nutzt vorhandene Machinerie: eindeutige Titel → Suche findet die Seite als Top-Treffer, Link-Modal fürs Verlinken, Bearbeiten/Rechte/Aliase inklusive. Zum Sammeln/Erkennen dient eine eigene Kategorie „Begriffsklärung" (Übersichtsseite, Icon, Kategorie-Suche gratis). Automatische Gruppierung wurde verworfen: Aliase sind eindeutig und Wikipedia-Beispiele wie „Sache" lassen sich nur manuell zuordnen.
  - *Optionaler Ausbau (Backlog):* strukturiertes „Verweisliste"-Element im Editor (Artikel + Kurzbeschreibung) statt manuellem Verlinken – gehört zum [Editor-Rework](#editor) bzw. zur Verlinkungs-Hilfe.

### Darstellung / UI
- [x] **Kategorie-Icons in Übersichten** – Icon **und** Name der Kategorie(n) rechtsbündig in allen Artikel-Übersichten (Artikelliste, Suche, Kategorie-Detail, Projekt-Detail, Startseite). Icons liegen außerhalb des Webroots und werden im Controller per `encodeArticleCategoryIcons()` als base64-Data-URI eingebettet. Fallback: Kategorien ohne Icon zeigen nur den Namen.

### Editor
- [ ] **TinyMCE anpassen / eigene Elemente** – die TinyMCE-Editoren anpassen oder ähnliche Elemente selbst nachbauen/einbetten.
- [ ] **Spoiler & Platzhalter-Links überarbeiten** – die Funktionsweise der Spoiler-Funktion und der Platzhalter-Links für noch nicht existierende Artikel neu aufsetzen. Vermutlich Teil des Editor-Reworks.
- [ ] **Verlinkungs-Hilfe beim Schreiben** – beim Verfassen automatisch prüfen, ob zum gerade geschriebenen Wort bereits ein Artikel existiert, und die Verlinkung anbieten. Verwandt mit der Such-Autocomplete und den Aliasen.

### Benachrichtigungen / Engagement
- [ ] **Erinnerungs-Newsletter** – Nutzer per E-Mail erinnern, einen Artikel zu schreiben, wenn sie über einen bestimmten Zeitraum keinen verfasst haben.

### Statistiken
- [x] **Statistiken v1** – eigene Seite `/statistics` mit vier Auswertungen: Autoren-Rangliste (nicht-leere Artikel, aktueller Monat + gesamt, alle Nutzer), Wachstum seit Beginn (kumulierte Linie, mit/ohne leere Artikel, Inline-SVG), Artikel pro Kategorie und meistgenutzte Quellen (je Balken + Top-N-Donut mit „Sonstige"). Neues Flag `users.test_user` blendet Test-Konten und deren Artikel aus. Aggregate zählen private Artikel mit (keine Artikel namentlich). *Ausbau folgt (weitere Kennzahlen, Hover-Tooltips).*
  - *DB:* Spalte `users.test_user TINYINT(1) DEFAULT 0` muss angelegt sein.
