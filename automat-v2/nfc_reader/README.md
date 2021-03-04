# NFC-Reader to MQTT

Add to file /etc/udev/rules.d/99-com.rules and insert
```bash
 KERNEL=="hidraw*", SUBSYSTEM=="hidraw", MODE="0666"
```
and run afterwards:
```bash
sudo udevadm trigger
```

