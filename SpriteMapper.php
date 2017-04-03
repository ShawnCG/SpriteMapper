<?php
namespace Gravy\SpriteMapper;

use Gravy\SpriteMapper\Sprite;
use Gravy\SpriteMapper\Spritepacker;

class SpriteMapper
{
    private $config;
    private $root = [];
    private $height = 0;
    private $width = 0;



    private $sprites = [];
    private $sprites_data = [];

    private static $defaults = [
        'type'        => 'png',
        'fill'        => 'transparent',
        'compression' => 70
    ];

    public function __construct ( $config = [] )
    {
        $this->config = array_merge(self::$defaults, $config);
    }

    public function __get ( $name )
    {
        return $this->{$name};
    }

    public function add ($sprite_url)
    {
        $sprite = new Sprite($sprite_url);
        $this->sprites[] = $sprite;

        return $sprite;
    }

    public function removeSprite ( Sprite $sprite )
    {
        if ($key = array_search($sprite, $this->sprites, true)) {
            array_splice($this->sprites, $key, 1);
            return true;
        }

        return false;
    }

    public function save ()
    {
        $contents = $this->render(false);
    }

    public function render ( $echo = true )
    {
        
        ob_start();
        //
        $this->generateSpritemap();
        $contents = ob_get_clean();

        if ($echo === true) {
            echo $contents;
        } else {
            return $contents;
        }
    }

    private function generateSpritemap ()
    {
        // Fisrt, sort the nodes from biggest to smallest
        $sprites = $this->sprites;
        $len = count($sprites);
        $node;

        usort($sprites, function ( $a, $b ) {
            $asa = $a->height;
            $bsa = $b->height;
            return ($asa > $bsa) ? -1 : 1;
        });

        $packer = new SpritePacker;
        $packer->fit($sprites);

        $this->width = $packer->pack->w;
        $this->height = $packer->pack->h;

        $spritemap = imagecreatetruecolor($this->width, $this->height);
        $color = imagecolorallocatealpha($spritemap, 255, 255, 255, 127);
        imagefill($spritemap, 0, 0, $color);
        imagecolortransparent($spritemap, imagecolorallocatealpha($spritemap, 0, 0, 0, 127));
        imagealphablending($spritemap, false);
        imagesavealpha($spritemap, true);

        foreach ($sprites as $sprite) {
            if ($sprite->fit) {
                $fit = $sprite->fit;
                imagecopyresampled($spritemap, $sprite->getResource(), $fit->x, $fit->y, 0, 0, $sprite->width, $sprite->height, $sprite->original_width, $sprite->original_height);
            }
        }
        imagepng($spritemap, __DIR__.'/test.png');
    }
}
