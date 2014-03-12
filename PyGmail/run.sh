#!/usr/bin/env bash
#python2.7 gmail.py


n=$( ps -ef | grep -v grep | grep "gmail.py" | wc -l)

if [[ n -eq 1 ]]; then
       exit 
fi

# 运行gmail.py
/usr/local/bin/python2.7 /var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/PyGmail/gmail.py  1>logs/log.`date "+%Y-%m-%d:%H:%M:%S"`.txt
