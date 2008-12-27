var JWBuddyIcon =
{
	es : true,
	cache : [],
	uid : null,
	init:function(){
		JWBuddyIcon.createDiv();
		$$('img.buddy').each(function(elem){JWBuddyIcon.mouseEvent(elem)});
		$("buddy_bor").addEvent("mouseout",function(ev){JWBuddyIcon.hideBor(ev)});	//changed on 1221
		(window.ie ? document : window).addEvent('click', JWBuddyIcon.initClick);
	},
	initClick:function(){
		if(oBuddyDiv.className=="hd_block"){	// add on 1221
			JWBuddyIcon.es = true;
			JWBuddyIcon.reMoveClass($("show_btn"),"poi_u");
			JWBuddyIcon.addClass(oBuddyDiv,"no");
		}
	},
	createDiv:function(){
		oBuddyDiv = document.createElement("div");
		oBuddyDiv.className = "hd_block no";
		oBuddyDiv.id = "buddy_bor";
		document.body.appendChild(oBuddyDiv);
	},
	mouseEvent:function(elem){
		var uid = $(elem).getProperty('icon');
		elem.addEvent("mouseover",function(ev)
		{
			if(JWBuddyIcon.es){
				oBuddyDiv.innerHTML = "<div class='hed'><a id='show_btn' href='javascript:void(0)' onClick=\"JWBuddyIcon.shOrHid(this,'oBuddyCon',event, "+uid+")\" class='poi'></a><a href='#'><img id='th_img' src='http://asset.jiwai.de/images/img.gif' /></a></div>";
				JWBuddyIcon.reMoveClass(oBuddyDiv,"no");
				var oImg = $("th_img");
				oImg.setAttribute("src",this.getProperty('src'));
				oImg.parentNode.setAttribute("href",(this.parentNode.nodeName == "A")?this.parentNode:this.parentNode.parentNode);
				var ct = (window.ie)?2:2;
				var cl = (window.ie)?1:2;
				oBuddyDiv.style.top = getIE(this).t - ct + "px";
				oBuddyDiv.style.left = getIE(this).l - cl + "px";
			}		
		});
	},
	hideBor:function(e){	//changed on 1221
		e=e||window.event;
		var o = e.toElement||e.relatedTarget;
		if(oBuddyDiv.contains){
			if(!(oBuddyDiv.contains(event.toElement))&&JWBuddyIcon.es){
				oBuddyDiv.addClass(" no");
				return;
			}
		}else{
			var tar = $("buddy_bor");
			if(o.className!="hed"&&o.id!="show_btn"&&o.id!="th_img"&&JWBuddyIcon.es){
				oBuddyDiv.addClass(" no");
			}
		}
		
	},
	shOrHid:function(th,ob,e,uid){
		if(!oBuddyCon){
			var oBuddyCon = document.createElement("div");
			oBuddyDiv.appendChild(oBuddyCon);
			oBuddyCon.id = "oBuddyCon";
			oBuddyCon.className="con_block no";
		}
		if ( JWBuddyIcon.cache[uid] ) {
			oBuddyCon.innerHTML = JWBuddyIcon.cache[uid];
		} else {
			var time_stamp = (new Date()).getTime();
			oBuddyCon.innerHTML = '<img src="http://asset.jiwai.de/images/avatar/load.gif" width="20" height="20" />';
			var ajax_url = '/wo/ajax/getop/'+uid+'?'+time_stamp;
			new Ajax(ajax_url, {
				method: 'get', 
				data: null, 
				onSuccess: function(e,x) { 
					oBuddyCon.innerHTML = e; 
					JWBuddyIcon.cache[uid]=e;
					}
			}).request();
}

		JWBuddyIcon.cancelBubble(e);
		if(th.className.indexOf("poi_u")==-1){
			JWBuddyIcon.es = false;
			JWBuddyIcon.addClass(th,"poi_u");
			JWBuddyIcon.reMoveClass($(ob),"no");
		}else{
			JWBuddyIcon.es = true;
			JWBuddyIcon.reMoveClass(th,"poi_u");
			JWBuddyIcon.addClass($(ob),"no");
		}
		try{oBuddyCon.addEvent("click", function(e){JWBuddyIcon.cancelBubble(e)})}catch(e){};	// add on 1221
	},
	cancelBubble:function(e){	// add on 1221
		if (e.stopPropagation){
			e.stopPropagation();
		}else{
			window.event.cancelBubble = true;
		}
	},
	addClass:function(ob,classname){
		ob.className += " "+classname;
	},
	reMoveClass:function(ob,classname){
		ob.className = ob.className.replace(" "+classname,"");
	}
}
