#!/usr/bin/python3

import cv2
from pyzbar import pyzbar
import imutils
from imutils.video import VideoStream
import time
import paho.mqtt.publish as publish
import hashlib
import json
import argparse
import requests #pip3 install requests
import logging


logging.basicConfig(format="%(asctime)-15s %(levelname)-8s  %(message)s")
logger = logging.getLogger("QR-Code Scanner")

parser = argparse.ArgumentParser()
parser.add_argument("security_str", help="A string to securely calculate the md5sum.")
parser.add_argument("-v", "--verbosity", help="increase output verbosity", default=0, action="count")
args = parser.parse_args()
logger.setLevel(logging.WARNING-(args.verbosity*10 if args.verbosity <=2 else 20) )

logger.info("Verbosity min. at info level.")

##

r = requests.post("https://api.pushover.net/1/messages.json", data = {
          "token": "atxpqduwx1qo2yg1bgxysxh7vgww8k", #APP key
          "user": "uTGmV4oeANh23KMHAuwK3CHwupRRVK",
          "message": 'Die Software wurde gerade neugestartet.',
          "priority": 1,
          "title": "Neustart"
        })
logger.info(r.text)
logger.info("data send to pushover")

##

# initialize video stream
vs = VideoStream(usePiCamera = True, resolution=(1920, 1440)  ).start()

time_last_debug_picture_saved = 0
time_script_started = time.time() #to terminate script after some time to prevent possible hang up of hard or software

logger.info("Script completed initialisation.")
while (time.time() - time_script_started < 60*60*1): #terminate after one hour of runtime

  time.sleep(1.0) # to not heat up the CPU too much

  # read from camera
  frame = vs.read()

  # read from a debug file for test purposes only
#  frame = cv2.imread('/home/pi/Pictures/card_0.png', 0) #0 converts it to greyscale

  # for better performance, resize the image
#  frame = imutils.resize(frame, width=1200)
  frame_small = imutils.resize(frame, width=640)
  if (time.time() - time_last_debug_picture_saved >= 10):
    cv2.imwrite("/dev/shm/time_{}.png".format(round(time.time())), frame_small) #for debug reasongs
    time_last_debug_picture_saved = time.time()
    logger.info("debug picture saved")

  # find and decode all barcodes in this frame
  barcodes = pyzbar.decode(frame)

  for barcode in barcodes:
    cv2.imwrite("/home/pi/Pictures/time_{}.png".format(round(time.time())), frame)

    d = None
    try:
      d = json.loads(barcode.data.decode("utf-8"))
    except:
      logger.warning("ERROR: QR-Code does not contain a valid JSON string.")

    if 'count' in d:
      expected_sum = hashlib.md5("{}{}".format(args.security_str, d['count']).encode('utf-8')).hexdigest()
      if expected_sum == d['hash']:
        logger.info("Valid QR-Code for nr: {}".format(d['count']))
        cv2.imwrite("/home/pi/Pictures/card_{}.png".format(d['count']), frame) # for later analysis

        cv2.imwrite("/home/pi/Pictures/lastvalid.png", frame_small) #for pushover
        r = requests.post("https://api.pushover.net/1/messages.json", data = {
          "token": "atxpqduwx1qo2yg1bgxysxh7vgww8k", #APP key
          "user": "uTGmV4oeANh23KMHAuwK3CHwupRRVK",
          "message": '''<p>Gültiger QR-Code-Scan</p>
<p>count: {}</p>
<a href="https://www.eue-turnt.de/schild/PushoverFeedback.php?count={}&hash={}">Erlauben? Dann hier drücken.</a>'''.format(d['count'], d['count'], d['hash']),
          "priority": 2,
          "html": 1,
          "expire": 60,
          "retry": 120,
          "sound": "persistent",
          "title": "Neue Laufkarte"
        },
        files = {
          "attachment": ("lastvalid.png", open("/home/pi/Pictures/lastvalid.png", "rb"), "image/png")
        })
        logger.info(r.text)
        logger.info("data send to pushover")

        publish.single("homie/qrscanner/valid", d['count'], hostname="localhost")
        time.sleep(30.0)
      else:
        logger.info("Invalid Data scanned: {}".format( barcode.data.decode("utf-8") ))
        publish.single("homie/qrscanner/invaliddata", barcode.data.decode("utf-8"), hostname="localhost")

cv2.destroyAllWindows()
vs.stop()

logger.info("Script terminated. Total runtime: {:.2f} min".format( (time.time()-time_script_started)/60 ) )
