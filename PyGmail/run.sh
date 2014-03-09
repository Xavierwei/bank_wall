#!/usr/bin/env bash
#python2.7 gmail.py

n=$( ps -ef | grep "python gmail" | wc -l)

# 判断： 如果没有进程在跑脚本 就直接删除.lock
if [[ n -eq 1 ]]; then
	rm .lock
fi

# 运行gmail.py
/usr/local/bin/python2.7 /var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/PyGmail/gmail.py  1>logs/log.`date "+%Y-%m-%d:%H:%M:%S"`.txt


