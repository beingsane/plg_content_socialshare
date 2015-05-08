<?php defined('_JEXEC') or die;

if( !defined('DS') ) {
	define('DS', DIRECTORY_SEPARATOR);
}

class PlgContentSocialShare extends JPlugin {

	protected $autoloadLanguage = true;
	
	public function onContentBeforeDisplay( $context, &$row, &$params, $page = 0 ) {
		if( $this->params->get( 'position', 'onContentBeforeDisplay' ) == 'onContentBeforeDisplay' ) {
			return $this->getShares( $row );
		}
	}
	
	public function onContentAfterDisplay( $context, &$row, &$params, $page = 0 ) {
		if( $this->params->get( 'position', 'onContentBeforeDisplay' ) == 'onContentAfterDisplay' ) {
			return $this->getShares( $row );
		}
	}
	
	protected function getShares( $row ){
	
        JHtml::_('bootstrap.framework');
		$app 	= JFactory::getApplication();
		$doc	= JFactory::getDocument();
		$user	= JFactory::getUser();
		$uri	= JFactory::getURI();
		
		$twittervia	= $this->params->get('twitter-via');
		$theme		= $this->params->get('theme', 'basic');
		$view		= $app->input->getCmd('view');
		$views		= $this->params->get('views');
		$html		= '';
		
		$serviceArray = '';
	
		if( !$user->id AND $app->getName() != 'site' ) {
			return false;
		}
	
		if( !in_array($view, $views) AND count($views) != 0 ) {
			return false;
		}
		
		/*
		$html .= '<pre>';
		$html .= count($views) . '<br>';
		$html .= print_r($views, true);
		$html .= '</pre>';
		//*/
		
		
		if( !defined('SOCIALSHARE_LOADED') ) {
			$doc->addStylesheet('plugins/content/socialshare/assets/socialshare/themes/' . $theme . '/css/styles.min.css');
			$doc->addScriptDeclaration($this->loadAjax());
			require_once(JPATH_PLUGINS . DS . 'content' . DS . 'socialshare' . DS . 'assets' . DS . 'socialshare' . DS . 'library' . DS . 'shares.php');
			$socialshares = new SocialShares();
			define('SOCIALSHARE_LOADED', true);
		}
		
		$url = $uri->toString( array ('scheme', 'host', 'port' ) ) . JRoute::_( ContentHelperRoute::getArticleRoute( $row->slug, $row->catid ) );
		
		$html .= '<ul class="social-shares">';
		
		foreach( $socialshares->services as $service => $options ) {
			if( $this->params->get( $service ) ) {
				$serviceArray[$service] = $options;
			}
		}
		
		foreach( $serviceArray as $service => $options ) {
		
			$params = '';
			
			if( $service == 'twitter' AND !empty($twittervia) ) {
				$params = '&amp;via=' . $twittervia;
			}
		
			$html .= '<li class="' . $service . '">';
			$html .= '<a href="' . $socialshares->getUrlToShare($service, $url, $row->title) . $params . '" data-url="' . $url . '" data-service="' . $service .'">';
			$html .= '<span class="service">' . $options['name'] . '</span>';
			$html .= '<span class="count loading">&nbsp;</span>';
			$html .= '</a>';
			$html .= '</li>';
			
			unset($params);
		}
		$html .= '</ul>';

		return $html;
	}
	
	protected function loadAjax() {
		return "		jQuery(document).ready(function (){
			jQuery('.social-shares > li').each(function(i, e){
				var a		= $(this).children('a');
				var url		= a.data('url');
				var service	= a.data('service');
				
				a.on('click', function(e){
					window.open( jQuery(this).attr('href'), service, \"width=500, height=580, left=50, top=50\" );
					e.preventDefault();
				});
				
				jQuery.ajax({
					type: 'POST',
					url: '" . JURI::root() . "plugins/content/socialshare/assets/socialshare/ajax.php',
					data: {
						url: url,
						service: service
					},
					success: function(data){
						a.children('.count').removeClass('loading');
						a.children('.count').text(data);
					},
					error: function(jqXHR, textStatus, errorThrown){
						a.children('.count').addClass('error').removeClass('loading');
					}
				});
			});
		});";
	}
}