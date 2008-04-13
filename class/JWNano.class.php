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
	
	static private function FormatVote( $id, $status )
	{
		if ( false == ($vote_items = JWSns::ParseVoteItem($status)) )
			return $status;

		$vote_result = JWNanoVote::DoVoteInfo( $id );

		$status = preg_replace( '/\s*{([^\{\}]+)\}\s*/iU', '', $status );

		$vote_form = '<FORM style="padding:10px; margin:5px; border:1px dashed #CCC;" ACTION="/wo/nano/vote" method="POST" id="vote_form_'.$id.'">';
		$vote_form .= '<lable><strong>'.$status.'</strong></lable>';
		$vote_form .= '<input type="hidden" name="id" value="'.$id.'"/>';
		$vote_form .= '<UL style="margin:0px;">';

		$rand_choice = rand(0,count($vote_items));
		foreach ( $vote_items AS $key=>$item )
		{
			$r = intval(@$vote_result[$key+1]['total']);
			$checked = ($key == $rand_choice) ? "checked" : null;
			$vote_form .= '<li style="list-style:none; margin:5px 0;"><input type="radio" name="choice" '.$checked.' value="'.($key+1).'"/>'.$item.'［'.$r.'票］</li>';
		}

		$vote_form .= '</UL>';
		$vote_form .= '<input class="submitbutton" type="submit" value="投一票"/>';
		$vote_form .= '</FORM>';

		return $vote_form;
	}
}
?>
