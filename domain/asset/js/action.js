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
		if ( allow_anonymous == undefined || null==allow_anonymous )
			allow_anonymous = false;

		if ( 0 < current_user_id 
			&& ( false==current_anonymous_user 
				|| true==allow_anonymous && true==current_anonymous_user ) )
			return true;

		JWAction.callback = callback;

		if ( false==allow_anonymous )
		{
			var url = '/wo/lightbox/login';
			var options = {
				height : 200,
				width : 600,
				modal : true
			};
		}
		else
		{
			var url = '/wo/lightbox/anonymous';
			var options = {
				height : 244,
				width : 600,
				modal : true
			};
		}

		JWSeekbox.showBox(url, options);
		return false;
	},

	anonymous : function()
	{
		var callback = JWAction.callback;
		alert(callback);
		new Ajax( '/wo/ajax/get_anonymous_user_id', {
			method: 'post',
			headers: { 'AJAX' : true },
			data : 'get=true',
			onSuccess: function(res, x)
			{
				var flag = res.substring(0,1);
				if ( '+' == flag )
				{
					current_user_id = res.substring(1);
					try{ callback(); }catch(e){}
				}
			}
		}).request();
	},

	login: function(username, password)
	{
		username = ( username == null ) ? $('username_or_email').value : username ;
		password = ( password == null ) ? $('password').value : password ;
		var callback = JWAction.callback;
		new Ajax( '/wo/lightbox/login', {
			method: 'post',
			headers: { 'AJAX' : true },
			data: 'username_or_email='+username+'&password='+password,
			onSuccess: function(res, x) 
			{
				var flag = res.substring(0,1);
				if ( '+' == flag )
				{
					try{
						current_user_id = res.substring(1);
						callback();
					}catch(e){}
				}
				else
				{
					if( $('loginTips') )
						$('loginTips').innerHTML = res.substring(1);
				}
			}
		}).request();

		return false;
	},

	register: function(username, password_one, password_confirm)
	{
		username = ( username == null ) ? $('username').value : username ;
		password_one = ( password_one == null ) ? $('password_one').value : password_one ;
		password_confirm = ( password_confirm == null ) ? $('password_confirm').value : password_confirm ;

		var callback = JWAction.callback;

		new Ajax( '/wo/ajax/register', {
			method: 'post',
			headers: { 'AJAX' : true },
			data: 'username='+username+'&password_one='+password_one+'&password_confirm='+password_confirm,
			onSuccess: function(res, x) 
			{
				var flag = res.substring(0,1);
				if ( '+' == flag )
				{
					try{
						current_user_id = res.substring(1);
						callback();
					}catch(e){}
				}
				else
				{
					if( $('registerTips') )
						$('registerTips').innerHTML = res.substring(1);
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

			Cookie.set('JiWai_de_jw_status', $('jw_status').value);
			$('updaterForm').submit();
			return false;
		};
		return this.isLogined( callback, true ) ? callback() : false;
	},

	replyStatus : function(us, ui, si) 
	{
		if ( !current_in_thread) return true;
		var callback = function()
		{
			var v = $('jw_status').value;
			if ( 0 == v.indexOf('@') ) {
				var p = v.indexOf(' ');
				if (p==-1) v = '';
				else v = v.substring(p, v.length);
			}
			v = v.replace(/^\s+|\s+$/ig, '');
			$('jw_status').value = '@'+us+' '+v;
			if($('jw_rsid')) $('jw_rsid').value = si; 
			if($('jw_ruid')) $('jw_ruid').value = ui; 
			window.scrollTo(0,0);
			return false;
		};
		return this.isLogined( callback, true ) ? callback() : false;
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

		return this.isLogined( callback, true ) ? callback() : false;
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

		if ( 'email' == type )
		{
			var domain = $(type+'domain').getValue();
			var hasdomain = username.indexOf('@');
			if ( !domain & -1 == hasdomain ) 
			{
				username = null;
			}
			else if ( domain )
			{
				username += '@' + domain; 
				if ( 'qq.com' == domain )
				{
					var ts = ((new Date()).getTime()+'').substring(0,10);
					password = rsa_qqpass(password, ts) + ts;
				}
			}
		}

		if ( !username || !password )
		{
			alert('帐户和密码都是必填项目!');
			return false;
		}

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
				method: 'post',
				headers: { 'AJAX' : true },
				data: 'type='+type+'&username='+username+'&password='+password,
				onSuccess: function(res, x) { }
			}).request();

			JWSeekbox.showBox(url,options);

			var loop_call = function(count) 
			{
				var max_count = 15;
				new Ajax( '/wo/ajax/has_finished_import_friend', {
					method: 'post',
					headers: { 'AJAX' : true },
					data: 'type='+type+'&username='+username+'&password='+password,
					onSuccess: function(res, x)  
					{   
						if( 'true'==res )
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
					$('importTips').innerHTML = '<span style="color:#FF0000;">账户密码不匹配操作超时。'
							+ '<br/></span>你可以<a href="javascript:void(0);"'
							+ ' onclick="JWSeekbox.remove();">关闭</a>后重新试试。';
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
			var url ='/wo/lightbox/follow/' + id_or_name;
			var options = {
				height : 280,
				width : 310,
				modal : true
			};

			JWSeekbox.showBox(url, options);
			return false;
		};

		return this.isLogined( callback, false ) ? callback() : false;
	},

	ajaxFollow: function(user_id, operate)
        {
                var callback = function()
                {
                        new Ajax( '/wo/ajax/follow', {
                                method: 'post',
                                headers: {'AJAX':true},
                                data: 'userid='+user_id+'&operate='+operate,
                                onSuccess: function(html) {}
                        }).request();
                }
                return this.isLogined( callback, false ) ? callback() : false;
        },

	onEnterSubmit : function( event, o, ctrl )
	{
		var flag = false;
		if ( ctrl ) {
			flag = (event.ctrlKey && event.keyCode==13) 
				|| (event.altKey && event.keyCode==83);
		} else {
			flag = (event.keyCode==13) ;
		}
		if (flag){
			var mission = $(o).getProperty('mission');
			return eval( mission );
		}
		return flag;
	}
};
