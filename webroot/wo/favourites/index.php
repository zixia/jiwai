<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');

JWUser::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();


$status_ids		= JWFavourite::GetFavourite($logined_user_info['id']);
?>

<html>

<?php JWTemplate::html_head() ?>

<body class="favourings" id="favourings">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>


<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


<h2> 我的收藏 </h2>

<p>将更新旁边的星标点亮后，它们就会被存在这里啦！</p>


<?php
$favourite_ids = JWFavourite::GetStatusListUser($logined_user_id);

?>
<table class="doing" cellspacing="0">

	<tr class="odd hentry" id="status_51680632">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/okka" class="url"><img alt="okka" class="photo fn" src="http://assets1.twitter.com/system/user/profile_image/5803152/normal/img1332_w19.gif?1178436192" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/okka" title="okka">okka</a></strong>
		
					<span class="entry-title entry-content">
			  プレゼン資料作成中．GWっておいしいの？
			</span>

			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/okka/statuses/51680632" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-06T07:26:13+00:00">04:26 PM May 06, 2007</abbr></a>
						from web
      
			<span id="status_actions_51680632">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/51680632', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_51680632').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_51680632" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>

</tr>
<tr class="even hentry" id="status_51551322">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/al3x" class="url"><img alt="Alex Payne" class="photo fn" src="http://assets1.twitter.com/system/user/profile_image/18713/normal/Cam-1.jpg?1177965111" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/al3x" title="Alex Payne">al3x</a></strong>
		
					<span class="entry-title entry-content">
			  At Super Happy Dev House in Los Gatos.  Mood: reasonably happy, not "super" happy.
			</span>

			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/al3x/statuses/51551322" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-06T04:06:46+00:00">09:06 PM May 05, 2007</abbr></a>
						from web
      
			<span id="status_actions_51551322">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/51551322', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_51551322').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_51551322" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>

</tr>
<tr class="odd hentry" id="status_51505192">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/crystal" class="url"><img alt="Crystal" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/15/normal/Photo_135.jpg?1171993518" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/crystal" title="Crystal">crystal</a></strong>
		
					<span class="entry-title entry-content">
			  don quixote! at the sf ballet at last.
			</span>

			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/crystal/statuses/51505192" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-06T03:04:06+00:00">08:04 PM May 05, 2007</abbr></a>
						from txt
      
			<span id="status_actions_51505192">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/51505192', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_51505192').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_51505192" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>

</tr>
<tr class="even hentry" id="status_51444762">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/bs" class="url"><img alt="Britt Selvitelle" class="photo fn" src="http://assets1.twitter.com/system/user/profile_image/309073/normal/122304070_bd189bf030.jpg?1172813790" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/bs" title="Britt Selvitelle">bs</a></strong>
		
					<span class="entry-title entry-content">
			  @blaine last time it was non existant.. We should stop on the way out
			</span>

			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/bs/statuses/51444762" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-06T01:40:55+00:00">06:40 PM May 05, 2007</abbr></a>
						from txt
              <a href="http://twitter.com/blaine/statuses/51420872">in reply to blaine</a>
      
			<span id="status_actions_51444762">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/51444762', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_51444762').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_51444762" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="odd hentry" id="status_50400772">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/al3x" class="url"><img alt="Alex Payne" class="photo fn" src="http://assets1.twitter.com/system/user/profile_image/18713/normal/Cam-1.jpg?1177965111" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/al3x" title="Alex Payne">al3x</a></strong>
		
					<span class="entry-title entry-content">

			  Commuting is a chance to read more Runaways. Damn fine comic.
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/al3x/statuses/50400772" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-05T01:33:00+00:00">06:33 PM May 04, 2007</abbr></a>
						from txt
      
			<span id="status_actions_50400772">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/50400772', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_50400772').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_50400772" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="even hentry" id="status_50367532">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>
		
					<span class="entry-title entry-content">

			  peterc is right.  currently digging a cd i hated on the first listen.  those are always the ones i end up liking the most.
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/50367532" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-05T00:49:04+00:00">05:49 PM May 04, 2007</abbr></a>
						from web
      
			<span id="status_actions_50367532">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/50367532', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_50367532').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_50367532" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="odd hentry" id="status_50318972">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>
		
					<span class="entry-title entry-content">

			  "ur an american and you don't own a vehicle... what message are you trying to send?"
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/50318972" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T23:39:56+00:00">04:39 PM May 04, 2007</abbr></a>
						from web
      
			<span id="status_actions_50318972">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/50318972', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_50318972').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_50318972" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="even hentry" id="status_50136862">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>
		
					<span class="entry-title entry-content">

			  "i want to send you some uml" "some what?" "can you read uml?" "no" "i thought you were a developer?" "yeah, i thought you were, too"
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/50136862" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T19:59:13+00:00">12:59 PM May 04, 2007</abbr></a>
						from web
      
			<span id="status_actions_50136862">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/50136862', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_50136862').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_50136862" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="odd hentry" id="status_49984572">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>
		
					<span class="entry-title entry-content">

			  "What's up! I think my sites can be built with no spec, but keep changing.  Aren't changes easy to do?" -- potential client
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/49984572" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T17:23:47+00:00">10:23 AM May 04, 2007</abbr></a>
						from web
      
			<span id="status_actions_49984572">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/49984572', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_49984572').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_49984572" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="even hentry" id="status_49687522">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/zixia" class="url"><img alt="Zhuohuan Li" class="photo fn" src="http://assets3.twitter.com/system/user/profile_image/762460/normal/DSC01663_1024x768.jpg?1177944712" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/zixia" title="Zhuohuan Li">zixia</a></strong>
		
					<span class="entry-title entry-content">

			  Hello, Phone. ~
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/zixia/statuses/49687522" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T13:01:45+00:00">09:01 PM May 04, 2007</abbr></a>
						from web
      
			<span id="status_actions_49687522">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/49687522', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_49687522').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_49687522" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
			<a href="/t/status/destroy/49687522" onclick="if (confirm('Sure you want to delete this update? There is NO undo!')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href;var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'delete'); f.appendChild(m);f.submit(); };return false;" title="Delete this update?"><img alt="Icon_trash" border="0" src="http://assets0.twitter.com/images/icon_trash.gif?1178793536" /></a>
		</span>

		</span>
	</td>
</tr>
<tr class="odd hentry" id="status_49485682">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>

		
					<span class="entry-title entry-content">
			  "Language speed tests are as relevant as promises made by politicians on election day." -- <a href="http://tinyurl.com/ytvac9" target="_blank">http://tinyurl.com/ytvac9</a>
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/49485682" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T08:33:44+00:00">01:33 AM May 04, 2007</abbr></a>
						from web
      
			<span id="status_actions_49485682">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/49485682', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_49485682').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_49485682" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="even hentry" id="status_49481642">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>

		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>
		
					<span class="entry-title entry-content">
			  yessssss -- found out how to auto-generate syntax highlighted html from vim.  the day is mine!  <a href="http://ozmm.org/story.rb.html" target="_blank">http://ozmm.org/story.rb.html</a>
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/49481642" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T08:27:41+00:00">01:27 AM May 04, 2007</abbr></a>

						from web
      
			<span id="status_actions_49481642">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/49481642', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_49481642').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_49481642" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="odd hentry" id="status_49449592">
  
	
			<td class="thumb vcard author">

			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>
		
					<span class="entry-title entry-content">
			  that took, like, all my energy
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/49449592" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T07:43:01+00:00">12:43 AM May 04, 2007</abbr></a>

						from web
      
			<span id="status_actions_49449592">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/49449592', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_49449592').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_49449592" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="even hentry" id="status_49300132">
  
	
			<td class="thumb vcard author">

			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>
		
					<span class="entry-title entry-content">
			  whoa redhanded is over?  sad, sad day.
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/49300132" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T04:07:43+00:00">09:07 PM May 03, 2007</abbr></a>

						from web
      
			<span id="status_actions_49300132">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/49300132', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_49300132').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_49300132" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="odd hentry" id="status_49264522">
  
	
			<td class="thumb vcard author">

			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>
		
					<span class="entry-title entry-content">
			  hey she remembered my drink
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/49264522" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T03:24:32+00:00">08:24 PM May 03, 2007</abbr></a>

						from web
      
			<span id="status_actions_49264522">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/49264522', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_49264522').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_49264522" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="even hentry" id="status_49215312">
  
	
			<td class="thumb vcard author">

			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>
		
					<span class="entry-title entry-content">
			  fighting my inbox with a swyrd
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/49215312" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T02:27:54+00:00">07:27 PM May 03, 2007</abbr></a>

						from web
      
			<span id="status_actions_49215312">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/49215312', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_49215312').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_49215312" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="odd hentry" id="status_49168522">
  
	
			<td class="thumb vcard author">

			  <a href="http://twitter.com/defunkt" class="url"><img alt="Chris Wanstrath" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/713263/normal/3d12d0e9ca30c97d15a1e826c4553a1d.jpg?1176056431" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/defunkt" title="Chris Wanstrath">defunkt</a></strong>
		
					<span class="entry-title entry-content">
			  just ran into one of the aforementioned bank tellers on the street.  what a strange, strange day.
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/defunkt/statuses/49168522" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-04T01:32:31+00:00">06:32 PM May 03, 2007</abbr></a>

						from web
      
			<span id="status_actions_49168522">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/49168522', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_49168522').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_49168522" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>
<tr class="even hentry" id="status_48907732">
  
	
			<td class="thumb vcard author">

			  <a href="http://twitter.com/al3x" class="url"><img alt="Alex Payne" class="photo fn" src="http://assets1.twitter.com/system/user/profile_image/18713/normal/Cam-1.jpg?1177965111" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/al3x" title="Alex Payne">al3x</a></strong>
		
					<span class="entry-title entry-content">
			  @5dots and @sroberts totally told me so about Google Reader.  Consider me a convert.
			</span>
			
				
		<span class="meta entry-meta">
						  <a href="http://twitter.com/al3x/statuses/48907732" class="entry-date" rel="bookmark"><abbr class="published" title="2007-05-03T20:31:54+00:00">01:31 PM May 03, 2007</abbr></a>

						from im
              <a href="http://twitter.com/5dots/statuses/46707722">in reply to 5dots</a>
      
			<span id="status_actions_48907732">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/48907732', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_48907732').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_48907732" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>

<tr class="odd hentry" id="status_7299351">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/herock" class="url"><img alt="herock" class="photo fn" src="http://assets3.twitter.com/system/user/profile_image/754003/normal/herock.jpg?1170567018" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/herock" title="herock">herock</a></strong>
		
					<span class="entry-title entry-content">
			  给 twitter 拉了两个新用户，分特
			</span>
			
				
		<span class="meta entry-meta">

						  <a href="http://twitter.com/herock/statuses/7299351" class="entry-date" rel="bookmark"><abbr class="published" title="2007-03-13T06:40:23+00:00">02:40 PM March 13, 2007</abbr></a>
						from im
      
			<span id="status_actions_7299351">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/7299351', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_7299351').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_7299351" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>

<tr class="even hentry" id="status_1445623">
  
	
			<td class="thumb vcard author">
			  <a href="http://twitter.com/sailor" class="url"><img alt="Wilson" class="photo fn" src="http://assets0.twitter.com/system/user/profile_image/4013/normal/bw11.jpg?1171961000" /></a>
		</td>
		
	<td>	
					<strong><a href="http://twitter.com/sailor" title="Wilson">sailor</a></strong>
		
					<span class="entry-title entry-content">
			  It's actually quite cool when someones code breaks and you don't have to fix it
			</span>
			
				
		<span class="meta entry-meta">

						  <a href="http://twitter.com/sailor/statuses/1445623" class="entry-date" rel="bookmark"><abbr class="published" title="2006-12-20T22:03:27+00:00">08:03 AM December 21, 2006</abbr></a>
						from web
      
			<span id="status_actions_1445623">

			<a href="#" onclick="new Ajax.Request('/favourings/destroy/1445623', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_1445623').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_full" border="0" id="status_star_1445623" src="http://assets2.twitter.com/images/icon_star_full.gif?1178793536" /></a>
	
	</span>

		</span>
	</td>
</tr>

</table>

<div class="pagination">
<br/>
</div>

	
		</div><!-- wrapper -->
	</div><!-- content -->
</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

