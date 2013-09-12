<?php

function instantErrorHandler ($errno, $errstr, $errfile, $errline)
{
    global $errormsg;
    if ($errno != E_NOTICE)
        $errormsg[] = 'PHP '.PHP_VERSION.': '.$errstr.' in '.$errfile.' on line '.$errline. ' ['.$errno.']';
}

function cacheName($file,$filters,$cachetype,$cachefilterorder)
{
    $filename = basename($file);
    $foldername = str_replace($_SERVER['DOCUMENT_ROOT'].'/','',dirname($file));
    if ($cachefilterorder === false) sort($filters, SORT_STRING);
    $filters = implode('/',$filters);

    if ($cachetype == 'hash')
    {
        return md5($foldername.'/'.$filename.'/'.$filters);
    }
    elseif ($cachetype == 'long')
    {
        return str_replace(array('/','.'),array('_','_'),$foldername.'/'.$filename.'/'.$filters);
    }
    elseif ($cachetype == 'mixed')
    {
        return str_replace(array('/','.'),array('_','_'),$filename.'_'.md5($foldername.'/'.$filters));
    }
    else
    {
        return false;
    }
}

class InstantPicture
{
    var $imgSrc = '';
    var $imgType = null;
    var $fileToFlush = null;

    var $quality = 80;

    var $error = array();
    var $debug = 0; // default: debugging off
    var $debugtype = 'jpg';


    function InstantPicture($config = array(), $imagefile = null, $filters = array())
    {
        $this->quality   = (isset($config['quality']))   ? $config['quality']   : $this->quality;
        $this->debug     = (isset($config['debug']))     ? $config['debug']     : $this->debug;
        $this->debugtype = (isset($config['debugtype'])) ? $config['debugtype'] : $this->debugtype;
        return $this->apply($imagefile, $filters);
    }

    function setImageType($type)
    {
        $this->imgType = $type;
    }

    function getImageTypeByFile($file)
    {
        // check if file exists
        if (is_file($file))
        {
            // file exists

            // get image type
            $info = getimagesize($file);
            $type = null; // set to null per default for unsupported types
            if ($info[2] == 1) $type = 'gif';
            if ($info[2] == 2) $type = 'jpg';
            if ($info[2] == 3) $type = 'png';

            // getimagesize cannot read type
            // fallback to getting type by file extension
            if ($type===null) $type = strtolower(substr($file,-3));

            return $type;
        }
        else
        {
            $this->error[] = 'InstantImage: file does not exist ('.$file.')';
            return false;
        }
    }

    function imageOpenFromFile($file)
    {
        // get Image type
        if ($this->imgType === null)
        {
            $imgType = $this->getImageTypeByFile($file);
        }
        else
        {
            $imgType = $this->imgType;
        }
        // 1. Check Image Type
        if ($imgType === 'gif' || $imgType === 'jpg' || $imgType === 'png')
        {
            // supported image type

            // save type
            if ($this->imgType == null) $this->imgType = $imgType;

            // 2. Check if file exists
            if (is_file($file))
            {
                // valid file

                // 3. Check server (PHP) support for image type
                $support = false;
                if ($this->imgType == 'gif' && (ImageTypes() & IMG_GIF)) $support = true;
                if ($this->imgType == 'jpg' && (ImageTypes() & IMG_JPG)) $support = true;
                if ($this->imgType == 'png' && (ImageTypes() & IMG_PNG)) $support = true;

                if ($support === true)
                {
                    // PHP supports image type

                    // 4. Open image as source
                    $src = '';
                    if ($this->imgType === 'gif') $src = imagecreatefromgif($file);
                    if ($this->imgType === 'jpg') $src = imagecreatefromjpeg($file);
                    if ($this->imgType === 'png') $src = imagecreatefrompng($file);
                    // check for errors
                    if ($src != '')
                    {
                        // no error
                        $this->imgSrc = $src;
                        return true;
                    }
                    else
                    {
                        // error, cannot open image

                        $this->error[] = 'InstantImage: cannot open image as source';
                        return false;
                    }
                }
                else
                {
                    // PHP don't support image type (maybe wrong GD lib)
                    $this->error[] = 'InstantImage: your server (PHP '.phpversion().') does not support '.$this->imgType;
                    return false;
                }

            }
            else
            {
                // does not exist or is not a file
                $this->error[] = 'InstantImage: file does not exist ('.$file.')';
                return false;
            }
        }
        else
        {
            // wrong (unsupported) image type

            if ($imgType !== false) $this->error[] = 'InstantImage: unsupported image type';
            return false;
        }

    }

    function imageSaveToFile($file,$quality=0)
    {
        // get preferences for jpeg quality
        if ($quality == 0) $quality = $this->quality;

        if (count($this->error) == 0)
        {
            // no errors, try to save image
            if ($this->imgType == 'gif') $saved = imagegif($this->imgSrc,$file);
            if ($this->imgType == 'jpg') $saved = imagejpeg($this->imgSrc,$file,$quality);
            if ($this->imgType == 'png') $saved = imagepng($this->imgSrc,$file);

            if (!$saved)
            {
                // image was not written
                $this->error[] = 'InstantImage: cannot write image to file';
                return false;
            }
            else
            {
                $this->fileToFlush = $file;
                return true;
            }
        }
        else
        {
            // errors, doesn't make sense to save the wrong image
            $this->error[] = 'InstantImage: do not save the image because of errors';
            return false;
        }
    }

    function applyFilter($filter)
    {
        // only by zero pre errors
        if (count($this->error) == 0)
        {
            // split up filter name and filter configuration
            $filterInfos = explode('-',$filter);
            eval("\$temp = new ".$filterInfos[0]." (\$this->imgSrc,\$filterInfos);");
            if ($temp->dest !== false)
            {
                // filter was applied
                // $filterUsed[] = $filterItem['value'];
                $this->imgSrc = $temp->dest;
                return true;
            }
            else
            {
                // filter was not applied, save filter errors
                foreach($temp->error as $error)
                {
                    $this->error[] = $filterInfos[0].': '.$error;
                }
                return false;
            }
        }
    }

    function applyFilters($filters)
    {
        // preset to true in order to allow an empty filters array
        $r = true;
        foreach ($filters as $filter)
        {
            $r = $this->applyFilter($filter);
        }
        return $r;
    }

    function apply($imagefile,$filters)
    {
        if ($imagefile !== null)
        {
            if ($this->imageOpenFromFile($imagefile))
            {
                return $this->applyFilters($filters);
            }
            else
            {
                return false;
            }
        }
    }

    function flush($file=null,$quality=0)
    {
        if ($quality==0) $quality = $this->quality;

        $source = '';

        if (count($this->error)>0)
        {
            $this->flushError();
        }
        else
        {
            // flush(void)
            if ($file === null) $file = $this->fileToFlush;
            // fallback to image resource when no file is specified
            if ($file === null) $source = $this->imgSrc;

            if ($file != null)
            {
                // check for file
                if (is_file($file))
                {
                    $type = $this->getImageTypeByFile($file);
                    $this->sendHeader($type);
                    return readfile($file);
                }
                else
                {
                    $this->error[] = 'InstantImage: no image to flush, cannot find file '.$file;
                    $this->flushError();
                    return false;
                }
            }
            elseif ($source != '')
            {
                // check for image type
                if ($this->imgType != null)
                {
                    if ($this->imgType == 'gif')
                    {
                        $this->sendHeader($this->imgType);
                        return imagegif($this->imgSrc);
                    }
                    elseif ($this->imgType == 'jpg')
                    {
                        $this->sendHeader($this->imgType);
                        return imagejpeg($this->imgSrc,null,$quality);
                    }
                    elseif ($this->imgType == 'png')
                    {
                        $this->sendHeader($this->imgType);
                        return imagepng($this->imgSrc);
                    }
                    else
                    {
                        $this->error[] = 'InstantImage: cannot flush image source, unsupported image type';
                        $this->flushError();
                        return false;
                    }
                }
                else
                {
                    $this->error[] = 'InstantImage: cannot flush image, do not know image type';
                    $this->flushError();
                    return false;
                }
            }
            else
            {
                // no file or source specified
                $this->error[] = 'InstantImage: no image to flush';
                $this->flushError();
                return false;
            }
        }
    }

    function sendHeader($type)
    {
        if ($type == 'gif') header("Content-type: image/gif");
        if ($type == 'jpg') header("Content-type: image/jpeg");
        if ($type == 'png') header("Content-type: image/png");
        if ($type == '404') header("HTTP/1.0 404 Not Found");
        return true;
    }

    function flushError($type=null)
    {
        if ($type===null) $type = $this->debugtype;

        // only show error when debugging is on
        if ($this->debug == 0)
        {
            // no debugging, send 404 (not found) instead
            $this->sendHeader('404');
        }
        else
        {
            // show errors in image
            // using some code by dev at numist dot net
            // see original: http://de3.php.net/imagefontheight#49340

            // init
            $errors = array_merge(array_unique($this->error)); // get all errors
            $font = 2; // define font
            // width and height for one font char
            $text_width = imagefontwidth($font);
            $text_height = imagefontheight($font);
            // image height depends on number of errors
            $height = count($errors) * $text_height;
            // image width depends on error with most chars
            $errorstrlen = 0;
            foreach ($errors as $error)
            {
                if (strlen($error) > $errorstrlen)
                {
                    $errorstrlen = strlen($error);
                }
            }
            $width = $errorstrlen * $text_width;

            // create image with dimensions to fit text
            // plus two extra rows/columns for border
            $errorImg = imagecreatetruecolor($width+(2*$text_width),$height+(2*$text_height));

            if($errorImg)
            {
                // image creation success

                // color
                $text_color = imagecolorallocate($errorImg, 200, 10, 10);

                // write errors to image
                foreach ($errors as $i => $error)
                {
                    imagestring($errorImg, $font, $text_width, $text_height + $i * $text_height, $error, $text_color);
                }

                // image output
                $this->sendHeader($type);
                if ($type=='gif') return imagegif($errorImg);
                if ($type=='jpg') return imagejpeg($errorImg,null,$this->quality);
                if ($type=='png') return imagepng($errorImg);
            }
            else
            {
                // image creation failed, so just dump the array along with extra error
                $errors[] = "Is GD Installed?";
                die(var_dump($errors));
            }
        }
    }
}

class InstantFilter
{
    var $error = array();
    var $dest;

    function InstantFilter()
    {
        // dummy
    }

    function pushError($str)
    {
        $this->error[] = $str;
    }
}

