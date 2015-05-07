<?php
	if( isset($_POST['service']) AND isset($_POST['url']) ) {
		require_once('socialshares.php');
		$service	= strip_tags($_POST['service']);
		$url		= strip_tags($_POST['url']);
		$socialshares = new SocialShares();
		echo $socialshares->getSharesByService($service, $url);
	}
?>
