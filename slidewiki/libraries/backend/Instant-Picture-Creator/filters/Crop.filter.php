<?php
/* How to use
 *
 * Crop width
 * ?filter=Crop-width-OFFSET-WIDTH
 * Crops WIDTH columns starting with column OFFSET.
 * ?filter=Crop-width-mOFFSET-WIDTH
 * Crops WIDTH columns starting with column (ImageWidth - OFFSET).
 * ?filter=Crop-width-OFFSET-mWIDTH
 * Crops columns starting with OFFSET ending with (ImageWidth - WIDTH).
 * ?filter=Crop-width-mOFFSET-mWIDTH
 * Crops columns starting with (ImageWidth - OFFSET) ending with (ImageWidth - WIDTH).
 *
 * Crop height
 * ?filter=Crop-height-OFFSET-WIDTH
 * Analog to width.
 *
 * Crop exact box
 * ?filter=Crop-exact-XOFFSET-YOFFSET-WIDTH-HEIGHT
 * Analog to width.
 */

class Crop extends InstantFilter
{

    // Security values are in pixel
    // value -1 means to skip the check
    var $security = array(
        "max-width"=>-1,
        "min-width"=>-1,
        "max-height"=>-1,
        "min-height"=>-1,
        "max-dim"=>-1, // maximum of dimension (x*y) (2560*1920 (=5MPix))
        "min-dim"=>-1 // minimum of dimension (x*y)
    );

    function Crop($source, $parameter = null)
    {

        $oldWidth = imagesx ($source);
        $oldHeight = imagesy ($source);
        $startX = 0;
        $startY = 0;

        if ($parameter[1] == 'width')
        {
            $startX = $this->calcSize($oldWidth, 0, $parameter[2]);

            $newWidth = $this->calcSize($oldWidth, $startX, $parameter[3]);
            $newHeight = $oldHeight;

            // security check of dimensions
            $this->checkSecurity($newWidth,$this->security['min-width'],$this->security['max-width']);
            $this->checkSecurity($newHeight,$this->security['min-height'],$this->security['max-height']);
            $this->checkSecurity($newWidth*$newHeight,$this->security['min-dim'],$this->security['max-dim']);
        }
        elseif ($parameter[1] == 'height')
        {
            $startY = $this->calcSize($oldHeight, 0, $parameter[2]);

            $newWidth = $oldWidth;
            $newHeight = $this->calcSize($oldHeight, $startY, $parameter[3]);

            // security check of dimensions
            $this->checkSecurity($newWidth,$this->security['min-width'],$this->security['max-width']);
            $this->checkSecurity($newHeight,$this->security['min-height'],$this->security['max-height']);
            $this->checkSecurity($newWidth*$newHeight,$this->security['min-dim'],$this->security['max-dim']);
        }
        elseif ($parameter[1] == 'exact')
        {
            $startX = $this->calcSize($oldWidth, 0, $parameter[2]);
            $startY = $this->calcSize($oldHeight, 0, $parameter[3]);

            $newWidth = $this->calcSize($oldWidth, $startX, $parameter[4]);
            $newHeight = $this->calcSize($oldHeight, $startY, $parameter[5]);

            // security check of dimensions
            $this->checkSecurity($newWidth,$this->security['min-width'],$this->security['max-width']);
            $this->checkSecurity($newHeight,$this->security['min-height'],$this->security['max-height']);
            $this->checkSecurity($newWidth*$newHeight,$this->security['min-dim'],$this->security['max-dim']);
        }
        elseif ($parameter[1] == '')
        {
            $this->pushError('no attributes');
        }
        else
        {
            $this->pushError('wrong attribute '.$parameter[1]);
        }

        if (count($this->error) == 0)
        {
            $this->dest = imagecreatetruecolor($newWidth, $newHeight);
            if (!imagecopy($this->dest, $source, 0, 0, $startX, $startY, $newWidth, $newHeight))
            {
                $this->dest = false;
                $this->pushError('cannot apply filter');
            }
        }
        else
        {
            $this->dest = false;
        }
    }

    function calcSize($oldSize, $start, $parameter)
    {
        if (preg_match("/^m/", $parameter)) {
            $parameter = substr($parameter, 1);
            if (strlen($parameter) == 0)
                return ($oldSize - $start);
            else
                return ($oldSize - $parameter - $start);
        }
        return $parameter;
    }

    function checkSecurity($value, $min, $max)
    {
        if ($min > 0 && $value < $min) $this->pushError('security issue, dimension fault ('.$value.' < '.$min.')');
        if ($max > 0 && $value > $max) $this->pushError('security issue, dimension fault ('.$value.' > '.$max.')');
    }
}
