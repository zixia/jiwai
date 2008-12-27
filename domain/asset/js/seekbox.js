/**
 * @author shwdai@gmail.com
 */
var JWSeekbox = {
	initialized : false,
	/* window size */
	width : 100,
	height : 100,
	/* div */
	TB_overlay : null,
	TB_window : null,

	init : function()
	{
		if (JWSeekbox.TB_overlay) return;
		JWSeekbox.TB_overlay = JWSeekbox.createDiv('TB_overlay');
		JWSeekbox.positionOverlay();

		JWSeekbox.TB_window = JWSeekbox.createDiv('TB_window');
		JWSeekbox.TB_window.setOpacity(0);
	},

	hideObject : function(open){
		var elements = $A(document.getElementsByTagName('object'));
		elements.extend(document.getElementsByTagName(window.ie ? 'select' : 'embed'));
		elements.each(function(el){ if (open) el.lbBackupStyle = el.style.visibility; el.style.visibility = open ? 'hidden' : el.lbBackupStyle; });
	},

	initOptions : function(options) {
		if ( !JWSeekbox.TB_overlay ) {
			JWSeekbox.hideObject(true);
			JWSeekbox.init();
		}
		return {
			modal : options['modal'] || true,
			ajax_width : JWSeekbox.width,
			ajax_height : JWSeekbox.height
		};
	},

	showBox : function(url, options) {
		var op = JWSeekbox.initOptions(options);
		var ajax_height = op['ajax_height'];
		var ajax_width = op['ajax_width'];
		var modal = op['modal'];
		var time = (new Date()).getTime();

		JWSeekbox.TB_window.innerHTML = "<div id='TB_ajaxContent' style='width:10px;height:10px;'></div>";
		url += (url.test(/\?/) ? '&' : '?') + '_t=' + time;
		var myRequest = new Ajax(url, {method:'get',headers:{AJAX:'true'},update:$('TB_ajaxContent'),onComplete:JWSeekbox.adjust}).request();
		window.onresize = window.onscroll= JWSeekbox.positionEffect;
		if ( false==modal ) JWSeekbox.TB_overlay.onclick = JWSeekbox.remove; 
	},

	showWindow : function(){
		JWSeekbox.TB_window.setStyle('opacity',1);
	},

	remove: function() {
		JWSeekbox.hideObject(false);
		JWSeekbox.TB_window.remove();
		JWSeekbox.TB_overlay.remove();
		JWSeekbox.TB_overlay=JWSeekbox.TB_window=window.onscroll=window.onresize=null;
		return false;
	},

	positionEffect : function() {
		JWSeekbox.positionOverlay();
		JWSeekbox.positionWindow();
	},

	positionWindow : function() {
		var p = JWSeekbox.size();
		var left = (p.sl + (p.w - JWSeekbox.width)/2) + 'px';
		var top = (p.st + (p.h - JWSeekbox.height)/2) + 'px';
		JWSeekbox.TB_window.setStyles({left:left, top:top, width:JWSeekbox.width, height:JWSeekbox.height});
	},

	positionOverlay : function(){
		var p = JWSeekbox.size(); 
		var height = p.sh+'px'; var width = p.sw+'px'; 
		JWSeekbox.TB_overlay.setStyles({ height:height, top:'0px', left:'0px', width:width});
	},

	createDiv : function(id) {
		return $(id) ? $(id) : new Element('div').setProperty('id', id).injectInside(document.body);
	},

	size : function(){
		var sl = window.getScrollLeft() || document.body.scrollLeft;
		var st = window.getScrollTop() || document.body.scrollTop;
		var sw = document.body.scrollWidth;
		var sh = document.body.scrollHeight;

		var h = window.getHeight() || document.body.clientHeight;
		var w = window.getWidth() || document.body.clientWidth;
		return {sl:sl,st:st,sw:sw,sh:sh, h:h,w:w};
	},

	adjust : function(){
		var ow = JWSeekbox.width;
		var oh = JWSeekbox.height;
		JWSeekbox.width = $('TB_ajaxContent').scrollWidth;
		JWSeekbox.height = $('TB_ajaxContent').scrollHeight;
		$('TB_ajaxContent').setStyles({ width: JWSeekbox.width+'px', height: JWSeekbox.height+'px' });
		JWSeekbox.positionWindow();
		JWSeekbox.showWindow();
	}
};
