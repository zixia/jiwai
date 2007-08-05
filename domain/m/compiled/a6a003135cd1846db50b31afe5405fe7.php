<card title="叽歪广场">
<? if(is_array($this->statuses)){foreach($this->statuses as $this->status) { ?>
    <a href="/<?= htmlSpecialChars($this->users[$this->status['idUser']]['nameScreen']) ?>/">
        <?= htmlSpecialChars($this->users[$this->status['idUser']]['nameScreen']) ?>
    </a>: 
    <?= htmlSpecialChars($this->status['status']) ?><br/>
<? }}?>
</card>
