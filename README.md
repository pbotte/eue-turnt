# eue-turnt und eue-turnt Kids

Dieses Repo umfasst nun die Funktionalität für den Eue-Turnt-Rundlauf (QR-Code Schilder mit Handyabruf) und dem Eue-Turnt-Kids-Rundlauf (RFID-Schlüsselanhänger).

## Hinweise zum Repository

Zum gleichzeitigen Herunterladen der Submodule:
```bash
git clone --recurse-submodules git@github.com:pbotte/eue-turnt.git
```

## Vorgehen bei neuen Trainings

Es werden Trainings angelegt, d.h. für jede Station wird eine Wordpress-Seite angelegt. Tipp: Ein Video im Hochkantformat kommt unterwegs gut an.
Diese Seiten müssen dann in den `/schild/index.php` eingetragen werden.

Abschließend müssen die Trainings zum Auswählen in der Wordpress-Seite "Trainingsauswahl" (als Button im Fließtext und im eingebetteten php-Code) eingetragen werden.

Fertig.

## Ablauf eines Trainings für den Sportler:

1. An den Schildern scannt der Sportler einen QR-Code. Dies folgt dem Schema: `/schild/?nr=1` etc.
2. Damit wird die `/schild/index.php`-Seite aufgerufen, die beim ersten Scann (noch kein Session-Cookie vorhanden) auf die Trainingsauswahlseite (Wordpress-Seite) verweist. Andernfalls geht es direkt auf die Übungsseite.
3. Nach 10 Schildern kann der Belohnungsautomat besucht werden.

## Datenbank auf Webserver

Für die Schild-Scans, damit man mitbekommt, wenn eines nicht mehr geht.

```SQL
CREATE TABLE `eue_schild_scans` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `schildNr` int(11) NOT NULL,
 `trainingsNr` int(11) DEFAULT NULL,
 `zeit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `userAgent` text COLLATE latin1_german1_ci,
 `sessionid` varchar(32) COLLATE latin1_german1_ci DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4174 DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci
```

Für den Belohungsautomaten

```SQL
eue_automat_feedback	CREATE TABLE `eue_automat_feedback` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `zeit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `automaten_zeit` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1174843 DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci


CREATE TABLE `eue_automat_status` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `zeit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `sessionID` varchar(32) COLLATE latin1_german1_ci DEFAULT NULL,
 `Slot0` tinyint(1) NOT NULL DEFAULT '1',
 `Slot1` tinyint(1) NOT NULL DEFAULT '1',
 `Slot2` tinyint(1) NOT NULL DEFAULT '1',
 `Slot3` tinyint(1) NOT NULL DEFAULT '1',
 `Slot4` tinyint(1) NOT NULL DEFAULT '1',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci

```

## Änderungen an Wordpress

### index.php

Die Lebenszeit der Session-Cookies wird hochgesetzt. Sonst geht die Session zwischen den QR-Code-Scanns immer wieder verloren.

```php index.php
<?php
ini_set('session.gc_maxlifetime', 60*60*10); // expires in 10h
ini_set('session.cookie_lifetime', 60*60*10); // expires in 10h
session_start();

//...
```

### Gewähltes Theme

Mesmerize

### Erweiterungen

Die folgenden Erweiterungen sind vorteilhaft oder sogar notwendig:

- 301 Redirects
- Add From Server
- Autoptimize
- Contact Form 7
- Insert PHP Code Snippet
- Mesmerize Companion
- OMGF (Minimize DNS requests and leverage browser cache by easily saving Google Fonts to your server and removing the external Google Fonts.)
- One Click Demo Import

Teils auch zum Erfüllen der DSGVO-Richtlinie. So konnte z.B. Verhindert werden, dass Dinge aus einem Google-Cache geladen werden.

### PHP Code Snippets innerhalb von Wordpress-Seiten

WICHTIG ist: Autoptimize ausführen, damit verschwinden die Fehler zu "Warning: Cannot modify header information - headers already sent by ...".

#### Tracking Name: Automat-Erledigte-Schilder

```php
<?php
session_start();
$sessionIDStr = session_id();

$AnzahlBesuchterSchilder = 0;
for ($i=1; $i<=10; $i++) {
    if (isset($_SESSION['besuchtesSchild'][$i])) {
        $AnzahlBesuchterSchilder++;
    }
}

$FehlendeSchilderStr = "";
$konsekutiveSchilder = 0;
for ($i=1; $i<=10; $i++) {
    if (!isset($_SESSION['besuchtesSchild'][$i])) {
      if ($konsekutiveSchilder == 0)  { 
        if (strlen($FehlendeSchilderStr) > 0) $FehlendeSchilderStr .= ", ";
        $FehlendeSchilderStr .= "$i";
      }
      $konsekutiveSchilder++;
    }
   else {
      if ($konsekutiveSchilder>2) {
        $FehlendeSchilderStr .= "-".($i-1);
      } else {
        if ($konsekutiveSchilder>1) {
          $FehlendeSchilderStr .= ", ".($i-1);
        }
      }
      $konsekutiveSchilder = 0;
    }
}
//Letztes Schild nochmal separat überprüfen
    if (!isset($_SESSION['besuchtesSchild'][10])) {
        if ($konsekutiveSchilder>2) {
          $FehlendeSchilderStr .= "-10";
        } else {
          if ($konsekutiveSchilder>1) {
            $FehlendeSchilderStr .= ", 10";
          }
        }
}


// "Test, ob schon Belohnung erhalten?\n";
if (isset($_SESSION['BelohnungErhalten']) and ($_SESSION['BelohnungErhalten'] == true) ) {
    echo "Du hast bereits Deine Belohnung erhalten. Bei Fehlern wende Dich bitte an uns.\n";
} else {
    if ($AnzahlBesuchterSchilder >= 10) {
        echo "<h2>Super! Du warst an allen Schildern. Hohl Dir jetzt Deine Belohnung an der Turnhalle ab.</h2>";
    } else {
      if ($AnzahlBesuchterSchilder == 9) {
        echo "Dir fehlt noch das folgende Schild: $FehlendeSchilderStr\n";
    } else {
        echo "Dir fehlen noch folgende ".(10-$AnzahlBesuchterSchilder)." Schilder: $FehlendeSchilderStr\n";
    }
    }
} //Test auf Belohnung
echo '<p><a href="https://www.eue-turnt.de/der-turner-gummibaeren-automat-ist-da/">Mehr Infos und Hilfe zum Belohnungsautomat</a>.</p>';
?>
```

#### Tracking Name: Schild-Verfuegbarkeit
```php
<?php
$servername = "rdbms.strato.de";
    $username = "U4099678";
    $password = "...";
    $dbname = "DB4099678";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $sql = "SELECT `schildNr`, MAX(`zeit`) AS LetzterSchildScan FROM `eue_schild_scans` WHERE schildNr <> 0 Group BY `schildNr` ORDER BY LetzterSchildScan ASC";

    if ($result = $conn->query($sql)) {
        echo "Wann wurde das Schild zuletzt gescannt?";

  printf("<table><tr><td>Schild Nr</td><td>Zeitpunkt</td><td>Alter in Stunden</td></tr>");
    while ($row = $result->fetch_assoc()) {
        printf ("<tr><td>%d</td><td>%s</td><td>%d</td></tr>\n", $row["schildNr"], $row["LetzterSchildScan"], round((time()-strtotime($row["LetzterSchildScan"])+3600*2)/3600) );
    }
  printf("</table>");

    /* free result set */
    $result->free();

    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
?>
```

#### Tracking Name: Schild-Auswertung
```php
<?php
$servername = "rdbms.strato.de";
    $username = "U4099678";
    $password = "...";
    $dbname = "DB4099678";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $sql = "SELECT COUNT(*) AS AnzahlSchildScans, MIN(trainingsNr) AS Training, MIN(zeit) AS Zeit FROM `eue_schild_scans` WHERE trainingsNr <> 0 GROUP BY sessionid, trainingsNr HAVING AnzahlSchildScans > 1 ORDER BY Zeit DESC";

    if ($result = $conn->query($sql)) {
        echo "Trainings mit mindestens zwei gescannten Schildern.";

   /* fetch associative array */
  printf("<table><tr><td>Zeit</td><td>Training</td><td>Anzahl Schild Scans</td></tr>");
    while ($row = $result->fetch_assoc()) {
        printf ("<tr><td>%s</td><td>%d</td><td>%d</td></tr>\n", $row["Zeit"], $row["Training"], $row["AnzahlSchildScans"]);
    }
  printf("</table>");

    /* free result set */
    $result->free();

    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }



    $conn->close();

?>
```

#### Tracking Name: Hinweis-zum-Start-einer-Session
```php
<?php
session_start();

if ((isset($_SESSION['schildnr'])) and (!isset($_SESSION['tnr'])) ) {
echo "<h1>Dein Training beginnt";
if ($_SESSION['schildnr']!=='0') {
echo " an Station ".$_SESSION['schildnr'];
}
echo "!</h1><p>Bevor es los geht wähle das Training.</p>";
}
?>
```

#### Tracking Name: Gehe-zur-Uebung
```php
<?php
session_start();

if ((isset($_SESSION['schildnr'])) and (isset($_SESSION['tnr'])) ) {
  ob_start(); // ensures anything dumped out will be caught
  $url = 'https://www.eue-turnt.de/schild/?nr='.$_SESSION['schildnr'];
  while (ob_get_status()) {   // clear out the output buffer
    ob_end_clean();
  }
  header( "Location: $url" );  // no redirect
  unset($_SESSION['schildnr']);
}
?>
```

#### Tracking Name: Trainingsauswahl
```php
<?php
session_start();

$tnr = $_GET['tnr'];
if (is_numeric($tnr)) {
  $tnr = intval($tnr);
  $_SESSION['tnr'] = $tnr;
  $bez= "Unbenanntes Training";
  if ($tnr==1) { $bez= "Einsteiger Training für jung und alt"; }
  if ($tnr==2) { $bez= "Tierische Moves"; }
  if ($tnr==3) { $bez= "Power Programm"; }
  if ($tnr==4) { $bez= "Fit durch die Weinberge"; }
  if ($tnr==5) { $bez= "Fit mit Ulli"; }
  if ($tnr==6) { $bez= "Leichtathletik Programm des LAV"; }
  if ($tnr==7) { $bez= "Fit mit Ulli - die zweite"; }
  if ($tnr==8) { $bez= "Kita Schloss Ardeck turnt für Kinder"; }
  if ($tnr==9) { $bez= "Kita Schloss Ardeck turnt für alle"; }
  if ($tnr==10) { $bez= "Kita Schloss Ardeck turnt mit Ball"; }
  if ($tnr==11) { $bez= "Kita Schloss Ardeck turnt mit Seil"; }
  if ($tnr==12) { $bez= "Kita Schloss Ardeck zeigt tierische Fortbewegungsarten"; }
  if ($tnr==13) { $bez= "Kita Schloss Ardeck - Entspannung für alle"; }
  if ($tnr==14) { $bez= "Kita Schloss Ardeck trainiert den Bambini-Lauf"; }
  $_SESSION['tname'] = $bez;
}

if (isset($_SESSION['tnr'])) {
  echo "<h2>Deine Wahl:</h2>";
   echo "<p>Du hast das Training <b>".$_SESSION['tname']."</b> ausgewählt. (Nr: ".$_SESSION['tnr'].") </p>";
  echo "<h2>Scanne jetzt das Schild!</h2>";
  echo "<p>&nbsp;</p>";
}
?>
```



