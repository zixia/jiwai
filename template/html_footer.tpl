<!--{if $oblockid}-->
<script type="text/javascript">
window.jiwai_init_hook_ctrelm = function(){
	ctrObj('ctr_{$oblockid}','elm_{$oblockid}');
};
</script>
<!--{/if}-->
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>
<script type="text/javascript">
window.jiwai_init_hook_urchintracker = function() {
	if (!window.urchinTracker) {
		setTimeout(window.jiwai_init_hook_urchintracker, 500);
		return;
	}
	_uacct = "UA-2771171-2";
	_uOsr[24]="iask"; _uOkw[24]="k";
	_uOsr[25]="sogou"; _uOkw[25]="query";
	_uOsr[26]="qihoo"; _uOkw[26]="kw";
	_uOsr[27]="daqi"; _uOkw[27]="content";
	_uOsr[28]="soso.com"; _uOkw[28]="w";
	_uOsr[29]="baidu"; _uOkw[29]="wd";
	_uOsr[30]="3721"; _uOkw[30]="name";
	_uOsr[31]="baidu"; _uOkw[31]="word";
	_uOsr[32]="qq.com"; _uOkw[32]="w";
	urchinTracker();
};
</script>
</body>
</html>
