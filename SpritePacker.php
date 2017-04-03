<?php
 namespace Gravy\SpriteMapper;
// Growing Bin Packer algorithm for positioning icons in a spritesheet
// using space as efficiently as possible.
//
// This code is a rewritten version of Jake Gordon's GrowingPacker algorithm.
// See <http://codeincomplete.com/posts/2011/5/7/bin_packing/> for more info.
// The algorithm has been adapted from Javascript to PHP and modified lightly.
//
class SpritePacker
{
    public $pack;
   
    // Note that $sprites needs to be converted to a StdObject temporarily
    // as arrays cannot be properly passed by reference. This algorithm
    // cannot work in PHP using arrays.
   
    public function fit(&$sprites, $def_w=null, $def_h=null)
    {
        $len = count($sprites);
        if (!isset($def_w)) {
            $w = $len > 0 ? $sprites[0]->width : 0;
        } else {
            $w = $def_w;
        }
        if (!isset($def_h)) {
            $h = $len > 0 ? $sprites[0]->height : 0;
        } else {
            $h = $def_h;
        }
        $this->pack = (object)array(
            'x' => 0,
            'y' => 0,
            'w' => $w,
            'h' => $h,
            'used' => false,
        );
       
        foreach ($sprites as &$sprite) {
            $node = $this->findNode($this->pack, $sprite->width, $sprite->height);
           
            if ($node) {
                $sprite->fit = $this->splitNode($node, $sprite->width, $sprite->height);
            } else {
                $sprite->fit = $this->growNode($sprite->width, $sprite->height);
            }
        }
    }
   
    public function findNode(&$pack, $w, $h)
    {
        if (@$pack->used) {
            $node = $this->findNode($pack->right, $w, $h);
            if ($node) {
                return $node;
            }
            $node = $this->findNode($pack->down, $w, $h);
            if ($node) {
                return $node;
            }
        } else if (($w <= $pack->w) && ($h <= $pack->h)) {
            return $pack;
        } else {
            return null;
        }
    }
   
    public function splitNode(&$node, $w, $h)
    {
        $node->used = true;
        $node->down = (object)array(
            'x' => $node->x,
            'y' => $node->y + $h,
            'w' => $node->w,
            'h' => $node->h - $h,
        );
        $node->right = (object)array(
            'x' => $node->x + $w,
            'y' => $node->y,
            'w' => $node->w - $w,
            'h' => $h,
        );
        return $node;
    }
   
    public function growNode($w, $h)
    {
        $canGrowDown = ($w <= $this->pack->w);
        $canGrowRight = ($h <= $this->pack->h);
       
        $shouldGrowRight = $canGrowRight && ($this->pack->h >= ($this->pack->w + $w));
        $shouldGrowDown = $canGrowDown && ($this->pack->w >= ($this->pack->h + $h));

        if ($shouldGrowRight) {
            return $this->growRight($w, $h);
        } else if ($shouldGrowDown) {
            return $this->growDown($w, $h);
        } else if ($canGrowRight) {
            return $this->growRight($w, $h);
        } else if ($canGrowDown) {
            return $this->growDown($w, $h);
        } else {
            // if this happens, sort sizes first
            return null;
        }
    }
    public function growRight($w, $h)
    {
        $this->pack = (object)array(
            'used' => true,
            'x' => 0,
            'y' => 0,
            'w' => $this->pack->w + $w,
            'h' => $this->pack->h,
            'down' => $this->pack,
            'right' => (object)array(
                'x' => $this->pack->w,
                'y' => 0,
                'w' => $w,
                'h' => $this->pack->h,
            )
        );
        $node = $this->findNode($this->pack, $w, $h);
        if ($node) {
            return $this->splitNode($node, $w, $h);
        } else {
            return null;
        }
    }
    public function growDown($w, $h)
    {
        $this->pack = (object)array(
            'used' => true,
            'x' => 0,
            'y' => 0,
            'w' => $this->pack->w,
            'h' => $this->pack->h + $h,
            'down' => (object)array(
                'x' => 0,
                'y' => $this->pack->h,
                'w' => $this->pack->w,
                'h' => $h,
            ),
            'right' => $this->pack
        );
        $node = $this->findNode($this->pack, $w, $h);
        if ($node) {
            return $this->splitNode($node, $w, $h);
        } else {
            return null;
        }
    }
}
