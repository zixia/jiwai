<h2>${htmlSpecialChars($userInfo['nameScreen'])}的资料</h2>
<p>姓名：{$userInfo['nameFull']}</p>
<p>位置：${JWLocation::GetLocationName($userInfo['location'])}</p>
<p>网址：{$userInfo['url']}</p>
<p>自述：{$userInfo['bio']}</p>
<p><a href="/{$userInfo['nameUrl']}/followings">关注${JWFollower::GetFollowingNum($userInfo['id'])}人</a>，<a href="/{$userInfo['nameUrl']}/followers">被${JWFollower::GetFollowerNum($userInfo['id'])}人关注</a></p>
