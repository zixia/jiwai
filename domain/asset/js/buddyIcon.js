var JWBuddyIcon =
{
	es : true,
	cache : [],
	uid : 0,
	init:function(){
		JWBuddyIcon.createDiv();
		$$('img.buddy').each(function(elem){JWBuddyIcon.mouseEvent(elem)});
		$('buddy_bor').addEvent('mouseout',function(ev){JWBuddyIcon.hideBor(ev)});	//changed on 1221
		(window.ie ? document : window).addEvent('click', JWBuddyIcon.initClick);
	},
	initClick:function(){
		if(oBuddyDiv.className=='hd_block'){	// add on 1221
			JWBuddyIcon.es = true;
			JWBuddyIcon.reMoveClass($('show_btn'),'poi_u');
			JWBuddyIcon.addClass(oBuddyDiv,'no');
		}
	},
	createDiv:function(){
		oBuddyDiv = document.createElement('div');
		oBuddyDiv.className = 'hd_block no';
		oBuddyDiv.id = 'buddy_bor';
		document.body.appendChild(oBuddyDiv);
	},
	pNodeHref: function(anode, onode) {
		var href = 'javascript:;';
		if ( onode.parentNode.nodeName == 'A' )
			href = onode.parentNode.href;
		if ( onode.parentNode.parentNode.nodeName == 'A' )
			href = onode.parentNode.parentNode.href;
		anode.href = href;
	},
	mouseEvent:function(elem){
		var uid = $(elem).getProperty('icon');
		elem.addEvent("mouseover",function(ev)
		{
			if(JWBuddyIcon.es){
				oBuddyDiv.innerHTML = "<div class='hed'><a id='show_btn' href='javascript:;' onClick=\"JWBuddyIcon.shOrHid(this,'oBuddyCon',event,"+uid+");\" class='poi'></a><a href='javascript:;'><img id='th_img' src='"+JiWai.AssetUrl('/images/img.gif')+ "' /></a></div>";
				JWBuddyIcon.reMoveClass(oBuddyDiv,'no');
				var oImg = $('th_img');
				oImg.src = this.getProperty('src');
				JWBuddyIcon.pNodeHref(oImg.parentNode, this);
				var ct = (window.ie)?2:2;
				var cl = (window.ie)?1:2;
				oBuddyDiv.style.top = getIE(this).t - ct+"px";
				oBuddyDiv.style.left = getIE(this).l - cl+"px";
			}
		});
	},
	hideBor:function(e){	//changed on 1221
		e=e||window.event;
		var o = e.toElement||e.relatedTarget;
		if(oBuddyDiv.contains){
			if(!(oBuddyDiv.contains(event.toElement))&&JWBuddyIcon.es){
				oBuddyDiv.addClass('no');
				return;
			}
		}else{
			var tar = $('buddy_bor');
			if( JWBuddyIcon.es 
					&& o.id != 'show_btn'
					&& o.id != 'th_img'
					&& o.className != 'hed' 
			  ) {
				oBuddyDiv.addClass('no');
			}
		}
		
	},
	shOrHid:function(th,ob,e,uid){
		if(!oBuddyCon){
			var oBuddyCon = document.createElement('div');
			oBuddyDiv.appendChild(oBuddyCon);
			oBuddyCon.id = 'oBuddyCon';
			oBuddyCon.className = 'con_block no';
		}
		if ( JWBuddyIcon.cache[uid] ) {
			oBuddyCon.innerHTML = JWBuddyIcon.cache[uid];
		} else {
			var time_stamp = (new Date()).getTime();
			oBuddyCon.innerHTML = '<img src="' + JiWai.AssetUrl('/images/avatar/load.gif') + '" width="20" height="20" />';
			var ajax_url = '/wo/ajax/getop/'+uid+'?'+time_stamp;
			new Ajax(ajax_url, {
				method: 'get', 
				onSuccess: function(e,x) { 
					oBuddyCon.innerHTML = e; 
					JWBuddyIcon.cache[uid]=e;
				}
			}).request();
		}

		JWBuddyIcon.cancelBubble(e);
		if(th.className.indexOf('poi_u')==-1){
			JWBuddyIcon.es = false;
			JWBuddyIcon.addClass(th,'poi_u');
			JWBuddyIcon.reMoveClass($(ob),'no');
		}else{
			JWBuddyIcon.es = true;
			JWBuddyIcon.reMoveClass(th,'poi_u');
			JWBuddyIcon.addClass($(ob),'no');
		}
		try{oBuddyCon.addEvent('click', function(e){JWBuddyIcon.cancelBubble(e)})}catch(e){};	// add on 1221
	},
	cancelBubble:function(e){	// add on 1221
		if (e.stopPropagation){
			e.stopPropagation();
		}else{
			window.event.cancelBubble = true;
		}
	},
	addClass:function(ob,classname){
		ob.className += ' '+classname;
	},
	reMoveClass:function(ob,classname){
		ob.className = ob.className.replace(' '+classname,'');
	}
};

function ddump(v) {
	if (current_user_id==89) {
		alert(v);
	}
}
