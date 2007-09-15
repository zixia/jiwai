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
JiWaiLocation.init('{"1":["\u5b89\u5fbd",[["35","\u5408\u80a5"],["36","\u6dee\u5357"],["37","\u868c\u57e0"],["38","\u5bbf\u5dde"],["39","\u6dee\u5317"],["40","\u961c\u9633"],["41","\u4eb3\u5dde"],["42","\u516d\u5b89"],["43","\u5de2\u6e56"],["44","\u6ec1\u5dde"],["45","\u829c\u6e56"],["46","\u5ba3\u57ce"],["47","\u9a6c\u978d\u5c71"],["48","\u94dc\u9675"],["49","\u9ec4\u5c71"],["50","\u5b89\u5e86"],["51","\u6c60\u5dde"]]],"2":["\u5317\u4eac",[["52","\u5317\u4eac"]]],"3":["\u798f\u5efa",[["53","\u798f\u5dde"],["54","\u8386\u7530"],["55","\u5b81\u5fb7"],["56","\u5357\u5e73"],["57","\u53a6\u95e8"],["58","\u6cc9\u5dde"],["59","\u6f33\u5dde"],["60","\u9f99\u5ca9"],["61","\u4e09\u660e"]]],"4":["\u7518\u8083",[["62","\u5170\u5dde"],["63","\u767d\u94f6"],["64","\u4e34\u590f"],["65","\u6b66\u5a01"],["66","\u5f20\u6396"],["67","\u9152\u6cc9"],["68","\u5609\u5cea\u5173"],["69","\u91d1\u660c"],["70","\u5929\u6c34"],["71","\u9647\u5357"],["72","\u5b9a\u897f"],["73","\u5e73\u51c9"],["74","\u5e86\u9633"],["75","\u7518\u5357"]]],"5":["\u5e7f\u4e1c",[["76","\u5e7f\u5dde"],["77","\u6e05\u8fdc"],["78","\u97f6\u5173"],["79","\u6885\u5dde"],["80","\u6c55\u5934"],["81","\u60e0\u5dde"],["82","\u6c55\u5c3e"],["83","\u6cb3\u6e90"],["84","\u6df1\u5733"],["85","\u73e0\u6d77"],["86","\u6f6e\u5dde"],["87","\u63ed\u9633"],["88","\u4e1c\u839e"],["89","\u6e5b\u6c5f"],["90","\u8302\u540d"],["91","\u8087\u5e86"],["92","\u4e91\u6d6e"],["93","\u4f5b\u5c71"],["94","\u4e2d\u5c71"],["95","\u6c5f\u95e8"],["96","\u9633\u6c5f"]]],"6":["\u5e7f\u897f",[["97","\u5357\u5b81"],["98","\u5d07\u5de6"],["99","\u767e\u8272"],["100","\u94a6\u5dde"],["101","\u5317\u6d77"],["102","\u7389\u6797"],["103","\u8d35\u6e2f"],["104","\u9632\u57ce\u6e2f"],["105","\u6842\u6797"],["106","\u8d3a\u5dde"],["107","\u68a7\u5dde"],["108","\u67f3\u5dde"],["109","\u6765\u5bbe"],["110","\u6cb3\u6c60"]]],"7":["\u8d35\u5dde",[["111","\u8d35\u9633"],["112","\u6bd5\u8282"],["113","\u516d\u76d8\u6c34"],["114","\u94dc\u4ec1"],["115","\u9ed4\u4e1c\u5357"],["116","\u9ed4\u5357"],["117","\u5b89\u987a"],["118","\u9ed4\u897f\u5357"],["119","\u9075\u4e49"]]],"8":["\u6d77\u5357",[["120","\u5b9a\u5b89\u53bf"],["121","\u5c6f\u660c\u53bf"],["122","\u6f84\u8fc8\u53bf"],["123","\u4e34\u9ad8\u53bf"],["124","\u767d\u6c99\u53bf"],["125","\u6d77\u53e3"],["126","\u6587\u660c"],["127","\u743c\u6d77"],["128","\u4e07\u5b81"],["129","\u510b\u5dde"],["130","\u4e09\u4e9a"],["131","\u4e94\u6307\u5c71"],["132","\u4fdd\u4ead"],["133","\u9675\u6c34"],["134","\u4e50\u4e1c"],["135","\u4e1c\u65b9"],["136","\u660c\u6c5f"],["137","\u743c\u4e2d"]]],"9":["\u6cb3\u5317",[["138","\u77f3\u5bb6\u5e84"],["139","\u8861\u6c34"],["140","\u90a2\u53f0"],["141","\u90af\u90f8"],["142","\u6ca7\u5dde"],["143","\u5510\u5c71"],["144","\u5eca\u574a"],["145","\u79e6\u7687\u5c9b"],["146","\u627f\u5fb7"],["147","\u4fdd\u5b9a"],["148","\u5f20\u5bb6\u53e3"]]],"10":["\u6cb3\u5357",[["149","\u90d1\u5dde"],["150","\u65b0\u4e61"],["151","\u7126\u4f5c"],["152","\u6d4e\u6e90"],["153","\u5b89\u9633"],["154","\u6fee\u9633"],["155","\u9e64\u58c1"],["156","\u8bb8\u660c"],["157","\u6f2f\u6cb3"],["158","\u9a7b\u9a6c\u5e97"],["159","\u4fe1\u9633"],["160","\u5468\u53e3"],["161","\u5e73\u9876\u5c71"],["162","\u6d1b\u9633"],["163","\u4e09\u95e8\u5ce1"],["164","\u5357\u9633"],["165","\u5f00\u5c01"],["166","\u5546\u4e18"]]],"11":["\u9ed1\u9f99\u6c5f",[["167","\u54c8\u5c14\u6ee8"],["168","\u7ee5\u5316"],["169","\u4f0a\u6625"],["170","\u4f73\u6728\u65af"],["171","\u9e64\u5c97"],["172","\u4e03\u53f0\u6cb3"],["173","\u53cc\u9e2d\u5c71"],["174","\u7261\u4e39\u6c5f"],["175","\u9e21\u897f"],["176","\u9f50\u9f50\u54c8\u5c14"],["177","\u5927\u5e86"],["178","\u9ed1\u6cb3"],["179","\u5927\u5174\u5b89\u5cad"]]],"12":["\u6e56\u5317",[["180","\u6b66\u6c49"],["181","\u5929\u95e8"],["182","\u5b5d\u611f"],["183","\u4ed9\u6843"],["184","\u6f5c\u6c5f"],["185","\u8346\u5dde"],["186","\u9ec4\u77f3"],["187","\u9102\u5dde"],["188","\u54b8\u5b81"],["189","\u9ec4\u5188"],["190","\u8944\u6a0a"],["191","\u968f\u5dde"],["192","\u5341\u5830"],["193","\u795e\u519c\u67b6"],["194","\u5b9c\u660c"],["195","\u6069\u65bd"],["196","\u8346\u95e8"]]],"13":["\u6e56\u5357",[["197","\u957f\u6c99"],["198","\u6e58\u6f6d"],["199","\u682a\u6d32"],["200","\u76ca\u9633"],["201","\u5cb3\u9633"],["202","\u5e38\u5fb7"],["203","\u6e58\u897f"],["204","\u5a04\u5e95"],["205","\u6000\u5316"],["206","\u8861\u9633"],["207","\u90b5\u9633"],["208","\u90f4\u5dde"],["209","\u6c38\u5dde"],["210","\u5f20\u5bb6\u754c"]]],"14":["\u5409\u6797",[["211","\u957f\u6625"],["212","\u5409\u6797"],["213","\u5ef6\u8fb9"],["214","\u901a\u5316"],["215","\u767d\u5c71"],["216","\u56db\u5e73"],["217","\u8fbd\u6e90"],["218","\u767d\u57ce"],["219","\u677e\u539f"]]],"15":["\u6c5f\u82cf",[["220","\u5357\u4eac"],["221","\u9547\u6c5f"],["222","\u5e38\u5dde"],["223","\u65e0\u9521"],["224","\u82cf\u5dde"],["225","\u5f90\u5dde"],["226","\u8fde\u4e91\u6e2f"],["227","\u6dee\u5b89"],["228","\u5bbf\u8fc1"],["229","\u76d0\u57ce"],["230","\u626c\u5dde"],["231","\u6cf0\u5dde"],["232","\u5357\u901a"]]],"16":["\u6c5f\u897f",[["233","\u5357\u660c"],["234","\u4e5d\u6c5f"],["235","\u666f\u5fb7\u9547"],["236","\u4e0a\u9976"],["237","\u9e70\u6f6d"],["238","\u5b9c\u6625"],["239","\u840d\u4e61"],["240","\u65b0\u4f59"],["241","\u8d63\u5dde"],["242","\u5409\u5b89"],["243","\u629a\u5dde"]]],"17":["\u8fbd\u5b81",[["244","\u6c88\u9633"],["245","\u8fbd\u9633"],["246","\u94c1\u5cad"],["247","\u629a\u987a"],["248","\u978d\u5c71"],["249","\u8425\u53e3"],["250","\u5927\u8fde"],["251","\u672c\u6eaa"],["252","\u4e39\u4e1c"],["253","\u9526\u5dde"],["254","\u671d\u9633"],["255","\u961c\u65b0"],["256","\u76d8\u9526"],["257","\u846b\u82a6\u5c9b"]]],"18":["\u5185\u8499\u53e4",[["258","\u4e4c\u5170\u5bdf\u5e03\u76df"],["259","\u5df4\u5f66\u6dd6\u5c14\u76df"],["260","\u547c\u548c\u6d69\u7279"],["261","\u5174\u5b89\u76df"],["262","\u5305\u5934"],["263","\u4e4c\u6d77"],["264","\u9102\u5c14\u591a\u65af"],["265","\u547c\u4f26\u8d1d\u5c14"],["266","\u8d64\u5cf0"],["267","\u9521\u6797\u90ed\u52d2\u76df"],["268","\u901a\u8fbd"],["269","\u963f\u62c9\u5584\u76df"]]],"19":["\u5b81\u590f",[["270","\u94f6\u5ddd"],["271","\u5434\u5fe0"],["272","\u77f3\u5634\u5c71"],["273","\u56fa\u539f"]]],"20":["\u9752\u6d77",[["274","\u897f\u5b81"],["275","\u6d77\u4e1c"],["276","\u9ec4\u5357"],["277","\u6d77\u5317"],["278","\u6d77\u5357"],["279","\u679c\u6d1b"],["280","\u7389\u6811"],["281","\u6d77\u897f"]]],"21":["\u5c71\u4e1c",[["282","\u6d4e\u5357"],["283","\u804a\u57ce"],["284","\u5fb7\u5dde"],["285","\u6dc4\u535a"],["286","\u6ee8\u5dde"],["287","\u4e1c\u8425"],["288","\u6f4d\u574a"],["289","\u70df\u53f0"],["290","\u5a01\u6d77"],["291","\u9752\u5c9b"],["292","\u6cf0\u5b89"],["293","\u83b1\u829c"],["294","\u6d4e\u5b81"],["295","\u83cf\u6cfd"],["296","\u4e34\u6c82"],["297","\u65e5\u7167"],["298","\u67a3\u5e84"]]],"22":["\u5c71\u897f",[["299","\u592a\u539f"],["300","\u664b\u4e2d"],["301","\u5415\u6881"],["302","\u5ffb\u5dde"],["303","\u6714\u5dde"],["304","\u5927\u540c"],["305","\u4e34\u6c7e"],["306","\u8fd0\u57ce"],["307","\u9633\u6cc9"],["308","\u957f\u6cbb"],["309","\u664b\u57ce"]]],"23":["\u9655\u897f",[["310","\u897f\u5b89"],["311","\u54b8\u9633"],["312","\u6e2d\u5357"],["313","\u5ef6\u5b89"],["314","\u6986\u6797"],["315","\u5b9d\u9e21"],["316","\u6c49\u4e2d"],["317","\u5b89\u5eb7"],["318","\u5546\u6d1b"],["319","\u94dc\u5ddd"]]],"24":["\u4e0a\u6d77",[["320","\u4e0a\u6d77"]]],"25":["\u56db\u5ddd",[["321","\u6210\u90fd"],["322","\u4e50\u5c71"],["323","\u51c9\u5c71"],["324","\u6500\u679d\u82b1"],["325","\u5fb7\u9633"],["326","\u7709\u5c71"],["327","\u7ef5\u9633"],["328","\u963f\u575d"],["329","\u96c5\u5b89"],["330","\u7518\u5b5c"],["331","\u5e7f\u5143"],["332","\u9042\u5b81"],["333","\u8fbe\u5dde"],["334","\u5df4\u4e2d"],["335","\u5357\u5145"],["336","\u5e7f\u5b89"],["337","\u5185\u6c5f"],["338","\u8d44\u9633"],["339","\u81ea\u8d21"],["340","\u5b9c\u5bbe"],["341","\u6cf8\u5dde"]]],"26":["\u5929\u6d25",[["342","\u5929\u6d25"]]],"27":["\u897f\u85cf",[["343","\u62c9\u8428"],["344","\u90a3\u66f2"],["345","\u660c\u90fd"],["346","\u5c71\u5357"],["347","\u65e5\u5580\u5219"],["348","\u963f\u91cc"],["349","\u6797\u829d"]]],"28":["\u65b0\u7586",[["350","\u4e4c\u9c81\u6728\u9f50"],["351","\u660c\u5409"],["352","\u4e94\u5bb6\u6e20"],["353","\u77f3\u6cb3\u5b50"],["354","\u535a\u5c14\u5854\u62c9"],["355","\u514b\u62c9\u739b\u4f9d"],["356","\u4f0a\u7281"],["357","\u5410\u9c81\u756a"],["358","\u54c8\u5bc6"],["359","\u5df4\u97f3\u90ed\u695e"],["360","\u963f\u514b\u82cf"],["361","\u963f\u62c9\u5c14"],["362","\u5580\u4ec0"],["363","\u56fe\u6728\u8212\u514b"],["364","\u514b\u5b5c\u52d2\u82cf"],["365","\u548c\u7530"]]],"29":["\u4e91\u5357",[["366","\u695a\u96c4"],["367","\u7ea2\u6cb3"],["368","\u6606\u660e"],["369","\u7389\u6eaa"],["370","\u66f2\u9756"],["371","\u662d\u901a"],["372","\u601d\u8305"],["373","\u6587\u5c71"],["374","\u897f\u53cc\u7248\u7eb3"],["375","\u4e34\u6ca7"],["376","\u5927\u7406"],["377","\u6012\u6c5f"],["378","\u5fb7\u5b8f"],["379","\u8fea\u5e86"],["380","\u4e3d\u6c5f"],["381","\u4fdd\u5c71"]]],"30":["\u6d59\u6c5f",[["382","\u676d\u5dde"],["383","\u7ecd\u5174"],["384","\u6e56\u5dde"],["385","\u5609\u5174"],["386","\u5b81\u6ce2"],["387","\u821f\u5c71"],["388","\u53f0\u5dde"],["389","\u91d1\u534e"],["390","\u4e3d\u6c34"],["391","\u8862\u5dde"],["392","\u6e29\u5dde"]]],"31":["\u91cd\u5e86",[["393","\u91cd\u5e86"]]],"32":["\u9999\u6e2f",[["394","\u9999\u6e2f"]]],"33":["\u6fb3\u95e8",[["395","\u6fb3\u95e8"]]],"34":["\u53f0\u6e7e",[["396","\u53f0\u6e7e"]]]}');