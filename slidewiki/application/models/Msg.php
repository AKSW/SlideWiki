<?php

class Msg extends Model {
	public $id;
	public $sender_id;
	public $receiver_id;
	public $title;
	public $content;
	public $msg_type="pm";
	public $date;
	
	private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
	
	public function save() {
		$this->initConnection ();
		$this->id = $this->dbInsert ( 'message', array ('sender_id' => $this->sender_id, 'receiver_id' => $this->receiver_id, 'title' => htmlentities ( $this->title ), 'content' => htmlentities ( $this->content ),'msg_type'=>$this->msg_type ) );
	}
	public function delete() {
		$this->initConnection ();
		$this->dbQuery ( 'DELETE FROM message WHERE  id=:id', array ('id' => $this->id ) );
	}
	public function send() {
		$sender = new User ();
		$sender->createFromID ( $this->sender_id );
		$receiver = new User ();
		$receiver->createFromID ( $this->receiver_id );
		$receiver_email=$receiver->getEmail();
		if($this->msg_type=='comment'){
			$tmp='comment';
		}else{
			$tmp='message';
		}
		$msg_subject = 'New '.$tmp.' from ' . $sender->username;
		$msg_body = '
		<table width="400" border="0" cellspacing="1" cellpadding="1" style="width:500px;">
		  <tr>
		    <td  rowspan="3" align="right" valign="top"><img src="http://slidewiki.org/?url=ajax/getAvatarSrc&id='.$this->sender_id.'" align="top"></td>
		    <td><a href="http://slidewiki.org/user/'.$this->sender_id.'">'.$sender->username.'</a></td>
		    <td align="right">'.date('m/d/Y h:i:s a', time()).' </td>
		  </tr>
		  <tr>
		    <td></td>
		    <td align="left"><b>'.$this->title.'</b></td>
		  </tr>
		  <tr>
		    <td></td>
		    <td align="left">'.nl2br($this->content).'</td>
		  </tr>
		</table><hr/>
		This message was sent to '.$receiver_email.'. If you don\'t want to receive these emails from SlideWiki in the future, please <a href="http://slidewiki.org">unsubscribe</a>.
		<br/>
		SlideWiki: <a href="http://slidewiki.org">http://slidewiki.org</a>	
		';	
		$headers = array ();
		$headers [] = "MIME-Version: 1.0";
		$headers [] = "Content-type: text/html; charset=utf-8";
		$headers [] = "From: SlideWiki <admin@slidewiki.org>";
		if (! empty ( $this->content )) {
			if (mail ( $receiver_email, $msg_subject, $msg_body, implode("\r\n", $headers) )) {
				return 1;
			} else {
				return 0;
			}
		}
		//echo $msg_body;
	}

}
