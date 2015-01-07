<?php
header('Content-Type: image/png');

$font = 'slkscr.ttf';
$font_size = 12;
$text = 'No text given';
$outline = '#000000';
$fill = '#FFFFFF';
$max_width = 80;

if (isset($_GET['text'])) $text = strip_tags( trim( $_GET['text'] ) );
if (isset($_GET['outline'])) $outline = strip_tags( trim( $_GET['outline'] ) );
if (isset($_GET['fill'])) $fill = strip_tags( trim( $_GET['fill'] ) );
if (isset($_GET['font_size'])) $font_size = intval( strip_tags( trim( $_GET['font_size'] ) ) );
if (isset($_GET['width'])) $max_width = intval( strip_tags( trim( $_GET['width'] ) ) );

$lines = explode("\n", wordwrap ($text, $max_width));

$widest = 0;
$image_width = 100;
foreach($lines as $line) {
    $type_space = imagettfbbox($font_size, 0, $font, $line);
    $width = abs($type_space[4] - $type_space[0]);
    if ($widest < $width) {
        $image_width = $width + 24;
        $widest = $width;
    }
}
$image_height = count($lines)*(abs($type_space[5] - $type_space[1])+3) + 24;

$im = imagecreatetruecolor($image_width, $image_height+15);
imagealphablending($im, true);
imagesavealpha($im, true);
imageantialias($im, false);

$transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $transparent);

$outline = hexToRGB($outline);
$fill = hexToRGB($fill);

$outline = imagecolorallocatealpha($im, $outline['r'], $outline['g'], $outline['b'], $outline['a']);
$shadow = imagecolorallocatealpha($im, $outline['r'], $outline['g'], $outline['b'], 102);
$fill = imagecolorallocatealpha($im, $fill['r'], $fill['g'], $fill['b'], $fill['a']);

drawBubble($im, $image_width, $image_height, $outline, $shadow, $fill);

$delta_y = 0;
foreach($lines as $line) {           
    $dimensions = imagettfbbox($font_size, 0, $font, $line);
    $delta_y =  $delta_y + ($dimensions[1] - $dimensions[7]) + 3;
    //centering x:
    $x = imagesx($imageCreator) / 2 - ($dimensions[4] - $dimensions[6]) / 2;
               // somehow -2 disables AA and is black, so im happy
    imagettftextSp($im, $font_size, 0, $image_width/2-1+$x, 10+$delta_y, -2, $font, $line);
}

imagepng($im);
imagedestroy($im);

// FUNCTIONS

// This is horrifyingly hardcoded. TODO
function drawBubble($im, $image_width, $image_height, $outline, $shadow, $fill)
{
    // fill
    imagefilledrectangle($im, 6, 6, $image_width-7, $image_height-7, $fill);

    // top
    imagefilledrectangle($im, 9, 3, $image_width-10, 5, $outline);
     
    // shadow-left
    imagefilledrectangle($im, 0, 12, 2, $image_height-7, $shadow);
    imagefilledrectangle($im, 3, $image_height-4, 8, $image_height-9, $shadow);
    imagefilledrectangle($im, 6, $image_height-1, $image_width-13, $image_height-3, $shadow);
    
    // left
    imagefilledrectangle($im, 6, 6, 8, 8, $outline);
    imagefilledrectangle($im, 3, 9, 5, $image_height-10, $outline);
    imagefilledrectangle($im, 6, $image_height-7, 8, $image_height-9, $outline);
    
    // right
    imagefilledrectangle($im, $image_width-7, 6, $image_width-9, 8, $outline);
    imagefilledrectangle($im, $image_width-4, 9, $image_width-6, $image_height-10, $outline);
    imagefilledrectangle($im, $image_width-7, $image_height-7, $image_width-9, $image_height-9, $outline);

    // bottom
    imagefilledrectangle($im, 9, $image_height-4, $image_width-10, $image_height-6, $outline);
    
    // bubble flow shadow
    imagefilledrectangle($im, $image_width/3-9, $image_height+3, $image_width/3, $image_height+15, $shadow);
    imagefilledrectangle($im, $image_width/3-6, $image_height, $image_width/3-3, $image_height+2, $shadow);
    
    // bubble flow
    imagefilledrectangle($im, $image_width/3-3, $image_height-6, $image_width/3-1, $image_height+2, $outline);
    imagefilledrectangle($im, $image_width/3+10, $image_height-6, $image_width/3+12, $image_height+2, $outline);
    imagefilledrectangle($im, $image_width/3+7, $image_height+3, $image_width/3+9, $image_height+5, $outline);
    imagefilledrectangle($im, $image_width/3+4, $image_height+6, $image_width/3+6, $image_height+8, $outline);
    imagefilledrectangle($im, $image_width/3-6, $image_height+3, $image_width/3+3, $image_height+11, $outline);
    imagefilledrectangle($im, $image_width/3-7, $image_height+12, $image_width/3-9, $image_height+15, $outline);
    imagefilledrectangle($im, $image_width/3, $image_height-6, $image_width/3+9, $image_height+2, $fill);
    imagefilledrectangle($im, $image_width/3-3, $image_height+3, $image_width/3+3, $image_height+8, $fill);
    imagefilledrectangle($im, $image_width/3+4, $image_height+3, $image_width/3+6, $image_height+5, $fill);
}

function hexToRGB($hex){
    $hex = ltrim($hex,'#');
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
    $a = 0;
    if (strlen($hex) == 8)
        $a = hexdec(substr($hex,6,2));
    return array('r' => $r, 'g' => $g, 'b' => $b, 'a' => $a); 
}

//http://stackoverflow.com/a/11953470
function imagettftextSp($image, $size, $angle, $x, $y, $color, $font, $text, $spacing = -1)
{        
    if ($spacing == -1)
    {
        imagettftext($image, $size, $angle, $x, $y, $color, $font, $text);
    }
    else
    {
        $temp_x = $x;
        $temp_y = $y;
        for ($i = 0; $i < strlen($text); $i++)
        {
            imagettftext($image, $size, $angle, $temp_x, $temp_y, $color, $font, $text[$i]);
            $bbox = imagettfbbox($size, 0, $font, $text[$i]);
            $temp_x += cos(deg2rad($angle)) * ($spacing + ($bbox[2] - $bbox[0]));
            $temp_y -= sin(deg2rad($angle)) * ($spacing + ($bbox[2] - $bbox[0]));
        }
    }
}
?>
