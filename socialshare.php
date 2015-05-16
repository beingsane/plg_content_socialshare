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
		$cssId		= $this->params->get('css_id') ? $this->params->get('css_id') : '';
		$cssClass	= $this->params->get('css_class') ? $this->params->get('css_class') : '';
		$html		= '';
		$newServiceArray = '';
		
		$bitlyState			= (boolean) $this->params->get('bitly-state');
		$bitlyDomain		= (string) $this->params->get('bitly-domain');
		$bitlyAccessToken	= (string) $this->params->get('bitly-access-token');
		
		if( !$user->id AND $app->getName() != 'site' ) {
			return false;
		}
		
		if( !in_array($view, $views) ) {
			return false;
		}
		
		/*
		$html .= '<pre>';
		$html .= count($views) . '<br>';
		$html .= print_r($view, true) . '<br>';
		$html .= print_r($views, true);
		$html .= '</pre>';
		//*/
		
		
		if( !defined('SOCIALSHARE_LOADED') ) {
			$doc->addStylesheet('plugins/content/socialshare/assets/socialshare/themes/' . $theme . '/css/styles.min.css');
			$doc->addScriptDeclaration($this->loadAjax());
			require_once(JPATH_PLUGINS . DS . 'content' . DS . 'socialshare' . DS . 'assets' . DS . 'socialshare' . DS . 'library' . DS . 'shares.php');
			define('SOCIALSHARE_LOADED', true);
		}
		
		$longUrl = $uri->toString( array ('scheme', 'host', 'port' ) ) . JRoute::_( ContentHelperRoute::getArticleRoute( $row->slug, $row->catid ) );

		// TODO: Error-Handling. If you test oin localhost, bitly give an 500-Error :-/
		if( $bitlyState AND !empty($bitlyDomain) AND !empty($bitlyAccessToken) ) {
			$shortUrl = trim( file_get_contents('https://api-ssl.bitly.com/v3/shorten?access_token=' . $bitlyAccessToken . '&longUrl=' . urlencode($longUrl) . '&domain=' . $bitlyDomain . '&format=txt') );
		}
		
		if( !empty($shortUrl) ) {
			$url = $shortUrl;
		} else {
			$url = $longUrl;
		}
		
		$layoutPath = JPluginHelper::getLayoutPath('content', 'socialshare');
		
		$socialshares = new SocialShares();
		
		foreach( $socialshares->services as $service => $options ) {
			if( $this->params->get( $service ) ) {
				
				$params = '';
				if( $service == 'twitter' AND !empty($twittervia) ) {
					$params = '&amp;via=' . $twittervia;
				}
				
				$newServiceArray['options']['css-id'] = $cssId;
				$newServiceArray['options']['css-class'] = $cssClass;
				$newServiceArray['options']['article-id'] = $app->input->getCmd('id');
				$newServiceArray['options']['article-url'] = $longUrl;
				$newServiceArray['services'][$service] = $options;
				$newServiceArray['services'][$service]['share-url'] = $socialshares->getUrlToShare($service, $url, $row->title) . $params;
				
			}
		}
		
		ob_start();
		
		require_once($layoutPath);
		
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
	
	protected function loadAjax() {
		
		return "		jQuery(document).ready(function (){
			var shareUl	= jQuery('.social-shares');
			var shareLi	= shareUl.children('li');
			var shareA	= shareLi.children('a');
			
			shareLi.addClass('loading');
				
			shareA.on('click', function(e){
				window.open( jQuery(this).attr('href'), jQuery(this).parent('li').data('service'), \"width=500, height=580, left=50, top=50\" );
				e.preventDefault();
			});
			
			shareLi.each(function(i, e){
				
				var li	= jQuery(this);
				var a	= li.children('a');
				
				jQuery.ajax({
					type: 'POST',
					url: '" . JURI::root() . "plugins/content/socialshare/assets/socialshare/ajax.php',
					data: {
						url: shareUl.data('url'),
						service: li.data('service')
					},
					success: function(data){
						li.removeClass('loading');
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