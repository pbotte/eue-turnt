#!/usr/bin/python3

import serial
import sys
import time
from datetime import datetime
import paho.mqtt.client as paho
import json
import re
import logging
import argparse

format = "%(asctime)-9s %(levelname)-8s  %(message)s"
logging.basicConfig(format=format, datefmt="%H:%M:%S")
logger = logging.getLogger("redeemspiral communicator")

parser = argparse.ArgumentParser(description='Communicator between MQTT and serial device.')
parser.add_argument("-v", "--verbosity", help="increase output verbosity", default=0, action="count")
parser.add_argument("-b", "--mqtt-broker-host", help="MQTT broker hostname", default="localhost")
parser.add_argument("-t", "--watchdog-timeout", help="timeout in seconds for the watchdog", default=20*60*10, type=int) # ca. 10 mins
parser.add_argument("mqtt_client_name", help="MQTT client name. Needs to be unique in the MQTT namespace, eg fsr-ww.", type=str)
parser.add_argument("serial_device_name", help="Serial port used, eg /dev/ttyUSB0", type=str)
args = parser.parse_args()
logger.setLevel(logging.WARNING-(args.verbosity*10 if args.verbosity <=2 else 20) )

print(args)
mqtt_client_name_with_id = args.mqtt_client_name + '-f'
logger.info("MQTT client name: "+mqtt_client_name_with_id )
logger.info("Watchdog timeout (seconds): "+str(args.watchdog_timeout))
logger.info('Use the following Serial-Device: '+str(args.serial_device_name) )
ser = serial.Serial(args.serial_device_name, 115200, timeout=0.1)

queue = []

def on_message(client, userdata, message):
  m = message.payload.decode("utf-8")

  logger.info("Topic: "+message.topic+" message: {}".format(m))
  msplit = re.split("/", message.topic)
  logger.info("{}".format(msplit))
  if len(msplit) == 3 and msplit[2].lower() == "set" and msplit[1].lower() == mqtt_client_name_with_id:
    ser.write(b'+')
    logger.debug("redeem cmd sent" )

def on_connect(client, userdata, flags, rc):
  if rc==0:
    logger.info("MQTT connected OK. Return code "+str(rc) )
    for i in range(4):
      client.subscribe('homie/{}-{}/set'.format(args.mqtt_client_name, i))
    logger.debug("MQTT: Subscribed to all topics")
  else:
    logger.error("Bad connection. Return code="+str(rc))

def on_disconnect(client, userdata, rc):
  if rc != 0:
    logger.warning("Unexpected MQTT disconnection. Will auto-reconnect")

client= paho.Client(mqtt_client_name_with_id)
client.on_message=on_message
client.on_connect = on_connect
client.on_disconnect = on_disconnect
logger.info("connecting to broker: "+args.mqtt_broker_host+". If it fails, check whether the broker is reachable. Check the -b option.")
client.connect(args.mqtt_broker_host)
client.loop_start() #start loop to process received messages in separate thread
logger.debug("MQTT Loop started.")


WatchDogCounter = args.watchdog_timeout
while (WatchDogCounter > 0):
  line = ser.readline()
  if line:
    line = line.decode("utf-8").strip().replace('\r','').replace('\n','')
    logger.debug("serial read: {}".format(line))
    j = json.loads(line)
    mqtt_client_name_with_id = '{}-{}'.format(args.mqtt_client_name, j['config_id'])
    client.publish("homie/"+mqtt_client_name_with_id+"/status", line, qos=1, retain=False)
    WatchDogCounter = args.watchdog_timeout
  time.sleep(.05)

  WatchDogCounter -= 1

ser.close()

