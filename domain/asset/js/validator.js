/*
 *	JiWai.de Lib
 *	Author: zixia@zixia.net
 *	AKA Inc.
 *	2007-05
 */

var JWValidator = { 
	mVersion : 1,

	ajax_url : '/wo/validator/ajax',
	
	init: function(){
		for (var index=0; index<arguments.length; index++) {
			var f = arguments[ index ];
			var c = $(f).elements;
			for(var i=0;i<c.length;i++){
				m=c[i];
				var p = this.attr(m,'type');
				var a = this.attr(m,'ajax');
				if( p=='text' && a!=null){
					m.onblur = function(){
						JWValidator.ajax( this );
					}
				}
			}
			//$(f).addEvent('submit', function(){return JWValidator.validate(this)});
		}
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
		i.style.display = (v=='') ? 'none' : 'block';
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

				var v= (b) ? m.options[m.selectedIndex].value.replace(/\-+/,'') : m.value;
				v = v.length;

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
				return '';
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
	}
}
