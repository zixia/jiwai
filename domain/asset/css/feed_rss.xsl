<Q:stylesheet version="1.0"
  xmlns:Q = "http://www.w3.org/1999/XSL/Transform"
  xmlns:sy = "http://purl.org/rss/1.0/modules/syndication/"
  xmlns:rss = "http://purl.org/rss/1.0/"
  xmlns:Interglacial = "http://interglacial.com/rss/#Misc1"
  xmlns = "http://www.w3.org/1999/xhtml"
>

<Q:output method="html" />
<Q:template match="/">

<Q:element name="html"><Q:attribute name="class">RssToHtmlByXsl</Q:attribute>
<head>
<script type="text/javascript"><![CDATA[
var is_decoding;
var DEBUG = 0;

function complaining (s) { alert(s);  return new Error(s,s); }

if(!(   document.getElementById )) throw complaining("Your browser is too old to render this page properly." + "  Consider going to getfirefox.com to upgrade."); 
function check_decoding () {
  var d = document.getElementById('cometestme');
  if(!d) {
    throw complaining("Can't find an id='cometestme' element?");
  } else if(!('textContent' in d)) {
    // It's a browser with a halfassed DOM implementation (like IE6)
    // that doesn't implement textContent!  Assume that if it's that
    // dumb, it probably doesn't implement disable-content-encoding.
  } else {
    var ampy = d.textContent;
    //if(DEBUG > 1) { alert("Got " + ampy); }

    if(ampy == undefined) throw complaining("'cometestme' element has undefined text content?!");
    if(ampy == ''       ) throw complaining("'cometestme' element has empty text content?!"    );

    if      (ampy == "\x26" ) { is_decoding =  true; }
    else if (ampy == "\x26amp;" ) { is_decoding = false; }
    else             { throw complaining('Insane value: "' + ampy + '"!'); }
  }

  var msg =
   (is_decoding == undefined) ? "I can't tell whether the XSL processor supports disable-content-encoding!D"
   : is_decoding ? "The XSL processor DOES support disable-content-encoding"
   : "The XSL processor does NOT support disable-content-encoding" ;
  if(DEBUG) alert(msg);
  return msg;
}

function go_decoding () {
  check_decoding();
  if(is_decoding) {
    DEBUG && alert("No work needs doing - already decoded!");
    return;
  }
  var to_decode = document.getElementsByName('decodeme');
  if(!( to_decode && to_decode.length )) {
    DEBUG && alert("No work needs doing - no elements to decode!");
    return;
  }
  if(!(  ( "innerHTML"   in to_decode[0]) &&  ( "textContent" in to_decode[0])  ))
    throw complaining( "Your JavaScript version doesn't implement DOM " +
     "properly enough to show this page correctly.  " +
     "Consider going to getfirefox.com to upgrade.");
  var s;
  for(var i = to_decode.length - 1; i >= 0; i--) { 
    s = to_decode[i].textContent;
    if( s == undefined || (s.indexOf('&') == -1 && s.indexOf('<') == -1)) {
      // the null or markupless element needs no reworking
    } else {
      to_decode[i].innerHTML = s;  // that's the magic
    }
  }
  return;
}
]]></script>
  <Q:element name="meta">
   <Q:attribute name="content-type">text/html; charset=utf-8</Q:attribute>
  </Q:element>
  <Q:element name="link">
   <Q:attribute name="rel">stylesheet</Q:attribute>
   <Q:attribute name="href">/css/nova_rss.css</Q:attribute>
   <Q:attribute name="type">text/css</Q:attribute>
   <Q:attribute name="title">mainlook</Q:attribute>
  </Q:element>

<Q:for-each select="/rss/channel/title">
  <title>RSS: <Q:value-of select="."/></title>
</Q:for-each>


<!--
 Make a nice link-alternate thing so that when viewed in Firefox et al,
 the little "RSS" subscribey-icon appears.
-->
<Q:for-each select="/rss/channel/Interglacial:self_url">
  <link rel="alternate" type="application/rss+xml">
    <Q:attribute name="href"><Q:value-of select="."/></Q:attribute>
    <Q:choose>
      <Q:when test="/rss/channel/title">
        <Q:attribute name="title"><Q:value-of select="/rss/channel/title"/></Q:attribute>
      </Q:when>
      <Q:otherwise>
        <Q:attribute name="title">This RSS feed</Q:attribute>
      </Q:otherwise>
    </Q:choose>
  </link>

</Q:for-each>

</head>

<body onload="go_decoding();explicate_lastBuildDate();">

<div id="cometestme" style="display:none;"><Q:text disable-output-escaping="yes" >&amp;amp;</Q:text></div>

<p class='meantForReader'>This is an RSS file.</p>
<p class='back'><a href="/" accesskey="U" title="Back to site">[Back]</a></p>



<blockquote class='aboutThisFeed'>
<Q:for-each select="/rss/channel/lastBuildDate"><p><em>
 Last feed update:</em>
 <span id="lastBuildDate"><Q:value-of select="."/></span></p></Q:for-each>

<Q:for-each select="/rss/channel/Interglacial:livejournal"
  ><p><em>LiveJournal:</em>

  <a href="http://syndicated.livejournal.com/{.}/profile"
    title="about the Livejournal syndication of this feed"
  ><img
   src="http://interglacial.com/rss/lj_syndicated.gif"
   width="16" height="16" class="lj"
  /></a><a href="http://syndicated.livejournal.com/{.}/?style=mine"
     accesskey="l" title="the LiveJournal view of this feed"
  ><Q:value-of select="."/></a></p></Q:for-each>

<Q:for-each select="/rss/channel/Interglacial:generator_url"
  ><p><em>Perl generator:</em>
  <a accesskey="p" href="{.}">source here</a></p></Q:for-each>
<Q:for-each select="/rss/channel/webMaster"><p><em>
 Feed admin:</em> <Q:value-of select="." /></p></Q:for-each>
<Q:for-each select="/rss/channel/language"><p><em>
 Language:</em>
 <Q:value-of select="." />
</p></Q:for-each>


</blockquote>


<h1 class="feedtitle"><a accesskey="0" href="{/rss/channel/link}">
  <Q:value-of select="/rss/channel/title"/>
</a></h1>

<Q:for-each select="/rss/channel/description">
  <Q:if test=". != /rss/channel/title" >
  <!-- no point in printing them both if they're the same -->
    <p class='desc'><Q:value-of select="."/></p>
  </Q:if>
</Q:for-each>


<Q:if test="/rss/channel/sy:updatePeriod" >
  <p class='updatefreq'>This feed updates

    <Q:variable name="F" select="/rss/channel/sy:updateFrequency" />
    <Q:choose>
      <Q:when test="$F = '' or $F = 1" > once </Q:when>
      <Q:otherwise> <Q:value-of select="$F"/> times </Q:otherwise>
    </Q:choose>

    <Q:value-of select="/rss/channel/sy:updatePeriod"/>.
    Don't poll it any more often than that! 
  </p>
</Q:if>

<Q:if test="/rss/channel/item/enclosure" >
  <p class="notes">This RSS feed is also a
   <a href='http://en.wikipedia.org/wiki/Podcasting'
   >Podcast</a>, which you can read in iTunes, WinAmp, etc.</p>
</Q:if>

<Q:variable name="C" select="count(/rss/channel/item)" />
<p class='leadIn'>
  <Q:choose>
    <Q:when test="$C = 0" >No items </Q:when>
    <Q:when test="$C = 1" >The only item </Q:when>
    <Q:otherwise>The <Q:value-of select="$C" /> items </Q:otherwise>
  </Q:choose>
  currently in this feed:
</p>



<dl class='Items'>

<Q:if test='$C = 0'>  <dt>(Empty)</dt> </Q:if>


<Q:for-each select="/rss/channel/item">

<dt>

  <Q:for-each select="enclosure">
     <!-- There can be 0, 1, or many enclosures for each item. -->
     <span class="enclosure"><a href="{@url}" type="{@type}"
       title="Click to download a '{@type}' file of about {@length} bytes"
     ><img
       alt  ="Click to download a '{@type}' file of about {@length} bytes"
       src="http://interglacial.com/rss/dl_icon.gif"
       width="35" height="36" border="0"
     /></a></span>
  </Q:for-each>

  <a href="{link}">
    <Q:if test="position() &lt; 10">
      <Q:attribute name='accesskey'><Q:value-of select="position()" /></Q:attribute>
    </Q:if>

    <Q:choose>
      <Q:when test="not(title) or title = ''" ><em>(No title)</em></Q:when>
      <Q:otherwise      ><Q:value-of select="title"/></Q:otherwise>
    </Q:choose>
  </a>
</dt>

<Q:if test="description" >
  <dd name="decodeme"
><Q:value-of  disable-output-escaping="yes" select="description" /></dd>
  <!--
   Alas, many implementations can't, and never will, directly
   support disable-output-escaping.  We try to work around that
   with our JavaScript thing.
  -->
</Q:if>
</Q:for-each>
</dl>



<!-- The bottom-of-page options: -->
<p class='end'>
[<a href="/">Back to home</a>]

<Q:for-each select="/rss/channel/Interglacial:self_url">
&#160;&#160;&#160;&#160;
 [<a href="http://feedvalidator.org/check?url={.}" accesskey="v">Validate
 this feed</a>]
</Q:for-each>

</p>


<p class="badcss">
Hm, you're apparently using a browser that doesn't
support stylesheets properly<em style="display: none">, or at all</em>.
You should really think about using
<a href="http://www.mozilla.org/products/firefox/">Firefox</a>
instead.
</p>

<script><![CDATA[
var month2num = {
    Jan:0, Feb:1, Mar:2, Apr:3, May:4, Jun:5,
    Jul:6, Aug:7, Sep:8, Oct:9, Nov:10, Dec:11
};

function RFC822_to_date (s) {
   var m = s.match(
    /^\w+, (\d\d) (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) (\d\d\d\d) (\d\d):(\d\d):(\d\d) \+0800$/
   );   // like: "Mon, 23 May 2005 07:04:59 GMT"
        //             1   2   3   4  5  6
   if(!m) return undefined;

   var moment = new Date();
   moment.setUTCFullYear( parseInt( m[3],10));
   moment.setUTCMonth(    month2num[m[2]] );
   moment.setUTCDate(     parseInt( m[1],10));
   moment.setUTCHours(    parseInt( m[4],10));
   moment.setUTCMinutes(  parseInt( m[5],10));
   moment.setUTCSeconds(  parseInt( m[6],10));
   moment.setUTCMilliseconds(0);
   if( isNaN( moment.getTime() ) ) return undefined;
   return moment;
}

function seconds_ago (dateobj) {
  return Math.round(
    ( new Date().getTime() - dateobj.getTime() ) / 1000
  );
}

function explicate_lastBuildDate () {
  if( ! document.getElementById ) return;  // sanity-check

  var el = document.getElementById('lastBuildDate');  if(!el     ) return;
  var text    = el.firstChild;                        if(!text   ) return;
  var lastmod = text.data;                            if(!lastmod) return;
  var mtime   = RFC822_to_date(lastmod);              if(!mtime  ) return;
  var s       = seconds_ago(mtime);                   if(isNaN(s)) return;
  s+=8*3600;

  el.setAttribute( 'title', mtime.toLocaleString() + "  (" + lastmod + ")" );
  s =
    (s<  -600)? undefined  // More than two minutes in the apparent future!?
   :(s<     1)? "just now"
   :(s<    90)?("about " +            s       .toString() + " seconds ago")
   :(s<  5400)?("about " + Math.round(s/   60).toString() + " minutes ago")
   :(s<129600)?("about " + Math.round(s/ 3600).toString() + " hours ago"  )
   :           (           Math.round(s/86400).toString() + " days ago"   )
  ;
  if(s) text.data = s;
  return;
}

]]></script>
</body>
</Q:element>
</Q:template>
</Q:stylesheet>
