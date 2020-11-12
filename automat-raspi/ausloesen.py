#!/usr/bin/python3

import time
import argparse
import PCA9685

parser = argparse.ArgumentParser(description='Ausloeser für den Automaten.')
parser.add_argument("slotStatus", help="Status der Slots als String, z.B. '1,0,0,0,1' ", type=str )
args = parser.parse_args()

pwm = PCA9685.PCA9685(0x40, debug=False)
pwm.setPWMFreq(50)

# Stellung zum Halten von Dingen: pwm.setServoPulse(i,1000)
# Stellung zum Abwerfen von Dingen: pwm.setServoPulse(i,2500)

my_list = [int(item) for item in args.slotStatus.split(',')]

if len(my_list) != 5:
  raise Exception('Nicht die richtige Anzahl in der Liste übergeben. Erwartet: 5 Zahlen mit Kommata getrennt.')

for i in range(0,5):
  if my_list[i] == 1:
    #Wieder in die Original-Stellung
    pwm.setServoPulse(i, 1000)
  else:
    pwm.setServoPulse(i, 2500)