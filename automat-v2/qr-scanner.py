#!/usr/bin/python3

# reinstall
# sudo apt install python3-opencv
# pip3 install pyzbar
# sudo apt install python-zbar
# pip3 install imutils
# pip3 install paho-mqtt
# sudo apt install python3-picamera

import time

time_last_debug_picture_saved = 0
time_script_started = time.time() #to terminate script after some time to prevent possible hang up of hard or software


import cv2
from pyzbar import pyzbar
import imutils
from imutils.video import VideoStream
import paho.mqtt.publish as publish
import hashlib
import json
import argparse
import requests #pip3 install requests
import logging
from pathlib import Path
import numpy as np


logging.basicConfig(format="%(asctime)-15s %(levelname)-8s  %(message)s")
logger = logging.getLogger("QR-Code Scanner")

parser = argparse.ArgumentParser()
parser.add_argument("security_str", help="A string to securely calculate the md5sum.")
parser.add_argument("-v", "--verbosity", help="increase output verbosity", default=0, action="count")
parser.add_argument("--no-pushover", help="if set, not pushover messages will be send.", action='store_true')
args = parser.parse_args()
logger.setLevel(logging.WARNING-(args.verbosity*10 if args.verbosity <=2 else 20) )

logger.info("Verbosity min. at info level.")


# floodfill like function
def find_components(arr):
  res = []
  dx,dy = [1,0,-1,0],[0,-1,0,1]
  N,M = arr.shape
  seen = np.zeros((N,M))
  for i in range(N):
    for j in range(M):
      if not seen[i][j] and arr[i][j]:
        todo=[(i,j)]
        seen[i][j] = 1
        cnt=0
        extreme_position = {'x':[i,i], 'y':[j,j]} #'x':min..max, 'y':min..max
        while todo:
          x,y = todo.pop()
          cnt = cnt+1
          for dX, dY in zip(dx,dy):
            X=x+dX
            Y=y+dY
            if X>=0 and X<N and  Y>=0 and Y<M and not seen[X][Y] and arr[X][Y]:
                todo.append((X,Y))
                seen[X][Y] = 1
                if X>extreme_position['x'][1]: extreme_position['x'][1]=X
                if X<extreme_position['x'][0]: extreme_position['x'][0]=X
                if Y>extreme_position['y'][1]: extreme_position['y'][1]=Y
                if Y<extreme_position['y'][0]: extreme_position['y'][0]=Y
        res.append({'pos':(i,j),'N_pixels':cnt,'extreme_position':extreme_position,
          'width':extreme_position['x'][1]-extreme_position['x'][0],
          'height':extreme_position['y'][1]-extreme_position['y'][0]})
  return res


##
if not args.no_pushover:
  r = requests.post("https://api.pushover.net/1/messages.json", data = {
          "token": "...", #APP key
          "user": "uTGmV4oeANh23KMHAuwK3CHwupRRVK",
          "message": 'Die Software wurde gerade neugestartet.',
          "priority": 1,
          "title": "Neustart"
        })
  logger.info(r.text)
  logger.info("data send to pushover")

##

# initialize video stream
video_width = 1920
video_height = 1440
page_width=148 #*mm #final page after cut
page_height=148 #*mm #final page after cut
qr_code_size = 60 #*mm

vs = VideoStream(usePiCamera = True, resolution=(video_width, video_height)  ).start()
logger.debug("wait for camera to adapt")
time.sleep(5)
last_frame = vs.read()

logger.info("Script completed initialisation.")
#while (time.time() - time_script_started < 60*60*24): #terminate after 24 hours of runtime
continue_loop = True
while continue_loop:
  #oben weiß
  publish.single("homie/qrscanner/message", "Lege die Laufkarte ein.", hostname="localhost")
  publish.single("homie/qrscanner/light", json.dumps( [[255,255,255]]*15 + [[0,0,0]]*15  ), hostname="localhost")
  time.sleep(0.5) #spend soem time to make the CPU not heating up too much

  # read from camera
  frame = vs.read()
  logger.debug("picture taken")
  loop_diff_test = cv2.subtract(frame, last_frame)
  result = not np.any(loop_diff_test)
  if result is True:
    logger.error("picture comparision shows: picture is NOT different comparend to last loop. This means, our camera connection is broken. Terminating.")
    continue_loop = False
  else:
    logger.debug("picture comparision shows: picture is different comparend to last loop. Good, our camera is still alive.")
    last_frame = frame.copy()

  # read from a debug file for test purposes only
#  frame = cv2.imread('/home/pi/Pictures/card_0.png', 0) #0 converts it to greyscale

  # for better performance, resize the image
#  frame = imutils.resize(frame, width=1200)
  frame_small = imutils.resize(frame, width=540)
  frame_gray = cv2.cvtColor(frame_small, cv2.COLOR_BGR2GRAY)
  if (time.time() - time_last_debug_picture_saved >= 10):
    cv2.imwrite("/dev/shm/time_{}.png".format(round(time.time())), frame_small) #for debug reasons
    time_last_debug_picture_saved = time.time()
    logger.info("debug picture saved")

  logger.debug("start searching for qr-codes")
  barcodes = pyzbar.decode(frame_gray)

  for barcode in barcodes: #for each barcode found
    cv2.imwrite("/home/pi/Pictures/time_{}.png".format(round(time.time())), frame) #for debugging
    publish.single("homie/qrscanner/message", "QR-Code gefunden.", hostname="localhost")
    publish.single("homie/qrscanner/sound", "qrcodefound.mp3", hostname="localhost")

    barcode_rel_x_pos = (barcode.rect.left+barcode.rect.width/2) / 540
    barcode_rel_y_pos = (barcode.rect.top+barcode.rect.height/2) / (540/video_width*video_height)
    logger.debug("QR-Code at position: {:.3f} {:.3f}".format(barcode_rel_x_pos, barcode_rel_y_pos))

    #ideal position at: 0.509 0.459
    if ( ( abs(barcode_rel_x_pos-0.51) > 0.1 ) or ( abs(barcode_rel_y_pos-0.459) > 0.05) ):
      #does not lie in the center, animation towards te center
      publish.single("homie/qrscanner/message", "Schiebe die Karte in die Mitte des Fachs.", hostname="localhost")
      publish.single("homie/qrscanner/sound", "pushtocenter.mp3", hostname="localhost")

      for i in range(3):
        publish.single("homie/qrscanner/light", json.dumps( [[255,255,0]]*15 + [[255,255,0]]*15 ), hostname="localhost")
        time.sleep(0.2)
        publish.single("homie/qrscanner/light", json.dumps( [[0,0,0]]*30 ), hostname="localhost")
        time.sleep(0.2)

    else: #lies in the middle/center
      publish.single("homie/qrscanner/message", "Die Karte liegt richtig.", hostname="localhost")

      d = None
      try:
        d = json.loads(barcode.data.decode("utf-8"))
      except:
        logger.warning("ERROR: QR-Code does not contain a valid JSON string.")

      if 'count' in d:
        expected_sum = hashlib.md5("{}{}".format(args.security_str, d['count']).encode('utf-8')).hexdigest()
        if expected_sum == d['hash']:
          logger.info("Valid QR-Code for nr: {}".format(d['count']))

          card_data = {'status': 0, 'time_lastseen': 0}

          my_filename = '/home/pi/cards/{}.json'.format(d['count'])
          my_file = Path(my_filename)
          if my_file.exists():
            logger.info("card info file already existing. attempt to read it.")
            with open(my_filename,'r') as r:
              try:
                card_data = json.load(r)
                logger.info("card info successfully read from file.")
              except:
                logger.warning("error while reading the card file")

          if card_data['status'] == 1:
            publish.single("homie/qrscanner/message", "Die Laufkarte wurde bereits eingelöst.", hostname="localhost")
            publish.single("homie/qrscanner/sound", "cardnotvalid.mp3", hostname="localhost")
            logger.warning("according to card info, this card is not valid any longer for redeem. Waitung for 1 seconds.")
            for i in range(3):
              publish.single("homie/qrscanner/light", json.dumps( [[255,0,0]]*30 ), hostname="localhost")
              time.sleep(.2)
              publish.single("homie/qrscanner/light", json.dumps( [[255,255,0]]*30 ), hostname="localhost")
              time.sleep(.2)
            publish.single("homie/qrscanner/light", json.dumps( [[255,0,0]]*30 ), hostname="localhost")
            time.sleep(1)

          else:
            cv2.imwrite("/home/pi/Pictures/card_top_{}.png".format(d['count']), frame) # for later analysis

            publish.single("homie/qrscanner/message", "2. Bild mit den Stanzungen machen...", hostname="localhost")

            #prepare picture
            publish.single("homie/qrscanner/light", json.dumps( [[0,255,0]]*15 + [[255,0,0]]*15 ), hostname="localhost")
            time.sleep(1) #spend some time to let the camera adapt
            frame_bottom = vs.read() #take a picture
            logger.debug("2nd picture taken with additional light from bottom")
            cv2.imwrite("/home/pi/Pictures/card_bottom_{}.png".format(d['count']), frame_bottom) # for later analysis

            frame_bottom_small = imutils.resize(frame_bottom, width=540)

            publish.single("homie/qrscanner/message", "Stanzungen werden überprüft...", hostname="localhost")

            #begin analysis
            logger.info("Barcode read: {}".format(barcode))

            barcode_rel_x_pos = (barcode.rect.left+barcode.rect.width/2) / video_width
            barcode_rel_y_pos = (barcode.rect.top+barcode.rect.height/2) / video_height

            img = frame_bottom_small
            rows,cols,ch = img.shape

            scale_factor = 540/4*3/page_width #540 = width of image, 4/3 aspect ratio of image
            margin = 0
            pts1 = np.float32(barcode.polygon)
            pts2 = np.float32([
              [(page_width/2-qr_code_size/2)*scale_factor+margin, (page_width/2-qr_code_size/2+margin)*scale_factor],
              [(page_width/2-qr_code_size/2)*scale_factor+margin, (page_width/2+qr_code_size/2-margin)*scale_factor],
              [(page_width/2+qr_code_size/2)*scale_factor-margin, (page_width/2+qr_code_size/2-margin)*scale_factor],
              [(page_width/2+qr_code_size/2)*scale_factor-margin, (page_width/2-qr_code_size/2+margin)*scale_factor]
              ])

            M = cv2.getPerspectiveTransform(pts1,pts2)

            dst = cv2.warpPerspective(img,M,(405,405))
            dst_top = cv2.warpPerspective(frame_small,M,(405,405))
            
            red = dst.copy()
            # set green and blue channels to 0
            red[:, :, 0] = 0
            red[:, :, 1] = 0

            green = dst.copy()
            # set red and blue channels to 0
            green[:, :, 0] = 0
            green[:, :, 2] = 0

            # compute difference: red-green
            green_to_red = green.copy()
            #shift the green part into the red channel
            green_to_red[:, :, 2] = green_to_red[:, :, 1]
            difference = cv2.subtract(red, green_to_red)


            #check the light from behind in the region of the qr code:
            # to determine the threshold for the Binary conversion, read 
            # the threshold from the center part of the image and the total
            # center part:
            cv2.imwrite("/dev/shm/last_warp_red_mask.png",red[120:300, 120:300])
            hist = cv2.calcHist([difference[120:300, 120:300]], [2], None, [256], [0,256])
            my_thres = 255
            for i in range(256):
              if hist[255-i] < 1: #below some small arbitrary threshold
                my_thres = 255-i

            #total image:
            hist_all = cv2.calcHist([difference], [2], None, [256], [0,256])
            my_thres_all = my_thres
            for i in range(my_thres, 256):
              if hist_all[i] > 0: #above some small arbitrary threshold
                my_thres_all = i
            my_thres_all = 100 if my_thres_all < 100 else my_thres_all
            logger.debug("mythres: {}, mythres_all: {}".format(my_thres, my_thres_all))

            red_dst = difference[:, :, 2].copy() #convert into single color image do not use cvtColor as it lower intensity on reds:  #cv2.cvtColor(red, cv2.COLOR_BGR2GRAY); #convert to gray
            # put threshold between my_thres and my_thres_all
            ret3,th3 = cv2.threshold(red_dst,my_thres + (my_thres_all-my_thres)/2,255,cv2.THRESH_BINARY) #cut out the light which still goes through the middle of the card
            th3 [80:320,80:320] = 0 #set the inner part to black


            dst_masked = dst.copy()
            dst_masked[th3 != 255] = [0,0,0]
            dst_top[th3 == 255] = [0,0,255]

            #end of analysis

            stamps = find_components(th3)
            number_stamps = 0
            for i in stamps:
              if i['N_pixels'] > 100 and i['N_pixels'] < 1500 and \
                i['width'] > 10 and i['width'] < 60 and \
                i['height'] > 10 and i['height'] < 60:
                number_stamps +=1
                logger.info("Stamp found: {}".format(i) )
              else:
                logger.debug("Stamp does not match requirements: {}".format(i) )
            if number_stamps>12: number_stamps=12

            v_img = cv2.vconcat([frame_small[0:405 , 0:405], dst_masked])
            cv2.imwrite("/dev/shm/last_combined.png", v_img) #will also be usede for pushover

            v_img = cv2.vconcat([
              cv2.hconcat([frame_small[0:405 , 0:405],           dst, red, difference]),
              cv2.hconcat([frame_bottom_small[0:405 , 0:405], green, dst_masked, dst_top])])
            cv2.imwrite("/dev/shm/last_combined_overview.png", v_img)




            if number_stamps>0:
              card_data['status'] = 1 # set card to be not valid any longer
              publish.single("homie/qrscanner/valid", d['count'], hostname="localhost")
              publish.single("homie/qrscanner/message", "Laufkarte gültig. Ausgabe!", hostname="localhost")
              publish.single("homie/qrscanner/sound", "redeem.mp3", hostname="localhost")

              if not args.no_pushover:
                r = requests.post("https://api.pushover.net/1/messages.json", data = {
                    "token": "...", #APP key
                    "user": "uTGmV4oeANh23KMHAuwK3CHwupRRVK",
                    "message": '''<p>Gültiger QR-Code-Scan</p>
                      <p>count: {}</p>
                      <a href="https://www.eue-turnt.de/schild/PushoverFeedback.php?count={}&hash={}">Erlauben? Dann hier drücken.</a>'''.format(d['count'], d['count'], d['hash']),
                    "priority": 2,
                    "html": 1,
                    "expire": 60,
                    "retry": 30,
                    "sound": "persistent",
                    "title": "Neue Laufkarte"
                  },
                  files = {
                    "attachment": ("last_combined_overview.png", open("/dev/shm/last_combined_overview.png", "rb"), "image/png")
                  })
                logger.info(r.text)
                logger.info("data send to pushover")

              for i in range(5):
                publish.single("homie/qrscanner/light", json.dumps( [[0,255,0]]*30 ), hostname="localhost")
                time.sleep(0.2)
                publish.single("homie/qrscanner/light", json.dumps( [[0,0,0]]*30 ), hostname="localhost")
                time.sleep(0.2)
              logger.info("Grünes Licht eingeschaltet. Jetzt Ausgabe.")
              publish.single("homie/qrscanner/light", json.dumps( [[0,255,0]]*30 ), hostname="localhost")
              time.sleep(1.0)
            else:
              logger.info("Rotes Blink-Licht wird eingeschaltet, da keine Stanzungen gefunden.")
              publish.single("homie/qrscanner/message", "Keine Stanzungen gefunden. Fehler? Schreibe uns!", hostname="localhost")
              publish.single("homie/qrscanner/sound", "nostampsfound.mp3", hostname="localhost")
              for i in range(3):
                publish.single("homie/qrscanner/light", json.dumps( [[255,0,0]]*30 ), hostname="localhost")
                time.sleep(.2)
                publish.single("homie/qrscanner/light", json.dumps( [[0,0,0]]*30 ), hostname="localhost")
                time.sleep(.2)
              publish.single("homie/qrscanner/light", json.dumps( [[255,0,0]]*30 ), hostname="localhost")
              time.sleep(1)


            card_data['time_lastseen'] = time.time()
            with open(my_filename, 'w') as outfile:
              json.dump(card_data, outfile)
            logger.info("card info written to file: {}".format(my_filename))

            time.sleep(2.0)
        else:
          logger.info("Invalid data scanned: {}".format( barcode.data.decode("utf-8") ))
          publish.single("homie/qrscanner/message", "Ungültiger QR-Code auf Laufkarte.", hostname="localhost")



cv2.destroyAllWindows()
vs.stop()

logger.info("Script terminated. Total runtime: {:.2f} min".format( (time.time()-time_script_started)/60 ) )

