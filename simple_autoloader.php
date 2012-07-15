<?php echo '<?php' ?>

/* * * * * * * * * * * * * * * * *
 * Automatically generated file  *
 * ! Edit with caution           *
 * * * * * * * * * * * * * * * * */
 
class Autoloader {
	protected static $files = array(
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
	
	public static function loadClass($name) {
		if (isset(self::$files[$name]))
		{
			include(__DIR__.self::$files[$name]);
		}
		else
		{
			throw new Exception("Class not found $name");
		}
	}
}

spl_autoload_register('Autoloader::loadClass');

?>
