/*
 *	JiWai.de Lib
 *	Author: zixia@zixia.net
 *	AKA Inc.
 *	2007-05
 */

var JWValidator = { 
	mVersion : 1,

	ajax_url : '/wo/validator/ajax/',
	
	init : function()
	{
		for (var index=0; index<arguments.length; index++) 
		{
			var el = $(arguments[ index ]);
			if( el ) 
			{
				JWValidator.initForm(el);
			}
		}
		$$('form.validator').each( function(el) { JWValidator.initForm(el); } );
	},
	
	initForm: function(el)
	{
		var c = el.elements;
		for(var i=0;i<c.length;i++)
		{
			m=c[i];
			var p = this.attr(m,'type');
			var a = this.attr(m,'ajax');
			if( p=='text' && a!=null){
				m.onblur = function(){
					JWValidator.ajax( this );
				}
			}
		}
		el.onsubmit = function() { return JWValidator.validate(el); };
	},

	geni: function(n,p,v){
		var o = n.childNodes;
		for(var i=0;i<o.length;i++) {
			if( this.attr( o[i],p )==v ){
				return o[i];
			}
		}
		return null;
	},
	
	hint: function(o,v){
		var i = this.geni(o.parentNode, 'tagName', 'I');
		if( i == null )
			return null;

		if( v == undefined ) {
			var v = i.innerHTML;
			return v=='' ? null : v;
		}

		i.innerHTML = v;
		i.style.display = (v=='') ? 'none' : 'inline';
	},

	attr: function(o, name){
		var o = $(o);

		if( o == undefined )
			return null;

		if( name == 'tagName' ) 
			return o.tagName;

		return o.getProperty(name);
	},

	value: function(o){
		if( this.attr(o,'type') == 'SELECT' ) {
			return this.trim( o.options[o.selectedIndex].value );
		} else {
			return this.trim( o.value );
		}
	},

	trim: function(n){
		if (null==n) n='';
		return n.replace(/([ \f\t\v]+$)|(^[ \f\t\v]+)/g,'');
	},

	validate: function(f){

		var s='';
		var n=0;
		var c= $(f).elements;

		for(var i=0;i<c.length;i++){
			var m=c[i];
			var t=m.tagName;
			var p=this.attr(m,'type');

			var b=t=='SELECT';
			var j=t=='TEXTAREA';
			var l=p=='text';

			var h=null;

			if ( (!m.disabled) && ( j || b || l || (p=='password') || (p=='file') ) ){
				if(l)
					m.value= this.trim(m.value);
				var a=this.attr(m,'alt');

				v = this.value(m).length;

				var x=parseInt( this.attr(m,'minlength') );
				var z=parseInt( this.attr(m,'maxLength') );
				if(isNaN(x))
					x=0;
				if(isNaN(z)||z<1)
					z=5000;

				if(v<x)
					h = (v==0) ? '请'+((b)?'选择':'输入')+a: a+'的长度不得小于'+x+'个字符';
				else if(v>z)
					h = a+'的最大允许长度为'+z+'个字符，而你输入了'+v+'个字符';

				if(h){
					if(h.indexOf(a)<0)
					h=a+': '+h;
					s+='\r\n'+(++n)+'. '+h;
				}
			}

			// fetch ajax result from hint
			if( h==null && this.attr(m, 'ajax') != null ) {
				h = this.hint(m);
				if( h != null )
					s+='\r\n'+(++n)+'. '+h;
			}

			//Check function
			var g=this.attr(m, 'check');
			if( h==null && g != null ) {
				eval('h=this.check_'+g+'(m)');
				if( h != null )
					s+='\r\n'+(++n)+'. '+h;
			}
			
			//compare two element;
			var g = this.attr(m, 'compare');
			if( h==null && g != null ) {
				h = this.compare_o(m, $(g));
				if( h != null )
					s+='\r\n'+(++n)+'. '+h;
			}
			
			b = n==0;
		}

		if(!b)
			alert('检查到下列表单错误，请纠正后重新提交：\r\n'+s);

		return b;
	},

	compare_o : function(m, n){
		var notnull = this.notnull(m);
		if( notnull != true && notnull != '' ){
			return notnull;
		}

		var a = this.attr(m, 'alt');
		if( a==null ) a = '确认密码';

		if( n==null )
			return '页面上缺少'+a+'的对比字段';

		var b = this.attr(n, 'alt');
		if( b==null ) b = '新密码';

		if( this.value(m) != this.value(n) )
			return a + '与' + b + '不一致';

		return null;
	},

	check_number : function(m){
		var a = this.attr(m, 'alt');
		if (/[\D]/g.test(this.value(m))) 
			return a + '必须是全数字';
		return null;
	},

	check_null : function(m){
		var notnull = this.notnull(m);
		if( notnull == true && notnull != '' ){
			return null;
		}
		return notnull;
	},

	check_nameScreen : function(m){
		return null;
	},

	notnull : function(o){
		if ( this.value(o) == '' || this.value(o) == null ) {
			if( ( this.attr(o, 'null') != null ) ) { //allow null
				return true;
			}
			var s = this.attr(o,'type') == 'SELECT' ? '选' : '填';
			var a = this.attr(o,'alt') == null ? '该项' : this.attr(o,'alt');

			return a + '为必' + s + '项，不能留空' ;
		}
		return true;
	},

	ajax : function(o){ //notnull
		var notnull = this.notnull(o);
		if( notnull != true ){
			return JWValidator.hint(o,notnull);
		}
		new Ajax( this.ajax_url, {
				method: 'get',
				data: 'k='
					+ encodeURIComponent(this.attr(o,'ajax'))
					+ '&v='
					+ encodeURIComponent(this.value(o)) ,
				onSuccess: function(e,x) {
					JWValidator.hint(o,e);
				}
			}
		).request();
	},

    validate2 : function(f,q)
    {
		var c= $(f).elements;
        var m,h;

		for(var i=0;i<c.length;i++)
        {
			m=c[i];
            h=this.hint(m);
            if(null !=h)
                return false;
        }

        if(null==q) return true;

        h=this.hint2($(q));
        if(null !=h)
            return false;

        return true;
    },

   validate1 : function(q,o,w)
   {
        var h;
        if(null==q) return true;
        q=$(q);

		var notnull = this.notnull($(o));
		if( notnull != true ){
			return JWValidator.hint2(q,notnull);
		}

		notnull = this.notnull($(w));
		if( notnull != true ){
			return JWValidator.hint2(q,notnull);
		}

        h=this.hint2(q);
        if(null !=h)
            return false;

        return true;
    },

    ajax2 : function(o,w,q,s,s2,d,n,t,c)
    {

        o=$(o);
        w=$(w);
        q=$(q);

        if(null==q) q=o;

		var notnull = this.notnull(o);
		if( notnull != true ){
			return JWValidator.hint2(q,notnull);
		}

		notnull = this.notnull(w);
		if( notnull != true ){
			return JWValidator.hint2(q,notnull);
		}

		new Ajax( this.ajax_url, 
        {
				method: 'get',
				data: 'k='
					+ encodeURIComponent(this.attr(o,'ajax2'))
					+ '&v='
					+ encodeURIComponent(this.value(o))
					+ '&v2='
					+ encodeURIComponent(this.value(w)) ,
				onSuccess: function(e,x) 
                {
    				JWValidator.hint2(q,e,s,s2,d,n,t,c);
				}
		}
		).request();
    },
	
	hint2: function(o,v,s,s2,d,n,t,c)
    {
		var i = o;
        var b;

		if( i == null )
			return null;

		if( v == undefined ) 
        {
			var v = i.innerHTML;
			return v=='' ? null : v;
		}

		i.innerHTML = v;
		b = (v=='') ? 'none' : 'block';
        i.setStyle('display',b);

        if (null != s)
        {
        b = ( v.indexOf(s) == -1)? 'none' : 'block';

        if ((null != s2)&&('none'==b))
            b = ( v.indexOf(s2) == -1)? 'none' : 'block';

            if((null != c)&&('none'==b))
                b = ( $(c).value.length < 1)? 'none' : 'block';
        
            if (null != n)
            {
                t=$(t);
                for(var j=1;j<=n;j++)
                {
                    if(null!=t )
                        if (2 != j)//一般不用判断
                            $(d + j).innerHTML=t.innerHTML;
                    $(d + j).setStyle('display',b);
                }
            }
        }
	},

    onNameOrDeviceBlur : function()
    {
        this.ajax2('user_DeviceNo1','user_nameScreen1','RegTips');
    },

    onPassBlur : function(m,d)
    {
        var g = this.attr(m, 'compare');
        if( m!=null && g != null ) {
            var v,x,z,h;
            if (null==d) d=$(m);

            h = this.compare_o($(m), $(g));
            if( h != null )
                return this.hint(d,h);

            this.hint(d,'');
        }

            return true;
    },

    onNameOrDeviceBlur2 : function()
    {
        this.ajax2('user_nameScreen','user_DeviceNo','RegTips2','第一次接触','不正确','DevNo2',3,'DevName','user_DeviceNo');
    }
};
