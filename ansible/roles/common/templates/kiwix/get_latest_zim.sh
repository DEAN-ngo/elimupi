for ENTRY in `rsync --recursive --list-only \
  download.kiwix.org::download.kiwix.org/zim/ | grep ".zim" | \
  tr -s ' ' | cut -d ' ' -f5 | sort -r` ; do RADICAL=`echo $ENTRY | \
  sed 's/_20[0-9][0-9]-[0-9][0-9]\.zim//g'`; if [[ $LAST != $RADICAL \
  ]] ; then echo $ENTRY ; LAST=$RADICAL ; fi ; done