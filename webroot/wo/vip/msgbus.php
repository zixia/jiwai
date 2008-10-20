<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();
JWLogin::MustLogined(true);
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script>
function handleContent(event)
{
    var result = event.target.responseText;
    document.getElementById("chat").innerHTML += "</br>" + result;
}

function load()
{
    var xrequest = new XMLHttpRequest();
    xrequest.multipart = true;
    try {
        xrequest.open("GET","http://jiwai.de/wo/vip/_msgbus",false);
        xrequest.onload = handleContent;
        xrequest.send(null);
    }
    catch (e) {
        alert(e);
    }
}
</script>
</head>

<body onload="load()">
<hr>
<text id="chat"><b>hello world</b></text>
</body>
</html>
