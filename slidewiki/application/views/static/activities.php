<?php 
		foreach ( $stream->activities as $i => $s ) {
			switch ($s->type) {
				case 'created_deck' :
					$s->type = 'created deck';
					break;
				case 'translated_deck_from' :
					$s->type = 'translated deck';
					break;
				case 'commented_deck_revision' :
					$s->type = 'commented deck';
					break;
				case 'followed_deck' :
					$s->type = 'started following deck';
					break;
				case 'translated_deck' :
					$s->type = 'translated deck';
					break;
				case 'created_deck_revision' :
					$s->type = 'created deck revision';
					break;
			}
			echo '<li><a href="http://slidewiki.org/user/'.$s->subject->id.'">'.$s->subject->username . '</a> ' . $s->type . ' <a href="http://slidewiki.org/deck/'.$s->object->id. '_' . $s->object->slug_title.'">'.$s->object->title.'</a> ('.$s->timestamp.')</li>';
		}
?>