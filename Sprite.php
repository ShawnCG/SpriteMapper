<?php
namespace Gravy\SpriteMapper;

class Sprite
{

    private $original_height;
    private $original_width;

    private $height = 0;
    private $width = 0;

    private $image_url;
    private $tmp_image;

    public function __construct ( $sprite_url )
    {
        $this->image_url = $sprite_url;
        $this->createTmpFile();
        $this->getDimensions();
    }

    public function __get ( $name )
    {
        if ($name == 'width' || $name == 'height') {
        }
    }

    public function read ()
    {
        $image = fopen($this->image_url);
        return fread($this->image);
    }

    private function createTmpFile ()
    {
        $this->tmp_image = tmpfile();
        $image_contents = file_get_contents($image);
        fwrite($tmp, $image_contents);
    }

    private function getDimensions ()
    {
    }


}
