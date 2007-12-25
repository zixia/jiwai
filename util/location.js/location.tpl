var JWLocation = {
	o: undefined,
	data: '{$v}',
	init: function(){
		JWLocation.o = eval( '(' + JWLocation.data + ')' );
	},
	select: function(idp, idc, pid, cid){
		var ep = $(idp);
		var ec = $(idc);
		if( !ep || !ec )
			return;

		if( ep.options.length == 0 ){
			ep.options.add( new Option('',0,false,false) );
			for( var k in JWLocation.o ){
				var v = JWLocation.o[k][0];
				ep.options.add( new Option( v , k, (pid && k==pid), (pid && k==pid) ) );
			}
		}

		ec.options.length = 0;
		var p = JWLocation.o[pid];
		if( p ) {
			ec.options.add( new Option('',0,false,false) );
			for(var i=0; i<p[1].length; i++){
				var k = p[1][i][0];
				var v = p[1][i][1];
				ec.options.add( new Option( v, k, (cid && k==cid), (cid && k==cid) ) );
			}
		}
	}
};
