</div>

<div class="footer">
<?php 
$logined_user_info = $this->GetUser();

//die(var_dump($logined_user_info));
if ( !empty($logined_user_info) )
{
	echo $this->FormOpen("", "TextSearch", "get"); 
	echo $this->HasAccess("write") ? "<a href=\"".$this->href("edit")."\" title=\"Click to edit this page\">Edit page</a> ::\n" : "";
	echo "<a href=\"".$this->href("history")."\" title=\"Click to view recent edits to this page\">Page History</a> ::\n";
	echo $this->GetPageTime() ? "<a href=\"".$this->href("revisions")."\" title=\"Click to view recent revisions list for this page\">".$this->GetPageTime()."</a> <a href=\"".$this->href("revisions.xml")."\" title=\"Click to view recent page revisions in XML format.\"><img src=\"images/xml.png\" width=\"36\" height=\"14\" align=\"middle\" style=\"border : 0px;\" alt=\"XML\" /></a> ::\n" : "";

	// if this page exists
	if ($this->page)
	{
		if ($owner = $this->GetPageOwner())
		{
			if ($owner == "(Public)")
			{
				print("Public page ".($this->IsAdmin() ? "<a href=\"".$this->href("acls")."\">(Edit ACLs)</a> ::\n" : "::\n"));
			}
			// if owner is current user
			elseif ($this->UserIsOwner())
			{
           			if ($this->IsAdmin())
           			{
					print("Owner: ".$this->Link($owner, "", "", 0)." :: <a href=\"".$this->href("acls")."\">Edit ACLs</a> ::\n");
            		} 
            		else 
            		{
					print("You own this page. :: <a href=\"".$this->href("acls")."\">Edit ACLs</a> ::\n");
				}
			}
			else
			{
				print("Owner: ".$this->Link($owner, "", "", 0)." ::\n");
			}
		}
		else
		{
			print("Nobody".($this->GetUser() ? " (<a href=\"".$this->href("claim")."\">Take Ownership</a>) ::\n" : " ::\n"));
		}
	}

	echo ($this->GetUser() ? "<a href='".$this->href("referrers")."' title='Click to view a list of URLs referring to this page.'>Referrers</a> :: " : "");
	echo 'Search: <input name="phrase" size="15" class="searchbox" />';
	echo $this->FormClose(); 
}
else
{
	echo '最后修改时间：' . $this->GetPageTime() ;
}
?>
</div>

<?php if ( 0 ) { ?>
<div class="smallprint">
<?php echo $this->Link("http://validator.w3.org/check/referer", "", "Valid XHTML 1.0 Transitional") ?> ::
<?php echo $this->Link("http://jigsaw.w3.org/css-validator/check/referer", "", "Valid CSS") ?> ::
Powered by <?php echo $this->Link("http://wikkawiki.org/", "", "Wikka Wakka Wiki ".$this->GetWakkaVersion()) ?>
</div>
<?php } ?>

<?php
	if ($this->GetConfigValue("sql_debugging"))
	{
		print("<div class=\"smallprint\"><strong>Query log:</strong><br />\n");
		foreach ($this->queryLog as $query)
		{
			print($query["query"]." (".$query["time"].")<br />\n");
		}
		print("</div>");
	}
?>
