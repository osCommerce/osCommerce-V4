#!/bin/bash

SCRIPT_DIR=`dirname $0`
cd ${SCRIPT_DIR}
/usr/bin/php -dmemory_limit=768M -f yii.php events/datasource > lib/console/runtime/logs/cron.txt 2>&1
