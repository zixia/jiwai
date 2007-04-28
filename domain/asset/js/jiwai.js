function loadAds(module) {
    /* Google */
   
    setTimeout("run_google();", 500);
 
}

function run_google() {
    if (!window.urchinTracker) {
        setTimeout(run_google, 500);
        return;
    }
    _uacct = "UA-287835-11";
    urchinTracker();
}
/*
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-287835-11";
urchinTracker();
</script>
*/
