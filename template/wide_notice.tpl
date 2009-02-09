<!--${
	$notice = JWSession::GetInfo('error') . JWSession::GetInfo('notice');
	if ($g_current_user_id && '/wo/'==$_SERVER['REQUEST_URI']) 
	{
		$balloon_ids = JWBalloon::GetBalloonIds($g_current_user_id);
		$balloon_rows = JWBalloon::GetDbRowsByIds($balloon_ids);
		for ($i=0;$i<count($balloon_ids);$i++)
		{
			$balloon_id = $balloon_ids[$i];
			$balloon_row = $balloon_rows[$balloon_id];
			$notice .= JWBalloonMsg::FormatMsg($balloon_id, $balloon_row['html']);
		}
	}
}-->
<!--{if $notice || $notice=$forcenotice }-->
<div id="tipnote">
	<div class="yel mar_b8">
		<div class="f">
			<div class="pad_t8 tipnote" onclick="JiWai.KillNote('tipnote')">{$notice}</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
</div>
<!--{/if}-->
