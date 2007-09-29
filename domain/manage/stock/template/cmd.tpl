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
			if( e.substring(0,1) == '+' ) {
				if( navigator.appName == 'Netscape' ){
					window.open( e.substring(1), null, 
								"'modal=yes,width=800,height=600,resizable=no");
				}else{
					window.showModalDialog( e.substring(1), self, 
								"dialogWidth:800px;dialogHeight:600px;help:no;");
				}
			}else{
				$('result').value = e + "\n" + $('result').value;
			}
		}
	}).request();

}
</script>
<h2>终端控制 -- 输入 HELP 查看帮助</h2>
<textarea id="cmd" class="cmd" onKeyDown="if(event.keyCode==13){onCmd(this);return false;}" WRAP></textarea><br/>
<textarea readonly id='result' class="res">{$result}</textarea>
<!--{include footer}-->
<script>
	$('cmd').focus();	
</script>
