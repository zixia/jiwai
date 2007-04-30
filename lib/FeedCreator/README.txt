Feedcreator-1.7.2-ppt, patched by Mohammad Hafiz bin Ismail

Legal
-------------
I hereby release my changes made into Feedcreator 1.7.2 under the terms of the GNU Lesser General Public License (GNU/LGPL)


Changes
-------------------------
- Add Atom 1.0 feed
- Add <enclosure> for RSS 2.0 and the similiar <link rel="enclosure"> for Atom 1.0
- Add EnclosureItem class that extends HtmlDescribable.


Changes by Fabian Wolf (info@f2w.de)
------------------------------------
- added output function outputFeed for on-the-fly feed generation


Other
---------------
- I MIGHT include pretty docs in the future, might.
- I've include a diff file against the vanilla feedcreator 1.7.2 for those who curious about what that Fabian and I have changed.



Original source code : http://www.bitfolge.de/rsscreator-en.html


http://my.iecn.net/bbs/view/102291.html

1.$item->data 传进去的值应该是int型的timestamp

2.需要设一下文件中的时区

3.如果中文页面用的是gb2312或者utf-8需要相应的把默认的encoding改掉

1.$item->data 传进去的值应该是int型的timestamp
>>如果是从mysql里取出来的，即便是整型（事实上也是字符串，在php程序中用会自动转换类型），也要用
>>intval 强制转换一下。

2.需要设一下文件中的时区
>>时区改这里：define("TIME_ZONE", "+08:00");

3.如果中文页面用的是gb2312或者utf-8需要相应的把默认的encoding改掉
>>编码改这里：var $encoding = "GBK";
>>不过事实这里只是一个默认值，调用时可以改的。
