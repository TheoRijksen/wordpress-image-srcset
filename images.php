<?php
class responsiveImage {
	public $width;
	public $height;
	public $ratio;
	public $path;
	public $description;
	public $sizes;
	public $html;
	public $extension;
	public $name;
	public $imagePaths = array();
	public $imageFolder = 'wp-content/uploads/responsive/';
	public $breakpoints = array( '(max-width: 575px)',
								 '(min-width: 576px) and (max-width: 767px)',
								 '(min-width: 768px) and (max-width: 991px)',
								 '(min-width: 992px) and (max-width: 1199px)',
								 '(min-width: 1200px)',
								);

	function __construct($attributes) {
		$this->path = $attributes->path;
		$this->description = $attributes->alt;
		$this->sizes = $attributes->sizes;
		$this->extension = $this->getExtension();
		$this->name = basename($this->path, '.'.$this->extension);
		$this->setImageSize();
	}

	/**
	* Set image source width and height
	*/
	private function setImageSize() {
		$imagesSize = getimagesize($this->path);
		$this->width = $imagesSize[0];
		$this->height = $imagesSize[1];
		$this->ratio = $this->width / $this->height;
	}

	/**
	* Generate images for each breakpoint
	*/
	private function generateJPG() {
		$imageSubFolder = $this->name;

		if (!file_exists($imageSubFolder)) {
			mkdir($imageSubFolder , 0755, true);
		}

		foreach($this->sizes as $size) {
			$image = wp_get_image_editor( $this->path );
			if ( ! is_wp_error( $image ) ) {
				$image->resize( $size, $size / $this->ratio, true );
				$image->save('./'.$this->imageFolder.'/'.$imageSubFolder.'/'.$this->name.'-'.$size.'.jpg');
			}
			array_push($this->imagePaths, './'.$this->imageFolder.'/'.$imageSubFolder.'/'.$this->name.'-'.$size.'.jpg');
		}
	}

	private function generateWebP() {
		foreach($this->sizes as $size) {
			$imageSource = './'.$this->imageFolder.'/'.$this->name.'/'.$this->name.'-'.$size.'.jpg';
			$imageDest = './'.$this->imageFolder.'/'.$this->name.'/'.$this->name.'-'.$size.'.webp';

			imagewebp(imagecreatefromjpeg($imageSource), $imageDest);
		}

	}

	/**
	* Generate the html
	*/
	private function constructImageHTML() {
		$this->html = '<picture>';
			$this->html .= '<source ';
			$this->html .= 'type="image/webp" ';
			$this->html .= $this->getSrcSet('webp');
			$this->html .= $this->getMediaQueries();
			$this->html .= $this->getDescription();
			$this->html .= '>' ;

			$this->html .= '<source ';
			$this->html .= 'type="image/jpg" ';
			$this->html .= $this->getSrcSet('jpg');
			$this->html .= $this->getMediaQueries();
			$this->html .= $this->getDescription();
			$this->html .= '>' ;

			$this->html .= $this->getFallback();
		$this->html .= '</picture>';
	}

	/**
	* Get the file extension
	*/
	private function getExtension() {
		return substr(strrchr($this->path,'.'),1);
	}

	/**
	* Get the srcset html
	*/
	private function getSrcSet($extention) {
		$srcSetHtml = '';

		if(count($this->sizes) > 0) {

			$srcSetHtml = 'srcset="';

			for ($i = 0; $i < count($this->sizes); $i++) {
				$srcSetHtml .= $this->imageFolder.$this->name.'/'.$this->name.'-'.$this->sizes[$i].'.'.$extention.' '.$this->sizes[$i].'w';
				($i+1 != count($this->sizes) ? $srcSetHtml .= ',' :  $srcSetHtml .= '" ');
			}
		}

		return $srcSetHtml;
	}

	/**
	* Get the media query html
	*/
	private function getMediaQueries() {
		$queryHtml = '';

		if(count($this->breakpoints)) {
			$queryHtml = ' sizes="';

			for ($i = 0; $i < count($this->breakpoints); $i++) {
				$queryHtml .= $this->breakpoints[$i];
				($i+1 != count($this->breakpoints) ? $queryHtml .= ',' :  $queryHtml .= '" ');
			}
		}

		return $queryHtml;
	}

	/**
	* Get the image alt description
	*/
	private function getDescription() {
		return ' alt="'.$this->description.'"';
	}

	/**
	* Get the image fallback
	*/
	private function getFallback() {
		if(!empty($this->path && $this->description))
		return '<img src="wp-content/uploads/responsive/'.$this->name.'/'.$this->name.'.jpg" alt="'.$this->description.'" />';
	}

	/**
	* Get the HTML
	*/
	public function getHTML(){
		//$this->generateJPG();
		//$this->generateWebP();
		$this->constructImageHTML();
		return $this->html;
	}
}