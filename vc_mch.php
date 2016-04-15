<?php
/**
 * Verify Code Generating
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//~ require init.php
require (__DIR__.'/core/init.php');

SimPHP::I(['modroot'=>'merchants', 'sessnode'=>'mch'])->boot(RC_SESSION);

$_SESSION['verifycode'] = '';

function _getcolor($color) {
  global $image;
  $color = preg_replace ("/^#/","",$color);
  $r = $color[0].$color[1];
  $r = hexdec ($r);
  $b = $color[2].$color[3];
  $b = hexdec ($b);
  $g = $color[4].$color[5];
  $g = hexdec ($g);
  $color = imagecolorallocate ($image, $r, $b, $g);
  return $color;
}

function _setnoise() {
  global $image, $width, $height, $back, $noisenum;
  for ($i=0; $i<$noisenum; $i++){
    $randColor = imageColorAllocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imageSetPixel($image, rand(0, $width), rand(0, $height), $randColor);
  }
}

//$randArray = array ('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
$randArray = array ('0','1','2','3','4','5','6','7','8','9');
$width  = 60;
$height = 30;
$len = 4;
//$bgcolor = "#cccccc";
$bgcolor = "#ffffff";
$noise = true;
//$noisenum = 50;
$noisenum = 0;
$border = 0;
$bordercolor = "#000000";
$image = imageCreate($width, $height);
$back = _getcolor($bgcolor);
imageFilledRectangle($image, 0, 0, $width, $height, $back);
$size = $width/($len+1);
$size = $size>$height ? $height-4 : $size;
$left = ($width-$len*($size+$size/10))/$size+4;
$code = '';
for ($i=0; $i<$len; $i++)
{
  srand((double)microtime()*1000000);
  $randtext = $randArray[array_rand($randArray)];
  $code .= $randtext;
  $textcolor = imageColorAllocate($image, rand(0, 100), rand(0, 100), rand(0, 100));
  $font = "misc/font/verifycode.ttf";
  $randsize = rand($size-$size/10, $size+$size/10);
  $location = $left+($i*$size+$size/10)+2;
  imagettftext($image, $randsize, 0, $location, $size+9, $textcolor, $font, $randtext);
}
if($noise) _setnoise();
$_SESSION['verifycode'] = $code;
$bordercolor = _getcolor($bordercolor);
if($border) imageRectangle($image, 0, 0, $width-1, $height-1, $bordercolor);
header("content-type:image/jpeg\r\n");
imagejpeg($image);
imagedestroy($image);
 
/*----- END FILE: vc_mch.php -----*/