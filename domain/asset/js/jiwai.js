/*
Script: PBBAcpBox.js
	Contains <PBBAcpBox>

Author:
	Pokemon_JOJO, <http://www.mibhouse.org/pokemon_jojo>

License:
	MIT-style license.

*/

/*
Class: PBBAcpBox
	Clone class of original javascript function : 'alert', 'confirm' and 'prompt'

Arguments:
	options - see Options below

Options:
	name - name of the box for use different style
	zIndex - integer, zindex of the box
	onReturn - return value when box is closed. defaults to false
	onReturnFunction - a function to fire when return box value
	BoxStyles - stylesheets of the box
	OverlayStyles - stylesheets of overlay
	showDuration - duration of the box transition when showing (defaults to 200 ms)
	showEffect - transitions, to be used when showing
	closeDuration - Duration of the box transition when closing (defaults to 100 ms)
	closeEffect - transitions, to be used when closing
	onShowStart - a function to fire when box start to showing
	onCloseStart - a function to fire when box start to closing
	onShowComplete - a function to fire when box done showing
	onCloseComplete - a function to fire when box done closing
*/
var PBBAcpBox = new Class({

	getOptions: function(){
		return {
			name: 'PBBAcp',
			zIndex: 65555,
			onReturn: false,
			onReturnFunction : Class.empty,
			BoxStyles: {
				'width': 500
			},
			OverlayStyles: {
				'background-color': '#000',
				'opacity': 0.7
			},
			showDuration: 200,
			showEffect: Fx.Transitions.linear,
			closeDuration: 100,
			closeEffect: Fx.Transitions.linear,
			moveDuration: 500,
			moveEffect: Fx.Transitions.backOut,
			onShowStart : Class.empty,
			onShowComplete : Class.empty,
			onCloseStart : Class.empty,
			onCloseComplete : function(properties) {
				this.options.onReturnFunction(this.options.onReturn);
			}.bind(this)
		};
	},

	initialize: function(options){
		this.setOptions(this.getOptions(), options);

		// création de l'overlay
		this.Overlay = new Element('div', {
			'id': 'BoxOverlay',
			'styles': {
				'display': 'none',
				'z-index': this.options.zIndex,
				'position': 'absolute',
				'top': '0',
				'left': '0',
				'background-color': this.options.OverlayStyles['background-color'],
				'opacity': 0,
				'height': window.getScrollHeight() + 'px',
				'width': window.getScrollWidth() + 'px'
			}
		});

		this.Content = new Element('div', {
			'id': this.options.name + '-BoxContent'
			, 'styles': { 'background-color': '#fff' }
		});

		this.InBox = new Element('div', {
			'id': this.options.name + '-InBox'
		}).adopt(this.Content);;
		
		this.Box = new Element('div', {
			'id': this.options.name + '-Box',
			'styles': {
				'display': 'none',
				'z-index': this.options.zIndex + 2,
				'position': 'absolute',
				'top': '0',
				'left': '0',
				'width': this.options.BoxStyles['width'] + 'px'
			}
		}).adopt(this.InBox);

    this.Overlay.injectInside(document.body);
    this.Box.injectInside(document.body);
    
		// Si le navigateur est redimentionné
		window.addEvent('resize', function() {
			if(this.options.display == 1) {
				this.Overlay.setStyles({
					'height': window.getScrollHeight() + 'px',
					'width': window.getScrollWidth() + 'px'
				});
				this.replaceBox();
			}
		}.bind(this));
		
		window.addEvent('scroll', this.replaceBox.bind(this));
	},

	/*
	Property: display
		Show or close box
		
	Argument:
		option - integer, 1 to Show box and 0 to close box (with a transition).
	*/	
	display: function(option){
		// Stop la transition en action si elle existe	
		if(this.Transition)
			this.Transition.stop();				

		// Show Box	
		if(this.options.display == 0 && option != 0 || option == 1) {
			this.Overlay.setStyle('display', 'block');
			this.options.display = 1;
			this.fireEvent('onShowStart', [this.Overlay]);

			// Nouvelle transition		
			this.Transition = this.Overlay.effect(
				'opacity', 
				{
					duration: this.options.showDuration,
					transition: this.options.showEffect,
					onComplete: function() {
						sizes = window.getSize();
						this.Box.setStyles({
							'display': 'block',
							'left': (sizes.scroll.x + (sizes.size.x - this.options.BoxStyles['width']) / 2).toInt()
						});
						this.replaceBox();
						this.fireEvent('onShowComplete', [this.Overlay]);
					}.bind(this)
				}
			).start(this.options.OverlayStyles['opacity']);
		}
		// Close Box
		else {
			this.Box.setStyles({
				'display': 'none',
				'top': 0
			});
			this.Content.empty();
			this.options.display = 0;

			this.fireEvent('onCloseStart', [this.Overlay]);

			// Nouvelle transition		
			this.Transition = this.Overlay.effect(
				'opacity',
				{
					duration: this.options.closeDuration,
					transition: this.options.closeEffect,
					onComplete: function() {
						this.fireEvent('onCloseComplete', [this.Overlay]);
					}.bind(this)
				}
			).start(0);
		}			
	},

	/*
	Property: replaceBox
		Move Box in screen center when brower is resize or scroll
	*/
	replaceBox: function() {
		if(this.options.display == 1) {
			sizes = window.getSize();

			if(this.MoveBox)
				this.MoveBox.stop();
			
			this.MoveBox = this.Box.effects({
				duration: this.options.moveDuration,
				transition: this.options.moveEffect
			}).start({
				'left': (sizes.scroll.x + (sizes.size.x - this.options.BoxStyles['width']) / 2).toInt(),
				'top': (sizes.scroll.y + (sizes.size.y - this.Box.offsetHeight) / 2).toInt()
			});
		}
	},

	/*
	Property: messageBox
		Core system for show all type of box
		
	Argument:
		type - string, 'alert' or 'confirm' or 'prompt'
		message - text to show in the box
		properties - see Options below
		input - text value of default 'input' when prompt
		
	Options:
		textBoxBtnOk - text value of 'Ok' button
		textBoxBtnCancel - text value of 'Cancel' button
		onComplete - a function to fire when return box value
	*/	
	messageBox: function(type, message, properties, input) {
		properties = Object.extend({
			'textBoxBtnOk': 'OK',
			'textBoxBtnCancel': 'Cancel',
			'textBoxInputPrompt': null,
			'onComplete': Class.empty
		}, properties || {});

		this.options.onReturnFunction = properties.onComplete;
		
		if(type == 'alert') {
			this.AlertBtnOk = new Element('input', {
				'id': 'BoxAlertBtnOk',
				'type': 'submit',
				'value': properties.textBoxBtnOk,
				'styles': {
					'width': '70px'
				}
			});
			
			this.AlertBtnOk.addEvent('click', function() {
				this.options.onReturn = true;
				this.display(0);
			}.bind(this));
		
			this.Content.setProperty('class','BoxAlert').setHTML(message + '<br />');
			this.AlertBtnOk.injectInside(this.Content);
			this.display(1);
		}
		else if(type == 'confirm') {
			this.ConfirmBtnOk = new Element('input', {
				'id': 'BoxConfirmBtnOk',
				'type': 'submit',
				'value': properties.textBoxBtnOk,
				'styles': {
					'width': '70px'
				}
			});

			this.ConfirmBtnCancel = new Element('input', {
				'id': 'BoxConfirmBtnCancel',
				'type': 'submit',
				'value': properties.textBoxBtnCancel,
				'styles': {
					'width': '70px'
				}
			});

			this.ConfirmBtnOk.addEvent('click', function() {
				this.options.onReturn = true;
				this.display(0);
			}.bind(this));

			this.ConfirmBtnCancel.addEvent('click', function() {
				this.options.onReturn = false;
				this.display(0);
			}.bind(this));		

			this.Content.setProperty('class','BoxConfirm').setHTML(message + '<br />');
			this.ConfirmBtnOk.injectInside(this.Content);
			this.ConfirmBtnCancel.injectInside(this.Content);
			this.display(1);
		}
		else if(type == 'prompt') {
			this.PromptBtnOk = new Element('input', {
				'id': 'BoxPromptBtnOk',
				'type': 'submit',
				'value': properties.textBoxBtnOk,
				'styles': {
					'width': '70px'
				}
			});

			this.PromptBtnCancel = new Element('input', {
				'id': 'BoxPromptBtnCancel',
				'type': 'submit',
				'value': properties.textBoxBtnCancel,
				'styles': {
					'width': '70px'
				}
			});			

			this.PromptInput = new Element('input', {
				'id': 'BoxPromptInput',
				'type': 'text',
				'value': input,
				'styles': {
					'width': '250px'
				}
			});

			this.PromptBtnOk.addEvent('click', function() {
				this.options.onReturn = this.PromptInput.value;
				this.display(0);
			}.bind(this));

			this.PromptBtnCancel.addEvent('click', function() {
				this.options.onReturn = false;
				this.display(0);
			}.bind(this));

			this.Content.setProperty('class','BoxPrompt').setHTML(message + '<br />');
			this.PromptInput.injectInside(this.Content);
			new Element('br').injectInside(this.Content);
			this.PromptBtnOk.injectInside(this.Content);
			this.PromptBtnCancel.injectInside(this.Content);
			this.display(1);
		}
		else {
			this.options.onReturn = false;
			this.display(0);		
		}
	},

	/*
	Property: alert
		Shortcut for alert
		
	Argument:
		properties - see Options in messageBox
	*/		
	alert: function(message, properties){
		this.messageBox('alert', message, properties);
	},

	/*
	Property: confirm
		Shortcut for confirm
		
	Argument:
		properties - see Options in messageBox
	*/
	confirm: function(message, properties){
		this.messageBox('confirm', message, properties);
	},

	/*
	Property: prompt
		Shortcut for prompt
		
	Argument:
		properties - see Options in messageBox
	*/	
	prompt: function(message, input, properties){
		this.messageBox('prompt', message, properties, input);
	}
});

PBBAcpBox.implement(new Events, new Options);
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
			var refresh = false;
			new Ajax( '/wo/status/destroy/'+id, {
				method: 'post',
				data: '_method=delete',
				headers: {'AJAX':true},
				onSuccess: function() {
					if (refresh) location.reload();
				}
			}).request();
			setTimeout(function() {
				var el = $('status_'+id);
				if (!el) { refresh = true; return; }
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
	EnableDevice: function(id, postdata) {
		new Ajax( '/wo/devices/enable/'+id, {
			method: 'post',
			headers: {'AJAX':true},
			data: postdata
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
	HideTip: function() {
		$('sitetip').style.display='none';
	},
	removeFriend: function(screenName) {
		new Ajax( '/wo/friendships/destory/'+screenName, {
			method: 'get',
			data: {'note': note},
			headers: {'AJAX':true},
			onSuccess: function () {
				if ($('subscribe1')) {
					$('subscribe1').setText('已订阅');
					$('subscribe1').onclick='';
				}
				if ($('subscribe2')) {
					$('subscribe2').setText('添加 '+screenName);
					$('subscribe2').onclick='JiWai.addFriend('+screenName+')';
				}
			}
		}).request();
	},
	_createFriend: function(screenName, note) {
		new Ajax( '/wo/friendships/create/'+screenName, {
			method: 'post',
			data: {'note': note},
			headers: {'AJAX':true},
			onSuccess: function () {
				if ($('subscribe1')) {
					$('subscribe1').setText('已订阅');
					$('subscribe1').onclick='';
				}
				if ($('subscribe2')) {
					$('subscribe2').setText('退定 '+screenName);
					$('subscribe2').onclick='JiWai.removeFriend('+screenName+')';
				}
			}
		}).request();
	},
	addFriend: function(screenName) {
		JiWai._createFriend(screenName, '');
		return false;
	},
	requestFriend: function(screenName, el) {
		var mba = new PBBAcpBox({
			name: 'JiWai'
		});
		mba.prompt(screenName+'希望验证你的身份，可以在下面输入一句话介绍你自己：', '', {onComplete:function(v){
			if (v===false) return;
			//JiWai._createFriend(screenName, v);
			location.href=el.href+'?note='+encodeURIComponent(v);
		}});
		return false;
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
