<?php
ini_set('session.gc_maxlifetime', 60*60*10); // expires in 10h
ini_set('session.cookie_lifetime', 60*60*10); // expires in 10h
session_start();
$sessionIDStr = session_id();

$servername = "rdbms.strato.de";
$username = "U4099678";
$password = "...";
$dbname = "DB4099678";

$AnzGesamtSlots = 1;

$RueckgabeText = "Unbekannter Text";
$debug = false;
//$_SESSION['BelohnungErhalten'] = false;

if ($debug) echo "<p>Seite mit Code auf dem Automat</p>";

if ($debug) echo "<pre>";
if ($debug) var_dump($_SESSION);

if ($debug) echo "Du warst an Schild:\n";
$AnzahlBesuchterSchilder = 0;
$FehlendeSchilderStr = "";
for ($i=1; $i<=10; $i++) {
    if (isset($_SESSION['besuchtesSchild'][$i])) {
        if ($debug) echo "Schild $i: ja\n";
        $AnzahlBesuchterSchilder++;
    } else {
        if ($debug) echo "Schild $i: nein\n";
        $FehlendeSchilderStr .= "$i, ";
    }
}

if ($debug) echo "Test, ob schon Belohnung erhalten?\n";
if (isset($_SESSION['BelohnungErhalten']) and ($_SESSION['BelohnungErhalten'] == true) ) {
    if ($debug) echo "Du hast bereits Deine Belohnung erhalten. Bei Fehlern wende Dich bitte an uns.\n";
    $RueckgabeText = "Belohnung bereits erhalten. Bei Fehlern wende Dich bitte an uns.";
} else {
    if ($debug) echo "Noch nicht.\n\n";

    if ($AnzahlBesuchterSchilder >= 9) {
        if ($debug) echo "Super! Du warst an allen Schildern. Jetzt kommt Deine Belohnung.\n\n";

        if ($debug) echo "Aktueller Stand der Slots:";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

        $sql = "SELECT * FROM `eue_automat2_status` ORDER BY `zeit` DESC LIMIT 1";
        $ZuvergebenderSlot = -1;
        $SlotBefuellungsstand = array(); //Zwischenspeicherung des Eintrags, um ihn später - um einen Slot modifiziert - wieder eintragen zu können
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                if ($debug) printf("%d\n", $row["slot0"]);
                for ($i=0; $i<$AnzGesamtSlots; $i++) {
                    $SlotBefuellungsstand[$i] = $row["slot".$i];
                }
            }
            $result->free();
        } else {
            if ($debug) echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
            $RueckgabeText = "Fehler F2 aufgetreten.";
        }
        if ($debug) echo "\n";

        $ZuvergebenderSlot = 0; //Gib aus diesem slot eine Tüte

	if ($debug) echo "Du erhaeltst die Tuete aus Slot Nr: $ZuvergebenderSlot\n";
	if ($debug) var_dump($SlotBefuellungsstand);
	$SlotBefuellungsstand[$ZuvergebenderSlot]++; //Dieser Slot soll ausgeloest werden
	$sql = "INSERT INTO `eue_automat2_status` (`sessionID`, `slot0`) VALUES ('$sessionIDStr', ";
	for ($i=0; $i<$AnzGesamtSlots; $i++) {
		if ($i >0) {
		    $sql = $sql.",";
		}
		$sql = $sql."'".$SlotBefuellungsstand[$i]."' ";
	}
	$sql = $sql.");";
	if ($debug) echo "SQLStr: $sql\n";

	if ($conn->query($sql)) {
		if ($debug) echo "Erfolgreich ausgefuehrt.\n";
		$_SESSION['BelohnungErhalten'] = true;
		$RueckgabeText = "Belohnung erhalten. Bitte entnehmen.";

		if ($debug) echo "Zurücksetzen der Session\n";
		//Löschen der Session, damit neue Tüten erworben werden können.
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();   // destroy session data in storage

		ini_set('session.gc_maxlifetime', 60*60*10); // expires in 10h
		ini_set('session.cookie_lifetime', 60*60*10); // expires in 10h

		session_start();
		session_regenerate_id();
		if ($debug) var_dump($_SESSION);
	} else {
		if ($debug) echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
		$RueckgabeText = "Fehler F3 aufgetreten.";
	}
	if ($debug) echo "\n";


        $conn->close();

    } else {
        if ($debug) echo "Dir fehlen noch ein paar Schilder: $FehlendeSchilderStr\n";
        $RueckgabeText = "Dir fehlen noch Schilder: $FehlendeSchilderStr";
    }

} //Test auf Belohnung
if ($debug) echo "</pre>";

  $url = 'https://www.eue-turnt.de/schild/meldung.php?text='.urlencode($RueckgabeText);
  header( "Location: $url" );  // no redirect

?>
