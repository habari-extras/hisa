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
		
	/**
	 * Gets the sharing URL for a post 
	 **/
	public function get_share_url( $post )
	{
		$key = $post->info->access_key;
		
		if( $key == false ) // we need to create the key now
		{
			$key = sha1(rand(0, 65535) . $post->id);
			$post->info->access_key = $key;
			$post->update();
		}
		
		$url = $post->permalink . '?auth=' . $key;
		
		return $url;
	}
	
	/**
	 * Checks if the proper credential has been supplied to access the current post
	 **/
	private function is_authorized( $post = null, $deny = false )
	{
		$auth = Controller::get_var( 'auth' );
		
		// if there's no auth key, deny authorization automatically
		if( $auth == null )
		{
			return false;
		}
		
		// if someone has an auth token but should be denied, mess them up
		if( $deny == true )
		{
			// Utils::redirect( Site::get_url() );
			exit;
			return false;
		}
		
		// we assume the authorization is fine until actually testing the post
		if( $post != null )
		{
			if( $auth != $post->info->access_key )
			{
				return false;
			}
		}
				
		return true;
	}
	
	/**
	 * Give users access to the token if they passed along the proper key 
	 **/
	public function filter_user_token_access( $accesses, $user_id, $token_id )
	{
		// Utils::debug( $accesses, $user_id, $token_id );
		
		if( $this->is_authorized() )
		{
			$bitmask = ACL::get_bitmask( 0 );
			$bitmask->read = true;
			
			$accesses[0] = $bitmask->value;
		}
		
		
		return $accesses;
	}
	
	/**
	 * Log this request in as a user if a key is present in the URL
	 * @param $user Comes in from Habari empty usually
	 * @return User The user to be identified as, if a key is present in the URL
	 */
	public function filter_user_identify($user)
	{
		// Utils::debug( $user );
		
		ACL::clear_caches();
		
		// $user = User::get_by_name( 'lipsum' );
		
		// if(isset($_GET['mkey'])) {
		// 	list($id, $key) = explode('_', $_GET['mkey']);
		// 	$key = $this->get_user_key($id);
		// 	if($_GET['mkey'] == $key) {
		// 		$user = User::get_by_id($id);
		// 	}
		// }
		return $user;
	}
	
	/**
	 * Remove the restriction 
	 **/
	public function filter_template_where_filters( $filters)
	{
		if( $this->is_authorized() )
		{
			unset( $filters['status'] );
			// Utils::debug( $filters, $filters['status'] );
		}
		
		return $filters;
	}
	
	/**
	 * A helper function to prevent access with Hisa
	 **/
	public function deny_access()
	{
		$this->is_authorized( null, true );
	}
	
	/**
	 * Run the actual check of the post authorization here 
	 **/
	public function action_template_header( $theme )
	{
		
		if( $theme->posts instanceof Posts )
		{
			// if someone is trying to sneak into multiple posts, kill their attempt
			$this->deny_access();
			return;
		}
		elseif( $theme->post instanceof Post )
		{
			if( !$this->is_authorized( $theme->post ) )
			{
				$this->deny_access();
			}
			return;
		}
		else
		{
			return;
		}
		
		// if( !$this->is_authorized( $theme->post ) )
	}
	
}

?>