#pip3 install reportlab reportlab-qrcode
# user guide: https://www.reportlab.com/docs/reportlab-userguide.pdf

# fonts from Google fonts:
# https://fonts.google.com/?preview.text_type=custom

# Laufkartengenerator
# Erzeuge ein PDF, welches die Laufkarten zum Drucken bei der Druckerei erzeugt.
import os
os.system("pip3 install reportlab reportlab-qrcode") #quick and dirty

from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.lib.units import mm, cm
from reportlab.lib.colors import red, white, black
from reportlab.pdfgen.canvas import Canvas
from reportlab.lib.colors import CMYKColorSep
from reportlab_qrcode import QRCodeImage
import qrcode
import hashlib
import json

my_scurity_secret = "CHANGE TO YOUR SECRET"


#Register Fonts to use them and embed
pdfmetrics.registerFont(TTFont("OpenSans-Regular", './fonts/Open_Sans/OpenSans-Regular.ttf'))
pdfmetrics.registerFont(TTFont("OpenSans-Bold", './fonts/Open_Sans/OpenSans-Bold.ttf'))
pdfmetrics.registerFont(TTFont("CourierPrime-Regular", './fonts/Courier_Prime/CourierPrime-Regular.ttf'))

# do this trick to get rid of the Helvetica font referred to in the pdf when checking it with
# $ apt install poppler-utils
# $ pdffonts Laufkarten.pdf 
#
# from https://groups.google.com/g/reportlab-users/c/c0ZsnCz3hXk/m/eTjLl8PtXxMJ
from reportlab import rl_config
rl_config._SAVED['canvas_basefontname'] = 'OpenSans-Regular'
rl_config._startUp() 

TurnvereinRot = CMYKColorSep(25/100, 98/100, 91/100, 21/100, spotName='TurnvereinRot')

page_width=148*mm #final page after cut
page_height=148*mm #final page after cut
page_cut_margin = 3*mm # defined by printer / printer workshop
qr_code_size = 60*mm

start_count = 600
page_count = 600

stanzer_breite = 30*mm
zwischenraum = (page_width-4*stanzer_breite)/3


doc = Canvas('Laufkarten.pdf', pagesize=(page_width+2*page_cut_margin, page_height+2*page_cut_margin))
#doc.setFillOverprint(True)

for mycount in range(start_count, start_count+page_count):
#    doc.setFillColor(TurnvereinRot)
#    doc.rect(0,0,page_width+2*page_cut_margin, page_height+2*page_cut_margin, stroke=0, fill=1)
    
    #print page cut off margin
    doc.setFillColor(black)
    #doc.rect(page_cut_margin, page_cut_margin, page_width, page_height, stroke=1, fill=0)
    
    qr = QRCodeImage(size=qr_code_size,
        fill_color='black',
        back_color='white',
        border=1,
        version=2,
        error_correction=qrcode.constants.ERROR_CORRECT_H,
    )
    
    myhash = hashlib.md5('{}{}'.format(my_scurity_secret, mycount).encode('utf-8'))
    mydict = {"count":mycount, "hash": myhash.hexdigest()}
    qr.add_data(json.dumps(mydict))
    
    qr.drawOn(doc, page_cut_margin+page_width/2 - qr_code_size/2, page_cut_margin+page_height/2 - qr_code_size/2)
    
    doc.setFillColor(black)
    doc.setFont("CourierPrime-Regular", 8)
    doc.drawString(4*mm+page_cut_margin, 4*mm+page_cut_margin, "{}".format(mycount))
    
    doc.setFont("OpenSans-Bold", 18)
    doc.saveState()
    doc.drawCentredString(page_cut_margin+page_width/2, page_cut_margin+page_height/2 + qr_code_size/2 + 1*mm, "TV Gau-Algesheim".format(mycount))
    doc.rotate(90) #das Drehen scheint auch die Achsen zu spiegeln? Deshalb y -> -y
    doc.drawCentredString(page_cut_margin+page_width/2, -1*(page_cut_margin+page_height/2 - qr_code_size/2 - 1*mm), "Classic/Kids/Challenge".format(mycount))
    doc.rotate(90)
    doc.drawCentredString(-1* (page_cut_margin+page_width/2), -1*(page_cut_margin+page_height/2 - qr_code_size/2 - 1*mm), "Eue-Turnt-Läufe".format(mycount))
    doc.rotate(90)
    doc.drawCentredString(-1*(page_cut_margin+page_width/2), page_cut_margin+page_height/2 + qr_code_size/2 + 1*mm, "Laufkarte für alle".format(mycount))
    doc.restoreState()
    
    doc.setFillColor(black)
    #ecke unten links, unten re, oben li, oben re
    doc.saveState()
    doc.setLineWidth(0.2)
    doc.setFont("OpenSans-Regular", 8)
    for k in range(4):
        x_offset = 0 if (k==0) or (k==1) else -1*(2*page_cut_margin + page_width)
        y_offset = 0 if (k==0) or (k==3) else -1*(2*page_cut_margin + page_width)
        doc.roundRect(x_offset+page_cut_margin-1*cm, y_offset+page_cut_margin-1*cm, stanzer_breite+1*cm, stanzer_breite+1*cm, 2*mm, stroke=1, fill=0)
        for i in range(1,3):
            doc.roundRect(x_offset+page_cut_margin+i*(stanzer_breite + zwischenraum), y_offset+page_cut_margin-1*cm, stanzer_breite, stanzer_breite+1*cm, 2*mm, stroke=1, fill=0)
        for i in range(3):
          doc.drawCentredString(x_offset+page_cut_margin+i*(stanzer_breite + zwischenraum)+stanzer_breite/2, y_offset+page_cut_margin+stanzer_breite/2, "Stanz")
          doc.drawCentredString(x_offset+page_cut_margin+i*(stanzer_breite + zwischenraum)+stanzer_breite/2, y_offset+page_cut_margin+stanzer_breite/2-5*mm, "mich!")
        doc.rotate(90)
    doc.restoreState()
    
    doc.showPage() #finishes the current page
    
    
    doc.saveState()
    doc.setFont("OpenSans-Bold", 18)
    doc.drawCentredString(page_cut_margin+page_width/2, page_cut_margin+page_height-2*cm, "Turnverein Eintracht")
    doc.drawCentredString(page_cut_margin+page_width/2, page_cut_margin+page_height-3*cm, "1880 Gau-Algesheim")
    doc.setFont("OpenSans-Bold", 32)
    doc.drawCentredString(page_cut_margin+page_width/2, page_cut_margin+page_height-4.5*cm, "Eue-Turnt-Laufkarte")
    doc.setFont("OpenSans-Regular", 12)
    doc.drawString(page_cut_margin+1.5*cm, page_cut_margin+page_height-6.5*cm-0.7*cm*0, "1. Stanze an jeder Station deine Karte.")
    doc.drawString(page_cut_margin+1.5*cm, page_cut_margin+page_height-6.5*cm-0.7*cm*1, "    Auf der Vorderseite, in welches der Kästchen ist egal.")
    doc.drawString(page_cut_margin+1.5*cm, page_cut_margin+page_height-6.5*cm-0.7*cm*2, "2. Du brauchst 5 Figuren beim Kids-Lauf, 10 beim")
    doc.drawString(page_cut_margin+1.5*cm, page_cut_margin+page_height-6.5*cm-0.7*cm*3, "    Classic-Lauf und nur eine bei einer Challenge.")
    doc.drawString(page_cut_margin+1.5*cm, page_cut_margin+page_height-6.5*cm-0.7*cm*4, "3. Lege deine Karte mit dem QR-Code nach oben")
    doc.drawString(page_cut_margin+1.5*cm, page_cut_margin+page_height-6.5*cm-0.7*cm*5, "    in den Automaten.")
    doc.setFont("OpenSans-Bold", 12)
    doc.drawString(page_cut_margin+1.5*cm, page_cut_margin+page_height-6.5*cm-0.7*cm*6.5, "Genieße deine Belohnung und komme bald wieder!")
    doc.setFont("OpenSans-Regular", 12)
    doc.drawCentredString(page_cut_margin+page_width/2, page_cut_margin+page_height-6.5*cm-0.7*cm*8.5, "Hinweis: Die Karte nicht knicken und")
    doc.drawCentredString(page_cut_margin+page_width/2, page_cut_margin+page_height-6.5*cm-0.7*cm*9.5, "nur für einen Lauf verwenden.")
    doc.restoreState()
    
    doc.showPage() #finishes the current page
    
    print('Laufkarte {} erzeugt.'.format(mycount))


doc.save()
