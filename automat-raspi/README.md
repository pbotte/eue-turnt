# Belohnungsautomat

## Aufbau
- RasPi 3B
- Mit PoE Splitter von Sertronics
- Raspi HAT: https://www.waveshare.com/servo-driver-hat.htm
- Wichtig: Die Spannungsversorgung des HATs ist separat von der des RasPi. Sonst kommt es bei Bewegungen der Servomotoren zu Abstürzen des RasPi.
- 5 Servos Micro Servo SG90

## Logik

- der Zustand des Automaten ist vollständig in der Datenbank, Table automat-status (?) abgelegt. Vor Ort auf dem Rechner wird kein Zustand gespeichert.
- Alle 5 Sekunden ruft das Skript `a-check.php` diese Datenbank ab und stellt die Servos entsprechend.
- Die Einträge in der Datenbank werden durch die Datei in /schild/vorautomat.php getätigt.
- Das Nachlegen geschieht durch Aufruf von `a-nachlegen.php`

## Weiteres

- QR-Code-Erzeugung: http://sourceforge.net/apps/mediawiki/phpqrcode/index.php?title=Main_Page

## NFC-Scanner

Damit der Nutzer pi auch auf den Scanner zugreifen kann, d.h. die Datei `nfc-reader.py` ausgeführt werden kann:

Inhalt der Datei `/etc/udev/rules.d/99-hidraw.rules`:
```bash
KERNEL=="hidraw*", SUBSYSTEM=="hidraw", MODE="0664", GROUP="plugdev"
```

Außerdem noch den Nutzer pi in die entsprechende Gruppe:
```bash
sudo usermod -a -G plugdev pi
```

