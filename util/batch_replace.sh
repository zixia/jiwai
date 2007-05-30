find . -type f -name '*.php' -print | grep -v svn | 
  while read filename
  do
    (
    echo "processing $filename ..."
    perl -e '$content=""; while(<>){$content.=$_;} $content=~s#^<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\n##sig;print $content;' < $filename > $filename.xxxxx
    echo "baking $filename ..."
    mv $filename $filename.replace.bak
    echo "renaming $filename ..."
    mv $filename.xxxxx $filename # replace output files with original
    )
done
