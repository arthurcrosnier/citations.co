<?php
namespace App\Service;

class ImageVerif
{
    protected $largeur;
    protected $hauteur;
    protected $size;
    protected $mime;
    protected $error;

    /**
    * @param $image_size
    * @param $image_mime
    * @param $dimension
    */
    public function init($image_size, $image_mime, $dimension)
    {
        $this->size = $image_size;
        $this->mime = $image_mime;
        $this->largeur = $dimension['0'];
        $this->hauteur = $dimension['1'];
    }

    /**
    * @return false|string|null
    */
    public function verif_image()
    {
        $this->verif_size();
        $this->verif_ext();
        $this->verif_dimension();
        $error = $this->error;
        if(empty($error))
        {
            return null;
        }
        else
        {
            return substr($error,6);
        }
    }

    public function verif_size()
    {
        if($this->size > 2097152)
        {
            $this->error = $this->error."<br />Votre image est trop volumineuse, elle ne doit pas dépasser 2MO.(".$this->size.") ici";
        }

    }
    public function verif_ext()
    {
        if($this->mime != "jpeg" && $this->mime != "png" && $this->mime != "jpg")
        {
            $this->error = $this->error."<br />Format d'image incorrect. Les types d'images autorisés sont jpg/jpeg et png. (".$this->mime.") ici";
        }
    }
    public function verif_dimension()
    {
        if($this->largeur < 400 || $this->hauteur < 400)
        {
            $this->error = $this->error."<br />Les dimensions de votre image sont trop petites. Elles doivent avoir minimum 400 pixels en hauteur et en largeur.(".$this->largeur.") Largeur et (".$this->hauteur.") hauteur ici";
        }
    }
}