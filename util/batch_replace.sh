find . -type f -name '*.php' -print | grep -v svn | 
  while read filename
  do
    (
    echo "processing $filename ..."
    perl -e '$content=""; while(<>){$content.=$_;} $content=~s#您#你#sig;print $content;' < $filename > $filename.xxxxx
    echo "baking $filename ..."
    mv $filename $filename.replace.bak
    echo "renaming $filename ..."
    mv $filename.xxxxx $filename # replace output files with original
    )
done
