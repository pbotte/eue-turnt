#!/usr/bin/python3

import logging
import argparse
import paho.mqtt.publish as publish
import paho.mqtt.client as paho
import json, time
from pygame import mixer
import os.path

logging.basicConfig(format="%(asctime)-15s %(levelname)-8s  %(message)s")
logger = logging.getLogger("Sound MQTT player")

parser = argparse.ArgumentParser()
parser.add_argument("-v", "--verbosity", help="increase output verbosity", default=0, action="count")
args = parser.parse_args()
logger.setLevel(logging.WARNING-(args.verbosity*10 if args.verbosity <=2 else 20) )

logger.info("Verbosity min. at info level.")

mixer.init()

broker="192.168.2.91"

def on_connect(client, userdata, flags, rc):
  if rc==0:
    logger.debug("MQTT connected OK. Return code {}".format(rc))
    client.subscribe("homie/qrscanner/sound")
    logger.info("MQTT: Subscribed to all topics")
  else:
    logger.warning("Bad connection. Return code={}".format(rc) )

def on_disconnect(client, userdata, rc):
  if rc != 0:
    logger.warning("Unexpected MQTT disconnection. Will auto-reconnect")

def on_message(client, userdata, message):
  m = message.payload.decode("utf-8")
  logger.debug ("Topic: {}, Msg: {}".format(message.topic, m) )

  mixer.music.stop()
  if os.path.isfile(m):
    logger.info("playing: {}".format(m))
    mixer.music.load(m)
    mixer.music.play()
  else:
    logger.warning("File not found.")


client= paho.Client("sound-actor")

client.on_message=on_message
client.on_connect = on_connect
client.on_disconnect = on_disconnect

print("connecting to broker ",broker)
client.connect(broker)
client.loop_start() #start loop to process received messages in separate thread
logger.info("MQTT Loop started.")


while True:
  time.sleep(5)

client.disconnect()
client.loop_stop()
