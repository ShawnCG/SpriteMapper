<?php
namespace Gravy\SpriteMapper;

class Sprite
{

    private $original_height;
    private $original_width;

    public $fit = false;

    private $height = 0;
    private $width = 0;
    private $scale = 1;

    private $image_url;
    private $image_resource;

    public function __construct ( $sprite_url )
    {
        $this->image_url = $sprite_url;
        $this->createImageResource();
    }

    public function __get ( $name )
    {
        return $this->{$name};
    }

    public function height ( $height = false )
    {
        $this->height = $height;
    }

    public function width ( $width = false )
    {
        $this->width = $width;
    }

    public function scaleHeight ( $height = false )
    {
        $this->scale = $height / $this->original_height;
        $this->height = $height;
        $this->width = $this->original_width * $this->scale;
    }

    public function scaleWidth ( $width = false )
    {
        $this->scale = $width / $this->original_width;
        $this->width = $width;
        $this->height = $this->original_height * $this->scale;
    }

    public function getResource ()
    {
        return $this->image_resource;
    }

    private function createImageResource ()
    {
        $image_contents = file_get_contents($this->image_url);
        $res = imagecreatefromstring($image_contents);
        
        $this->width = $this->original_width = imagesx($res);
        $this->height = $this->original_height = imagesy($res);

        $new = imagecreatetruecolor($this->original_width, $this->original_height);
        imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
        imagealphablending($new, false);
        imagesavealpha($new, true);

        imagecopyresampled($new, $res, 0, 0, 0, 0, $this->original_width, $this->original_height, $this->original_width, $this->original_height);

        $this->image_resource = $new;
    }

}
