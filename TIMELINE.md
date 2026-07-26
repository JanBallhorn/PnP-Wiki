# Zeitstrahl & Kalender – Umsetzungsplan

Detailplan für das Roadmap-Feature **Zeitstrahl**. Stand: 2026-07-26, noch kein Code geschrieben.

## Entschieden (2026-07-26)

- **Volles Kalendersystem** – eigene Kalender mit selbst definierten Monaten, Wochentagen und Schaltregeln, nicht nur ein festverdrahteter aventurischer Kalender.
- **Artikel-Bezug optional** – ein Ereignis hat eigene Überschrift und eigenen Text und *kann* zusätzlich auf einen Artikel verweisen.
- **Zeiträume von Anfang an** – jedes Ereignis kann ein Zeitpunkt oder eine Spanne sein.

---

## 1. Bestandsaufnahme

Das Schema enthält bereits `timelines`, `timeline_events`, `calendars`, `calendar_years`, `calendar_months` und `calendar_days`. **Alle sechs Tabellen sind leer, und kein PHP-Codepfad kennt sie** – es gibt kein Model, kein Repository, keinen Controller. Es ist ein Entwurf, kein halbfertiges Feature: wir können die Struktur frei anpassen, ohne Rücksicht auf Bestandsdaten.

Die Namensgebung ist teils irreführend: **`calendar_years` enthält keine einzelnen Jahre**, sondern die Regeln, wie ein Jahr aufgebaut ist (`days_per_year`, `months_per_year`, `days_per_week`, `hours_per_day`, `gap_years`) plus die Bezeichnung der Ära (`year_definition` mit dem Kommentar *„like Hal"*, `year_definition_abbrevation`). Ich behandle sie im Folgenden als **Jahresdefinition** – eine Zeile pro Kalender.

`calendars.year_0BF` ist ein Epochen-Offset: welches Jahr dieses Kalenders dem Jahr 0 BF entspricht. Damit lassen sich verschiedene Kalender auf eine gemeinsame Achse legen.

## 2. Nötige Schema-Änderungen

Vier Lücken im Entwurf:

1. **`timelines` hat keinen Namen.** Ohne Titel lässt sich ein Zeitstrahl nicht benennen oder in einer Liste anzeigen.
2. **`timeline_events` hat keine Artikel-Verknüpfung** – genau die Anforderung aus der Roadmap.
3. **Keine Zeiträume** – nur ein Datum pro Ereignis.
4. **Keine sortierbare Größe.** Nach `(year, month, day)` zu sortieren funktioniert nur innerhalb eines Kalenders ohne Schaltregeln. Mit Schaltmonaten und über Kalendergrenzen hinweg braucht es eine absolute Tagesnummer (siehe Abschnitt 3).

Dazu kommt: `month` und `day` müssen **nullable** werden, damit ein Ereignis auch nur mit Jahresangabe („1032 BF") oder nur mit Monat („im Praios 1032 BF") erfasst werden kann. Die Genauigkeit ergibt sich dann daraus, welche Felder gesetzt sind – dafür braucht es keine eigene Spalte.

```sql
-- Zeitstrahl benennbar machen und an einen Kalender binden
ALTER TABLE `timelines`
    ADD `name`        VARCHAR(300) NOT NULL AFTER `id`,
    ADD `description` VARCHAR(1000) DEFAULT NULL AFTER `name`,
    ADD `calendar`    INT(10) NOT NULL AFTER `project`,
    ADD `last_edit`      TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    ADD `last_edit_by`   INT(10) NOT NULL;

-- Ereignisse: Artikel-Bezug, unscharfe Datumsangaben, Zeiträume, Sortierung
ALTER TABLE `timeline_events`
    MODIFY `month` INT(10) DEFAULT NULL,
    MODIFY `day`   INT(10) DEFAULT NULL,
    ADD `article`      INT(10) DEFAULT NULL AFTER `timeline`,
    ADD `year_to`      INT(10) DEFAULT NULL AFTER `year`,
    ADD `month_to`     INT(10) DEFAULT NULL AFTER `year_to`,
    ADD `day_to`       INT(10) DEFAULT NULL AFTER `month_to`,
    ADD `sort_key`     BIGINT  NOT NULL DEFAULT 0 AFTER `day_to`,
    ADD `sort_key_end` BIGINT  DEFAULT NULL AFTER `sort_key`,
    ADD KEY `timeline_sort` (`timeline`, `sort_key`),
    ADD KEY `article` (`article`);
```

`timeline_events.calendar` wird damit redundant, weil der Kalender am Zeitstrahl hängt (ein Zeitstrahl = ein Kalender). Die Spalte bleibt vorerst stehen und wird mit dem Kalender des Zeitstrahls gefüllt – sie kostet nichts und hält die Tür für gemischte Zeitstrahlen offen. Vorteil der Bindung am Zeitstrahl: die Sortierung ist ohne Umrechnung eindeutig, und der Ereignis-Editor kennt die Monatsnamen ohne Rückfrage.

> **Deployment:** Es gibt keinen Migrationsmechanismus. Diese Statements müssen wie damals `users.test_user` **manuell auf dem Server** eingespielt werden (phpMyAdmin oder MySQL-CLI) – am besten *vor* dem Push, sonst laufen die neuen Seiten in einen SQL-Fehler. Die Statements gehören zusätzlich in `docker/db-init/`, damit eine frische lokale Umgebung sie mitbekommt, und als Checkliste in `DEPLOY.md`.

## 3. Die Tagesnummer – Kern der ganzen Sache

Jedes Ereignis speichert eine absolute Tagesnummer (`sort_key`), berechnet beim Speichern. Nur damit sind Sortierung, „was passierte zwischen X und Y" und die Positionierung auf einer gezeichneten Achse möglich.

Grundformel, bezogen auf Jahr 0 des Kalenders:

```
tage(J)         = J * basisTageProJahr + schalttage(J)
schalttage(J)   = Σ  floor(J / intervall_m) * schalttage_m     über alle Monate m mit Schaltregel
tagesNummer(J, M, T) = tage(J) + Σ dauer(m) für alle Monate m vor M + (T - 1)
```

Drei Fallen, die im Code explizit adressiert werden müssen:

- **Negative Jahre.** Ein Datum „vor Bosparans Fall" ist ein negatives Jahr. `intdiv()` in PHP rundet zur Null hin, gebraucht wird echtes Abrunden: `intdiv(-3, 4) === 0`, aber `(int) floor(-3 / 4) === -1`. Für Jahre vor der Epoche muss `(int) floor()` verwendet werden, sonst verschiebt sich die Achse um bis zu ein Schaltintervall.
- **Unscharfe Daten.** Fehlt der Monat, zählt der Jahresanfang; fehlt der Tag, der Monatsanfang. Damit sortiert „1032 BF" vor „1. Praios 1032 BF" gleichauf – für die Darstellung reicht das, muss aber dokumentiert sein.
- **Kalenderänderungen invalidieren alle Tagesnummern.** Wer nachträglich einen Monat verlängert oder einschiebt, verschiebt jedes Ereignis dieses Kalenders. `CalendarRepository::save()` muss deshalb `TimelineEventRepository::recalculateSortKeys($calendar)` nachziehen. Ohne das laufen Kalender und Zeitstrahl still auseinander – das ist der wahrscheinlichste Bug in diesem Feature.

Die Berechnung gehört ins `Calendar`-Model (`toDayNumber(?int $y, ?int $m, ?int $d): int`), nicht ins Repository: sie ist reine Rechnerei auf den Kalenderregeln und dadurch ohne Datenbank testbar.

**Aventurien als Referenzdatensatz** (nicht hart verdrahtet, sondern als anlegbarer Kalender): 12 Monate à 30 Tage, plus die 5 namenlosen Tage als 13. Monat mit `gap_month = 1` und `month_duration_in_days = 5`; 7 Wochentage (Praiostag … Rohalstag); Ära-Kürzel „BF"; `year_0BF = 0`.

## 4. Zugriffsrechte

`timelines` hat schon `private`, `editable` und `project` – dasselbe Muster wie Artikel. Damit gilt:

- **Sichtbarkeit** über das vorhandene `Controller::getNonPrivate()`. Das erwartet auf dem Model `getPrivate()` und `getAuthorized()`. Statt einer neuen Tabelle `timeline_authorized` liefert `Timeline::getAuthorized()` die Autorisierten **des Projekts** – ein Zeitstrahl gehört ohnehin zu einem Projekt, und ein zweiter Rechte-Ort wäre nur eine Quelle für Abweichungen. Eigene Listen pro Zeitstrahl kann man später nachziehen.
- **Bearbeiten** analog zu `userCanEditArticle()`: eine `userCanEditTimeline()`-Prüfung serverseitig in jeder schreibenden Action, nicht nur im Template.
- **Kalender** sind projektübergreifend (die Tabelle hat kein `project`, aber `private`/`editable`) – ein Weltkalender ist in mehreren Projekten nutzbar. Sichtbarkeit über dieselben zwei Flags.

Ereignisse erben alles vom Zeitstrahl, brauchen also keine eigenen Flags. **Aber:** ein Ereignis kann auf einen *privaten* Artikel verweisen. Beim Rendern muss der Artikel-Link deshalb durch dieselbe Prüfung laufen wie in den Übersichten, sonst leakt ein öffentlicher Zeitstrahl die Titel privater Artikel.

## 5. Klassen und Dateien

Nach den Konventionen im Projekt (Model + Collection mit `CollectionTrait` + Repository mit `extends Repository implements RepositoryInterface`):

| Schicht | Neu |
|---|---|
| Models | `Calendar`, `CalendarYear`, `CalendarMonth`, `CalendarDay`, `Timeline`, `TimelineEvent` |
| Collections | `CalendarCollection`, `CalendarMonthCollection`, `CalendarDayCollection`, `TimelineCollection`, `TimelineEventCollection` |
| Repositories | `CalendarRepository`, `CalendarMonthRepository`, `CalendarDayRepository`, `TimelineRepository`, `TimelineEventRepository` |
| Controllers | `CalendarController`, `TimelineController` |
| Views | `calendarList.twig`, `editCalendar.twig`, `timelineList.twig`, `timeline.twig`, `editTimeline.twig` + Fragmente `newMonthRow.twig`, `newWeekdayRow.twig`, `newEvent.twig` |
| JS | `calendarForm.js`, `timelineForm.js` |

Beim Schreiben der Repositories zwei Dinge beachten, die im Bestand schon Fehler verursacht haben:

- **`bind_param` bindet per Referenz.** Ergebnisse von Methodenaufrufen sind dort nicht zulässig – erst in Variablen legen, dann binden (siehe `226d81c`).
- **Reihen-Namen dürfen nicht positionsabhängig sein.** Beim Steckbrief hängen `rowTopicN`/`rowInfoN` an der Abschnittsposition, was beim Löschen eine Umnummerierung erzwingt (siehe `4683530`). Für die Ereignis-Zeilen deshalb `name="event[]"`-Arrays mit einem `sequence`-Feld verwenden statt Namen mit Index im Namen.

## 6. Routen

Der Router bildet `/x/y` auf `XController::y()` ab, ohne Konfiguration – es reicht, die Controller anzulegen.

```
/calendar                 Liste der Kalender
/calendar/create          Formular
/calendar/save            POST
/calendar/edit?id=…       Formular
/calendar/delete?id=…
/timeline                 Liste der Zeitstrahlen
/timeline?id=…            Ansicht eines Zeitstrahls
/timeline/edit?id=…       Editor (Kopfdaten + Ereignisse)
/timeline/save            POST
/timeline/delete?id=…
```

## 7. Oberfläche

**Kalender-Editor** – Kopfdaten (Name, Ära-Bezeichnung + Kürzel, Epochen-Offset, Wochentage pro Woche, Stunden pro Tag), darunter zwei verschiebbare Listen nach dem Muster der Steckbrief-Zeilen (`controlButtons.twig`, `getTemplate()`): Monate (Name, Dauer, Schaltmonat ja/nein, Schalt-Intervall, Schalttage) und Wochentagsnamen. Live-Anzeige „ergibt N Tage pro Jahr" als Kontrolle.

**Zeitstrahl-Editor** – Kopfdaten (Name, Beschreibung, Projekt, Kalender, privat/editierbar), darunter die Ereignisliste. Pro Ereignis: Überschrift, Text, Datum (Jahr + Monat-Auswahl + Tag, alles außer Jahr optional), optional Bis-Datum, optional ein Artikel. **Für die Artikelauswahl ist die Arbeit schon getan:** `linkField.js` bringt die Artikel-Suche über `type=suggest` mit, dieselbe, die im Link-Modal steckt. Der Ereignistext läuft über TinyMCE wie die Absätze – und damit serverseitig zwingend durch `sanitizeHtml()`, weil er mit `|raw` ausgegeben wird.

**Darstellung** – zuerst eine vertikale, nach Jahr gruppierte Liste: robust, mobil brauchbar, ohne neue Technik. Zeiträume erhalten eine Klammer über die betroffenen Jahre. Später optional eine horizontale Achse als Inline-SVG – die Technik dafür steht schon in `statistics.twig`/`statisticsDonut.twig`.

## 8. Phasen

1. **Kalender** – Schema-Änderungen, Models/Collections/Repositories, Editor, Liste, Aventurien-Kalender als erster Datensatz. Für Nutzer noch unsichtbar, aber die Tagesnummer-Logik ist testbar.
2. **Zeitstrahl** – Zeitstrahl anlegen/bearbeiten, Ereignisse mit Datum, Zeitraum und optionalem Artikel, vertikale Darstellung, Rechteprüfung.
3. **Verzahnung** – Box „Auf der Zeitachse" auf der Artikelseite (`timeline_events.article = ?`), horizontale Achse, Filter nach Zeitraum.

Nach Phase 1 und 2 ist das Feature jeweils benutzbar abgeschlossen; Phase 3 ist Ausbau.

## 9. Offene Fragen

- **Jahresnamen:** `calendar_years.year_definition` („like Hal") – ist damit der Name der Ära gemeint oder benannte *einzelne* Jahre (in Aventurien haben Jahre Namen)? Bei letzterem braucht es eine weitere Tabelle.
- **Uhrzeit:** `hours_per_day` steht im Schema. Sollen Ereignisse eine Uhrzeit haben können? Vorschlag: nicht in v1.
- **Ein Kalender pro Zeitstrahl** – oder soll ein Zeitstrahl Ereignisse aus mehreren Kalendern mischen können (dann Umrechnung über `year_0BF` beim Sortieren)?
- **Mehrere Artikel pro Ereignis** war bewusst zurückgestellt. Falls das später kommt, ist eine Zwischentabelle `timeline_event_articles` der Weg – `timeline_events.article` bleibt dann als „Hauptartikel" bestehen.
