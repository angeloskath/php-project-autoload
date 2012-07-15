## PHP automatic autoloader creation

1. No convention imposed
2. Project watching script for complete auto-pilot (uses inotify)
3. Uses php tokenizer (no regexes) so it will not break in the future or by code that it has not been tested on

### Usage

    $> ./clsinc.php -p path/to/my/project
		... outputs only information ...
	
	$> ./clsinc.php -p path/to/my/project -t ./simple_autoloader.php
		... outputs to standard output ....
	
	$> ./clsinc.php -p path/to/my/project -t ./simple_autoloader.php -o path/to/file

	$> ./clsinc.php -p path/to/my/project -t ./simple_autoloader.php -v
		... prints info to stdout ...

### Usage with projectwatch script

	$> ./projectwatch.py
		... defaults to calling
			./clsinc.php -p . -t ./simple_autoloader.php -o ./autoloader.php -v
		whenever a change occurs ...

### Suggested usage

Add a _.bashrc_ alias or a path to the projectwatch.py script then cd to the path you want to keep your autoloader.php . Run projectwatch.py with the project's base directory as a parameter or none if it is the current directory .

### Run the example

	$> cd example
	$> ../projectwatch.py
	$> php index.php

Leave it running and go add new classes. It will reconstruct the autoloader.php and you can immediately use them in the index.php .
