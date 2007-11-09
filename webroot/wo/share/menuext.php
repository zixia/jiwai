<?php 
$serverName = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'JiWai.de';
?>
<script type="text/javascript">
if (external.menuArguments) {
	var m = external.menuArguments,
		u = m.location.href,
		d = m.document,
		el = m.event.srcElement,
		t = d.title,
		s = d.selection?(d.selection.type!='None'?d.selection.createRange().text:''):(d.getSelection?d.getSelection():''),
		e = encodeURIComponent;
	if (el.tagName.toLowerCase() == "a") {
		u = el.getAttribute("href");
		t = el.innerText;
	}
	void(window.open('http://<?php echo $serverName;?>/wo/share/s?u=' + e(u) + '&amp;t=' + e(t) + '&amp;d=' + e(s) + '&amp;', 'JiWaiSharer', 'toolbar=0,status=0,resizable=0,width=540,height=310'));
} else {
	history.go(-1);
}
</script>
