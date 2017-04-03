<?php
namespace Gravy\SpriteMapper;

use Gravy\SpriteMapper\Sprite;

class SpriteMapper
{
    private $config;
    private $shape = [];
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
        $this->generateSpritemap();
        if ($echo === true) {
            //
        } else {
            ob_start();
            //
            $contents = ob_get_clean();
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
            $asa = $a->x * $a->y;
            $bsa = $b->x * $b->y;
            return ($asa < $bsa) ? -1 : 1;
        });

        $this->width = $len > 0 ? $sprites[0]->width : 0;
        $this->height = $len > 0 ? $sprites[0]->height : 0;
        $this->shape = [ x=> 0, y=> 0, w=> $this->width, h=> $this->height ];
        foreach ($sprites as $key => $sprite) {
            $width = $sprite->width();
            $height = $sprite->height();
            if ($node = $this->findNode($this->shape, $width, $height))
                $sprite->data['fit'] = $this->splitNode($node, $width, $height);
            else
                $sprite->data['fit'] = $this->growNode($width, $height);
        }
    }

    private function findNode (&$root, $w, $h) {
        if ($root['used'])
            return $this->findNode($root['right'], $w, $h) || $this->findNode($root['down'], $w, $h);
        else if (($w <= $root['w']) && ($h <= $root['h']))
            return $root;
        else
            return null;
    }

    private function splitNode (&$node, $w, $h) {
        $node['used'] = true;
        $node['down']  = [ x=> $node['x'], y=> $node['y'] + $h, w=> $node['w'], h=> $node['h'] - $h ];
        $node['right'] = [ x=> $node['x'] + $w, $y=> $node['y'], w=> $node['w'] - $w, $h=> $h ];
        return $node;
    }



    private function growNode ($w, $h) {
        $canGrowDown  = ($w <= $this->shape['w']);
        $canGrowRight = ($h <= $this->shape['h']);

        $shouldGrowRight = $canGrowRight && ($this->shape['h'] >= ($this->shape['w'] + $w)); // attempt to keep square-ish by growing right when height is much greater than width
        $shouldGrowDown  = $canGrowDown  && ($this->shape['w'] >= ($this->shape['h'] + $h)); // attempt to keep square-ish by growing down  when width  is much greater than height

        if ($shouldGrowRight)
            return $this->growRight($w, $h);
        else if ($shouldGrowDown)
            return $this->growDown($w, $h);
        else if ($canGrowRight)
            return $this->growRight($w, $h);
        else if ($canGrowDown)
            return $this->growDown($w, $h);
        else
            return null; // need to ensure sensible root starting size to avoid this happening
    }

    private function growRight ($w, $h) {
        $this->shape = [
            used=> true,
            x=> 0,
            y=> 0,
            w=> $this->shape['w'] + $w,
            h=> $this->shape['h'],
            down=> $this->shape,
            right=> [ x=> $this->shape['w'], y=> 0, w=> $w, h=> $this->shape['h'] ]
        ];
        if ($node = this.findNode($this->shape, $w, $h))
            return this.splitNode($node, $w, $h);
        else
            return null;
    }

    private function growDown ($w, $h) {
        $this->shape = [
            used=> true,
            x=> 0,
            y=> 0,
            w=> $this->shape['w'],
            h=> $this->shape['h'] + $h,
            down=>  [ x=> 0, y=> $this->shape['h'], w=> $this->shape['w'], h=> $h ],
            right=> $this->shape
        ];
        if ($node = $this->findNode($this->shape, $w, $h))
            return $this->splitNode($node, $w, $h);
        else
            return null;
    }
}
