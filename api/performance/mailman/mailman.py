import os.path
# -*- coding: utf-8 -*-

import os
from smtplib import SMTP
import email
import mimetypes
import email.mime.application
import random
from email import encoders
from email.mime.audio import MIMEAudio
from email.mime.base import MIMEBase
from email.mime.image import MIMEImage
import datetime
import threading

class SendMailThread (threading.Thread):
  def __init__(self, group=None, target=None, name=None, args=(), kwargs={}):
    self.next_file_pos = 0
    threading.Thread.__init__(self, group, target, name, args, kwargs)
  
  def next_file(self):
    pos = self.next_file_pos
    self.next_file_pos = self.next_file_pos + 1 
    return files[pos]
    
  def run(self):
    # 一个线程发50封邮件
    count = 50
    
    print "Start thread to send mail [Thread ID : %s]" %(self.getName())
    print "Thread [%s]: Begin to login mail server" %(self.getName())
    smtp = reconnect_server()
    print "Thread [%s]: Login mail server success " %(self.getName())

    while (count > 0):
      print "Thread [%s]: Random mail body" %(self.getName())
      file = self.next_file()
      body = random_mail_body(file)
      
      print "Thread [%s]: Random mail body success" %(self.getName())

      try:
        print "Thread [%s] : Trying to send mail"  %(self.getName())
        smtp.sendmail(config["from"], config["api_mail"], body)
        print "Thread [%s]: Mail has been sent " %(self.getName())
      except:
        print "Thread [%s]: Reconnect to server " %(self.getName())
        smtp = reconnect_server()
        print "Thread [%s]:  Reconnected server" %(self.getName())
        # 这里，重新链接服务器后 上一封邮件没有发送成功
        count = count + 1

      count = count - 1

    print "Thread [%s]: Quit"  %(self.getName())  
    smtp.quit()

config = {
  # SMTP account / 发件人
  "user": "upload@wall150ans.com",
  "pass": "hbsg1502014",
  # 收件人
  "api_mail": "397420507@qq.com",
  # 貌似没用
  "from": "testdev@fuel-it-up.com"
}

files = ["/Users/jackeychen/Downloads/sgtestmaterial/yarratrams.mpeg",
  "/Users/jackeychen/Downloads/sgtestmaterial/ScreenFlow_2.mpg"]

def random_mail_body(file = ''):
  msg = email.mime.Multipart.MIMEMultipart()
  msg["Subject"] = "Text video from test script"
  msg["From"] = config["from"]
  msg["To"] = config["api_mail"]
  msg["Date"] = datetime.datetime.now().strftime( "%d/%m/%Y %H:%M" )
  body = email.mime.Text.MIMEText("""Hi, I am just test script. ignore me please""")
  msg.attach(body)
  
  if file is "":
    file = random.choice(files)
  
  msg["Subject"] += " From file "+ os.path.basename(file)
  
  ctype, coding = mimetypes.guess_type(file)
  
  maintype, subtype = ctype.split("/", 1)
  att = None
  if maintype == "image":
    fp = open(file, 'rb')
    att = MIMEImage(fp.read(), _subtype = subtype)
    fp.close()
  elif maintype == "audio":
    fp = open(file, "rb")
    att = MIMEAudio(fp.read(), _subtype = subtype)
    fp.close()
  else:
    fp = open(file, 'rb')
    att = MIMEBase(maintype, subtype)
    att.set_payload(fp.read())
    fp.close()
    # Encode the payload using Base64
    encoders.encode_base64(att)
    att.add_header('Content-Disposition', 'attachment; filename=%s' %(os.path.basename(file)))
    
  if att:
    msg.attach(att)
  print "File [%s] will be sent to server"  %(file)
  return msg.as_string()
  

def reconnect_server():
  smtp = SMTP()
  smtp.connect('smtp.gmail.com', 587)
  smtp.starttls()
  smtp.login(config["user"], config["pass"])
  
  return smtp

if __name__ == "__main__":
  
  # 发一千封邮件
  # 一个线程50封邮件， 20个线程就是 1000个邮件
  thread_count  = 20
  threads = []
  for i in range(0, 20):
    new_thread = SendMailThread()
    print "New thread with name [%s]" %( new_thread.getName() )
    new_thread.setDaemon(True)
    threads.append(new_thread)
    
  # 开始线程
  for thread in threads:
    thread.start()
    
  # 等待线程
  for thread in threads:
    thread.join()
    
  print "Finished"
  
  


