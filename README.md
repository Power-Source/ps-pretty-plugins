# PS Pretty Plugins

Verleihe den Plugin-Seiten in Multisite-Netzwerken das Aussehen eines App Stores – mit ausgewählten Bildern, Kategorien und einer leistungsfähigen Suche.

## Was ist PS Pretty Plugins?

Mit **PS Pretty Plugins** kannst Du Plugins in Kategorien gruppieren, ihnen auffällige Funktionsbilder geben und sie in einem benutzerfreundlichen Raster anzeigen.

Das macht das Finden und Installieren von Plugins zum Kinderspiel. PS Pretty Plugins bietet Netzwerkadministratoren eine zentrale Steuerung und Konfiguration der Plugin-Seite auf allen Websites eines Multisite-Netzwerks.

Kombiniert mit **PS Bloghosting** kannst Du daraus sogar einen komfortablen Plugin-Shop für Deine Websites erstellen.

Große Symbole und ein vertrautes Rasterlayout sorgen für eine deutlich ansprechendere Darstellung als die standardmäßige Plugin-Liste.

![Screenshot der Plugins-Seite mit dem Rasterlayout](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/prettyplugins-ss12.png)

> Große Symbole und ein Rasterlayout machen das Entdecken von Plugins zum Kinderspiel.

Plugins können mehreren Kategorien zugeordnet werden. Die Kategorien werden von Dir als Netzwerkadministrator definiert und machen es Webseitenadministratoren leichter, neue Funktionen zu entdecken und zu installieren.

![Screenshot der Plugin-Kategorien](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/prettyplugins-ss2.png)

> Gruppiere Plugins in Kategorien, um sie leichter finden zu können.

Eine umfassende Einstellungsseite ermöglicht es Dir, alle wichtigen Aspekte der Plugin-Seite zu verwalten – darunter Titel, Untertitel, Bilder und die Sichtbarkeit von Beschreibungen. Deine Einstellungen kannst Du außerdem exportieren und importieren.

![Screenshot der Einstellungsseite](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/prettyplugins-ss11.png)

> Konfiguriere alle Aspekte des Verhaltens und Aussehens der Plugin-Seite.

Für jedes Plugin steht Dir eine detaillierte Steuerung zur Verfügung. Du kannst den Plugin-Namen, die URL und die Beschreibung überschreiben, das Plugin beliebig vielen vorhandenen Kategorien zuweisen oder neue Kategorien erstellen und ein eigenes Vorschaubild festlegen.

![Screenshot der Plugin-Details-Bearbeitungsfunktion](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/prettyplugins-ss41.png)

> Überschreibe die wichtigsten Plugin-Attribute und passe die Darstellung individuell an.

PS Pretty Plugins gibt Dir umfangreiche Kontrolle über das Aussehen und Verhalten der Plugin-Seite und macht es für Webseitenadministratoren einfacher denn je, Plugins zu verwalten, neue Funktionen zu entdecken und diese auf ihrer Website zu nutzen.

## Verwendung

### Voraussetzungen

PS Pretty Plugins wurde speziell für **WordPress- und ClassicPress-Multisite-Installationen** entwickelt.

> **Wichtig:** Das Plugin funktioniert ausschließlich in einer Multisite-Installation und ist nicht für einzelne Websites vorgesehen.

### Einstellungen konfigurieren

Nach der Installation und Aktivierung findest Du in Deinem Netzwerkadministrator unter **Netzwerkeinstellungen** den neuen Menüpunkt **PS Pretty Plugins**.

![PS Pretty Plugins im Netzwerkmenü](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/pretty-plugins-1000-menu.png)

> Die Einstellungen sind recht einfach. Aber lass uns sie trotzdem gemeinsam durchgehen. 😉

![Einstellungen von PS Pretty Plugins](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/pretty-plugins-1000-settings.png)

1. Setup-Modus aktivieren/deaktivieren.
2. Das gewünschte Design auswählen.
3. Das Ziel der Plugin-Links festlegen.
4. Die Einstellungen für Screenshots auswählen.
5. Plugin-Beschreibungen ein- oder ausblenden.
6. Die Beschriftungen der Plugin-Seite anpassen.

#### 1. Setup-Modus

Wahrscheinlich möchtest Du **Setup-Modus aktivieren** zunächst auf **Wahr** gesetzt lassen, während Du PS Pretty Plugins konfigurierst.

Wenn der Setup-Modus aktiviert ist, kannst Du auf der Hauptseite eine Vorschau Deiner Konfiguration anzeigen. Die Unterseiten sind davon nicht betroffen.

Sobald Du mit Deiner Konfiguration zufrieden bist, setzt Du den Setup-Modus auf **Falsch**. Damit wird die neue Plugin-Darstellung auf den Unterseiten Deines gesamten Netzwerks aktiviert.

#### 2. Theme für die Plugin-Seite

Mit **Theme für Plugin-Seite auswählen** legst Du fest, welches Design für die verfügbaren Plugins auf den Unterseiten verwendet wird.

Derzeit sind folgende Themes enthalten:

- QuickSand
- 3Eight
- Material

Du kannst auch Dein eigenes Theme erstellen. Kopiere dazu einfach das **QuickSand**-Theme aus:

```text
/pretty-plugins/themes/
```

Benenne die Kopie anschließend um und passe Layout und Stylesheet nach Deinen Vorstellungen an.

Dein eigenes Theme kannst Du anschließend in folgenden Ordner hochladen:

```text
/wp-content/uploads/prettyplugins/themes/
```

Danach steht es in den Einstellungen zur Auswahl zur Verfügung.

#### 3. Plugin-Link-Ziel

Für das Ziel eines Plugin-Links stehen Dir vier Möglichkeiten zur Verfügung:

**Plugin-Original-URL**

Verwendet die URL, die der Plugin-Autor im Plugin hinterlegt hat.

**Benutzerdefinierte Plugin-URL**

Verwendet die URL, die Du individuell für das jeweilige Plugin festgelegt hast.

**Info-URL des Plugins oder, falls keine Info-URL vorhanden ist, Original-URL**

Diese Option bietet Dir das Beste aus beiden Varianten. Wenn Du für ein Plugin keine eigene Info-URL hinterlegt hast, wird automatisch die vom Plugin-Autor angegebene Original-URL verwendet.

**Deaktiviert**

Deaktiviert die Plugin-Links vollständig.

#### 4. Screenshot-Einstellungen

Hier legst Du fest, welche Bilder für die Plugins verwendet werden sollen.

**Ersten Screenshot automatisch laden**

Wenn diese Option auf **Wahr** gesetzt ist, sucht PS Pretty Plugins automatisch im Hauptverzeichnis des Plugins nach einem passenden Bild.

Die Suche erfolgt in dieser Reihenfolge:

1. `screenshot-1.png`
2. `logo.png`
3. `logo.jpg`

Wenn keine dieser Dateien gefunden wird, wird für das Plugin kein automatisches Bild angezeigt.

**Screenshot automatisch mit korrektem Namen laden**

Mit dieser Option sucht PS Pretty Plugins in:

```text
/wp-content/uploads/prettyplugins/screenshots/
```

nach Bildern, die Du dort mit dem passenden Dateinamen abgelegt hast.

Wenn sich das Plugin beispielsweise hier befindet:

```text
/wp-content/plugins/akismet/akismet.php
```

sollte der Screenshot folgenden Namen verwenden:

```text
akismet-akismet.png
```

Bei dieser Methode werden nur PNG-Bilder unterstützt.

#### 5. Beschreibungen minimieren

Mit **Beschreibungen minimieren** kannst Du die Plugin-Beschreibungen auf den Unterseiten ausblenden.

Wenn die Option aktiviert ist, werden zunächst nur die Screenshots angezeigt. Webseitenadministratoren können die Beschreibung bei Bedarf über den entsprechenden Detail-Link einblenden.

Das sorgt insbesondere bei vielen installierten Plugins für eine deutlich übersichtlichere Darstellung.

#### 6. Beschriftungen verwalten

Mit den Einstellungen unter **Beschriftungen für Plugin-Seite verwalten** kannst Du die Texte anpassen, die Deinen Webseitenadministratoren angezeigt werden.

**Plugin-Seitentitel**

Ändert den Namen des Menüelements und den Titel am Anfang der Plugin-Seite.

Du könntest beispielsweise statt „Plugins“ den Begriff „Addons“ verwenden.

**Plugin-Seitenbeschreibung**

Der benutzerdefinierte Beschreibungstext, der direkt unter dem Titel der Plugin-Seite angezeigt wird.

**Benutzerdefiniertes Link-Label**

Legt fest, welchen Text Benutzer anklicken können, um die Plugin-URL aufzurufen, die Du unter den Link-Einstellungen festgelegt hast.

Wenn Plugin-Links deaktiviert wurden, wird dieses Label nicht angezeigt.

### Zusätzliche Tools

PS Pretty Plugins stellt einige zusätzliche Werkzeuge zur Verwaltung Deiner Plugin-Daten und Einstellungen bereit.

![Werkzeuge von PS Pretty Plugins](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/pretty-plugins-1000-tools.png)

1. `config.xml` exportieren/importieren.
2. Alle Daten löschen.

#### Einstellungen exportieren und importieren

Du kannst Deine Einstellungen als `config.xml`-Datei **exportieren**, um sie auf Deinem Computer zu sichern.

Die Datei kann anschließend wieder in folgenden Ordner importiert werden:

```text
/wp-content/uploads/prettyplugins/
```

#### Zurücksetzen

Die **Reset**-Funktion ist praktisch, wenn Du mit einer neuen Konfiguration von vorne beginnen möchtest. 🙂

## Plugin-Details bearbeiten

Du kannst die Darstellung jedes einzelnen Plugins individuell anpassen.

Öffne dazu im Netzwerkadministrator:

**Plugins → Installierte Plugins**

Bei jedem Plugin findest Du den Link **Details bearbeiten**.

![Plugin-Details bearbeiten](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/pretty-plugins-1000-edit.png)

Dadurch wird ein vertrauter Bearbeitungsbereich geöffnet, der dem Schnellbearbeitungsbereich beim Bearbeiten von Beiträgen ähnelt.

Schauen wir uns zunächst den ersten Bereich an.

![Plugin-Details – erster Bereich](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/pretty-plugins-1000-edit-details.png)

Du kannst folgende Informationen individuell festlegen:

**Name**

Gib einen benutzerdefinierten Namen ein, um den ursprünglichen Plugin-Namen zu ersetzen.

Wenn das Feld leer bleibt, wird weiterhin der tatsächliche Plugin-Name verwendet.

**Info-URL**

Hier kannst Du die vom Plugin-Autor angegebene URL ersetzen.

Beachte, dass diese URL nur verwendet wird, wenn Du bei **Plugin-Link-Ziel** eine entsprechende benutzerdefinierte URL-Option ausgewählt hast.

**Bild-URL**

Hier kannst Du ein eigenes Bild für das Plugin festlegen.

Du hast dabei mehrere Möglichkeiten:

- Wähle ein Bild aus Deiner Medienbibliothek.
- Gib den Namen einer Datei an, die Du in `/wp-content/uploads/prettyplugins/screenshots/` hochgeladen hast.
- Verlinke auf ein Bild, das an anderer Stelle gehostet wird.

Die empfohlenen Bildabmessungen betragen:

```text
600 × 450 Pixel
```

Schauen wir uns nun den zweiten Bereich an.

![Plugin-Details – Kategorien](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/pretty-plugins-1000-edit-details-2.png)

### Kategorien

Um Kategorien hinzuzufügen, klicke auf **Neue Kategorie**, gib den gewünschten Namen ein und klicke anschließend auf **Hinzufügen**.

Danach kannst Du die neue Kategorie aus der Liste auswählen.

- Ein Plugin kann mehreren Kategorien gleichzeitig zugeordnet werden.
- Kategorien, die von keinem Plugin verwendet werden, werden automatisch entfernt.

### Beschreibung

Du kannst für jedes Plugin eine eigene **Beschreibung** hinterlegen.

Diese ersetzt die Beschreibung, die ursprünglich vom Plugin-Autor bereitgestellt wurde.

Wenn Du das Feld leer lässt, wird stattdessen die ursprüngliche Plugin-Beschreibung verwendet.

Wenn Du mit der Bearbeitung fertig bist, klicke auf **Aktualisieren**.

Deine Änderungen werden anschließend direkt in der Plugin-Liste des Netzwerkadministrators berücksichtigt.

![Plugin-Details – Beschreibung](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/pretty-plugins-1000-edit-details-3.png)

## Ansicht für Webseitenadministratoren

Sobald alles konfiguriert ist und Du den **Setup-Modus** in den allgemeinen Einstellungen auf **Falsch** setzt, sehen Deine Webseitenadministratoren eine Plugin-Seite ähnlich der folgenden:

![Plugin-Seite aus Sicht eines Webseitenadministrators](https://psource.eimen.net/wp-content/uploads/sites/10/2026/08/pretty-plugins-1000-site-view.png)

Aktive Plugins werden mit einem grünen Band im Bild gekennzeichnet.

In diesem Beispiel sind die Beschreibungen so eingestellt, dass sie zunächst ausgeblendet werden. Webseitenadministratoren können die Beschreibung über den entsprechenden Detail-Link einblenden.

Wenn Du eigene Bilder für die Bänder verwenden möchtest, kannst Du die Standardbilder im jeweiligen Theme ersetzen.

Beim mitgelieferten QuickSand-Theme findest Du diese beispielsweise unter:

```text
/pretty-plugins/themes/quick-sand/images/
```

Wenn Du ein eigenes Theme erstellt hast, kannst Du stattdessen die Bilder im entsprechenden `/images/`-Ordner Deines eigenen Themes ersetzen.

### Kategorien

Oberhalb der Plugin-Liste und im Plugins-Menü stehen die von Dir eingerichteten Kategorien als Filter zur Verfügung.

Klickt ein Webseitenadministrator auf eine Kategorie, werden nur Plugins angezeigt, die dieser Kategorie zugeordnet wurden.

Damit kannst Du beispielsweise Kategorien wie:

- Sicherheit
- SEO
- Medien
- Kommunikation
- Design
- Produktivität

anlegen und Deinen Webseitenadministratoren eine übersichtliche Auswahl bieten.

## PS Bloghosting-Integration

Oh ja, die gibt es! 😎

PS Pretty Plugins lässt sich direkt mit **PS Bloghosting** verbinden.

Wenn Du das Premium-Plugin-Modul von PS Bloghosting verwendest und dort Plugins bestimmten Bloghosting-Ebenen zugeordnet hast, erkennt PS Pretty Plugins diese Zuordnungen automatisch.

Plugins, die einer PS Bloghosting-Ebene zugeordnet sind, werden auf Websites, die diese Ebene noch nicht erreicht haben, entsprechend gekennzeichnet.

Bewege den Mauszeiger über ein solches Plugin, erhältst Du einen Hinweis wie:

> Upgrade auf [Stufe]

Wenn die Website noch nicht auf die erforderliche PS Bloghosting-Ebene aktualisiert wurde und der Webseitenadministrator versucht, eines dieser Plugins aufzurufen, kann er direkt zur entsprechenden Upgrade-Seite von PS Bloghosting weitergeleitet werden.

Damit lässt sich PS Pretty Plugins hervorragend als **Plugin-Shop für ein Multisite-Bloghosting-System** einsetzen.

Du kannst außerdem Pretty-Plugins-Kategorien erstellen, die Deine PS Bloghosting-Ebenen widerspiegeln, beispielsweise:

- Free
- Premium
- Super

So können Deine Webseitenadministratoren auf einen Blick erkennen, welche zusätzlichen Plugins mit einem Upgrade ihrer Website verfügbar werden.

## Lizenz

PS Pretty Plugins ist Open Source.

Weitere Informationen zur Lizenz findest Du in der mitgelieferten `LICENSE`-Datei.