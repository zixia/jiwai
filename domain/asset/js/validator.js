/*
 *	JiWai.de Lib
 *	Author: zixia@zixia.net
 *	AKA Inc.
 *	2007-05
 */

var JWValidator = 
{ 
    ajax_url : '/wo/validator/ajax',

    init: function(f){
        var c = $(f).elements;
        for(var i=0;i<c.length;i++){
            m=c[i];
            var p = this.attr(m,"type");
            var a = this.attr(m,"ajax");
            if( p=="text" && a!=null){
                m.onblur = function(){
                    var a = JWValidator.attr(this,"ajax");
                    eval("JWValidator.ajax_"+a+"(this)");
                }
            }
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
            return v=="" ? null : v;
        }

        if( i != null ) {
            i.innerHTML = v;
        }
    },

    attr: function(o, name){
        var o = $(o);

        if( o == undefined )
            return null;

        if( name == 'tagName' ) 
            return o.tagName;

        return o.getProperty(name);
    },

    trim: function(n){
        return n.replace(/([ \f\t\v]+$)|(^[ \f\t\v]+)/g,"");
    },

    validate: function(f){

        var s="";
        var n=0;
        var c= $(f).elements;

        for(var i=0;i<c.length;i++){
            var m=c[i];
            var t=m.tagName;
            var p=this.attr(m,"type");

            var b=t=="SELECT";
            var j=t=="TEXTAREA";
            var l=p=="text";

            if ( (!m.disabled) && ( j || b || l || (p=="password") || (p=="file") ) ){
                if(l)
                    m.value= this.trim(m.value);
                var h="";
                var a=this.attr(m,"alt");

                var v= (b) ? m.options[m.selectedIndex].value.replace(/\-+/,"") : m.value;
                v = v.length;

                var x=parseInt( this.attr(m,"minlength") );
                var z=parseInt( this.attr(m,"maxLength") );
                if(isNaN(x))
                    x=0;
                if(isNaN(z)||z<1)
                    z=5000;
                if(v<x)
                    h = (v==0) ? "请"+((b)?"选择":"输入")+a: a+"的长度不得小于"+x+"个字符";
                else if(v>z)
                    h = a+"的最大允许长度为"+z+"个字符，而你输入了"+v+"个字符";

                if(h!=""){
                    if(h.indexOf(a)<0)
                    h=a+": "+h;
                    s+="\r\n"+(++n)+". "+h;
                }
            }

            var h=null;
            var g=this.attr(m, "check");
            if( g != null ) {
                eval('h=this.check_'+g+'(m)');
            }else{
                h = this.hint(m);
            }
            if( h != null )
                s+="\r\n"+(++n)+". "+h;
            b = n==0;
        }

        if(!b)
            alert("检查到下列错误，请纠正后再提交：\r\n"+s);

        return b;
    },

    check_nameScreen : function(m){
        return null;
    },

    ajax_nameScreen : function(m){
        var h = "";
        this.ajax('nameScreen', m.value, function(o) {
            JWValidator.hint(m,o);
        });
    },

    ajax : function(k, v, c){
        new Ajax( this.ajax_url, {
                method: 'get',
                data: 'k='+encodeURIComponent(k)+'&v='+encodeURIComponent(v) ,
                onSuccess: function(e,x) {
                    c(e,x);
                },
            }
        ).request();
    }
};
