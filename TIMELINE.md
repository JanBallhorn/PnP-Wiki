# Zeitstrahl & Kalender – Umsetzungsplan

Detailplan für das Roadmap-Feature **Zeitstrahl**. Stand: 2026-07-26, noch kein Code geschrieben.

## Ausgangslage

Das Schema enthält bereits `timelines`, `timeline_events`, `calendars`, `calendar_years`, `calendar_months` und `calendar_days`. **Alle sechs Tabellen sind leer, und kein PHP-Codepfad kennt sie** – es gibt kein Model, kein Repository, keinen Controller. Es ist ein Entwurf, kein halbfertiges Feature: die Struktur ist frei anpassbar, ohne Rücksicht auf Bestandsdaten. Ebenfalls verwaist: `lists`/`list_elements` und `maps`.

## Entschieden (2026-07-26)

| Frage | Entscheidung |
|---|---|
| Kalendertiefe | Volles System: eigene Monate, Wochentage, Ären, Schaltregeln |
| Schaltjahre | Ja, einfaches Intervall (alle N Jahre X Extratage), keine gregorianischen Ausnahmen |
| Jahreslänge | Wird aus der Monatsliste **abgeleitet**, nicht eingegeben |
| Monatsbezug eines Ereignisses | `calendar_months.id`, nicht die Monatsnummer |
| Jahr 0 | Pro Kalender einstellbar (`has_year_zero`) |
| Epoche | Neutraler Tages-Offset, in der gespeicherten Tagesnummer enthalten |
| Ären | Mehrere pro Kalender, je vier Bezeichnungen (vor/nach × Name/Kürzel) |
| Kalender pro Ereignis | Frei wählbar – Quellen datieren in ihrem eigenen Kalender |
| Kalender-Verankerung | Über ein Bezugsdatum, nicht über eine rohe Tageszahl |
| Anzeige fremder Kalender | Original **und** Umrechnung in den Leitkalender |
| Unscharfe Daten | Nicht umrechnen, stattdessen Bereich als Hinweis |
| Struktur löschen, die benutzt wird | Sperren mit Hinweis |
| Verknüpfter Artikel gelöscht | Verknüpfung lösen, Titel in die Ereignisüberschrift retten |
| Ereignis-Editor | Spiegelfeld aus `linkField.js`, 1000 Zeichen |
| Uhrzeiten | Nein – später als **zweite** Sortierspalte nachrüstbar |
| Artikel pro Ereignis | Einer (struktureller Bezug), weitere als Links im Text |
| Gleichstände | Unscharfe zuerst, dann Anlagereihenfolge |
| Lange Zeitstrahlen | Alles laden, Jahres-Sprungleiste |

---

## 1. Datenmodell

### Kalender

`calendar_years` wird durch `calendar_eras` **ersetzt**. Die alte Tabelle mischte zwei Ebenen: Ära-Bezeichnungen (pro Ära) und Kalenderstruktur (pro Kalender). Sobald es mehrere Ären pro Kalender gibt, wären die Strukturfelder pro Ära dupliziert und damit per Konstruktion widersprüchlich. `days_per_year`, `months_per_year` und `days_per_month` fallen ganz weg – sie ergeben sich aus der Monatsliste, und mit Schaltjahren kann `days_per_year` gar keinen einzelnen richtigen Wert haben.

```sql
ALTER TABLE `calendars`
    ADD `epoch_offset_days` BIGINT      NOT NULL DEFAULT 0,
    ADD `has_year_zero`     TINYINT(1)  NOT NULL DEFAULT 1,
    ADD `days_per_week`     INT(10)     NOT NULL DEFAULT 7,
    ADD `hours_per_day`     INT(10)     NOT NULL DEFAULT 24,
    -- Verankerung: dasselbe Datum in zwei Kalendern, daraus wird epoch_offset_days berechnet
    ADD `anchor_calendar`   INT(10) DEFAULT NULL,
    ADD `anchor_year`       INT(10) DEFAULT NULL,
    ADD `anchor_month`      INT(10) DEFAULT NULL,
    ADD `anchor_day`        INT(10) DEFAULT NULL,
    ADD `anchor_own_year`   INT(10) DEFAULT NULL,
    ADD `anchor_own_month`  INT(10) DEFAULT NULL,
    ADD `anchor_own_day`    INT(10) DEFAULT NULL;

DROP TABLE `calendar_years`;
CREATE TABLE `calendar_eras` (
    `id`            INT(10) NOT NULL AUTO_INCREMENT,
    `published`     TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    `calendar`      INT(10) NOT NULL,
    `name_after`    VARCHAR(100) NOT NULL,   -- "nach Bosparans Fall"
    `abbrev_after`  VARCHAR(50)  NOT NULL,   -- "BF"
    `name_before`   VARCHAR(100) NOT NULL,   -- "vor Bosparans Fall"
    `abbrev_before` VARCHAR(50)  NOT NULL,   -- "v. BF"
    `year_offset`   INT(10) NOT NULL DEFAULT 0,
    `is_default`    TINYINT(1) NOT NULL DEFAULT 0,
    `sequence`      INT(10) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `calendar` (`calendar`),
    CONSTRAINT `calendar_eras_ibfk_1` FOREIGN KEY (`calendar`)
        REFERENCES `calendars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

`calendar_months` bleibt wie es ist und trägt die Schaltregeln (`gap_year_days`, `gap_year_interval`). **`gap_month_after` bleibt ungenutzt auf NULL**: normale Monate und Zusatzperioden liegen in *einer* Sequenz über `month_number`, `gap_month` markiert nur „zählt nicht als Monat" für die Darstellung. `gap_month_after` wäre eine zweite, konkurrierende Sortierordnung, die man beim Rechnen mit der ersten mischen müsste.

`calendar_days` trägt **Wochentagsnamen** – Praiostag, Rondratag usw. Das ist *nicht* Teil eines Datums: der Wochentag ergibt sich aus der Tagesnummer modulo `days_per_week` und wird nie gespeichert.

### Zeitstrahl und Ereignisse

```sql
ALTER TABLE `timelines`
    ADD `name`         VARCHAR(300)  NOT NULL AFTER `id`,
    ADD `description`  VARCHAR(1000) DEFAULT NULL AFTER `name`,
    ADD `calendar`     INT(10)   NOT NULL AFTER `project`,   -- Leitkalender
    ADD `last_edit`    TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    ADD `last_edit_by` INT(10)   NOT NULL;

ALTER TABLE `timeline_events`
    MODIFY `month`     INT(10) DEFAULT NULL,        -- jetzt calendar_months.id
    MODIFY `day`       INT(10) DEFAULT NULL,        -- Tag im Monat
    MODIFY `text`      MEDIUMTEXT DEFAULT NULL,
    ADD `era`          INT(10) DEFAULT NULL AFTER `calendar`,
    ADD `article`      INT(10) DEFAULT NULL AFTER `timeline`,
    ADD `year_to`      INT(10) DEFAULT NULL AFTER `year`,
    ADD `month_to`     INT(10) DEFAULT NULL AFTER `year_to`,
    ADD `day_to`       INT(10) DEFAULT NULL AFTER `month_to`,
    ADD `sort_key`     BIGINT  NOT NULL DEFAULT 0 AFTER `day_to`,
    ADD `sort_key_end` BIGINT  DEFAULT NULL AFTER `sort_key`,
    ADD KEY `timeline_sort` (`timeline`, `sort_key`),
    ADD KEY `article` (`article`),
    ADD KEY `event_calendar` (`calendar`);
```

Drei Felder verdienen eine Begründung:

- **`calendar` ist nicht redundant.** Der Monat als ID impliziert den Kalender – aber nur bei Datumsangaben *mit* Monat. Bei einem jahresgenauen Ereignis ist diese Spalte die einzige Information darüber, in welchem Kalender das Jahr gilt.
- **`era` hält die Quellentreue.** Datiert eine Quelle in „n. H." statt „BF", soll das Ereignis so angezeigt werden, wie es datiert wurde. Standard ist die Vorgabe-Ära des Kalenders.
- **`year` ist der interne Wert**, era-neutral und mit Jahr 0, nicht die eingegebene Zahl. Nur so sortiert es. Die Umrechnung in die Ära passiert bei Eingabe und Anzeige.

Die Genauigkeit eines Datums braucht keine eigene Spalte – sie ergibt sich daraus, welche Felder NULL sind.

### Fremdschlüssel

Das Schema arbeitet mit echten Constraints (48 vorhanden, 25 davon `CASCADE`), daran halten wir uns:

| Beziehung | Verhalten |
|---|---|
| `timeline_events.timeline` | `CASCADE` – Ereignisse gehören ihrem Zeitstrahl |
| `calendar_eras/_months/_days.calendar` | `CASCADE` |
| `timeline_events.month` → `calendar_months` | Standard (verweigern) + App-Prüfung mit Hinweis |
| `timelines.calendar`, `timeline_events.calendar` | Standard (verweigern) |
| `timeline_events.article` | `SET NULL` – neu im Projekt, aber hier richtig |
| `timeline_events.era` | `SET NULL` |

Regeln, die nicht der Datenbank überlassen werden können: mindestens eine Ära pro Kalender, kein Löschen der letzten. Und beim Löschen eines Artikels wandert dessen Titel in `timeline_events.headline`, falls die leer ist – **vor** dem `SET NULL`, sonst ist die Information weg.

---

## 2. Die Tagesnummer

Jedes Ereignis speichert eine absolute Tagesnummer, berechnet beim Speichern. Nur damit sind Sortierung, Zeitraum-Abfragen, Kalender-Umrechnung und Achsenpositionen möglich.

```
tage(J)              = J * basisTageProJahr + schalttage(J) + epoch_offset_days
schalttage(J)        = Σ  floor(J / intervall_m) * extratage_m      über Monate m mit Schaltregel
tagesNummer(J, M, T) = tage(J) + Σ dauer(m, J) für alle Monate m vor M + (T - 1)
```

`dauer(m, J)` ist die Monatslänge **im Jahr J** – inklusive Extratage, wenn m in J ein Schaltmonat ist. Das ist der Teil, den man leicht übersieht: liegt der Schaltmonat im Juni und das Datum im August, muss der Extratag für dieses konkrete Jahr in der Monatssumme stecken, nicht nur in der Jahresgrenze.

Vier Fallen, die im Code explizit adressiert sein müssen:

1. **Negative Jahre.** `intdiv()` rundet in PHP zur Null hin, gebraucht wird echtes Abrunden: `intdiv(-3, 4) === 0`, aber `(int) floor(-3 / 4) === -1`. Ohne `floor()` verschiebt sich die Achse vor der Epoche um bis zu ein Schaltintervall.
2. **Kein Jahr 0.** Bei `has_year_zero = 0` gibt es kein Jahr 0: intern wird durchgezählt (astronomisch), bei Eingabe und Anzeige umgerechnet. Internes Jahr 0 wird dann als „1 v. BF" angezeigt.
3. **Unscharfe Daten.** Fehlt der Monat, zählt der Jahresanfang; fehlt der Tag, der Monatsanfang.
4. **Der Rückweg wird gebraucht.** Für die Umrechnung in andere Kalender, für Achsenbeschriftungen und für den Wochentag braucht es `fromDayNumber()` – die Umkehrung inklusive Schaltlogik. Das ist die Stelle, die Tests braucht; die Hinrichtung allein genügt nicht.

Die Rechnerei gehört ins `Calendar`-Model, nicht ins Repository: sie arbeitet nur auf den Kalenderregeln und ist damit ohne Datenbank testbar.

### Neuberechnung

Wer einen Monat verlängert oder ein Bezugsdatum verschiebt, verschiebt alle Tagesnummern dieses Kalenders. Zwei Dinge sind dabei leicht falsch zu machen:

- **Die Neuberechnung läuft über `timeline_events.calendar`, nicht über `timelines.calendar`.** Sonst blieben genau die Ereignisse falsch, die aus fremden Kalendern eingetragen wurden – also die, um deren Bequemlichkeit es bei dieser Entscheidung ging.
- **Verankerte Kalender hängen mit dran.** Ist Kalender B über ein Bezugsdatum an A verankert und A ändert seine Struktur, ändert sich Bs Offset. Die Neuberechnung muss der Ankerkette folgen. Zirkuläre Anker (A→B→A) werden beim Speichern abgelehnt.

---

## 3. Zugriffsrechte

`timelines` hat `private`, `editable` und `project` – dasselbe Muster wie Artikel.

- **Sichtbarkeit** über das vorhandene `Controller::getNonPrivate()`. Das erwartet `getPrivate()` und `getAuthorized()` am Model. `Timeline::getAuthorized()` liefert die Autorisierten **des Projekts**, statt eine zweite Rechte-Tabelle einzuführen – ein zweiter Rechte-Ort wäre nur eine Quelle für Abweichungen.
- **Bearbeiten** über eine `userCanEditTimeline()`-Prüfung serverseitig in jeder schreibenden Action, nicht nur im Template.
- **Kalender** sind projektübergreifend (die Tabelle hat kein `project`) – ein Weltkalender ist in mehreren Projekten nutzbar. Sichtbarkeit über `private`/`editable`.
- **Ereignisse** erben alles vom Zeitstrahl.

Ein Fallstrick: ein Ereignis kann auf einen **privaten** Artikel verweisen. Der Artikel-Link muss deshalb durch dieselbe Prüfung wie in den Übersichten, sonst verrät ein öffentlicher Zeitstrahl die Titel privater Artikel.

---

## 4. Klassen und Dateien

| Schicht | Neu |
|---|---|
| Models | `Calendar`, `CalendarEra`, `CalendarMonth`, `CalendarDay`, `Timeline`, `TimelineEvent` |
| Collections | `CalendarCollection`, `CalendarEraCollection`, `CalendarMonthCollection`, `CalendarDayCollection`, `TimelineCollection`, `TimelineEventCollection` |
| Repositories | `CalendarRepository`, `CalendarEraRepository`, `CalendarMonthRepository`, `CalendarDayRepository`, `TimelineRepository`, `TimelineEventRepository` |
| Controllers | `CalendarController`, `TimelineController` |
| Views | `calendarList.twig`, `editCalendar.twig`, `timelineList.twig`, `timeline.twig`, `editTimeline.twig` + Fragmente `newMonthRow.twig`, `newWeekdayRow.twig`, `newEraRow.twig`, `newEvent.twig` |
| JS | `calendarForm.js`, `timelineForm.js` |

Zwei Dinge, die im Bestand schon Fehler verursacht haben und hier vermeidbar sind:

- **`bind_param` bindet per Referenz.** Ergebnisse von Methodenaufrufen sind dort nicht zulässig – erst in Variablen legen, dann binden (siehe `226d81c`).
- **Keine positionsabhängigen Feldnamen.** Beim Steckbrief hängen `rowTopicN`/`rowInfoN` an der Abschnittsposition, was beim Löschen eine Umnummerierung erzwingt und ohne die stillschweigend falsche Daten speichert (siehe `4683530`). Hier stattdessen `name="event[]"`-Arrays mit einem `sequence`-Feld.

### Routen

Der Router bildet `/x/y` auf `XController::y()` ab, ohne Konfiguration.

```
/calendar · /calendar/create · /calendar/save · /calendar/edit?id= · /calendar/delete?id=
/timeline · /timeline?id= · /timeline/edit?id= · /timeline/save · /timeline/delete?id=
```

---

## 5. Oberfläche

**Kalender-Editor** – Kopfdaten (Name, Jahr 0 ja/nein, Wochentage pro Woche, Stunden pro Tag), darunter drei verschiebbare Listen nach dem Muster der Steckbrief-Zeilen (`controlButtons.twig` + `getTemplate()`): Monate (Name, Dauer, Zusatzperiode, Schaltintervall, Extratage), Wochentagsnamen und Ären (vier Bezeichnungen, Jahres-Offset, Vorgabe). Dazu eine abgeleitete Anzeige „ergibt 365 Tage, in Schaltjahren 366" als Kontrolle und das Verankerungs-Formular („dieses Datum entspricht jenem Datum in Kalender X").

**Zeitstrahl-Editor** – Kopfdaten (Name, Beschreibung, Projekt, Leitkalender, privat/editierbar), darunter die Ereignisliste. Pro Ereignis: Überschrift, Text, Kalender, Datum und optionales Bis-Datum, optional ein Artikel.

Das Datum wird **vorzeichenfrei** eingegeben: Zahlenfeld plus Auswahlliste mit den Richtungen aller Ären („1032 · BF ▾"). Niemand tippt „−512", sondern „512 v. BF". Die Monatsliste hängt am gewählten Kalender – alle Monate aller Kalender werden einmal vorgeladen und clientseitig gefiltert, statt pro Zeile nachzuladen.

Für die Artikelauswahl ist die Arbeit schon getan: `linkField.js` bringt die Artikel-Suche über `type=suggest` mit, dieselbe, die im Link-Modal steckt. Dasselbe Spiegelfeld trägt den Ereignistext – gerenderte Links, keine Formatierung, 1000 Zeichen. Der Text geht mit `|raw` raus und muss deshalb serverseitig durch `sanitizeHtml()`.

**Darstellung** – vertikale, nach Jahr gruppierte Liste mit Jahres-Sprungleiste. Zeiträume bekommen eine Klammer über die betroffenen Jahre. Ereignisse aus fremden Kalendern zeigen Original und Umrechnung („12. Eismond 4711 Zwergenkalender ≡ 3. Rondra 1032 BF"); bei unscharfen Angaben statt einer Umrechnung ein Bereich als Hinweis, weil ein jahresgenaues Datum in einem Kalender mit anderem Jahresanfang keinen einzelnen Jahreswert hat. Sortierung: `sort_key`, dann unscharfe zuerst (`month IS NULL DESC`, `day IS NULL DESC`), dann `id`.

**Laden** – Ereignisse in einem Rutsch, Artikeltitel gebündelt in einer Abfrage (`ArticleRepository::headlinesByIds()` kann das fast, es fehlt die Sichtbarkeitsprüfung), Kalender mit Monaten und Ären einmal pro Kalender. Sonst ist das ein N+1 in der Größenordnung, die im Projekt schon mal aufgeräumt wurde.

---

## 6. Phasen

1. **Kalender** – Schema, Models/Collections/Repositories, Tagesnummer plus Umkehrung mit Tests, Editor, Liste, Aventurien als erster Datensatz. Für Nutzer noch unsichtbar.
2. **Zeitstrahl** – anlegen und bearbeiten, Ereignisse mit Datum, Zeitraum, Fremdkalender und optionalem Artikel, vertikale Darstellung, Rechteprüfung.
3. **Verzahnung** – Box „Auf der Zeitachse" auf der Artikelseite (`timeline_events.article = ?`), horizontale Achse als Inline-SVG (Technik steht in `statistics.twig`), Zeitraum-Filter.

Nach Phase 1 und 2 ist das Feature jeweils benutzbar abgeschlossen, Phase 3 ist Ausbau.

## 7. Deployment

Es gibt keinen Migrationsmechanismus. Die Statements aus Abschnitt 1 müssen wie damals `users.test_user` **manuell auf dem Server** eingespielt werden – und zwar *vor* dem Push, sonst laufen die neuen Seiten in einen SQL-Fehler. Sie gehören zusätzlich nach `docker/db-init/`, damit eine frische lokale Umgebung sie mitbekommt, und als Checkliste nach `DEPLOY.md`.
