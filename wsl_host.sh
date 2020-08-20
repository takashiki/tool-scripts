#!/bin/bash

# Funcion: automatic set windows ip to wsl2 hosts file
# Author: takashiki
# Website: https://qxzzf.com

hostip=$(cat /etc/resolv.conf | grep nameserver | awk '{ print $2 }')
sed -i -r "/^ *[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+ +windows/d" /etc/hosts
echo "${hostip} windows" >> /etc/hosts
