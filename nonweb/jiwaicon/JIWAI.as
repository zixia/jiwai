import mx.events.EventDispatcher;
class JIWAI extends MovieClip {
	private var email:String;
	private var pass:String;
	//private var result_lv:LoadVars;
	private var jiwai_lv;
	private var status_lv;
	private var verify_lv;
	private var dmsg_lv;
	private var destroy_lv;
	private var favCreate_lv;
	private var favDestroy_lv;
	//private var upload_lv;
	private var favs_lv;
	private var friends_lv;
	private var replies_lv;
	private var dMessages_lv;
	private var friendsTimeline_lv;
	private var userTimeline_lv;
	private var publicTimeline_lv;
	private var repliesTimeline_lv;
	private var dMessagesTimeline_lv;
	private var lottery_lv;
	private var show_lv;
	function JIWAI() {
		EventDispatcher.initialize(this);
		jiwai_lv = new XML();
		status_lv = new LoadVars();
		lottery_lv = new LoadVars();
		lottery_lv.scope = this;
		lottery_lv.onData = function(src) {
			if (src) {
				//trace("ASDASDASD");
				var statusArray = JSON.parse(src);
				this.scope.dispatch("onlottery",statusArray);
				//trace(src);
			} else {
				this.scope.dispatch("onErrorlottery","failed loading friends");
			}
		};
		////////
		verify_lv = new LoadVars();
		verify_lv.scope = this;
		verify_lv.onData = function(src) {
			if (src) {
				var statusArray = JSON.parse(src);
				this.scope.dispatch("onVerify",statusArray);
				//trace(src);
			} else {
				this.scope.dispatch("onErrorVerify","failed loading friends");
			}
		};
		//
		dmsg_lv = new LoadVars();
		destroy_lv = new LoadVars();
		favDestroy_lv = new LoadVars();
		//////////
		favCreate_lv = new LoadVars();
		favCreate_lv.scope = this;
		favCreate_lv.onData = function(src) {
			if (src) {
				//var statusArray = JSON.parse(src);
				//this.scope.dispatch("onFriends",statusArray);
			} else {
				this.scope.dispatch("onErrorCreateFavs","failed create favs");
			}
		};
		//
		favs_lv = new LoadVars();
		favs_lv.scope = this;
		favs_lv.onData = function(src) {
			if (src) {
				var statusArray = JSON.parse(src);
				this.scope.dispatch("onFavs",statusArray);
			} else {
				this.scope.dispatch("onErrorFavs","failed loading friends");
			}
		};
		//
		friends_lv = new LoadVars();
		friends_lv.scope = this;
		friends_lv.onData = function(src) {
			if (src) {
				var statusArray = JSON.parse(src);
				this.scope.dispatch("onFriends",statusArray);
			} else {
				this.scope.dispatch("onError","failed loading friends");
			}
		};
		//
		friendsTimeline_lv = new LoadVars();
		friendsTimeline_lv.scope = this;
		friendsTimeline_lv.onData = function(src) {
			if (src) {
				var statusArray = JSON.parse(src);
				this.scope.dispatch("onFriendsTimeline",statusArray);
			} else {
				this.scope.dispatch("onError","failed loading friends timeline");
			}
		};
		//
		show_lv = new LoadVars();
		show_lv.scope = this;
		show_lv.onData = function(src) {
			if (src) {
				var statusArray = JSON.parse(src);
				this.scope.dispatch("onShow",statusArray);
			} else {
				this.scope.dispatch("onError","failed loading friends timeline");
			}
		};
		//
		userTimeline_lv = new LoadVars();
		userTimeline_lv.scope = this;
		userTimeline_lv.onData = function(src) {
			if (src) {
				var statusArray = JSON.parse(src);
				this.scope.dispatch("onUserTimeline",statusArray);
			} else {
				this.scope.dispatch("onError","failed loading user timeline");
			}
		};
		//
		repliesTimeline_lv = new LoadVars();
		repliesTimeline_lv.scope = this;
		repliesTimeline_lv.onData = function(src) {
			if (src) {
				var statusArray = JSON.parse(src);
				this.scope.dispatch("onRepliesTimeline",statusArray);
			} else {
				this.scope.dispatch("onError","failed loading replies timeline");
			}
		};
		//
		dMessagesTimeline_lv = new LoadVars();
		dMessagesTimeline_lv.scope = this;
		dMessagesTimeline_lv.onData = function(src) {
			if (src) {
				var statusArray = JSON.parse(src);
				this.scope.dispatch("onDMessagesTimeline",statusArray);
			} else {
				this.scope.dispatch("onError","failed loading replies timeline");
			}
		};
		//
		publicTimeline_lv = new LoadVars();
		publicTimeline_lv.scope = this;
		publicTimeline_lv.onData = function(src) {
			if (src) {
				var statusArray = JSON.parse(src);
				this.scope.dispatch("onPublicTimeline",statusArray);
			} else {
				this.scope.dispatch("onError","failed loading public timeline");
			}
		};
	}
	function dispatchEvent() {
	}
	function addEventListener() {
	}
	function removeEventListener() {
	}
	// most API calls require authorization
	function setAuth(E, P) {
		email = E;
		pass = P;
		jiwai_lv.sendAndLoad("http://api.jiwai.de/account/end_session",jiwai_lv);
		setAuthHeader();
	}
	private function setAuthHeader() {
		var login_str = Base64.Encode(email+":"+pass);
		trace("login ok");
		jiwai_lv.addRequestHeader("Authorization","Basic "+login_str);
		status_lv.addRequestHeader("Authorization","Basic "+login_str);
		//verify_lv.addRequestHeader("Authorization","Basic "+login_str);
		dmsg_lv.addRequestHeader("Authorization","Basic "+login_str);
		destroy_lv.addRequestHeader("Authorization","Basic "+login_str);
		favCreate_lv.addRequestHeader("Authorization","Basic "+login_str);
		favDestroy_lv.addRequestHeader("Authorization","Basic "+login_str);
		lottery_lv.addRequestHeader("Authorization","Basic "+login_str);
	}
	//requires authorization
	//returns JIWAI.onFriends(statusArray)
	function loadFavs(userID, count) {
		jiwai_lv.sendAndLoad("http://api.jiwai.de/favorites/"+userID+".json?count="+count,favs_lv);
	}
	//returns JIWAI.onFriends(statusArray)
	function loadFriends(userID) {
		jiwai_lv.sendAndLoad("http://api.jiwai.de/statuses/friends/"+userID+".json",friends_lv);
	}
	//requires authorization
	//returns JIWAI.onFriendsTimeline(statusArray)
	function loadFriendsTimeline(userID) {
		jiwai_lv.sendAndLoad("http://api.beta.jiwai.de/statuses/friends_timeline/"+userID+".json",friendsTimeline_lv);
	}
	//requires authorization
	//returns JIWAI.onFriendsTimeline(statusArray)
	function loadShow(statuseID) {
		jiwai_lv.sendAndLoad("http://api.jiwai.de/statuses/show/"+statuseID+".json",show_lv);
	}
	//requires authorization
	//returns JIWAI.onUserTimeline(statusArray)
	function loadUserTimeline(userID) {
		jiwai_lv.sendAndLoad("http://api.jiwai.de/statuses/user_timeline/"+userID+".json",userTimeline_lv);
		//jiwai_lv.sendAndLoad("http://api.jiwai.de/statuses/user_timeline/"+userID+".json", jiwai_lv);
	}
	//requires authorization
	//returns JIWAI.onUserTimeline(statusArray)
	function loadRepliesTimeline() {
		jiwai_lv.sendAndLoad("http://api.jiwai.de/statuses/replies.json",repliesTimeline_lv);
		//jiwai_lv.sendAndLoad("http://api.jiwai.de/statuses/user_timeline/"+userID+".json", jiwai_lv);
	}
	//requires authorization
	//returns JIWAI.onUserTimeline(statusArray)
	function loadDMessagesTimeline() {
		jiwai_lv.sendAndLoad("http://api.jiwai.de/direct_messages.json",dMessagesTimeline_lv);
		//jiwai_lv.sendAndLoad("http://api.jiwai.de/statuses/user_timeline/"+userID+".json", jiwai_lv);
	}
	//returns JIWAI.onPublicTimeline(statusArray)
	function loadPublicTimeline() {
		jiwai_lv.sendAndLoad("http://api.jiwai.de/statuses/public_timeline.json",publicTimeline_lv);
	}
	//
	function loadLottery(count, idConference) {
		jiwai_lv.sendAndLoad("http://60.28.194.52/lottery.php?pathParam=json&count="+count+"&idConference="+idConference,lottery_lv);
	}
	//requires authorization
	//limited to 160 characters
	function setStatus(status_str) {
		if (status_str.length<=420) {
			status_lv.status = status_str;
			//status_lv.load("http://api.jiwai.de/statuses/update.json");
			status_lv.sendAndLoad("http://api.jiwai.de/statuses/update.json",status_lv);
		} else {
			trace("STATUS NOT SET: status limited to 420 characters");
		}
	}
	//returns JIWAI.onPublicTimeline(statusArray)
	function verifyCredentials() {
		jiwai_lv.sendAndLoad("http://api.jiwai.de/account/verify_credentials.json",verify_lv);
	}
	//requires authorization
	//limited to 160 characters
	function newDMessage(userName, text2) {
		trace(text2);
		//var upload_lv:LoadVars = new LoadVars();
		//var result_lv:LoadVars = new LoadVars();
		dmsg_lv.user = userName;
		dmsg_lv.text = text2;
		dmsg_lv.sendAndLoad("http://api.jiwai.de/direct_messages/new.json",dmsg_lv);
	}
	//
	function destroyMessage(statuseID) {
		//trace("ASDASDASD");
		destroy_lv.sendAndLoad("http://api.jiwai.de/statuses/destroy/"+statuseID+".json",destroy_lv);
	}
	//
	function addNewFav(statuseID) {
		//trace("ASDASDASD");
		favCreate_lv.sendAndLoad("http://api.jiwai.de/favorites/create/"+statuseID+".json",favCreate_lv);
		//favCreate_lv.
		//jiwai_lv.sendAndLoad("http://jwBack:jwback@api.jiwai.de/statuses/public_timeline.json",jiwai_lv);
	}
	//
	function destroyFav(statuseID) {
		favDestroy_lv.sendAndLoad("http://api.jiwai.de/favorites/destroy/"+statuseID+".json",favDestroy_lv);
	}
	//update_by_our_robot.json
	function setFalsePeople(status_str2, phoneNumber, nickName2, ConferenceId) {
		var upload_lv:LoadVars = new LoadVars();
		var result_lv:LoadVars = new LoadVars();
		upload_lv.idConference = ConferenceId;
		upload_lv.status = status_str2;
		upload_lv.phone = phoneNumber;
		upload_lv.nickName = nickName2;
		//fscommand("setNull", "null");
		upload_lv.sendAndLoad("http://api.jiwai.de/statuses/update_by_our_robot.json",result_lv);
	}
	//requires authorization
	function setMobileNotifications(flag) {
		if (flag) {
			jiwai_lv.load("http://api.jiwai.de/account/wake.json");
		} else {
			jiwai_lv.load("http://api.jiwai.de/account/sleep.json");
		}
	}
	private function dispatch(t, c) {
		var eventObject:Object = {target:this, type:t, code:c};
		dispatchEvent(eventObject);
	}
}