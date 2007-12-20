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

	isLogined : function(callback)
	{
		if( current_user_id > 0 )
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

	importFriends : function(type, username, password)
	{   
		type = ( type == null ) ? 'msn' : type ;
		username = ( username == null ) ? $(type+'username').value : username ;
		password = ( password == null ) ? $(type+'password').value : password ;

		var callback = function()
		{   
			new Ajax( '/wo/invitations/get_friends', {
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

		return this.isLogined( callback ) ? callback() : false;
	}
};
