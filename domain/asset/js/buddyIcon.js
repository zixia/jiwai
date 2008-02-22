/**
 * @author seek@jiwai.com
 * @date 2007-12-05
 * @version $Id$
 */

var JWBuddyIcon = 
{
	cache: [],
	ajax_cache: [],
	curr_target: null,
	curr_uid: null,

	init: function()
	{
		$$('img.buddy_icon').each( function(el) { JWBuddyIcon.initIcon(el); } );
		window.addEvent('resize', JWBuddyIcon.globalResize);
		(window.ie ? $(document.body) : window).addEvent('click', JWBuddyIcon.globalClick);
	},

	globalMouseMove: function(ev)
	{
		if( JWBuddyIcon.curr_target )
		{
			var evt = new Event(ev);
			var up = $('avatar_'+JWBuddyIcon.curr_uid+'_up');
			var down = $('avatar_'+JWBuddyIcon.curr_uid+'_down');
			if( down.style.display != 'block' && false == JWBuddyIcon.onDivBody(evt, up) )
				JWBuddyIcon.hideAvatarDiv(true);
		}
	},

	globalClick: function(ev)
	{
		/* Add TB_window for lightbox */
		if( JWBuddyIcon.curr_target && null == $('TB_window') )
		{
			var evt = new Event(ev);
			var up = $('avatar_'+JWBuddyIcon.curr_uid+'_up');
			var down = $('avatar_'+JWBuddyIcon.curr_uid+'_down');
			if( false==JWBuddyIcon.onDivBody(evt, up) && false == JWBuddyIcon.onDivBody(evt,down) )
				JWBuddyIcon.hideAvatarDiv(true);
		}
	},

	onDivBody: function(e, o)
	{
		var p = o.getPosition(); var x = p.x; var y = p.y;
		return e.page.x > x && e.page.x < x + o.offsetWidth && e.page.y > y && e.page.y < y + o.offsetHeight;
	},

	globalResize: function()
	{
		if( JWBuddyIcon.curr_target )
			JWBuddyIcon.showAvatarDiv( JWBuddyIcon.curr_target ); 
	},
	
	initIcon: function(el)
	{
		el.onmouseover = function(ev) { JWBuddyIcon.iconMouseOver(ev); };
	},

	hideAvatarDiv: function(force)
	{
		if( JWBuddyIcon.curr_target ) 
		{
			if( JWBuddyIcon.cache[JWBuddyIcon.curr_uid] )
			{
				var down = $('avatar_'+JWBuddyIcon.curr_uid+'_down');
				if( down.style.display != 'block' || true==force) 
				{
					JWBuddyIcon.cache[JWBuddyIcon.curr_uid].style.display = 'none';
					JWBuddyIcon.hideDown( JWBuddyIcon.curr_uid );
				}
				else return false;
			}
			JWBuddyIcon.curr_target = null;
			JWBuddyIcon.curr_uid = null;
		}
		return true;
	},

	hideDown: function(uid)
	{
		var up = $('avatar_'+uid+'_up');
		var nav = $('avatar_'+uid+'_nav');
		var down = $('avatar_'+uid+'_down');

		var is_down = down.style.display == 'block';
		if( is_down == false )
			return true;

		var hover = nav.hasClass('nav_up_hover');
		var r_class = hover ? 'nav_up_hover' : 'nav_up';
		var a_class = hover ? 'nav_down_hover' : 'nav_down';

		nav.removeClass(r_class);
		nav.addClass(a_class);

		up.toggleClass('up_down');
		down.style.display = 'none';
	},

	showDown: function(uid)
	{
		var up = $('avatar_'+uid+'_up');
		var nav = $('avatar_'+uid+'_nav');
		var down = $('avatar_'+uid+'_down');

		var is_down = down.style.display == 'block';
		if( is_down )
			return true;

		var hover = nav.hasClass('nav_down_hover');
		var r_class = hover ? 'nav_down_hover' : 'nav_down';
		var a_class = hover ? 'nav_up_hover' : 'nav_up';

		nav.removeClass(r_class);
		nav.addClass(a_class);

		up.toggleClass('up_down');
		down.style.display = 'block';
		JWBuddyIcon.setAvatarContent( uid );
	},

	iconMouseOver: function(ev)
	{
		var evt = new Event(ev);
		var target = $(evt.target);
		if ( JWBuddyIcon.hideAvatarDiv() ) 
		{
			JWBuddyIcon.showAvatarDiv(target);
			JWBuddyIcon.curr_target = target;
			JWBuddyIcon.curr_uid = target.getProperty('icon');
		}
	},

	showAvatarDiv: function(target)
	{
		var pos = target.getPosition();
		var width = target.offsetWidth;
		var height = target.offsetHeight;

		var border = target.getStyle('border-top-width');
		if ( window.ie && border )try{var w=border.substr(0,1);width+=2*w;height+=2*w;}catch(e){}

		var left = pos.x + width/2 - 24 - 3; 
		var top = pos.y + height/2 - 24 - 3;

		var d = JWBuddyIcon.getAvatarDiv(target);

		d.setStyle('left', left );
		d.setStyle('top', top );
		d.setStyle('position', 'absolute' );
		d.setStyle('display', 'block' );

		//faint
	},

	getAvatarDiv: function(target)
	{
		var src = target.getProperty('src');
		var pos = target.getPosition();
		var uid = target.getProperty('icon');
		return JWBuddyIcon.cache[uid] || JWBuddyIcon.createInitDiv(target) ;
	},

	setAvatarContent: function(uid)
	{
		var down = $('avatar_'+uid+'_down');
		if( JWBuddyIcon.ajax_cache[uid] ) 
		{
			down.innerHTML = JWBuddyIcon.ajax_cache[uid];
		}
		else
		{
			down.innerHTML = '<img src="'+JiWai.AssetUrl('/images/avatar/load.gif')+'" width="20" height="20">';
			var time_stamp = (new Date()).getTime();
			var a = new Ajax( '/wo/ajax/getop/'+uid+'?'+time_stamp, {
				method: 'get',
				data: null,
				onSuccess: function(e,x) {
					down.innerHTML = e;
					JWBuddyIcon.ajax_cache[uid] = e;
				}
			}).request();
		}
	},

	createInitDiv: function(target) 
	{
		var uid = target.getProperty('icon');
		var d = document.createElement('div');
		var src = target.getProperty('src');
		var title = target.getProperty('title');
		var href = target.parentNode.tagName=='A' ? target.parentNode.href : 'javascript:void(0);';

		document.body.appendChild(d);
		JWBuddyIcon.cache[uid] = d;

		/** set d **/
		d.id = 'wtAvatar';
		
		d.innerHTML = '<div id="avatar_'+uid+'_up" class="up">' + 
				'<div class="avatar"><a href="'+href+'"><img style="margin:0px; border:0px; padding:0px;" height="48" title="'+title+'" width="48" src="'+src+'"/></a></div>' +
				'<div id="avatar_'+uid+'_nav" class="nav nav_down"></div>' +
				'<div style="clear:both;"></div>' + 
				'</div>' +
				'<div id="avatar_'+uid+'_down" class="down"></div>';

		$('avatar_'+uid+'_up').addEvent('mouseout', JWBuddyIcon.globalMouseMove);
		$('avatar_'+uid+'_nav').addEvent('click', JWBuddyIcon.avatarNavClick);
		$('avatar_'+uid+'_nav').addEvent('mouseover', JWBuddyIcon.avatarNavMouse);
		$('avatar_'+uid+'_nav').addEvent('mouseout', JWBuddyIcon.avatarNavMouse);

		/** return d **/
		return $(d);
	},

	onAvatarNav: function(ev,uid)
	{
		var evt = new Event(ev);
		var up = $('avatar_'+uid+'_up');

		if( evt.type == 'mouseleave' )
			return false;

		var x = evt.page.x;
		var y = evt.page.y;

		var ux = up.getPosition().x;
		var uy = up.getPosition().y;

		var on = true;
		if( x < ux + 52 || x > ux + 67 || y < uy+6 || y > uy + 50 )
			on = false;

		return on;
	},

	avatarNavMouse: function(ev)
	{
		var uid = JWBuddyIcon.curr_target.getProperty('icon');
		var nav = $('avatar_'+uid+'_nav');
		if( true ) 
		{
			if( nav.hasClass('nav_down') )
			{
				nav.removeClass('nav_down');
				nav.addClass('nav_down_hover');
			}else if( nav.hasClass('nav_up') )
			{
				nav.removeClass('nav_up');
				nav.addClass('nav_up_hover');
			} else if( nav.hasClass('nav_down_hover') )
			{
				nav.removeClass('nav_down_hover');
				nav.addClass('nav_down');
			}else if( nav.hasClass('nav_up_hover') )
			{
				nav.removeClass('nav_up_hover');
				nav.addClass('nav_up');
			}
		}
	},

	avatarNavClick: function(ev)
	{
		var uid = JWBuddyIcon.curr_target.getProperty('icon');
		var down = $('avatar_'+uid+'_down');
		if( down.style.display == 'block' )
		{
			JWBuddyIcon.hideDown(uid);
		}
		else
		{
			JWBuddyIcon.showDown(uid);
		}
	},

	__construct: function()
	{
		this.cache = this.ajax_cache = [];
		this.curr_target = null;
		this.curr_uid = null;
	}
};
