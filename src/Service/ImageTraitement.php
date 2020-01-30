<?php
namespace App\Service;
ini_set("gd.jpeg_ignore_warning", 1);
class ImageTraitement
{
	protected $SourceFile;
	protected $DestinationFile;

	protected $citation;
	protected $auteur;

	protected $width_image_const = 600;
	protected $height_image_const = 800;
	protected $width_base;
	protected $height_base;

	protected $font_size = 32;

	protected $image;
	protected $image_p;

    protected $mime;
    protected $noImageFace;
    protected $fiction;
    protected $rootDir;

    /**
     * ImageTraitement constructor.
     */
	public function __construct()
	{
	    $this->noImageFace = false;
        $this->rootDir = realpath('./../');
	}

    /**
     * @param $SourceFile
     * @param $DestinationFile
     * @param $citation
     * @param $auteur
     * @param $mime
     * @param bool $fiction
     */
      public function init($SourceFile, $DestinationFile, $citation, $auteur, $mime, $fiction = false)
      {
        if ($SourceFile == null)
        {
            $this->noImageFace = true;
            $SourceFile = $this->rootDir."/assets/watermark/black.jpg";
            $mime = "jpg";
        }
        $this->SourceFile = $SourceFile;
        $this->DestinationFile = $DestinationFile;
        $this->citation = trim($citation);
        $this->auteur = trim($auteur);
        $this->mime = $mime;
        $this->fiction = $fiction;
      }

    /**
     * @return bool
     */
	public function traitement() 
	{
      // //------------------------------------------------------------------------------ START
        if (($this->mime == "jpeg" || $this->mime == "jpg"))
            if (@imagecreatefromjpeg($this->SourceFile) === false)
                return false;
            else
                $imageCreateFirst = imagecreatefromjpeg($this->SourceFile);
        else if ($this->mime == "png")
            if (@imagecreatefrompng($this->SourceFile) === false)
                return false;
            else
                $imageCreateFirst = imagecreatefrompng($this->SourceFile);
        $this->width_base = imagesx($imageCreateFirst);
        $this->height_base = imagesy($imageCreateFirst);
        //list($this->width_base, $this->height_base) = getimagesize($this->SourceFile);
        $this->image_p = imagecreatetruecolor($this->width_image_const, $this->height_image_const);
        $this->image = $this->createimagefrom($this->mime);
        // //------------------------------------------------------------------------------ FILTER
        $this->filtre_petit_point();
        if ($this->noImageFace == false)
        $this->filtre_citation();
        // //------------------------------------------------------------------------------ RESIZE
        $this->crop_resize_image();
        // //------------------------------------------------------------------------------ TEXTE
        $this->write_text();
        // //------------------------------------------------------------------------------ WATERMARK
        $this->watermark();
        // //------------------------------------------------------------------------------ FINISH
        imagepng( $this->image_p, $this->DestinationFile, 2);

        exec('optipng '.$this->DestinationFile);
        //  //------------------------------------------------------------------------------ DESTROY
        imagedestroy($this->image);
        imagedestroy($this->image_p);
        return true;
	}

    /**
     * @param $string
     * @return mixed
     */
	public function corrige_accent($string)
	{
	    $string = str_replace("é", "É", $string);
	    $string = str_replace("è", "È", $string);
	    $string = str_replace("ç", "Ç", $string);
	    $string = str_replace("ê", "Ê", $string);
	    $string = str_replace("ë", "Ë", $string);
	    $string = str_replace("ñ", "Ñ", $string);
	    $string = str_replace("á", "Á", $string);
	    $string = str_replace("à", "À", $string);
	    $string = str_replace("â", "Â", $string);
	    $string = str_replace("ö", "Ö", $string);
	    $string = str_replace("ô", "Ô", $string);
	    $string = str_replace("œ", "Œ", $string);
	    $string = str_replace("û", "Û", $string);
	    $string = str_replace("î", "Î", $string);
	    $string = str_replace("ï", "Ï", $string);
	    $string = str_replace("ü", "Ü", $string);
	    $string = str_replace("æ", "Æ", $string);
      $string = str_replace("ù", "Ù", $string);
      $string = str_replace("ú", "Ú", $string);
	    return $string;
	}

    /**
     * @param $type
     * @return false|resource
     */
	public function createimagefrom($type)
	{
      if ($type == "")
        $type = "jpg";
      if($type == "jpeg" || $type == "jpg")
      {
        $image = ImageCreateFromJpeg($this->SourceFile);
      }
      else
      {
        $image = imagecreatefrompng($this->SourceFile);
      }
      return $image;
	}

    /**
     *
     */
	public function filtre_petit_point()
	{
      $x = 1200;
      $y = 1600;
      $color1 = imagecolorallocatealpha($this->image,200,240,242,118);
      $color2 = imagecolorallocatealpha($this->image,220,220,220,113);
     imagefill($this->image_p,0,0,$color1);
      for($i = 0; $i < $x; $i++) {
          for($j = 0; $j < $y; $j++) {
              if (mt_rand(0,1) == 1) imagesetpixel($this->image, $i, $j, $color2);
          }
      }
	}

    /**
     * function resize ans crop auto the image
     */
	public function crop_resize_image()
	{
        $original_aspect = $this->width_base / $this->height_base;
        $final_aspect = $this->width_image_const / $this->height_image_const;

        if ($original_aspect >= $final_aspect)
        {
           // If image final est plus petit que image upload
           $new_height = $this->height_image_const;
           $new_width = $this->width_base / ($this->height_base / $this->height_image_const);
        }
        else
        {
           // If image final est plus grande que image upload
           $new_width = $this->width_image_const;
           $new_height = $this->height_base / ($this->width_base / $this->width_image_const);
        }
        imagecopyresampled(
        	$this->image_p,
			$this->image,
			0 - ($new_width - $this->width_image_const) / 2, // Center the image horizontally
			0 - ($new_height - $this->height_image_const) / 2, // Center the image vertically
			0, 0,
			$new_width, $new_height,
			$this->width_base, $this->height_base
        );
	}

    /**
     * write citation and auteur on image
     */
	public function write_text()
	{
      $black = imagecolorallocatealpha ($this->image_p, 0, 0, 0,80);
      $white = imagecolorallocate($this->image_p, 255, 255, 255);
      $citation = strtoupper($this->corrige_accent($this->citation));
      $auteur = strtoupper($this->corrige_accent($this->auteur));
      putenv('GDFONTPATH=' . realpath('./../'));
      $font = "/assets/font/HelveticaNeuebd";
      //$font = getcwd().'\font\HelveticaNeuebd.ttf';
      $this->font_size = 40 - (strlen($citation) / 16);
      if ($this->font_size > 32)
          $this->font_size = 32;
      $this->imagettftextbox($this->font_size,0,6,675.5,$black,$font,$citation,596); //citation
      $this->imagettftextbox($this->font_size,0,3.5,680.5,$black,$font,$citation,596); //citation
      $this->imagettftextbox($this->font_size,0,3.5,675,$white,$font,$citation,596); //citation
      $this->imagettftextbox(16,0,10.5,731.5,$black,$font,$auteur,590); // auteur
      $this->imagettftextbox(16,0,9,733,$white,$font,$auteur,590); // auteur
	}

    /**
     * put watermarks on image
     */
	public function watermark () 
	{ 
	  $rand_right = rand(1,2);
	  if($rand_right == 1) { $logodroite = imagecreatefrompng($this->rootDir.'/assets/watermark/logo_droite2.png'); }
	  else {   $logodroite = imagecreatefrompng($this->rootDir.'/assets/watermark/logo_droite1.png');       }
	  $logophoto = imagecreatefrompng($this->rootDir.'/assets/watermark/logophoto.png');
	  $slogan = imagecreatefrompng($this->rootDir.'/assets/watermark/slogan2.png');
	  imagecopy($this->image_p, $logophoto, 3, 3, 0, 0, 180, 180); //logophoto
	  imagecopy($this->image_p, $slogan, 3, 178, 0, 0, 170, 32); //slogan
	  imagecopy($this->image_p, $logodroite, 444, 20, 0, 0, 134, 37);  //logoright
	  imagedestroy($logodroite); 
	  imagedestroy($slogan); 
	  imagedestroy($logophoto);
	}

    /**
     * @param $size
     * @param $angle
     * @param $left
     * @param $top
     * @param $color
     * @param $font
     * @param $text
     * @param $max_width
     */
	public function imagettftextbox($size, $angle, $left, $top, $color, $font, $text, $max_width)
	{
        $text_lines = explode("\n", $text); // Supports manual line breaks!
        $align = "center";
        $lines = array();
        $line_widths = array();
        $nbmin = 11;
        $largest_line_height = 50;
        foreach($text_lines as $block)
        {
            $current_line = ''; // Reset current line
            
            $words = explode(' ', $block); // Split the text into an array of single words
            
            $first_word = TRUE;
            
            $last_width = 0;
            
            for($i = 0; $i < count($words); $i++)
            {
                $item = $words[$i];
                $dimensions = imagettfbbox($size, $angle, $font, $current_line . ($first_word ? '' : ' ') . $item);
                $line_width = $dimensions[2] - $dimensions[0];
                $line_height = $dimensions[1] - $dimensions[7];
                
                // if($line_height > $largest_line_height) $largest_line_height = $line_height;
                
                if($line_width > $max_width && !$first_word)
                {
                    $lines[] = $current_line;
                    
                    $line_widths[] = $last_width ? $last_width : $line_width;
                    
                    if($i == count($words))
                    {
                        continue;
                    }
                    
                    $current_line = $item;
                }
                else
                {
                    $current_line .= ($first_word ? '' : ' ') . $item;
                }
                
                if($i == count($words) - 1)
                {
                    $lines[] = $current_line;
                    
                    $line_widths[] = $line_width;
                }
                
                $last_width = $line_width;
                    
                $first_word = FALSE;
            }
            
            if($current_line)
            {
                $current_line = $item;
            }
        }
        
        $i = 0;
        $lines = array_reverse($lines);
        $line_widths = array_reverse($line_widths);
        foreach($lines as $line)
        {
              $dimensions = imagettfbbox($size, $angle, $font, $line);
              $line_width = $dimensions[2] - $dimensions[0];
              $left_offset = ($max_width - $line_width) / 2;  
            imagettftext($this->image_p, $size, $angle, $left + $left_offset, $top - ($largest_line_height * $i), $color, $font, $line);
            $i++;
        }
	}

    /**
     * filter image
     */
  public function filtre_citation()
  {
    $r = rand(0, 20);
    $r = 800; // bypass for test
    if ($r == 0)
      $this->sepia2();
    elseif ($r == 0)
      $this->light();
    elseif ($r == 1)
      $this->fuzzy();
    elseif ($r == 2)
      $this->boost();
    elseif ($r == 3)
      $this->antique();
    elseif ($r == 4)
      $this->vintage();
    elseif ($r == 5)
      $this->freshblue();
    elseif ($r == 6)
      $this->tender();
    elseif ($r == 7)
      $this->forest();
    elseif ($r == 8)
      $this->retro();
    else
      $this->gray();
  }

  // + grey
  public function sepia2() {
    $this->gray();
    imagefilter($this->image, IMG_FILTER_GRAYSCALE);
    imagefilter($this->image, IMG_FILTER_BRIGHTNESS, -10);
    imagefilter($this->image, IMG_FILTER_CONTRAST, -20);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 60, 30, -15);

    return $this;
  }

  // + grey
  public function light() {
    $this->gray();
    imagefilter($this->image, IMG_FILTER_BRIGHTNESS, 10);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 100, 50, 0, 10);
    
    return $this;
  }

  // - gray
  public function aqua() {
    
    imagefilter($this->image, IMG_FILTER_COLORIZE, 0, 70, 0, 30);
    
    return $this;
  }

  //+gray
  public function fuzzy() {
    
    $this->gray();
    $gaussian = array(
        array(1.0, 1.0, 1.0),
        array(1.0, 1.0, 1.0),
        array(1.0, 1.0, 1.0)
    );

    imageconvolution($this->image, $gaussian, 9, 20);
    
    return $this;
  }

  //-gray
  public function boost() {
    
    imagefilter($this->image, IMG_FILTER_CONTRAST, -35);
    imagefilter($this->image, IMG_FILTER_BRIGHTNESS, 10);
    
    return $this;
  }

  public function gray() {

	    if ($this->fiction)
        {
            return ($this->grayFiction());

        }

    imagefilter($this->image, IMG_FILTER_GRAYSCALE);
    imagefilter($this->image, IMG_FILTER_CONTRAST,5);
    imagefilter($this->image, IMG_FILTER_BRIGHTNESS,-33);
    
    return $this;
  }

    public function grayFiction() {

        imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        imagefilter($this->image, IMG_FILTER_CONTRAST,5);
        imagefilter($this->image, IMG_FILTER_BRIGHTNESS,-33);

        return $this;
    }

  //+gray
  public function antique() {
    $this->gray();
    imagefilter($this->image, IMG_FILTER_BRIGHTNESS, 0);
    imagefilter($this->image, IMG_FILTER_CONTRAST, -30);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 75, 50, 25);

    return $this;
  }

  //-gray
  public function vintage() {
    imagefilter($this->image, IMG_FILTER_BRIGHTNESS, 10);
    imagefilter($this->image, IMG_FILTER_GRAYSCALE);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 40, 10, -15);

    return $this;
  }
  
  public function hermajesty() {
    imagefilter($this->image, IMG_FILTER_BRIGHTNESS, -10);
    imagefilter($this->image, IMG_FILTER_CONTRAST, -5);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 80, 0, 60);

    return $this;
  }

  public function freshblue() {
    $this->gray();
    imagefilter($this->image, IMG_FILTER_CONTRAST, -5);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 20, 0, 80, 60);

    return $this;
  }

  public function tender() {
    $this->gray();
    imagefilter($this->image, IMG_FILTER_CONTRAST, 5);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 80, 20, 40, 50);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 0, 40, 40, 100);
    imagefilter($this->image, IMG_FILTER_SELECTIVE_BLUR);

    return $this;
  }

  public function dream() {
     $this->gray();
    imagefilter($this->image, IMG_FILTER_COLORIZE, 150, 0, 0, 50);
    imagefilter($this->image, IMG_FILTER_NEGATE);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 0, 50, 0, 50);
    imagefilter($this->image, IMG_FILTER_NEGATE);
    imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);

    return $this;
  }

  public function forest() {
    imagefilter($this->image, IMG_FILTER_COLORIZE, 0, 0, 150, 50);
    imagefilter($this->image, IMG_FILTER_NEGATE);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 0, 0, 150, 50);
    imagefilter($this->image, IMG_FILTER_NEGATE);
    imagefilter($this->image, IMG_FILTER_SMOOTH, 10);

    return $this;
  }

  public function orangepeel() {
    imagefilter($this->image, IMG_FILTER_COLORIZE, 100, 20, -50, 20);
    imagefilter($this->image, IMG_FILTER_SMOOTH, 10);
    imagefilter($this->image, IMG_FILTER_BRIGHTNESS, -10);
    imagefilter($this->image, IMG_FILTER_CONTRAST, 10);
    imagegammacorrect($this->image, 1, 1.2 );

    return $this;
  }

  public function retro() {
    imagefilter($this->image, IMG_FILTER_GRAYSCALE);
    imagefilter($this->image, IMG_FILTER_COLORIZE, 100, 25, 25, 50);

    return $this;
  }

    /**
     * @return $this
     */
  public function washed() {
    imagefilter($this->image, IMG_FILTER_BRIGHTNESS, 30);
    imagefilter($this->image, IMG_FILTER_NEGATE);
    imagefilter($this->image, IMG_FILTER_COLORIZE, -50, 0, 20, 50);
    imagefilter($this->image, IMG_FILTER_NEGATE );
    imagefilter($this->image, IMG_FILTER_BRIGHTNESS, 10);
    imagegammacorrect($this->image, 1, 1.2);

    return $this;
  }
}

?>