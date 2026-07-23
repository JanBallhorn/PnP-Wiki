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
- [ ] **Alternative Überschriften (Aliase) für Artikel** – ein Artikel kann mehrere Überschriften haben. Wenn ein Alias einen Treffer erzeugt, soll der zugehörige Artikel in der Such-Übersicht angezeigt werden.
- [ ] **Such-Vorschläge per Ajax (Autocomplete)** – während des Tippens Treffer vorschlagen, z. B. „bor" → „Bornland". Sollte auch die Aliase aus dem Punkt oben berücksichtigen.

### Artikel-Verknüpfung *(größere Features)*
- [ ] **Zeitstrahl** – Artikel entlang einer Zeitachse verknüpfen und darstellen.
- [ ] **Familienstammbäume** – Artikel als Personen in einem Stammbaum verknüpfen und darstellen.
- [ ] **Sammel-/Begriffsklärungsseiten** – Seiten, die mehrere ähnliche Artikel unter einem Begriff bündeln und vergleichbar nebeneinanderstellen (analog zu Wikipedias Begriffsklärung, z. B. „Objekt" → „Sache", „Objekt (Grammatik)", „Objekt (Programmierung)"). Offen: wie die Zuordnung entsteht (manuell gepflegt vs. automatisch über gleiche/ähnliche Überschriften – hängt mit den Aliasen zusammen).

### Darstellung / UI
- [x] **Kategorie-Icons in Übersichten** – Icon **und** Name der Kategorie(n) rechtsbündig in allen Artikel-Übersichten (Artikelliste, Suche, Kategorie-Detail, Projekt-Detail, Startseite). Icons liegen außerhalb des Webroots und werden im Controller per `encodeArticleCategoryIcons()` als base64-Data-URI eingebettet. Fallback: Kategorien ohne Icon zeigen nur den Namen.

### Editor
- [ ] **TinyMCE anpassen / eigene Elemente** – die TinyMCE-Editoren anpassen oder ähnliche Elemente selbst nachbauen/einbetten.
