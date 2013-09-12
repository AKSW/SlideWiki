<?php
	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', dirname(dirname(__FILE__)));

	// include bootstrap
	require_once (ROOT . DS . 'application' . DS . 'config' . DS . 'config.php');
	
	class NotifyHourly extends User {
		
		function __construct() {
                    // extracting notification options for this script
                    // if you want other cronjob script use filename like
                    // notify_%notification_interval%.php where %notification_interval%
                    // is an interval name (we have 4 parameters for this field in DB now:
                    // off, hourly, daily, weekly
                    // name is case sensitive!
                    $filename = explode(DS, __FILE__);
                    $filename = end($filename);
                    $filename = explode('.', $filename);
                    $filename = $filename[0];
                    $notification_interval = substr($filename, 7);

                    $this->initConnection ();

                    //get the list of users with hourly notifications
                    $users = $this->dbGetCol('SELECT users.id FROM users INNER JOIN preference ON users.id = preference.user_id WHERE preference.preference="notification_interval" AND preference.value="'. $notification_interval . '"');
                    foreach ($users as $user_id) {
                        $stream = new Stream();
                        $stream->getUserNotifications($user_id, $notification_interval);
                        $message = '';
                        $message = $this->messageFromStream($user_id, $stream);
                        if ($message){
                            $message = '<div style="width:450px; border:1px solid #000;"><div style="padding:5px;color:white; background-color:#345270">SlideWiki news stream for the latest hour:</div><div style="padding:5px;">'. $message . '</div></div>';
                            $this->sendEmailTo($user_id, $message);
                        } 
                    }
                }
                function messageFromStream($user_id, $stream){
                    $message = false;
                    foreach($stream->activities as $activity){                        
                        if ($activity->subject->id != $user_id){                            
                            $object = $activity->object;
                            $subject = $activity->subject;
                            
                            switch ($activity->type){
                                case 'created_deck_revision' :
                                    $deck_revision_id = $object->id;
                                    $title = $object->title;
                                    $type_text = 'created new revision for ';
                                    $object_type = 'deck';
                                    break;
                                case 'created_deck' :
                                    $deck_revision_id = $object->id;
                                    $title = $object->title;
                                    $type_text = 'created ';
                                    $object_type = 'deck';
                                    break;
                                case 'followed_deck' :
                                    $deck_revision_id = $object->id;
                                    $title = $object->title;
                                    $type_text = 'started following ';
                                    $object_type = 'deck';
                                    break;
                                case 'translated_deck' :
                                    $deck_revision_id = $object->id;
                                    $title = $object->title;
                                    $type_text = 'translated ';
                                    $object_type = 'deck';
                                    break;
                                case 'translated_deck_from' :
                                    $deck_revision_id = $object->id;
                                    $title = $object->title;
                                    $type_text = 'translated ';
                                    $object_type = 'deck';
                                    break;
                                case 'commented_deck_revision' :
                                    $deck_revision_id = $object->id;
                                    $title = $object->title;
                                    $type_text = 'commented ';
                                    $object_type = 'deck';
                                    break; 
                                case 'answered_test' :
                                    $deck_revision_id = $object->id;
                                    $title = $object->title;
                                    $type_text = 'answered test for ';
                                    $object_type = 'deck';
                                    break;
                                case 'created_slide_revision' :
                                    $usage = $object->getUsage();
                                    $deck_revision_id = $usage[0]->id;
                                    $position = $object->position;
                                    $title = $object->title;
                                    $type_text = 'created new revision for ';
                                    $object_type = 'slide';
                                    break;
                                case 'translated_slide_from' :
                                    $usage = $object->getUsage();
                                    $deck_revision_id = $usage[0]->id;
                                    $position = $object->position;
                                    $title = $object->title;
                                    $type_text = 'translated ';
                                    $object_type = 'slide';
                                    break;
                                case 'followed_slide' :
                                    $usage = $object->getUsage();
                                    $deck_revision_id = $usage[0]->id;
                                    $position = $object->position;
                                    $title = $object->title;
                                    $type_text = 'started following ';
                                    $object_type = 'slide';
                                    break;
                                case 'created_slide_revision' :
                                    $usage = $object->getUsage();
                                    $deck_revision_id = $usage[0]->id;
                                    $position = $object->position;
                                    $title = $object->title;
                                    $type_text = 'created new revision for ';
                                    $object_type = 'slide';
                                    break;
                                case 'created_slide' :
                                    $usage = $object->getUsage();
                                    $deck_revision_id = $usage[0]->id;
                                    $position = $object->position;
                                    $title = $object->title;
                                    $type_text = 'created ';
                                    $object_type = 'slide';
                                    break;
                                case 'translated_slide':
                                    $usage = $object->getUsage();
                                    $deck_revision_id = $usage[0]->id;
                                    $position = $object->position;
                                    $title = $object->title;
                                    $type_text = 'translated ';
                                    $object_type = 'slide';
                                    break;
                                case 'translated_slide_revision' :
                                    $usage = $object->getUsage();
                                    $deck_revision_id = $usage[0]->id;
                                    $position = $object->position;
                                    $title = $object->title;
                                    $type_text = 'revised the translation of ';
                                    $object_type = 'slide';
                                    break;
                                case 'commented_slide_revision' :
                                    $usage = $object->getUsage();
                                    $deck_revision_id = $usage[0]->id;
                                    $position = $object->position;
                                    $title = $object->title;
                                    $type_text = 'commented ';
                                    $object_type = 'slide';
                                    break;
                                case 'created_question_revision' :
                                    $usage = $activity->text->getUsage();
                                    $deck_revision_id = $usage[0]->id;
                                    $position = $activity->text->position;
                                    $title = $activity->text->title;
                                    $type_text = 'update question for ';
                                    $object_type = 'slide';
                                    break;
                                case 'created_question' :
                                    $usage = $activity->text->getUsage();
                                    $deck_revision_id = $usage[0]->id;
                                    $position = $activity->text->position;
                                    $title = $activity->text->title;
                                    $type_text = 'created question for ';
                                    $object_type = 'slide';
                                    break;
                                case 'followed_user' :
                                    $type_text = 'started following ';
                                    $object_type = 'user';
                                default : 
                                    break;
                            }

                            // compose the e-mail
                            //link to the user who made the changes
                            $message .= '<a target = "_blank" href="' . BASE_PATH . 'user/' . $activity->subject->id .'">' . $activity->subject->username . '</a> ';
                            //type of the change
                            $message .= $type_text;                            
                            //link to the changed object
                            switch ($object_type) {
                                case 'deck':
                                    //TODO: slug_title
                                    $message .= "<a target='_blank' href='" . BASE_PATH . "deck/". $deck_revision_id;
                                    $message .= "'>" . $title . "</a> "  . "deck" . "<br>";
                                    break;
                                case 'slide' :
                                    $message .= "<a target='_blank' href='" . BASE_PATH . "slide/". $object->id;
                                    $message .= "'>" . $title . "</a> "  . "slide" . "<br>";
                                    break;
                                case 'user' :
                                    $message .= "<a target='_blank' href='" . BASE_PATH . "user/". $object->id;
                                    $message .= "'>" . $object->username . "</a> "  . "<br>";
                                    break;
                                default:
                                    break;                                   
                            }
                        }
                    }
                    return $message;
                }
		function composeEmailFor($user_id, $notification_table) {
			$message = array ();
			foreach($notification_table as $row) {
				if($row['user_id'] == $user_id) {
					array_push($message, $row['message']);
				}
			}
			$emailMessage = implode('<br/>',$message);
			return $emailMessage;
		}
		
		function extractUsers($notification_table) {
			$users = array();			
			foreach($notification_table as $row) {
				$already_added = false;
				foreach($users as $user) {
					if($user == $row['user_id']) {
						$already_added = true;
					}
				}
				
				if(!$already_added) {
					$users[$row['user_id']] = array();
				}
			}
			return $users;
		}
		
		function readNotificationsTable() {
			$notification_table = $this->dbQuery ( 'SELECT * FROM notifications');
			return $notification_table;
		}
		
		function emptyNotificationsFor($user_id) {
			$this->dbQuery ( 'DELETE FROM notifications WHERE user_id=:user_id', array('user_id' => $user_id));
		}
		
		function getNotificationIntervalFor($users) {
			foreach($users as $user_id => $user_params) {
				$user = new User();
				$user->createFromID($user_id);
				$users[$user_id]['notification_interval'] = $user->getNotificationInterval();
			}
			return $users;
		}
		
		private function initConnection() {
			// connect to db
			if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
				die ( "Could not connect to db" );
		}
		
		function getUserEmail($user_id) {
			$user_email = $this->dbGetOne ( 'SELECT email FROM users WHERE id=:user_id', array('user_id' => $user_id));
			return $user_email;
		}
		
		function sendEmailTo($user_id, $email_message) {
			// need to check the mail function
			// got "SMTP server response: 530 SMTP authentication is required" message
			// trying to send message to google mail
			
			$to = $this->getUserEmail($user_id);
			$subject = "Slidewiki notification (do not reply)";
			$txt = $email_message;
                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
			$headers .= "From: admin@slidewiki.org" . "\r\n";
			
			mail($to,$subject,$txt,$headers);
		}
	}	
	
	function __autoload($className) {
		if (file_exists(ROOT . DS . 'application' . DS . 'library' . DS . $className . '.class.php')) {
			require_once(ROOT . DS . 'application' . DS . 'library' . DS . $className . '.class.php');
		} else if (file_exists(ROOT . DS . 'application' . DS . 'controllers' . DS . $className . '.php')) {
			require_once(ROOT . DS . 'application' . DS . 'controllers' . DS . $className . '.php');
		} else if (file_exists(ROOT . DS . 'application' . DS . 'models' . DS . $className . '.php')) {
			require_once(ROOT . DS . 'application' . DS . 'models' . DS . $className . '.php');
		} else {
			if ($className != strtolower($className))
			{
				__autoload(strtolower($className));
			}
			else
			{
				/* Error Generation Code Here */
			}
		}
	}
	
	new NotifyHourly();
?>
