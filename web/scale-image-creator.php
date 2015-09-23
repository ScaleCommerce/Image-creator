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

$requestPath = $_SERVER["REQUEST_URI"];
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

    if (!file_exists($masterPath)) {
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
    make_path($targetPath, true);
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
 * A function that builds a path an makes sure the structure exists.
 *
 * Source: http://edmondscommerce.github.io/php/php-recursive-create-path-if-not-exists.html
 * Create  Directory Tree if Not Exists
 * If you are passing a path with a filename on the end, pass true as the second parameter to snip it off
 *
 * @param            $pathname
 * @param bool|false $is_filename
 *
 * @return bool
 */
//*/
function make_path($pathname, $is_filename = false)
{
    if ($is_filename) {
        $pathname = substr($pathname, 0, strrpos($pathname, '/'));
    }

    // Check if directory already exists
    if (is_dir($pathname) || empty($pathname)) {
        return true;
    }

    // Ensure a file does not already exist with the same name
    $pathname = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $pathname);

    if (is_file($pathname)) {
        trigger_error('mkdirr() File exists', E_USER_WARNING);
        return false;
    }
    // Crawl up the directory tree
    $next_pathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR));
    if (make_path($next_pathname, $is_filename)) {
        if (!file_exists($pathname)) {
            return mkdir($pathname);
        }
    }

    return false;
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