#!/bin/bash

readonly GIT="/usr/bin/git"
readonly USER_GROUP="www:www"
readonly PROJECTS=(
    "/data/wwwroot/default"
)

function update()
{
    local project=$1
    cd ${project}
    ${GIT} reset --hard
    ${GIT} pull
    /bin/chown ${USER_GROUP} ${project} -R
}

for project in ${PROJECTS[@]};do
    update ${project}
done
