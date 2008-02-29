## NOTE: DO NOT FORGET THE BLANKET [] IF YOU WANNAR TAG IT

%feedMap = (
    'newsmth'   => {
        'nameScreen'=> 'newsmth',
        'nameFull'  => '[水木社区]',
        'password'  => 'newsmthbbsdem1ma',
        'feedUrl'   => 'http://www3.newsmth.net/rssi.php?h=1',
    },
    'slashdot'  => {    ## GFWed
        'nameScreen'=> 'slashdot',
        'nameFull'  => '[slashdot]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'http://rss.slashdot.org/Slashdot/slashdot',
    },
    'freshmeat' => {    ## Too much
        'nameScreen'=> 'freshmeat',
        'nameFull'  => '[freshmeat]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'http://feeds.pheedo.com/freshmeatnet_announcements_global',
    },
    'sourceforge'   => {
        'nameScreen'=> 'sourceforge',
        'nameFull'  => '[sourceforge]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'http://feeds.pheedo.com/sourceforgenet_news',
    },
    'engadget'      => {
        'nameScreen'=> 'engadget',
        'nameFull'  => '[瘾科技]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'http://cn.engadget.com/rss.xml',
    },
    'youkuhot'      => {
        'nameScreen'=> 'youkuhot',
        'nameFull'  => '[优酷]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'http://www.youku.com/index/rss_cool_v/',
        'filter'    => 's/www\.youku/v\.youku/gi',
        'ratelimit' => 1,
    },
    'fedora'        => {
        'nameScreen'=> 'fedora',
        'nameFull'  => '[fedora]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'http://planet.fedoraproject.org/rss20.xml',
    },
    'microsoft'     => {
        'nameScreen'=> 'microsoft',
        'nameFull'  => '[microsoft]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'http://planet.fedoraproject.org/rss20.xml',
    },
    'msdn'          => {
        'nameScreen'=> 'msdn',
        'nameFull'  => '[msdn]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'http://www.microsoft.com/feeds/MSDN/globalfeeds/en-us/Global-MSDN-en-us.xml',
    },
    'dw-world'      => {
        'nameScreen'=> 'dw-world',
        'nameFull'  => '[德国之声]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'https://poxy.dotft.com/tunnel.php?broken=http://rss.dw-world.de/rdf/rss-chi-all',
        'filter'    => 's/\?maca=chi-rss-chi-all-\d+-rdf//g',
    },
    'edu2do'        => {
        'nameScreen'=> 'edu2do',
        'nameFull'  => '[益学会]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'http://edu2do.com/blog/feed/',
    },
    'memedia'       => {
        'nameScreen'=> 'memedia.cn',
        'nameFull'  => '[草莓周刊]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'https://poxy.dotft.com/tunnel.php?broken=http://memedia.cn/feed/',
    },
    'twitter'       => {
        'nameScreen'=> 'twitter',
        'nameFull'  => '[MicroBlogging]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'https://poxy.dotft.com/tunnel.php?broken=http://feeds.feedburner.com/TwitterBlog',
        'version'   => 1,
    },
    'jaiku'         => {
        'nameScreen'=> 'jaiku',
        'nameFull'  => '[MicroBlogging]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'https://poxy.dotft.com/tunnel.php?broken=http://www.jaiku.com/blog/feed/',
    },
    'google'        => {
        'nameScreen'=> 'googleblog',
        'nameFull'  => '[Google]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'https://poxy.dotft.com/tunnel.php?broken=http://feeds.feedburner.com/blogspot/MKuf',
        'version'   => 1,
        'redirect'  => 'feedburner',
    },
    'ftchinese'     => {
        'nameScreen'=> 'ftchinese',
        'nameFull'  => '[News]',
        'password'  => 'geekdem1ma',
        'feedUrl'   => 'http://www.ftchinese.com/sc/rss_s.jsp',
        'version'   => 1,
    },
);

