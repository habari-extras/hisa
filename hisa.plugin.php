<?php

class Hisa extends Plugin
{
	
	public function action_update_check()
	{
		Update::add( $this->info->name, '593d17c5-c36f-4f93-8cb8-43832ba0844b', $this->info->version );
	}
	
	public function action_form_publish( $form, $post )
	{
		if ( 0 == $post->id ) {
			return;
		}
		
		
		$url = $post->permalink;

		$sharing = $form->publish_controls->append( 'fieldset', 'share', _t( 'Share', 'hisa' ) );
		$sharing->append( 'text', 'share_url', 'null:null', _t( 'Share URL', 'hisa' ), 'tabcontrol_text' );
		$sharing->share_url->value = $url;
		
		// $settings->append( 'checkboxes', 'tokens', 'null:null', _t( 'Membership', 'membership' ), $tokens );
		// $settings->class = 'container formcontrol transparent';

		// If this is an existing post, see if it has tokens already
		// if ( 0 != $post->id ) {
		// 	$form->tokens->value = $post->has_tokens( array_keys( $tokens ) );
		// }
	}
	
}

?>