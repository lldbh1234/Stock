#!/bin/bash
#* * * * * sh /home_path/shell/plate.sh

step=3 #间隔的秒数，不能大于60    
    
for (( i = 0; i < 60; i=(i+step) )); do
    $(curl 'http://localhost/cron/plate')
    sleep $step
done

exit 0