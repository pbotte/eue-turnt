<?php
ini_set('session.gc_maxlifetime', 60*60*10); // expires in 10h
ini_set('session.cookie_lifetime', 60*60*10); // expires in 10h
session_start();



$Str = "";
$schildNr = $_GET['nr'];
if (!is_numeric($schildNr)) {
    $schildNr = 0;
}
if (!isset($_SESSION['besuchtesSchild'])) {
    $_SESSION['besuchtesSchild'] = array();
}
$_SESSION['besuchtesSchild'][$schildNr] = time();

$trainingsNr = Null;
if (!isset($_SESSION['tnr'])) {
    $_SESSION['schildnr'] = $schildNr; //Dorthin geht es nach der Auswahl zurück.
    $Str = "auswahl/";
} else {
    $trainingsNr = $_SESSION['tnr'];
    if ($_SESSION['tnr'] == 1) { // Allgemeines Training
        if ($schildNr == "0") { $Str = "start-station/"; }
        if ($schildNr == "1") { $Str = "uebungsstation-1/"; }
        if ($schildNr == "2") { $Str = "uebungsstation-2/"; }
        if ($schildNr == "3") { $Str = "uebungsstation-3/"; }
        if ($schildNr == "4") { $Str = "uebungsstation-4/"; }
        if ($schildNr == "5") { $Str = "uebungsstation-5/"; }
        if ($schildNr == "6") { $Str = "uebungsstation-6/"; }
        if ($schildNr == "7") { $Str = "uebungsstation-7/"; }
        if ($schildNr == "8") { $Str = "uebungsstation-8/"; }
        if ($schildNr == "9") { $Str = "uebungsstation-9/"; }
        if ($schildNr == "10") { $Str = "uebungsstation-10/"; }
    }

    if ($_SESSION['tnr'] == 2) { //Tierische Moves
        if ($schildNr == "0") { $Str = "tierisch-viel-spass-mit-animal-moves/"; }
        if ($schildNr == "1") { $Str = "tierische-moves-1/"; }
        if ($schildNr == "2") { $Str = "tierische-moves-2/"; }
        if ($schildNr == "3") { $Str = "tierische-moves-3/"; }
        if ($schildNr == "4") { $Str = "tierische-moves-4/"; }
        if ($schildNr == "5") { $Str = "tierische-moves-5/"; }
        if ($schildNr == "6") { $Str = "tierische-moves-6/"; }
        if ($schildNr == "7") { $Str = "tierische-moves-7/"; }
        if ($schildNr == "8") { $Str = "tierische-moves-8/"; }
        if ($schildNr == "9") { $Str = "tierische-moves-9/"; }
        if ($schildNr == "10") { $Str = "tierische-moves-10/"; }
    }

    if ($_SESSION['tnr'] == 3) { //Power Programm
        if ($schildNr == "0") { $Str = "power-programm-mit-maya-und-maxima/"; }
        if ($schildNr == "1") { $Str = "power-programm-1/"; }
        if ($schildNr == "2") { $Str = "power-programm-2/"; }
        if ($schildNr == "3") { $Str = "power-programm-3/"; }
        if ($schildNr == "4") { $Str = "power-programm-4/"; }
        if ($schildNr == "5") { $Str = "power-programm-5/"; }
        if ($schildNr == "6") { $Str = "power-programm-6/"; }
        if ($schildNr == "7") { $Str = "power-programm-7/"; }
        if ($schildNr == "8") { $Str = "power-programm-8/"; }
        if ($schildNr == "9") { $Str = "power-programm-9/"; }
        if ($schildNr == "10") { $Str = "power-programm-10/"; }
    }

    if ($_SESSION['tnr'] == 4) { //Fit durch die Weinberge
        if ($schildNr == "0") { $Str = "fit-durch-die-weinberge-0/"; }
        if ($schildNr == "1") { $Str = "fit-durch-die-weinberge-1/"; }
        if ($schildNr == "2") { $Str = "fit-durch-die-weinberge-2/"; }
        if ($schildNr == "3") { $Str = "fit-durch-die-weinberge-3/"; }
        if ($schildNr == "4") { $Str = "fit-durch-die-weinberge-4/"; }
        if ($schildNr == "5") { $Str = "fit-durch-die-weinberge-5/"; }
        if ($schildNr == "6") { $Str = "fit-durch-die-weinberge-6/"; }
        if ($schildNr == "7") { $Str = "fit-durch-die-weinberge-7/"; }
        if ($schildNr == "8") { $Str = "fit-durch-die-weinberge-8/"; }
        if ($schildNr == "9") { $Str = "fit-durch-die-weinberge-9/"; }
        if ($schildNr == "10") { $Str = "fit-durch-die-weinberge-10/"; }
    }

    if ($_SESSION['tnr'] == 5) { //fit mit Uli
        if ($schildNr == "0") { $Str = "fit-mit-ulli-0"; }
        if ($schildNr == "1") { $Str = "fit-mit-ulli-1/"; }
        if ($schildNr == "2") { $Str = "fit-mit-ulli-2/"; }
        if ($schildNr == "3") { $Str = "fit-mit-ulli-3/"; }
        if ($schildNr == "4") { $Str = "fit-mit-ulli-4/"; }
        if ($schildNr == "5") { $Str = "fit-mit-ulli-5/"; }
        if ($schildNr == "6") { $Str = "fit-mit-ulli-6/"; }
        if ($schildNr == "7") { $Str = "fit-mit-ulli-7/"; }
        if ($schildNr == "8") { $Str = "fit-mit-ulli-8/"; }
        if ($schildNr == "9") { $Str = "fit-mit-ulli-9/"; }
        if ($schildNr == "10") { $Str = "fit-mit-ulli-10/"; }
    }

    if ($_SESSION['tnr'] == 6) { //LAV Training
        if ($schildNr == "0") { $Str = "lav-training-start/"; }
        if ($schildNr == "1") { $Str = "lav-training-1/"; }
        if ($schildNr == "2") { $Str = "lav-training-2/"; }
        if ($schildNr == "3") { $Str = "lav-training-3/"; }
        if ($schildNr == "4") { $Str = "lav-training-4/"; }
        if ($schildNr == "5") { $Str = "lav-training-5/"; }
        if ($schildNr == "6") { $Str = "lav-training-6/"; }
        if ($schildNr == "7") { $Str = "lav-training-7/"; }
        if ($schildNr == "8") { $Str = "lav-training-8/"; }
        if ($schildNr == "9") { $Str = "lav-training-9/"; }
        if ($schildNr == "10") { $Str = "lav-training-10/"; }
    }
    
    if ($_SESSION['tnr'] == 7) { //Fit mit Ulli die zweite
        if ($schildNr == "0") { $Str = "fit-mit-ulli-die-zweite/"; }
        if ($schildNr == "1") { $Str = "fit-mit-ulli-die-zweite-1"; }
        if ($schildNr == "2") { $Str = "fit-mit-ulli-die-zweite-2"; }
        if ($schildNr == "3") { $Str = "fit-mit-ulli-die-zweite-3/"; }
        if ($schildNr == "4") { $Str = "fit-mit-ulli-die-zweite-4/"; }
        if ($schildNr == "5") { $Str = "fit-mit-ulli-die-zweite-5/"; }
        if ($schildNr == "6") { $Str = "fit-mit-ulli-die-zweite-6/"; }
        if ($schildNr == "7") { $Str = "fit-mit-ulli-die-zweite-7/"; }
        if ($schildNr == "8") { $Str = "fit-mit-ulli-die-zweite-8/"; }
        if ($schildNr == "9") { $Str = "fit-mit-ulli-die-zweite-9/"; }
        if ($schildNr == "10") { $Str = "fit-mit-ulli-die-zweite-10/"; }
    }
    if ($_SESSION['tnr'] == 8) { //Kiga 1
        if ($schildNr == "0") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-start/"; }
        if ($schildNr == "1") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-1/"; }
        if ($schildNr == "2") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-2"; }
        if ($schildNr == "3") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-3/"; }
        if ($schildNr == "4") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-4/"; }
        if ($schildNr == "5") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-5/"; }
        if ($schildNr == "6") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-6/"; }
        if ($schildNr == "7") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-7/"; }
        if ($schildNr == "8") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-8/"; }
        if ($schildNr == "9") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-9/"; }
        if ($schildNr == "10") { $Str = "kita-schloss-ardeck-turnt-fuer-kinder-10/"; }
    }
    if ($_SESSION['tnr'] == 9) { //Kiga 2
        if ($schildNr == "0") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-start/"; }
        if ($schildNr == "1") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-1/"; }
        if ($schildNr == "2") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-2"; }
        if ($schildNr == "3") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-3/"; }
        if ($schildNr == "4") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-4/"; }
        if ($schildNr == "5") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-5/"; }
        if ($schildNr == "6") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-6/"; }
        if ($schildNr == "7") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-7/"; }
        if ($schildNr == "8") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-8/"; }
        if ($schildNr == "9") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-9/"; }
        if ($schildNr == "10") { $Str = "kita-schloss-ardeck-turnt-fuer-alle-10/"; }
    }

    if ($_SESSION['tnr'] == 10) { //Kiga 3 mit Ball
        if ($schildNr == "0") { $Str = "kita-schloss-ardeck-turnt-mit-ball-start/"; }
        if ($schildNr == "1") { $Str = "kita-schloss-ardeck-turnt-mit-ball-1/"; }
        if ($schildNr == "2") { $Str = "kita-schloss-ardeck-turnt-mit-ball-2"; }
        if ($schildNr == "3") { $Str = "kita-schloss-ardeck-turnt-mit-ball-3/"; }
        if ($schildNr == "4") { $Str = "kita-schloss-ardeck-turnt-mit-ball-4/"; }
        if ($schildNr == "5") { $Str = "kita-schloss-ardeck-turnt-mit-ball-5/"; }
        if ($schildNr == "6") { $Str = "kita-schloss-ardeck-turnt-mit-ball-6/"; }
        if ($schildNr == "7") { $Str = "kita-schloss-ardeck-turnt-mit-ball-7/"; }
        if ($schildNr == "8") { $Str = "kita-schloss-ardeck-turnt-mit-ball-8/"; }
        if ($schildNr == "9") { $Str = "kita-schloss-ardeck-turnt-mit-ball-9/"; }
        if ($schildNr == "10") { $Str = "kita-schloss-ardeck-turnt-mit-ball-10/"; }
    }

    if ($_SESSION['tnr'] == 11) { //Kiga 4 mit Seil
        if ($schildNr == "0") { $Str = "kita-schloss-ardeck-turnt-mit-seil-start/"; }
        if ($schildNr == "1") { $Str = "kita-schloss-ardeck-turnt-mit-seil-1/"; }
        if ($schildNr == "2") { $Str = "kita-schloss-ardeck-turnt-mit-seil-2"; }
        if ($schildNr == "3") { $Str = "kita-schloss-ardeck-turnt-mit-seil-3/"; }
        if ($schildNr == "4") { $Str = "kita-schloss-ardeck-turnt-mit-seil-4/"; }
        if ($schildNr == "5") { $Str = "kita-schloss-ardeck-turnt-mit-seil-5/"; }
        if ($schildNr == "6") { $Str = "kita-schloss-ardeck-turnt-mit-seil-6/"; }
        if ($schildNr == "7") { $Str = "kita-schloss-ardeck-turnt-mit-seil-7/"; }
        if ($schildNr == "8") { $Str = "kita-schloss-ardeck-turnt-mit-seil-8/"; }
        if ($schildNr == "9") { $Str = "kita-schloss-ardeck-turnt-mit-seil-9/"; }
        if ($schildNr == "10") { $Str = "kita-schloss-ardeck-turnt-mit-seil-10/"; }
    }

    if ($_SESSION['tnr'] == 12) { //Kiga 5 tierische Fortbewegung
        if ($schildNr == "0") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-start/"; }
        if ($schildNr == "1") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-1/"; }
        if ($schildNr == "2") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-2"; }
        if ($schildNr == "3") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-3/"; }
        if ($schildNr == "4") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-4/"; }
        if ($schildNr == "5") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-5/"; }
        if ($schildNr == "6") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-6/"; }
        if ($schildNr == "7") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-7/"; }
        if ($schildNr == "8") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-8/"; }
        if ($schildNr == "9") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-9/"; }
        if ($schildNr == "10") { $Str = "kita-schloss-ardeck-zeigt-tierische-fortbewegungsarten-10/"; }
    }

    if ($_SESSION['tnr'] == 13) { //Kiga 6 Entspannung
        if ($schildNr == "0") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-start/"; }
        if ($schildNr == "1") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-1/"; }
        if ($schildNr == "2") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-2"; }
        if ($schildNr == "3") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-3/"; }
        if ($schildNr == "4") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-4/"; }
        if ($schildNr == "5") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-5/"; }
        if ($schildNr == "6") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-6/"; }
        if ($schildNr == "7") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-7/"; }
        if ($schildNr == "8") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-8/"; }
        if ($schildNr == "9") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-9/"; }
        if ($schildNr == "10") { $Str = "kita-schloss-ardeck-entspannung-fuer-alle-10/"; }
    }

    if ($_SESSION['tnr'] == 14) { //Kiga Bambini-Lauf
        if ($schildNr == "0") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-start/"; }
        if ($schildNr == "1") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-1/"; }
        if ($schildNr == "2") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-2"; }
        if ($schildNr == "3") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-3/"; }
        if ($schildNr == "4") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-4/"; }
        if ($schildNr == "5") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-5/"; }
        if ($schildNr == "6") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-6/"; }
        if ($schildNr == "7") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-7/"; }
        if ($schildNr == "8") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-8/"; }
        if ($schildNr == "9") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-9/"; }
        if ($schildNr == "10") { $Str = "kita-schloss-ardeck-trainiert-den-bambini-lauf-10/"; }
    }


}

//header("Status: 301 Moved Permanently");
header("Location: https://www.eue-turnt.de/".$Str);


//printf("<p>Schild-Nr: %d</p>", $schildNr);
//printf("<p>Str: %s</p>", $Str);
//var_dump($schildNr);

if (is_numeric($schildNr)) {

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

    $userAgent = $conn->real_escape_string($_SERVER['HTTP_USER_AGENT']);
    $sessionIDStr = session_id();

    $sql = "INSERT INTO `eue_schild_scans` (`schildNr`, `userAgent`, `trainingsNr`, `sessionid`) VALUES ('".$schildNr."', '".$userAgent."', '".$trainingsNr."', '".$sessionIDStr."');";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();

}

?>
