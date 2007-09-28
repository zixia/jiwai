<!--{include header}-->
<script>
function onCmd(id){
	var v = $(id).value;
	$(id).value = '';
	if( !v ) return;

	if( v.toUpperCase() == 'CLEAR' ) {
		$('result').value = '[robot@stock]$ '+v;
		return;
	}

	new Ajax( '/docmd.php?cmd='+encodeURIComponent(v), 
	{
		method: 'get',
		onSuccess: function(e) {
			$('result').value = e + "\n" + $('result').value;
		}
	}).request();

}
</script>
<h2>命令结果 - 输入 HELP 查看帮助</h2>
<textarea id="cmd" class="cmd" onKeyDown="if(event.keyCode==13){onCmd(this);return false;}" WRAP></textarea><br/>
<textarea readonly id='result' class="res">{$result}</textarea>
<!--{include footer}-->
<script>
	$('cmd').focus();	
</script>
