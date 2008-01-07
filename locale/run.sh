#!/bin/bash

if [ "x${1}" == "xgenerate" ]; then
    for dir in zh_CN zh_TW en_US ja_JP; do
        echo "generate ${dir}/LC_MESSAGES/messages.po"
        xgettext -k=_ --keyword=__ --keyword=_e -j -F -i --from-code utf-8 \
            `find ../ -type f -iname "*.php"` -o ${dir}/LC_MESSAGES/messages.po 
    done
else
    for dir in zh_CN zh_TW en_US ja_JP; do
        echo "generate ${dir}/LC_MESSAGES/messages.mo"
        msgfmt -f "${dir}/LC_MESSAGES/messages.po" -o "${dir}/LC_MESSAGES/messages.mo"
    done
fi
