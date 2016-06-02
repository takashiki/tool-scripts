#!/bin/bash

#Funcion: backup archives and mysql database
#Author: takashiki
#Website: http://blog.skyx.in

#IMPORTANT!!!Please Setting the following Values!

readonly EXPIRED_DAYS=3
readonly BACKUP_HOME="/data/backup/"
readonly MYSQL_DUMP="/usr/local/mysql/bin/mysqldump"
readonly ENABLE_QINIU=1
readonly QSHELL="/usr/bin/qshell"
readonly QINIU_BUCKET="backup"
######~Set Directory you want to backup~######
readonly BACKUP_DIR=("/data/wwwroot/dir" "/data/wwwroot/dir2")
readonly EXCLUDE_DIR=("vendor")

######~Set MySQL Database you want to backup~######
readonly BACKUP_DB=("db" "db2")

######~Set MySQL UserName and password~######
readonly MYSQL_USERNAME='root'
readonly MYSQL_PASSWORD='password'

#Values Setting END!

BACKUP_TIME=$(date +"%Y%m%d%H%M")
CURRENT_BACKUP_HOME=${BACKUP_HOME}${BACKUP_TIME}/
OLD_BACKUP=$(date +"%Y%m%d" --date="${EXPIRED_DAYS} days ago")*

function backup_archive()
{
    local backup_path=$1
    local dir_name=`echo ${backup_path##*/}`
    local pre_dir=`echo ${backup_path}|sed 's/'${dir_name}'//g'`
    local exclude=''
    for dir in ${EXCLUDE_DIR[@]};do
       exclude=${exclude}' --exclude='${dir}
    done
    tar zcf ${CURRENT_BACKUP_HOME}${dir_name}-${BACKUP_TIME}.tar.gz -C ${pre_dir} ${dir_name} ${exclude}
}

function backup_sql()
{
    local dump_file=${CURRENT_BACKUP_HOME}db-$1-${BACKUP_TIME}.sql
    ${MYSQL_DUMP} -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} $1 > ${dump_file}
    tar zcf ${CURRENT_BACKUP_HOME}db-$1-${BACKUP_TIME}.tar.gz -C ${CURRENT_BACKUP_HOME} ${dump_file##*/}
    /bin/rm -f ${dump_file}
}

function qiniu_upload()
{
    local temp_file=${CURRENT_BACKUP_HOME}${BACKUP_TIME}.tar
    tar cvf ${temp_file} -C ${CURRENT_BACKUP_HOME} . --exclude=${temp_file##*/}
    echo "tar cvf ${temp_file} -C ${CURRENT_BACKUP_HOME} *"
    ${QSHELL} rput ${QINIU_BUCKET} ${BACKUP_TIME}.tar ${temp_file} 
    #rm -f ${temp_file}
}

if [ ! -f ${MYSQL_DUMP} ]; then  
    echo "mysqldump command not found.please check your setting."
    exit 1
fi

if [ ! -d ${CURRENT_BACKUP_HOME} ]; then  
    mkdir -p ${CURRENT_BACKUP_HOME}
fi

echo "Backup website files..."
for dd in ${BACKUP_DIR[@]};do
    backup_archive ${dd}
done

echo "Backup Databases..."
for db in ${BACKUP_DB[@]};do
    backup_sql ${db}
done

echo "Delete old backup files: ${BACKUP_HOME}${OLD_BACKUP} ..."
rm -rf ${BACKUP_HOME}${OLD_BACKUP}


if [ ${ENABLE_QINIU} ]; then  
    echo "Upload backup files to qiniu..."
    qiniu_upload
fi

echo "complete."
