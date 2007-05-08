/*
 *	JiWai.de Lib
 *	Author: zixia@zixia.net
 *	AKA Inc.
 *	2007-05
 */

var JiWai = 
{ 
	mVersion 	: 1,

	GetBgColor : function () 
	{
   		var id_element = $(arguments[0]);

		var color;
		var n=0;
		while ( 100>n++ ) 
		{
			try {
				color = id_element.getStyle('background-color');
			} catch ( e ) {
				break;
			}

			id_element = $(id_element.parentNode);

			if ( 'transparent'!=color )
				break;
		}

		if ( 'transition'==color )
			color = '#fff';

		return color;
	},

	Yft		: function (idElement) 
	{
		this.mIdElement = idElement;

		background_color = $(idElement).getStyle('background-color');

		orig_color 		= this.GetBgColor(idElement);

		yellow_color	= new Color('#ff0');
		yellow_color	= yellow_color.mix(orig_color);

		//$(idElement).effect('background-color').start('#f00',orig_color);

		$(idElement).effect(
			'background-color'
			,{
			 	duration: 1500
				,transition: Fx.Transitions.Quad.easeOut
			}
		).start(
			orig_color
			,yellow_color
		).chain( function () {
			$(idElement).effect(
				 'background-color'
				,{
					 duration: 1500
					,transition: Fx.Transitions.Bounce.easeOut
				}
			).start(yellow_color,orig_color).chain( function () {
				$(idElement).setStyle('background-color', background_color);
			})
		});
	}

}

