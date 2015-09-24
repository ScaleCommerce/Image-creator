<?php
/**
 * This file is part of a ScaleCommerce GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://scale.sc
 *
 * @version 0.1
 * @author  Joscha Krug <jk@scale.sc>
 * @url http://scale.sc
 */

//ToDo: If not in Dev mode don't show exceptions

include('../vendor/autoload.php');

if(file_exists('../config.php')){
    include('../config.php');
}else{
    exit('Your settings are missing!');
}

$requestPath = str_replace('//','/', $_SERVER["REQUEST_URI"]);
$targetPath  = $rootPath . $requestPath;

// Check if file exists
if(!sendFile($targetPath)){
    $pathArray    = explode('/', $requestPath);
    $pathArray[3] = 'master';

    $sizeArray = explode('_', $pathArray[6]);

    unset($pathArray[6]);

    $masterPath = $rootPath . implode('/', $pathArray);

    $width     = $sizeArray[0];
    $height    = $sizeArray[1];
    if (is_dir($masterPath) || !file_exists($masterPath)) {

        // Send the NoPic
        $masterPath = $noPicSrc;
        $targetPath = substr($targetPath, 0, strrpos($targetPath, '/')).'/nopic.jpg';
    }
    generateImage($masterPath, $targetPath, $width, $height);
    sendFile($targetPath);
}

/**
 * Generation of the Images by the given parameters.
 *
 * @param $masterPath
 * @param $targetPath
 * @param $width
 * @param $height
 */
function generateImage($masterPath, $targetPath, $width, $height)
{
    if((int) $width < 1)
    {
        $width = 150;
    }

    if((int) $height < 1)
    {
        $height = 150;
    }

    if(!is_dir(dirname($targetPath))) {
        mkdir(dirname($targetPath), 0755, true);
    }

    //ToDo Make configurable
    //Source http://harikt.com/blog/2012/12/17/resize-image-keeping-aspect-ratio-in-imagine/
    $imagine   = new Imagine\Gd\Imagine();
    $size      = new Imagine\Image\Box($width, $height);
    $mode      = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
    $resizeimg = $imagine->open($masterPath)->thumbnail($size, $mode);
    $sizeR     = $resizeimg->getSize();
    $widthR    = $sizeR->getWidth();
    $heightR   = $sizeR->getHeight();

    $preserve = $imagine->create($size);
    $startX   = $startY = 0;
    if ($widthR < $width) {
        $startX = ($width - $widthR) / 2;
    }
    if ($heightR < $height) {
        $startY = ($height - $heightR) / 2;
    }
    $preserve->paste($resizeimg, new Imagine\Image\Point($startX, $startY))->save($targetPath);
}

/**
 * Outputs the file if found
 *
 * @param $srcPath Path to the file
 *
 * @return boolean
 */
function sendFile($srcPath)
{
    if (file_exists($srcPath)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        header('Content-Type: ' . finfo_file($finfo, $srcPath));
        finfo_close($finfo);
        @readfile($srcPath);

        return true;
    }

    return false;
}