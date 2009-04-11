<!--${
    $user = JWUser::GetUserInfo($g_page_user_id);
    if (isset($user['srcRegister']) && $user['srcRegister'] == 'bomeiti') {
        $ad_bomeiti_register = JWPartner_Bomeiti::GetAdUrl(JWPartner_Bomeiti::BOMEITI_REGISTER, $user);
    }
}-->
<!--{if $ad_bomeiti_register}-->
<div class="side1 mar_b8">
	<div class="pagetitle">
		<h3>博媒体</h3>
	</div>
    <script type="text/javascript" src="{$ad_bomeiti_register}"></script>
</div>
<!--{/if}-->
