<?php echo '<?php' ?>

/* * * * * * * * * * * * * * * * *
 * Automatically generated file  *
 * ! Edit with caution           *
 * * * * * * * * * * * * * * * * */
 
 spl_autoload_register( function ($name) {
	 static $files = array(
<?php
	$baseDir = strlen($finder->getLastParsedDirectory());
	foreach ($findings as $file=>$classes) {
		foreach ($classes as $class) {
?>
		"<?php echo $class ?>"=>"<?php echo substr($file,$baseDir) ?>",
<?php
		}
	}
?>
	);
	
	if (isset($files[$name]))
	{
		include(__DIR__.$files[$name]);
	}
	else
	{
		throw new Exception("Class not found $name");
	}
 });

?>
