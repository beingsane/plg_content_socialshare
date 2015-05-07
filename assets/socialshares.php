<?php
/*
** Written by Martin Skroch | novusidea.de (2013-03-18)
**
** http://cube3x.com/2013/01/get-social-share-counts-a-complete-guide/
** https://gist.github.com/jonathanmoore/2640302
** http://desizntech.info/2011/04/popular-social-media-share-link-snippets/
** http://4rapiddev.com/internet/share-link-url-of-facebook-twitter-google-plus-stumbleupon-reddit-digg-and-tumblr/
** http://sharedcount.com/documentation.php
** http://blog.programmableweb.com/2012/02/01/81-news-apis-digg-fanfeedr-and-clearforest/
** http://stackoverflow.com/questions/10591424/how-can-i-create-a-custom-stumbleupon-button
*/
class SocialShares {

	public $services = array(
		'twitter'	=> array(
			'name'		=> 'Twitter',
			'share-url'	=> 'https://twitter.com/share?url={url}&amp;text={txt}',
			'api-url'	=> 'https://cdn.api.twitter.com/1/urls/count.json?url={url}'
		),
		'facebook'	=> array(
			'name'		=> 'Facebook',
			'share-url'	=> 'https://www.facebook.com/sharer.php?u={url}&amp;t={txt}',
			'api-url'	=> 'https://graph.facebook.com/?id={url}'
		),
		'google'	=> array(
			'name'		=> 'Google+',
			'share-url'	=> 'https://plus.google.com/share?url={url}&amp;title={txt}',
			'api-url'	=> 'https://clients6.google.com/rpc?key=AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ',
			'api-post'	=> '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"{url}","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]'
		),
		'linkedin'	=> array(
			'name'		=> 'LinkedIn',
			'share-url'	=> 'https://www.linkedin.com/cws/share?u={url}&t={txt}',
			'api-url'	=> 'https://www.linkedin.com/countserv/count/share?url={url}&amp;format=json'
		),
		'pinterest'	=> array(
			'name'		=> 'Pinterest',
			'share-url'	=> 'https://pinterest.com/pin/create/button/?url={url}&amp;media={img}&amp;description={txt}',
			'api-url'	=> 'https://api.pinterest.com/v1/urls/count.json?url={url}'
		),
		'stumbleupon'	=> array(
			'name'		=> 'StumbleUpon',
			'share-url'	=> 'https://www.stumbleupon.com/badge/?url={url}',
			'api-url'	=> 'https://www.stumbleupon.com/services/1.01/badge.getinfo?url={url}'
		),
		'delicious'	=> array(
			'name'		=> 'Delicious',
			'share-url'	=> 'https://delicious.com/post?url={url}&amp;title={txt}',
			'api-url'	=> 'https://feeds.delicious.com/v2/json/urlinfo/data?url={url}'
		),
		/*'xing'	=> array(
			'share-url'	=> 'https://www.xing.com/social_plugins/share?url={url}&wtmc=XING;&sc_p=xing-share',
			'api-url'	=> 'https://api.xing.com/'
		)*/
	);
	
	public function getJsonByService($service = '', $url = '') {
		
		if( !function_exists('curl_init') ){
			echo 'Sorry, cURL is not installed!';
			return false;
		}
		
		if( !array_key_exists( $service, $this->services ) ) {
			echo 'Sorry, the service ' . $service . ' is not supported.';
			return false;
		}
		
		if($jsonUrl = preg_replace('#\{url\}#i', $url, $this->services[$service]['api-url']) ) {
		
			$ch		= curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $jsonUrl);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if( isset($this->services[$service]['api-post']) && $post = preg_replace('#\{url\}#i', $url, $this->services[$service]['api-post']) ) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
			
			$json	= curl_exec($ch);
			
			curl_close($ch);
		
			return $json;
		}

	}
	
	public function getSharesByService($service, $url) {
		
		if( $service == 'pinterest' ) {
			$json = json_decode( preg_replace('|^receiveCount\((.*?)\)$|i', "\$1", $this->getJsonByService($service, $url)), true );
		} else {
			$json = json_decode( $this->getJsonByService($service, $url), true );
		}
		
		switch( $service ){
			case 'twitter':
			case 'linkedin':
			case 'pinterest':
				$count = isset($json['count']) ? $json['count'] : false;
				break;
			case 'facebook':
				$count = isset($json['shares']) ? $json['shares'] : false;
				break;
			case 'delicious':
				$count = isset($json[0]['total_posts']) ? $json[0]['total_posts'] : false;
				break;
			case 'stumbleupon':
				$count = isset($json['result']['views']) ? $json['result']['views'] : false;
				break;
			case 'google':
				$count = isset($json[0]['result']['metadata']['globalCounts']['count']) ? $json[0]['result']['metadata']['globalCounts']['count'] : false;
				break;
		}
		
		$count = $count == '' ? 0 : $count;
		
		return $count;
		
	}
	
	public function getUrlToShare($service = '', $url = '', $txt = '', $img = ''){
		
		$newUrl = preg_replace('#\{url\}#i', rawurlencode($url), $this->services[$service]['share-url']);
		$newUrl = preg_replace('#\{txt\}#i', rawurlencode($txt), $newUrl);
		$newUrl = preg_replace('#\{img\}#i', rawurlencode($img), $newUrl);
		
		return $newUrl;
		
	}
	
	public function getLinkOfSharesFrom($service = '', $url = '', $txt = '', $img = '', $options = array()){
	
		if( is_array( $options['attributes'] ) ) {
			$attributes = '';
			foreach( $options['attributes'] as $key => $value ){
				$attributes .= ' ' . $key . '="' . $value . '"';
			}
		}
		
		return '<a href="' . $this->getUrlToShare($service, $url, $txt, $img) . '"' . $attributes . '>' . $this->getSharesByService($service, $url) . '</a>';
		
	}
	
	public function getTruncatedCount($count){
		
		if( $count > 0 && $count <= 1000 ){
			$newCount = $count;
		} else
		if( $count > 1000 && $count <= 1000000 ) {
			$newCount = '>' . number_format($count, 1, '.', ',');
		} else
		if( $count > 1000000 ){
			$newCount = '>' . number_format($count, 1, '.', ',');
		}
	
		return $newCount;
		
	}
	
}
?>