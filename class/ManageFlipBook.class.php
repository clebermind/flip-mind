<?php

    /**
     * Generate the flipbook
     *
     * @since       1.0.0
     */

    // Exit if accessed directly
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

class ManageFlipBook
{
	
	/**
	 *
	 * a key sera usada para guardar como chave do pdf
	 * conseguir aumentar tamanho da imagem que está sendo gerada
	 *
	 */
	
	private $file = null;
	private $key = null;
	private $fileId = null;
	private $imagesDirectory = null;
	
	public function __construct($file=null)
	{
		if(!is_null($file)) {
			$this->setFile($file);
		}
	}
	
	public function setFile($file)
	{
		if (!is_file($file)) {
			throw new Exception('File didn\'t found:' . $file);
		}
		$this->file = pathinfo($file);
		
		if ($this->file['extension']!='pdf') {
			throw new Exception('Must be a PDF file:' . $file);
		}
	}
	
	public function setImagesDirectory($dir)
	{
		if (!is_dir($dir)) {
			throw new Exception('Directory didn\'t found:' . $dir);
		}
		$this->imagesDirectory = $dir;
	}
	
	public function setFileId($id)
	{
		if (!is_numeric($id)) {
			throw new Exception('Post Id must be numeric:' . $id);
		}
		$this->fileId = $id;
	}
	
	public function getKey()
	{
	    return $this->key;
	}
	
	public function genereteFlipBook()
	{
		
		if (is_null($this->file)) {
			throw new Exception('Have to set a PDF file');
		}
		
		if (is_null($imagesDirectory)) {
			$this->setImagesDirectory($this->createFlipBookDirectory());
		}
		
		$imagick = new Imagick();
		
		$imagick->readImage($this->file['dirname'] . '/' . $this->file['basename']);
		$pageCount = $imagick->getNumberImages();
		
		$imagick->setResolution(300, 300);
		$error = false;
		for ($countPages = 0; $countPages < $pageCount; $countPages++) {
			
			$imagick->readImage($this->file['dirname'] . '/' . $this->file['basename'] . "[{$countPages}]");
			$imagick->setImageFormat('jpeg');
			$imagick->setImageCompressionQuality(100);
		
			try {
				
				$countPages++;
				$imagick->thumbnailImage(1115, 1443, true);
				$imagick->writeImage("{$this->imagesDirectory}/" . $this->sanitizeString($this->file['filename']) . "_{$countPages}-large.jpg");
				
				$imagick->thumbnailImage(500, 650, true);
				$imagick->writeImage("{$this->imagesDirectory}/" . $this->sanitizeString($this->file['filename']) . "_{$countPages}.jpg");
				
				$imagick->thumbnailImage(76, 100, true);
				$imagick->writeImage("{$this->imagesDirectory}/" . $this->sanitizeString($this->file['filename']) . "_{$countPages}-thumb.jpg");
				$countPages--;
				
				//echo "Page {$countPages} splited and converted<br />\n";
			} catch (Exception $e) {
				//echo 'Caught exception: ',  $e->getMessage(), "\n";
				$countPages = $pageCount+1;
				$error = true;
			}
			
			$imagick->clear(); 
			$imagick->destroy(); 
		}
		
		if($error===false) {
			if($this->generateJson($pageCount)) {
				return true;
			} else {
				return false;
			}
		} else {
			$this->deleteFlipBookDirectory();
			return false;
		}
		
	}
	
	private function createFlipBookDirectory()
	{
		$rootPath = plugin_dir_path( __DIR__ )  . 'flipbook-files';
		
		$count = 0;
		while(is_dir("{$rootPath}/{$this->key}")){
			$this->generateKey($num);
			$count++;
		}

		try {
			mkdir("{$rootPath}/{$this->key}", 0755);
			
			return "{$rootPath}/{$this->key}";
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		
	}
	
	private function generateKey($num=null)
	{
		$this->key = md5($this->file['filename'] . $num . time());
	}
	
	private function generateJson($numberOfPages)
	{
		
		$json = json_encode(array('key'=>$this->key, 'fileName'=>$this->sanitizeString($this->file['filename']), 'numberOfPages'=>(int)$numberOfPages, 'fileId'=>$this->fileId));
		if(file_put_contents($this->imagesDirectory . '/conf.json', $json)) {
			return true;
		} else {
			return false;
		}
		
	}
	
	public function deleteFlipBookDirectory()
	{
		
		if(is_null($this->imagesDirectory)) {
			throw new Exception('Have to set a Directory to delete');
		}
		
		$it = new RecursiveDirectoryIterator($this->imagesDirectory, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
		foreach($files as $file) {
			if ($file->isDir()){
				rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}
		
		rmdir($this->imagesDirectory);
		
		return true;
		
	}
	
	private function sanitizeString($str)
	{
		
		$str = preg_replace('/[áàãâä]/ui', 'a', $str);
		$str = preg_replace('/[éèêë]/ui', 'e', $str);
		$str = preg_replace('/[íìîï]/ui', 'i', $str);
		$str = preg_replace('/[óòõôö]/ui', 'o', $str);
		$str = preg_replace('/[úùûü]/ui', 'u', $str);
		$str = preg_replace('/[ç]/ui', 'c', $str);
		$str = preg_replace('/[^a-z0-9]/i', '_', $str);
		$str = preg_replace('/_+/', '_', $str);
		
		return $str;
	}
	
}