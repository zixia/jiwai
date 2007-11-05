<h2>${htmlSpecialChars($userInfo['nameScreen'])}的资料</h2>
<p>位置：${JWLocation::GetLocationName($userInfo['location'])}</p>
<p>网址：{$userInfo['url']}</p>
<p>自述：{$userInfo['bio']}</p>
<p><a href="/{$userInfo['nameUrl']}/friends">${JWFriend::GetFriendNum($userInfo['id'])}个好友</a>，<a href="/{$userInfo['nameUrl']}/followers">${JWFollower::GetFollowerNum($userInfo['id'])}个粉丝</a></p>
