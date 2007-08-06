<card title="叽歪de/{$userInfo['nameScreen']}">
<!--{include wo/update}-->
<!--{foreach $statuses as $status}-->
    <a href="${buildUrl('/'.$users[$status['idUser']]['nameScreen'].'/')}">
        ${htmlSpecialChars($users[$status['idUser']]['nameScreen'])}
    </a>: 
    {$status['status']}<br/>
<!--{/foreach}-->
<!--{include shortcut}-->
</card>
