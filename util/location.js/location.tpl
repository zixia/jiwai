var JiWaiLocation = {
	o: undefined,
	init: function(d){
		JiWaiLocation.o = eval( '(' + d + ')' );
	},
	select: function(idp, idc, pid, cid){
		var ep = $(idp);
		var ec = $(idc);
		if( !ep || !ec )
			return;

		if( ep.options.length == 0 ){
			ep.options.add( new Option('',0,false,false) );
			for( var k in JiWaiLocation.o ){
				var v = JiWaiLocation.o[k][0];
				ep.options.add( new Option( v , k, (pid && k==pid), (pid && k==pid) ) );
			}
		}

		var p = JiWaiLocation.o[pid];
		if( p ) {
			ec.options.length = 0;
			ec.options.add( new Option('',0,false,false) );
			for(var i=0; i<p[1].length; i++){
				var k = p[1][i][0];
				var v = p[1][i][1];
				ec.options.add( new Option( v, k, (cid && k==cid), (cid && k==cid) ) );
			}
		}
	}
};
JiWaiLocation.init('{$v}');
