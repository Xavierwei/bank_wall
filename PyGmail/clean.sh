#!/usr/bin/env bash
#python2.7 gmail.py

# Cron 
############ 每月第一天执行清理JOB
#  * * 1 * * cd /var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/PyGmail && bash clean.sh 

n=$( ps -ef | grep -v grep | grep "gmailclean.py" | wc -l)

if [[ n -eq 1 ]]; then
       exit
fi

# 运行gmailclean.py
/usr/local/bin/python2.7 /var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/PyGmail/gmailclean.py  1>logs/log.`date "+%Y-%m-%d:%H:%M:%S"`.txt
