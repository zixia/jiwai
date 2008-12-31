function colorSelect(cid, start_color) 
{
	var start_color = start_color==undefined ? '000000' : start_color;
	var elem = $(cid);
	var valu = $(cid + '_value');
	var vari = $(cid + '_variable');
	try {
		var default_bg_color = new Color(start_color);
		var default_fg_color = default_bg_color.invert();
		elem.setStyle('background-color', default_bg_color);
		elem.setStyle('color', default_bg_color);
		var color_r = new MooRainbow(cid, 
		{
			 id: cid + '_moo_id'
			,startColor: default_bg_color
			,imgPath: JiWai.AssetUrl('/lib/mooRainbow/images/')
			,wheel: true
			,onChange: function(color) 
			{
				elem.setStyle('background-color', color.hex);
				elem.setStyle('color',(new Color(color.hex)).invert());
				valu.value = color.hex;
				vari.innerHTML = color.hex;
			}
		});
	} catch (e) {}
};

function theSameHeight() {
	var ol = $("leftBar");
	var or = $("rightBar");
	if ( !ol || !or ) return;
	var maxHeight = (parseInt(ol.offsetHeight)>=parseInt(or.offsetHeight))?parseInt(ol.offsetHeight):parseInt(or.offsetHeight);
	if(parseInt(ol.offsetHeight)>=parseInt(or.offsetHeight))
		or.style.height = maxHeight+"px";
	else
		ol.style.height = maxHeight+"px";
};

function textCounter(field, countfield, maxlimit) {
	if (field.value.length > maxlimit) 
		countfield.innerHTML = 0;
	else 
		countfield.innerHTML = maxlimit - field.value.length;
};

function clearBothHeight() {
	$('leftBar').style.height = 'auto';
	$('rightBar').style.height = 'auto';
};

function ctrObj(ctrElem,signElem){
	clearBothHeight();
	var myctrer = $(ctrElem);
	var mySlider = $(signElem);
	if(mySlider.style.display!="none"){
		myctrer.className="max";
		mySlider.style.display="none";
	}else{
		myctrer.className="min";
		mySlider.style.display="block";
	}
	theSameHeight();
};

function reSetHeight(){
	clearBothHeight();
	theSameHeight();
};

function clearValue(o){
	if($(o)){
		$(o).value = "";
		$(o).className+=' focus'
	}
};

var sv_1 = "关键字...";
var sv_2 = "QQ  MSN Email id...";
function searchValue(obj,def_sv){
	var o = $(obj);
	o.className=o.className.replace(/\bfocus\b/,'')
	if(o.value != ""){
		o.style.color = "#333";
	}else{
		o.value = def_sv;
		o.style.color = "#666";
	}
};

var thLabelArr = [];
function setLabel(ob,id){
	var yet=true;
	if(id){
		var thLabel = $(id).title;
		if(thLabelArr.length!=0){
			for(i in thLabelArr){
				if(thLabel == thLabelArr[i])
					yet = false;
			}
		}
		if(yet){
			$(ob).value += "["+thLabel+"]";
			thLabelArr.push(thLabel);
		}
	}else{
		$(ob).value += "[]";
	}
	textCounter($("jw_status").form.jw_status,$('count'),70);
};

function changeSend(s){
	$("device").innerHTML = s;
	$("othObj").style.visibility="hidden";
	setTimeout(function(){$("othObj").style.visibility="visible"},500);
};

function getNameArr(ob,TagName){
	var obj = $(ob).getElementsByTagName(TagName);
	return obj;
};

function CheckAll(id,checked) {
	var ob = getNameArr(id,"input");
	var len = ob.length;
	for (var i=0; i < len; i++) {
		ob[i].checked=checked;
	}
};

function getIE(e){ 
	var t=e.offsetTop; 
	var l=e.offsetLeft; 
	var h=e.offsetHeight;
	var w=e.offsetWidth;
	while(e=e.offsetParent){ 
		t+=e.offsetTop; 
		l+=e.offsetLeft;
	}
	return {b:t+h,t:t,l:l,w:w}
};

function showSetBor(id){
	clearBothHeight();
	var o_ids = {
		0:'fb_block',
		1:'tw_block',
		2:'ff_block'
	};
	for(var i in o_ids) {
		var o_id = o_ids[i];
		if ( o_id != id && $(o_id) ) {
			$(o_id).style.display = "none";
		}
	}
	if(id && $(id)){
		$(id).style.display = "block";
	}
	theSameHeight();
};

function showtxt(id,str){
	$(id).innerHTML = str;
};

function getIE(e){ 
	var t=e.offsetTop; 
	var l=e.offsetLeft; 
	var h=e.offsetHeight;
	var w=e.offsetWidth;
	while(e=e.offsetParent){ 
		t+=e.offsetTop; 
		l+=e.offsetLeft;
	}
	return {b:t+h,t:t,l:l,w:w}
}

function radiovalue(name) {
	var elm = document.getElementsByName(name);
        val = '';
        for (i = 0; i < elm.length; i++) {
		if (("radio"==elm[i].type) && (true==elm[i].checked)){
			return elm[i].value;
                }
        }       
	return '';
}

function opendialog(url, width, height) {
	if( document.all ) {  
		feature = 'dialogWidth:'+width+'px;dialogHeight:'+height+'px;status:no;help:no;center:yes;';
		window.showModalDialog(url,'preview',feature); 
	} else {
		feature = 'width='+width+',height='+height+',menubar=no,toolbar=no,location=no,scrollbars=no,status=no,modal=yes'; 
		window.open(url,'preview',feature);  
	}
};

window.jiwai_init_hook_bgblack = function() {
	$$('.bg_black div').each(function(elem){
		elem.parentNode.className='';
		var c = $(elem).getChildren()[0];

		//photo
		if(c&&c.tagName=='A') {
			var s = $(c).getChildren()[0];
			if(s && s.tagName=='IMG') {
				s.onload = function() {
					var w = s.offsetWidth;
					$(elem.parentNode).setStyles('background-color:#000;padding:4px;width:'+w+'px;');
				}
				s.src = s.src;
			}
		}
		//video
		else if(elem.className.test(/e_video/)) {
			var w = c.offsetWidth;
			$(elem.parentNode).setStyles('background-color:#000;padding:4px;width:'+w+'px;');
		}
	});
};

window.jiwai_init_hook_eheight = theSameHeight;
