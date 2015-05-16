<?php defined('_JEXEC') or die;

$id		= 'social-shares-' . $newServiceArray['options']['article-id'] . ' ' .$newServiceArray['options']['css-id'];
$class	= 'social-shares ' . $newServiceArray['options']['css-class'];
?>

<ul id="<?php echo trim($id); ?>" class="<?php echo trim($class); ?>" data-url="<?php echo $newServiceArray['options']['article-url']; ?>">
	
	<?php foreach( $newServiceArray['services'] as $name => $service ) : ?>
	<li class="<?php echo $name; ?>" data-service="<?php echo $name; ?>">
		
		<a href="<?php echo $service['share-url'] ?>" class="<?php echo $name; ?>">
			<span class="service"><?php echo $service['name'] ?></span>
			<span class="count">&nbsp;</span>
		</a>
		
	</li>
	<?php endforeach; ?>
	
</ul>