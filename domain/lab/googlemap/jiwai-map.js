var map; //global

var Latlng = {
  北京:[39.92,116.46],
  上海:[31.22,121.48],
  天津:[39.13,117.2],
  重庆:[29.59,106.54],
  浙江:[30.26,120.19],
  香港:[22.2,114.1],
  澳门:[22.13,113.33],
  台湾:[25.05,121.5],
  河北:[38.03,114.48],
  内蒙古:[40.82,111.65],
  辽宁:[41.8,123.38],
  山西:[37.87,112.53],
  吉林:[43.88,125.35],
  黑龙江:[45.75,126.63],
  江苏:[32.04,118.78],
  安徽:[31.86,117.27],
  福建:[26.08,119.3],
  江西:[28.68,115.89],
  山东:[36.65,117],
  河南:[34.76,113.65],
  湖北:[30.52,114.31],
  湖南:[28.21,113],
  海南:[20.02,110.35],
  广西:[22.84,108.33],
  四川:[30.67,104.06],
  贵州:[26.57,106.71],
  云南:[25.04,102.73],
  西藏:[29.97,91.11],
  陕西:[34.27,108.95],
  甘肃:[36.03,103.73],
  青海:[36.56,101.74],
  宁夏:[38.47,106.27],
  新疆:[43.77,87.68],
  广东:[23.16,113.23]
};

function initMap() {
  if (GBrowserIsCompatible()) {
	map = new GMap2(document.getElementById('map'));
	map.setCenter(new GLatLng(34.68491,112.47605), 5);
	map.addControl(new GSmallMapControl());
	map.addControl(new GOverviewMapControl());
  }
}

window.onload = function(){
	initMap();
	JiWaiTimeline.init();
	setInterval(JiWaiTimeline.heartbeat, 10000);

}

var JiWaiTimeline = {
	 public_timeline : []
	,current_pos : 0
	,api_url : 'http://api.jiwai.de/statuses/public_timeline.json'
	,fetch: function(apiUrl,num) { 
		if ( !num )
			num = 10;
		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.src= apiUrl + '?' + Math.random() + '&callback=JiWaiTimeline.update&count=' + num;
		document.getElementsByTagName('head')[0].appendChild(s);
		s = null;
	}
	,update: function(jsonSource){
		var max_id = JiWaiTimeline.getMaxId();

//console.log("max_id: " + max_id);

		var p;
		for (var i=jsonSource.length-1; i>=0; --i){
			if ( jsonSource[i].id > max_id )
			{
//console.log( jsonSource[i].id + " is  larger than " + max_id );
				JiWaiTimeline.add(jsonSource[i]);
			}
			else
			{
//console.log( jsonSource[i].id + " is  less than " + max_id );
			}
		}

	}
	,init : function() {
		JiWaiTimeline.fetch(JiWaiTimeline.api_url, 100);
	}
	,heartbeat : function() {
//console.log ( "in heartbeat " + JiWaiTimeline.current_pos );
		if ( 0==JiWaiTimeline.current_pos )
			JiWaiTimeline.fetch(JiWaiTimeline.api_url);

//console.log ( "after fetch " + JiWaiTimeline.current_pos );
		status_data = JiWaiTimeline.public_timeline[JiWaiTimeline.current_pos];
//console.log ( "hb - " + status_data.user.location + ":" + JiWaiTimeline.current_pos );
		lh = JiWaiTimeline.formateStatus(status_data);
//console.log(lh.Latlng[0]);
		JiWaiTimeline.vision(lh);

		if ( JiWaiTimeline.current_pos > 0 )
			JiWaiTimeline.current_pos--;
		else
			JiWaiTimeline.current_pos = JiWaiTimeline.public_timeline.length-1;

		if ( JiWaiTimeline.current_pos<0 )
			JiWaiTimeline.current_pos = 0;
	}
	,getMaxId : function(){
		var n = JiWaiTimeline.public_timeline.length;
		if ( 0==n )
			return 0;

		return JiWaiTimeline.public_timeline[0].id;
	}
	,add : function(status_data){

//console.log ( "in add user " + status_data.user.screen_name  + " location: " + status_data.user.location + " current pos " + JiWaiTimeline.current_pos + " pub len: " + JiWaiTimeline.public_timeline.length);

		
		var user_location = false;
		try {
			user_location = status_data.user.location.split(' ')[0];
		} catch(e) {
		}

		if ( !user_location || !Latlng[user_location] )
			return;

//console.log ( "add user " + status_data.user.screen_name  + " location: " + status_data.user.location + " current pos " + JiWaiTimeline.current_pos + " pub len: " + JiWaiTimeline.public_timeline.length);


/*
			user_location = JiWaiTimeline.getRandomCity();

console.log ( "set user location : " + user_location );
*/

		JiWaiTimeline.public_timeline.unshift(status_data);
//console.log(status_data);
		JiWaiTimeline.current_pos++;

		while ( JiWaiTimeline.public_timeline.length >= 20 ){
//console.log("pop");
			JiWaiTimeline.public_timeline.pop();
		}


		if ( JiWaiTimeline.public_timeline.length && JiWaiTimeline.current_pos >= JiWaiTimeline.public_timeline.length )
			JiWaiTimeline.current_pos = JiWaiTimeline.public_timeline.length-1;

	}
	,getRandomCity : function(){
		c = 0;
		n = Math.floor(Math.random()*30);
		for ( l in Latlng )
		{
			if ( c++>n )
			{
				user_location = l;
				break;
			}
		}
	}
	,formateStatus : function (status_data){
		var j = status_data
		var html = '<div class="entry">';
		html 	+= '<p class="s' + Math.ceil(Math.random() * 10) + '">'
				+ j.text 
				+ '</p><a href="http://jiwai.de/' + j.user.screen_name + '/" target="_blank">'
				+ '<img src="' + j.user.profile_image_url + '" />'  + j.user.name + '</a> 于 ' 
				+ j.user.location 
				+ ' <em>' 
				+ '<a href="http://jiwai.de/' + j.user.screen_name + '/statuses/' + j.id + '" target="_blank">' 
				+ JiWaiTimeline.relative_time(j.created_at) 
				+  '</a></em></div>';

		return {
			Latlng: Latlng[j.user.location],
			Html: html
		};
	}
	,vision : function(lh){
		var point = new GLatLng(lh.Latlng[0], lh.Latlng[1]);
		map.openInfoWindowHtml(point, lh.Html);
	}
	,relative_time : function(time_value) {   
    	var values = time_value.split(" ");
    	time_value = values[1] + " " + values[2] + ", " + values[5] + " " + values[3];
                                            
    	var parsed_date = Date.parse(time_value);
                                            
    	var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
    	var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
    
    	if(delta < 60) {
        	return '就在刚才'
    	} else if(delta < (60*60)) {
        	return (parseInt(delta / 60)).toString() + ' 分钟前';
    	} else if(delta < (24*60*60)) {
        	return (parseInt(delta / 3600)).toString() + ' 小时前';
    	}

    	return (parseInt(delta / 86400)).toString() + ' 天前';
	}
}
