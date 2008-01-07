/**
 *	JiWai.de Lib
 *	Author: wqsemc@jiwai.com 
 *	AKA Inc.
 *	2007-12-07
 */

var JWAction = 
{ 
	mVersion : 1,
	callback : null,

	isLogined : function(callback, allow_anonymous)
	{
		if ( allow_anonymous == undefined )
			allow_anonymous = true;
		else if ( allow_anonymous == false )
			allow_anonymous = (current_anonymouse_user==false);

		if ( current_user_id > 0 && allow_anonymous==true )
			return true;

		JWAction.callback = callback;

		var caption='登录叽歪';
		var url ='/wo/lightbox/login';
		var rel='';
		var options = {
			height : 240,
			width : 310,
			focus : 'username_or_email'
		};

		TB_show(caption, url, rel, options);
		return false;
	},

	login: function(username, password)
	{
		username = ( username == null ) ? $('username_or_email').value : username ;
		password = ( password == null ) ? $('password').value : password ;
		var callback = JWAction.callback;

		new Ajax( '/wo/lightbox/login', {
			method: 'POST',
			headers: { 'AJAX' : true },
			data: 'username_or_email='+username+'&password='+password,
			onSuccess: function(responseText, x) 
			{
				var flag = responseText.substring(0,1);
				if ( '+' == flag )
				{
					try{
						current_user_id = responseText.substring(1);
						callback();
					}catch(e){}
				}
				else
				{
					if( $('loginTips') )
						$('loginTips').innerHTML = responseText.substring(1);
				}
			}
		}).request();

		return false;
	},

	updateStatus : function()
	{
		var callback = function()
		{
			if( !$('jw_status').value ) 
				return false;

			Cookie.set( 'JiWai_de_jw_status', $('jw_status').value);
			$('updaterForm').submit();
			return false;
		};

		return this.isLogined( callback ) ? callback() : false;
	},

	updateThread: function()
	{
		var callback = function()
		{
			if( !$('jw_status').value ) 
				return false;

			Cookie.set( 'JiWai_de_jw_status', $('jw_status').value);
			Cookie.set( 'JiWai_de_jw_idStatusReplyTo', $('idStatusReplyTo').value);
			Cookie.set( 'JiWai_de_jw_idUserReplyTo', $('idUserReplyTo').value);

			$('updaterForm').submit();
			return false;
		};

		return this.isLogined( callback ) ? callback() : false;
	},

	redirect : function( o )
	{
		var callback = function()
		{
			location.href = $(o).href;		
			return false;
		};

		return this.isLogined( callback ) ? callback() : false;
	},

	submit : function( form )
	{
		var callback = function()
		{
			$(form).submit();
			return false;
		};

		return this.isLogined( callback ) ? callback() : false;
	},

	doTrash : function ( idStatus )
	{
		var callback = function()
		{
			JiWai.DoTrash(idStatus);
			return false;
		};

		return this.isLogined( callback ) ? callback() : false;
	},
	
	toggleStar : function( idStatus )
	{
		var callback = function()
		{
			JiWai.ToggleStar(idStatus);
			return false;
		};

		return this.isLogined( callback ) ? callback() : false;
	},

	importFriend : function(type)
	{   
		var type = ( type == null ) ? 'msn' : type ;
		var username = $(type+'username').value;
		var password = $(type+'password').value;

		var callback = function()
		{   
			var caption = '导入用户';
			var url ='/wo/lightbox/lightbox_import_friend';
			var rel='';

			var options = {
				height : 112,
				 width : 310
			};

			new Ajax( '/wo/ajax/send_request_import_friend', {
				method: 'POST',
				headers: { 'AJAX' : true },
				data: 'type='+type+'&username='+username+'&password='+password,
				onSuccess: function(responseText, x) { }
			}).request();

			TB_show(caption, url, rel, options);

			var loop_call = function(count) 
			{
				var max_count = 15;
				new Ajax( '/wo/ajax/has_finished_import_friend', {
					method: 'POST',
					headers: { 'AJAX' : true },
					data: 'type='+type+'&username='+username+'&password='+password,
					onSuccess: function(responseText, x)  
					{   
						if( 'true'==responseText )
						{   
							location.href = '/wo/invitations/invite_not_follow';
							return false;
						}   
					}   
				}).request();

				if ( count<max_count )
				{
					count++;
					window.setTimeout( function(){loop_call(count);}, 1000);
				}
				else
				{
					$('importTips').innerHTML = '<span style="color:#FF0000;">导入操作已经超时。'
							+ '<br/></span>你可以<a href="javascript:void(0);"'
							+ ' onclick="TB_remove();">关闭</a>后重新试试。';
				}
			}

			loop_call(0);

			return false;
		}; 

		return this.isLogined( callback ) ? callback() : false;
	},  

	follow : function( id_or_name, element )
	{
		var callback = function()
		{
			var caption = '关注用户';
			var url ='/wo/lightbox/follow/' + id_or_name;
			var rel='';

			var options = {
				height : 280,
				width : 310
			};

			TB_show(caption, url, rel, options);
			return false;
		};

		return this.isLogined( callback, false ) ? callback() : false;
	}
};
