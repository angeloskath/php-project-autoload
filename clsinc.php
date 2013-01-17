#!/usr/bin/php
<?php

include("classesfinder.php");

function printUsage($name) {
	$usage =<<<USAGE
Usage: $name [-h/--help]
                    [-p/--projectdir PRJ] 
                    [-t/--template TPL]
                    [-o/--output OUT]
                    [-e/--extension EXT]
                    [-v]

Parse some php files and produce a list with all
the classes, interfaces to be ussed for creating
php autoloaders for projects

Options:
-h, --help          Produces this help
-p, --projectdir    The start directory of the
                    project to parse (defaults to . )
-t, --template      The template file to format the
                    list (Prints info if not given)
-o, --output        A file to save the output (defaults
                    to STDOUT)
-e, --extension     A comma separated list of extensions
                    to consider as php files (default is
                    just 'php')
-m, --match         A regular expression to match the file
                    against. If provided the extension list
                    is ignored
-n, --nmatch        A regular expression that the file should
                    not match. Useful for excluding whole
                    directories (like test dirs etc.)
-v                  Print info in STDERR if template
                    is given

USAGE;
echo $usage;
die;
}

function printInfo($out,PHPClassesFinder $finder, $dur) {
	fprintf($out,
			"Found:\t%d class(es)\n\t%d interface(s)\n\tin %d files\n\tin %.2fms\n",
			$finder->getClassesCnt(),
			$finder->getInterfacesCnt(),
			$finder->getFileCnt(),
			$dur * 1000
		);
}

// just to limit what the tpl sees
function template($tpl,$data) {
	extract($data);
	include($tpl);
}

$longopts = array(
	"projectdir:",
	"template::",
	"output::",
	"extension:",
	"match:",
	"nmatch:",
	"help"
);
$options = getopt("p:t:o:e:vh",$longopts);
if (isset($options['help']) || isset($options['h']))
{
	printUsage($argv[0]);
}
if (isset($options['p']))
{
	$options['projectdir'] = $options['p'];
}
if (isset($options['t']))
{
	$options['template'] = $options['t'];
}
if (isset($options['o']))
{
	$options['output'] = $options['o'];
}
if (isset($options['m']))
{
	$options['match'] = $options['m'];
}
if (isset($options['n']))
{
	$options['nmatch'] = $options['n'];
}

if (!isset($options['projectdir']) || $options['projectdir'] == '')
{
	$options['projectdir'] = '.';
}

if (is_dir($options['projectdir']))
{
	$finder = new PHPClassesFinder;
	if (isset($options['e'])) $options['extension'] = $options['e'];
	if (isset($options['extension']))
	{
		$finder->setPermittedExtensions(explode(',',$options['extension']));
	}
	if (isset($options['match']))
	{
		$finder->setMatchExpression($options['match']);
	}
	if (isset($options['nmatch']))
	{
		$finder->setNegativeMatchExpression($options['nmatch']);
	}
	$start = microtime(true);
	$finder->parseDir($options['projectdir']);
	$dur = microtime(true)-$start;
	if (isset($options['template']))
	{
		if (file_exists($options['template']))
		{
			ob_start();
			$findings = $finder->getFindings();
			template($options['template'], array(
				'findings'=>$findings,
				'finder'=>$finder
			));
			$c = ob_get_contents();
			ob_end_clean();
			if (isset($options['output']))
			{
				file_put_contents($options['output'],$c);
				printInfo(STDOUT,$finder,$dur);
			}
			else
			{
				echo $c;
				if (isset($options['v']))
				{
					printInfo(STDERR,$finder,$dur);
				}
			}
		}
	}
	else
	{
		printInfo(STDOUT,$finder,$dur);
	}
}

?>
