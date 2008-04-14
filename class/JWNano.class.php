<?php
/**
 * @package JiWai.de
 * @copyright AKA Inc.
 * @author seek@jiwai.com
 * @version $Id$
 */

/**
 * JiWai.de JWNano.class.php
 */
class JWNano{

	static public function NanoFormat($status_id, $status=null, $status_type='NONE')
	{
		switch( $status_type )
		{
			case 'VOTE':
				$status = self::FormatVote($status_id, $status);
				break;
			default:
				break;
		}

		return $status;
	}
	
	static private function FormatVote( $id, $status, $device='web' )
	{
		if ( false == ($vote_item = JWSns::ParseVoteItem($status)) )
			return $status;

		$vote_result = JWNanoVote::DoVoteInfo( $id );
		$vote_row = JWNanoVote::GetDbRowByNumber( $id );
		$enabled =  (0 >= JWNanoVote::IsAvailable( $vote_row, $device, null, false )) ? 'disabled' : ''; 

		$status = $vote_item['status'];
		$items = $vote_item['items'];

		$vote_form = '<FORM style="padding:10px; margin:5px; border:1px dashed #CCC;" ACTION="/wo/nano/vote" method="POST" id="vote_form_'.$id.'">';
		$vote_form .= '<lable><strong>'.$status.'</strong></lable>';
		$vote_form .= '<input type="hidden" name="id" value="'.$id.'"/>';
		$vote_form .= '<input type="hidden" id="vote_choiced_'.$id.'" value="0"/>';
		$vote_form .= '<UL style="margin:0px;">';

		foreach ( $items AS $key=>$item )
		{
			$choice = $key+1;
			$r = intval(@$vote_result[$choice]['total']);
			$vote_form .=<<<_LI_
<li style="list-style:none; margin:5px 0;">
	<input onclick="$('vote_choiced_$id').value=$choice;" id="vote_choice_${id}_${choice}" type="radio" name="choice" value="$choice"/>
	<label for="vote_choice_${id}_${choice}">$item</label>［${r}票］
</li>
_LI_;
		}

		$vote_form .= '</UL>';
		$vote_form .= '<input class="submitbutton" onclick="return ($(\'vote_choiced_'.$id.'\').value > 0);" type="submit" value="投一票" '.$enabled.'/>';
		$vote_form .= '</FORM>';

		return $vote_form;
	}
}
?>
