<?php

/*
 * Name: PHPClassesFinder
 * 
 * Parses any php file in a directory and creates a list
 * with all the classes and interfaces found in those files
 * 
 * Design to be used for autoloader creation scripts.
 * 
 * Uses the php tokenizer instead of regex which means no problems
 * with any kind of php code.
 * 
 */

class PHPClassesFinder
{
	protected $cntClasses;
	protected $cntInterfaces;
	protected $cntFiles;
	
	protected $findings;
	
	protected $file_extensions = array(
		'php'=>1,
	);
	protected $match='';
	protected $nmatch='';
	
	protected $lastParsedDir;
	
	// utility function because SplFileInfo::getExtension was
	// added in PHP >= 5.3.6
	protected function getExtension(SplFileInfo $p) {
		return array_pop(explode('.',$p->getFilename()));
	}
	
	/*
	 * If we have a match regex check the path against it first, if
	 * it doesn't match return FALSE.
	 * Else check against the list of file extensions.
	 * 
	 * After it passes the above match tests check that it doesn't
	 * match the negative match regex (if we have one).
	 * 
	 * Thus one can select files and also deselect files.
	 * 
	 * name: shouldParse
	 * @param $path The path to check if it should be parsed
	 * @return bool
	 * */
	protected function shouldParse($path) {
		if (strlen($this->match)>2)
		{
			if (!preg_match($this->match,$path))
				return false;
		}
		else
		{
			if (!isset($this->file_extensions[$this->getExtension($path)]))
				return false;
		}
		if (strlen($this->nmatch)>2)
		{
			if (preg_match($this->nmatch,$path))
				return false;
		}
		return true;
	}
	
	/*
	 * Parse the directory for classes and interfaces
	 * 
	 * name: parseDir
	 * @param $d the path of the directory
	 * @return void
	 */
	public function parseDir($d) {
		$this->lastParsedDir = $d;
		$this->findings = array();
		$this->cntClasses = $this->cntInterfaces = $this->cntFiles = 0;
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($d),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($iterator as $path)
		{
			if (!$path->isDir() && $this->shouldParse($path))
			{
				$this->parseFile($path);
			}
		}
	}
	
	/*
	 * Parse a file for classes and interfaces
	 * Each finding is put in the $findings member variable
	 * in the following manner
	 * 
	 * $this->findings[$file_path] = array( full_classname1,
	 * 									full_classname2, .... );
	 * 
	 * The very simple parsing algorithm is the following
	 * if current token is class capture the following string token
	 * if current token is interface capture the following string token
	 * if current token is namespace capture until ';'
	 * 
	 * name: parseFile
	 * @param SplFileInfo $f the file to parse
	 * @return void
	 */
	public function parseFile(SplFileInfo $f) {
		$this->cntFiles ++;
		$tokens = token_get_all(file_get_contents($f->getPathname()));
		$namespace = '';
		$what = 0;
		$findings = array();
		foreach ($tokens as $token)
		{
			switch ($token[0])
			{
				case T_CLASS:
					$what = 1;
					break;
				case T_INTERFACE:
					$what = 2;
					break;
				case T_NAMESPACE:
					$namespace = '';
					$what = 3;
					break;
				default:
					switch ($what)
					{
						case 0:
							break;
						case 1:
							if ($token[0] == T_STRING)
							{
								$findings[] = $namespace.$token[1];
								$what = 0;
								$this->cntClasses ++;
							}
							break;
						case 2:
							if ($token[0] == T_STRING)
							{
								$findings[] = $namespace.$token[1];
								$what = 0;
								$this->cntInterfaces ++;
							}
							break;
						case 3:
							if ($token[0] == ';')
							{
								$namespace .= '\\';
								$what = 0;
							}
							else if ($token[0] == T_STRING || $token[0] == T_NS_SEPARATOR)
							{
								$namespace .= $token[1];
							}
							break;
						default:
							break;
					}
					break;
			}
		}
		$this->findings[$f->getPathname()] = $findings;
	}

	// set the permitted extensions
	// only files matching those extensions are processed
	public  function setPermittedExtensions(array $exts) {
		$this->file_extensions = array_combine(array_values($exts),range(1,count($exts)));
	}
	
	// set the match expression
	// if set only files that match are processed
	public function setMatchExpression($match) {
		$this->match = "#$match#";
	}
	
	// set the negative match
	// if set only files that DO NOT match are processed
	public function setNegativeMatchExpression($nmatch) {
		$this->nmatch = "#$nmatch#";
	}

	// Simple getter functions for the parsed data or stats
	public function getFindings() {
		return $this->findings;
	}
	public function getClassesCnt() {
		return $this->cntClasses;
	}
	public function getInterfacesCnt() {
		return $this->cntInterfaces;
	}
	public function getFileCnt() {
		return $this->cntFiles;
	}
	public function getLastParsedDirectory() {
		return $this->lastParsedDir;
	}
}

?>
