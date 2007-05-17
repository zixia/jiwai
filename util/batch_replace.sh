find . -type f -name '*.php' -print | grep -v svn | 
  while read filename
  do
    (
    echo "processing $filename ..."
    perl -e 'while(<>){s/GetStatusRowsByIds/GetStatusDbRowsByIds/i;print;}' < $filename > $filename.xxxxx
    echo "baking $filename ..."
    mv $filename $filename.replace.bak
    echo "renaming $filename ..."
    mv $filename.xxxxx $filename # replace output files with original
    )
done
