/*
 *	JiWai.de Lib
 *	Author: zixia@zixia.net
 *	AKA Inc.
 *	2007-05
 */

var JiWai = 
{ 
	mVersion 	: 1,

	GetBgColor : function () 
	{
   		var id_element = $(arguments[0]);

		var color;
		var n=0;
		while ( 100>n++ ) 
		{
			try {
				color = id_element.getStyle('background-color');
			} catch ( e ) {
				break;
			}

			id_element = id_element.getParent();

			if ( 'transparent'!=color )
				break;
		}

		if ( 'transparent'==color )
			color = '#fff';

		return color;
	},

	Yft		: function (selector, hideSecs) 
	{
		return;
/*
(function(){alert(1);return this;}).delay(1000);

alert('ok');
//.chain(function(){alert(2)}).chain(function(){alert(3)}).chain(function(){alert(4)});
*/

		$$(selector).each( function(yft_element) 
		{
			background_color = yft_element.getStyle('background-color');

			orig_color 		= this.GetBgColor(yft_element);

			yellow_color	= new Color('#ff0');
			yellow_color	= yellow_color.mix(orig_color);

			yft_element.effect(
				'background-color'
				,{
				 	duration: 3000
					,transition: Fx.Transitions.Quad.easeOut
				}
			).start(
				orig_color
				,yellow_color
			).chain( function () 
				{
					yft_element.effect
					(
					 	'background-color'
						,{
						 	duration: 1000
							,transition: Fx.Transitions.Bounce.easeOut
						}
					).start(yellow_color,orig_color)
				}
			).chain( function () 
				{
					yft_element.setStyle('background-color', background_color);

					if ( hideSecs )
					{
						(
							function()
							{
								var mySlider = new Fx.Slide(yft_element, {duration: 500});
								mySlider.toggle();
								//yft_element.setStyle('display', 'none');
							}
						).delay(hideSecs*1000); // FIXME
					}
				}
			) 
		}, JiWai ); // end each
	},
	AssetUrl: function(src) {
		return "http://asset."+location.host+src;
	},
	AddScript: function(src) {
		var g = document.createElement("script");
		g.type = "text/javascript";
		g.src = JiWai.AssetUrl(src);
		document.getElementsByTagName('head')[0].appendChild(g);
	},
	ToggleStar: function(id) {
		var el = $('status_star_'+id);
		var action = (el.src.indexOf('full')==-1 ? 'create' : 'destroy');
		new Ajax( '/wo/favourites/'+action+'/'+id, {
			method: 'get',
			headers: {'AJAX':true},
			onSuccess: function() {
				el.src = el.src.replace(/throbber/g, action=='create' ? 'star_full' : 'star_empty');
			}
		}).request();
		el.src=JiWai.AssetUrl('/img/icon_throbber.gif') 
	},
	DoTrash: function(id) {
		if (confirm('请确认操作：删除后将永远无法恢复！')) 
		{
			new Ajax( '/wo/status/destroy/'+id, {
				method: 'post',
				data: '_method=delete',
				headers: {'AJAX':true},
				onSuccess: function() {
					if (!$('status_'+id)) location.reload();
				}
			}).request();
			setTimeout(function() {
				var el = $('status_'+id);
				if (!el) return;
				var line = el.getNext() || el.getPrevious();
				(new Fx.Slide(el)).slideOut().addEvent('onComplete', function() { el.remove(); });
				if (line) if (line.hasClass('line')) line.remove();
			}, 0);
		};
	},
	ChangeDevice: function(dev) {
		new Ajax( '/wo/account/update_send_via', {
			method: 'post',
			headers: {'AJAX':true},
			data: 'current_user[send_via]='+dev,
			onSuccess: function(html) { $('device_list').setHTML(html); }
		}).request();
	},
	Refresh: function() {
		var last = 0;
		$$('#timeline .odd').each(function(el) {
			var id = el.id.split('_')[1];
			if (id>last) last = id;
		});
		if (!last) return;
		new Ajax(location.path, {
			method: 'get',
			data: 'ajax&last='+last,
			onSuccess: function(html) {
				alert(html);
			}
		});
		setTimeout(JiWai.Refresh, RefreshInterval*1000);
	},
	AutoEmote: function() {
		if (!$("timeline")) return;
		_auto_emote = "timeline";
		JiWai.AddScript("/system/emote/themes/default.js");
	},
	ShowThumb: function(el) {
		el.style.display='inline';
	},
	HideThumb: function(el) {
		el.style.display='none';
	},
	ShowTip: function(txt, url) {
		$('sitetip').setHTML(txt + (url ? '<a href="'+url+'">查看</a> ' : '') + '<a href="#" onclick="JiWai.HideTip();">消除</a>');
		$('sitetip').style.display='block';
	},
	HideTip: function(){
		$('sitetip').style.display='none';
	},
	onLoad: function() {
		JiWai.AutoEmote();
		if (window.RefreshInterval && location.search && location.search.length>1) setTimeout(JiWai.Refresh, RefreshInterval*1000);
	},
	Init: function() {
		window.TimeOffset = window.ServerTime ? Math.floor((new Date()).getTime()/1000) - window.ServerTime : 0;
		window.addEvent('domready', JiWai.onLoad); 
	}
}

JiWai.Init();
