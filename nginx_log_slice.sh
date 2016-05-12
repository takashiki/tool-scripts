#!/bin/bash

#Funcion: slice nginx log
#Author: takashiki
#Website: http://blog.skyx.in

#IMPORTANT!!!Please Setting the following Values!

readonly LOGS=("/data/wwwlogs/frontend-access.log" "/data/wwwlogs/*nginx.log" "/data/wwwlogs/main")
readonly BAK_DIR="/data/wwwlogs/bak/"
readonly PID_FILE="/var/run/nginx.pid"

function process_path()
{
    local log_path=$1
    
    for log_file in `find ${log_path}`;do
        if [ ! -f ${log_file} ]; then
            continue
        fi

        process_file ${log_file}
    done
}

function process_file()
{
    local log_file=$1
    #重命名并迁移日志文件
    bak_name=`basename ${log_file}`_$(date -d "yesterday" +"%Y%m%d%H%M%S")
    mv ${log_file} ${BAK_DIR}${bak_name}
    tar zcf ${BAK_DIR}${bak_name}.tar.gz -C ${BAK_DIR} ${bak_name} --remove-files
}

if [ ! -f ${PID_FILE} ]; then  
    echo "pid file not exsits."
    exit 1
fi

if [ ! -d ${BAK_DIR} ]; then  
    mkdir ${BAK_DIR} -p
fi

for log_path in ${LOGS[@]};do
    process_path ${log_path}
done

#向nginx主进程发信号重新打开日志
kill -USR1 `cat ${PID_FILE}`

#清理7天前的日志
find ${BAK_DIR} -name '*'  -mtime +90 | xargs rm -rf {}