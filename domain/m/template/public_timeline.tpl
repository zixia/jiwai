<card title="叽歪广场">
<!--{foreach $statuses as $status}-->
    <a href="${buildUrl('/'.htmlSpecialChars($users[$status['idUser']]['nameScreen']).'/')}">
        ${htmlSpecialChars($users[$status['idUser']]['nameScreen'])}
    </a>: 
    ${htmlSpecialChars($status['status'])}<br/>
<!--{/foreach}-->
    <!--${$shortcut=array('index')}-->
    <!--{include shortcut}-->
</card>
