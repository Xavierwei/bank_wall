# -*- coding: utf-8 -*-

import imaplib, email, base64
from email.header import decode_header
import os
import sys
import subprocess
import pickle
import datetime
from ConfigParser import ConfigParser
import os.path
from smtplib import SMTP

reload(sys)
sys.setdefaultencoding('utf-8')

print sys.getdefaultencoding()

basepath = os.path.abspath(os.path.dirname(__file__))

def post_media_to_bankwall(desc="description", user="xx@xx.com", media="/path/to/media"):
  sys.path.append(os.path.join(basepath, "poster"))
  from poster.encode import multipart_encode
  from poster.streaminghttp import register_openers
  import urllib2
  import json

  register_openers()
  
  datagen, headers = multipart_encode({"photo": open(os.path.join(basepath , media), "rb"), 
    "desc": desc,
    "user": user})
  request = urllib2.Request("http://64.207.184.106/sgwall/api/node/postbymail", datagen, headers)
  res = urllib2.urlopen(request).read()
  res = json.loads(res)
  print res["message"]
  return res["message"]

def parse_base64_mail_mime(base_str):
  # TODO::
  return base_str

def reply_mail(mail_obj, is_success=True, body=None):
    print "begin to reply email to [%s] "  %(mail_obj["From"] or mail_obj["Reply-To"])
    original = mail_obj
    config = dict(load_config().items("smtp"))
    smtp = SMTP()
    smtp.connect('smtp.gmail.com', 587)
    smtp.starttls()
    smtp.login(config["user"], config["pass"])
    from_addr = "testdev@fuel-it-up.com"
    to_addr = original["Reply-To"] or parse_base64_mail_mime(original["From"])
    
    subject, encoding = decode_header(original["Subject"])[0]
    if encoding:
      subject = subject.decode(encoding)
      subj = u"Re: " + subject
    else:
      subj = u"Re: " + subject
    
    date = datetime.datetime.now().strftime( "%d/%m/%Y %H:%M" )
    message_text = "Hello\nThis is a mail from your server\n\nBye\n"
    if body is not None:
        msg = u"From: %s\nTo: %s\nSubject: %s\nDate: %s\n\n%s" % ( from_addr, to_addr, subj, date, body.replace('\\n','\n') )
    else:
        msg = u"From: %s\nTo: %s\nSubject: %s\nDate: %s\n\n%s" % ( from_addr, to_addr, subj, date, is_success.replace('\\n','\n') )

    smtp.sendmail(from_addr, to_addr, unicode(msg))
    smtp.quit()
    print "replied email to [%s] "  %(parse_base64_mail_mime(mail_obj["From"]) or mail_obj["Reply-To"])
  

def is_media(file):
  """ 判断是否是Media (图片/视频) """
  file = file.replace("(", "\(").replace(")", "\)")
  cmd = "/usr/bin/file -b --mime %s" % (file)
  mime = subprocess.Popen(cmd, shell=True, \
  stdout = subprocess.PIPE).communicate()[0]
  mime = mime.split(";")[0].strip()
  print "mime is [%s]" %(mime)
  
  mimes_allowed = [
    "image/gif", \
    "image/png", \
    "image/jpeg", \
    "image/jpg",  \
    "image/pjpeg", \
    "image/x-png", \
    "application/x-empty" , \
    "video/mp2p" , \
    "video/mov", \
    "video/quicktime", \
    "video/x-msvideo", \
    "video/x-ms-wmv", \
    "video/wmv", \
    "video/mp4", \
    "video/avi", \
    "video/3gp", \
    "video/3gpp", \
    "video/mpeg", \
    "video/mpg", \
    "application/octet-stream",\
    "video/x-ms-asf", \
    "video/x-ms-dvr", \
    "video/x-ms-wm",\
    'video/x-ms-wmv', \
    'video/x-msvideo', \
    'video/x-ms-asx', \
    'video/x-ms-wvx',\
    'application/x-troff-msvideo', \
    'video/x-ms-wmx']
  if mime in mimes_allowed:
    return True
  return False

def tune_file(file):
  _no_use,ext_name = os.path.splitext(file)
  if ext_name:
    return file
  file = file.replace("(", "\(").replace(")", "\)")
  cmd = "/usr/bin/file -b --mime %s" % (file)
  mime = subprocess.Popen(cmd, shell=True, \
  stdout = subprocess.PIPE).communicate()[0]
  mime = mime.split(";")[0].strip()
  
  mime_exts = {
    "image/gif" : 'gif', \
    "image/png" : "png", \
    "image/jpeg" : "jpeg", \
    "image/jpg" : 'jpg', \
    "image/pjpeg" : "jpg", \
    "image/x-png" : "png", \
    "application/x-empty" : "" , \
    "video/mp2p" : "mpeg", \
    "video/mov" : "mov", \
    "video/quicktime" : "mov", \
    "video/x-msvideo" : "avi", \
    "video/x-ms-wmv" : "wmv", \
    "video/wmv" : "wmv", \
    "video/mp4" : "mp4", \
    "video/avi" : "avi",  \
    "video/3gp" : "3gp", \
    "video/3gpp" : "3gp", \
    "video/mpeg" : "mpeg", \
    "video/mpg" : "mpeg", \
    "application/octet-stream" : "swf",\
    "video/x-ms-asf" : "asf", \
    "video/x-ms-dvr" : "dvr",  \
    "video/x-ms-wm"  : "wm", \
    'video/x-ms-wmv' : "wmv", \
    'video/x-msvideo' : "avi", \
    'video/x-ms-asx' : "asx", \
    'video/x-ms-wvx' : "WMV", \
    'application/x-troff-msvideo' : "avi", \
    'video/x-ms-wmx' : "wmx" \
    }
    
  _no_use,ext_name = os.path.splitext(file)
  if not ext_name:
    print "Old ext is None"
    new_ext_name = mime_exts[mime]
    print "ext is : %s" %(new_ext_name)
    if new_ext_name is not None:
      new_file = file + "." + new_ext_name
      print "new file name: %s" %(new_file)
      os.rename(file, new_file)
      print "Renamed file"
      file = new_file
      
  return file
  
  
  
def cache_mail(uuid, gmail_mail, filepath, inbox="inbox"):
  """缓存邮件内容"""
  print "mail id [%s] is being to cached " %(uuid)
  # From
  mfrom = email.utils.parseaddr(gmail_mail["From"])
  # Subject
  subject = gmail_mail["Subject"]
  subject, encoding = decode_header(subject)[0]
  if encoding:
    subject = subject.decode(encoding)
  
  # 打印提示
  print "Cached %s Email with %s subject !" %(mfrom, subject)
  
  cache_dir = os.path.join(basepath, "caches", inbox)
  if not os.path.isdir(cache_dir):
    os.makedirs(cache_dir)
  
  cache_file = os.path.join(cache_dir, uuid);
  if os.path.isfile(cache_file):
    return False
  cache_data = [uuid, mfrom, subject, filepath]
  sdata = pickle.dumps(cache_data)
  fp = open(cache_file, "wb")
  fp.write(sdata)
  fp.close()
  
  return cache_data

def rm_cache_mail(uuid, inbox):
  cache_dir = os.path.join(basepath, "caches", inbox)
  if not os.path.isdir(cache_dir):
    os.makedirs(cache_dir)
  
  cache_file = os.path.join(cache_dir, uuid);
  if os.path.isfile(cache_file) is False:
    return True

  os.unlink(cache_file)
  return True


def is_cached(uuid, inbox="inbox"):
  cache_dir = os.path.join(basepath, "caches", inbox)
  
  if not os.path.isdir(cache_dir):
    os.makedirs(cache_dir)
  
  cache_file = os.path.join(cache_dir, uuid)
  if not os.path.isfile(cache_file):
    return False
  return True

def reconnect_gmail(user, password):
  print "begin to reconnect mail server..."
  conn = imaplib.IMAP4_SSL("imap.gmail.com", 993)
  try:
    conn.login(user, password)
    print "login in mail account [%s] success" %(user)
    conn.select("inbox")
  except:
    print "Error when login with %s" %(user)
    return None
  return conn
    

def fetching_gamil(user, password, boxname = "inbox"):
  inbox = boxname
  # 只取最近10条邮件
  num = 10
  attachmentpath = "./attachments";
  attachmentpath = os.path.abspath(attachmentpath)
  if not os.path.isdir(attachmentpath):
    os.mkdir(attachmentpath)

  conn = imaplib.IMAP4_SSL("imap.gmail.com", 993)
  try:
    conn.login(user, password)
    print "login in mail account [%s] success" %(user)
    typ, data = conn.select(boxname)
    print "Selected box named [%s]." %(boxname)
  except:
    print "Error when login with %s" %(user)
    return

  # 选择一个 inbox

  # 执行search 命令
  # 只查询前2天的邮件 (多查询几天免得漏掉邮件)
  date = (datetime.date.today() - datetime.timedelta(1)).strftime("%d-%b-%Y")
  print "Fetching email since %s" %("(SENTSINCE {date})".format(date=date))
  result, data = conn.uid("search", None, "(SENTSINCE {date})".format(date=date))
  # result, data = conn.uid("search", None, "ALL")

  ids = data[0]
  id_list = ids.split()
  id_list.sort(reverse=True)
  
  print "ids [%s] of mail that be fetched " %(id_list)

  for eid in id_list:
    print "Begin fetch email [%s]" %(eid)
    if is_cached(eid, inbox):
      print "Email with uuid: [%s] is cached." %(eid)
      continue
      
    # 对取每个邮件进行异常处理
    try:
      result, email_data = conn.uid("fetch", eid, "(RFC822)")
    except:
        while (True):
          conn = reconnect_gmail(user, password)
          if conn is None:
              continue
          result, email_data = conn.uid("fetch", eid, "(RFC822)")
          break
    if result != "OK":
        continue
    try:
      gmail_mail = email.message_from_string(email_data[0][1])
    except:
      continue
      
    files_downloaded = []
    
    # Get attachment
    for part in gmail_mail.walk():
      if part.get_content_maintype() == "multipart":
        continue
      if part.get("Content-Disposition") is None:
        continue

      if part.get_filename() is None:
        continue
      filename = "".join(part.get_filename().split())
      is_decoed = False
          
      import time,hashlib
      #nowtimestamp = unicode(int(time.time())).encode("utf-8")
      #filename = unicode(filename).encode("utf-8")
      nowtimestamp =str(int(time.time()))
      md5 = hashlib.md5()
      try:
        md5.update(str(filename))
      except:
        continue
        
      ext = os.path.splitext(filename)[1]
      
      filename = ''.join([nowtimestamp , md5.hexdigest(), ext])

      if bool(filename):
        filepath = os.path.join(attachmentpath, filename)
        print "File: [%s] will be post." %(filepath)
        if not os.path.isfile(filepath):
            try:                
              fp = open(filepath, "wb")
              fp.write(part.get_payload(decode=True))
              fp.close()
            except Exception as e:
                print e
                continue
        else:
          # Exist same name file
          print "File : [%s] is downloaded" %(filepath)
        
        if is_media(filepath):
          # 如果文件名没有解码出来 则直接判断文件类型 然后加上文件mime 
          if is_decoed is False:
            filepath = tune_file(filepath)
          
          files_downloaded.append(filepath)
          # 在这里，先看是否已经有了缓存文件，如果有则不去发送图片到网站了
          if is_cached(eid, inbox):
            print "Mail with uuid [%s] is cached " %(eid)
            continue
          else:
            # 如果没有则先缓存图片再发送图片到网站
            data = cache_mail(eid, gmail_mail, filepath, inbox)

            if data is not None:
              print "begin to post data to bank wall"
              # 如果保存成功就发送数据到后台保存
              # From
              mfrom = email.utils.parseaddr(gmail_mail["From"])[1]
              # Subject
              subject = gmail_mail["Subject"]
              subject, encoding = decode_header(subject)[0]
              if encoding:
                subject = subject.decode(encoding)
              try:
                ret = post_media_to_bankwall(desc=subject, user=mfrom, media=filepath)
              except Exception as e:
                rm_cache_mail(eid, inbox)
                ret = None
                print e
              finally:
                if ret is not None:
                  reply_mail(gmail_mail, ret)
        else:
          if is_cached(eid):
            print "Mail with uuid [%s] is cached " %(eid)
            continue
          else:
            try:
              data = cache_mail(eid, gmail_mail, filepath)
              mfrom = email.utils.parseaddr(gmail_mail["From"])
              personal_name = ""
              if isinstance(mfrom, tuple):
                decoded_name = mfrom[0]
                if decoded_name[0:2] == "=?":
                  import base64
                  personal_name = decoded_name.replace("=?","").replace("?=","").split("?")
                  if personal_name[1] == "B":
                    personal_name = base64.b64decode(personal_name[2]).decode(personal_name[0])
              reply_mail(gmail_mail, True, "Bonjour "+ personal_name + ", \n\nLe type de fichier que vous venez de télécharger n'est pas supporté.\n\nL'équipe SG WALL\n\n\n\nDear "+ personal_name + ",\n\nThe file you upload is not support.\n\nSG WALL Team")
              print "File [%s] is not media " %(filepath)
            except Exception as e:
              print e
  try:
    conn.close()
    conn.logout()
  except:
    pass
  
  
def clean_dir(dir):
    if os.path.isdir(dir):
        paths = os.listdir(dir)
        for path in paths:
            filepath = os.path.join(dir, path)
            if os.path.isfile(filepath):
                try:
                    os.unlink(filepath)
                except:
                    print "error when remove attachment %s " %(filepath)
    return True

def load_config():
  try:
    config = ConfigParser()
    config.read("setting.ini")

    return config
  except Exception as e:
    print "setting.ini is not exists!"
    sys.exit(1)

def is_runing():
  if not os.path.isfile(".lock"):
    f = open(".lock", "w+")
    f.close()
  
  with open(".lock", "r+") as f:
    p = f.read()
    if len(p) == 0:
      return False
    else:
      return True
    
def make_lock_file():
  if not os.path.isfile(".lock"):
    f = open(".lock", "w+")
    f.close()
  
  with open(".lock", "r+") as f:
    f.write("True")

def rm_lock():
  try:
    os.unlink(".lock")
    print ".lock is removed "
  except:
    pass
  return True

def exit_handler(a1="", a2=""):
  # 退出程序时候 需要清除 attachments 
  # 必要时 清除 .lock
  clean_dir("./attachments")
  print "Cleaned attachments !"
  
  global IS_RUNING
  if IS_RUNING:
    rm_lock()
    
  # 如果是 signal 处理程序， 则清理完后还需要手动退出程序
  if a1:
    sys.exit(0)

# 全局变量
# 用来 判断程序是否只运行了一个
IS_RUNING = False

if __name__ == "__main__":
  # 注册退出处理程序
  import atexit
  # 注册signal 监听程序
  import signal
  atexit.register(exit_handler)
  signal.signal(signal.SIGINT, exit_handler)
  signal.signal(signal.SIGTSTP, exit_handler)
  
  # 首先检查是否有其他进程在运行
  if is_runing():
    print "Another process is runing"
    sys.exit(0)
  # 如果没有其他程序在运行得话，则要生成一个.lock文件
  else:
    IS_RUNING = True
    make_lock_file()
  
  try:
    config = load_config()
    account = dict(config.items("mailaccount"))
  except Exception as e:
    print "Exception: %s" %(e)
    sys.exit(1)
  
  # 程序到这里为止，就说明是只有一个进程在运行
  try:
    print "begin fetch mail from account [%s] " %(account['user'])
    boxnames = ["inbox", "[Gmail]/Spam"]
    
    for boxname in boxnames:
      fetching_gamil(account['user'], account['pass'], boxname)
  except Exception as e:
    print "Exception when fetch email !"
    exc_type, exc_value, exc_traceback = sys.exc_info()
    import traceback
    traceback.print_exception(exc_type, exc_value, exc_traceback)
    print e
  
  finally:
    sys.exit(1)
    
  

