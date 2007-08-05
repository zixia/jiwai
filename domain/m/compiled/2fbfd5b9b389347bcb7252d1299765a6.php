<card title="叽歪de/<?= $this->userInfo['nameScreen'] ?>">
<?= $this->userInfo['nameScreen'] ?>的消息|<a href="/<?= $this->userInfo['nameScreen'] ?>/with_friends/"><?= $this->userInfo['nameScreen'] ?>和好友</a><br/>
<? if(is_array($this->statuses)){foreach($this->statuses as $this->status) { ?>
    <?= $this->status['status'] ?><br/>
<? }}?>
</card>
