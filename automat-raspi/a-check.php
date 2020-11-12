#!/usr/bin/php
<?php
  $str = 'https://eue-turnt.de/schild/a-rueckmeldung.php?&time='.time().'&sicherung=';

while (true) {
  $myhash = hash('sha512', strval(time()).'...');
//  echo "Sicherheit: $myhash \n";

  $url_str = $str.$myhash;
//  echo "Laden: $url_str\n";
  $ret = file_get_contents($url_str);
  if ($ret === false) {
    echo "Fehler bei der Abfrage der URL";
  } else {
    $r = json_decode($ret,true);
//    var_dump($r);
    $last_line = system('timeout 5 /home/pi/ausloesen.py '.$r['slotStatus'], $retval); 
  }

  sleep(5);
}
?>