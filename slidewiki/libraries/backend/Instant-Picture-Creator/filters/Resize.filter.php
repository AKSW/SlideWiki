<?php

class Resize extends InstantFilter
{

    // Security values are in pixel
    // value -1 means to skip the check
    var $security = array(
        "max-width"=>-1,
        "min-width"=>-1,
        "max-height"=>-1,
        "min-height"=>-1,
        "max-dim"=>-1, // maximum of dimension (x*y)
        "min-dim"=>-1 // minimum of dimension (x*y)
    );

    function Resize($source, $parameter = null)
    {

        $oldWidth = imagesx ($source);
        $oldHeight = imagesy ($source);
        $startX = 0;
        $startY = 0;

        if ($parameter[1] == 'square')
        {
            if ($oldWidth != $oldHeight)
            {
                if ($oldWidth > $oldHeight)
                {
                    $temp = $oldWidth - $oldHeight;
                    $startX = $startX + intval($temp/2);
                    $oldWidth = $oldWidth - $temp;
                }
                else
                {
                    $temp = $oldHeight - $oldWidth;
                    $startY = $startY + intval($temp/2);
                    $oldHeight = $oldHeight - $temp;
                }
            }

            $newWidth = $parameter[2];
            $newHeight = $parameter[2];

            // security check of dimensions
            $this->checkSecurity($newWidth,$this->security['min-width'],$this->security['max-width']);
            $this->checkSecurity($newHeight,$this->security['min-height'],$this->security['max-height']);
            $this->checkSecurity($newWidth*$newHeight,$this->security['min-dim'],$this->security['max-dim']);
        }
        elseif ($parameter[1] == 'cutout')
        {
            $newWidth = $parameter[2];
            $newHeight = $parameter[3];

            $oldRatio = $oldWidth/$oldHeight;
            $newRatio = $newWidth/$newHeight;

            if ($oldRatio != $newRatio)
            {
                if ($oldRatio > $newRatio)
                {
                    $temp = $oldWidth - $newWidth*($oldHeight/$newHeight);
                    $startX = intval($temp/2);
                    $oldWidth = intval($oldWidth - $temp);
                }
                else
                {
                    $temp = $oldHeight - $newHeight*($oldWidth/$newWidth);
                    $startY = intval($temp/2);
                    $oldHeight = intval($oldHeight - $temp);
                }
            }

            // security check of dimensions
            $this->checkSecurity($newWidth,$this->security['min-width'],$this->security['max-width']);
            $this->checkSecurity($newHeight,$this->security['min-height'],$this->security['max-height']);
            $this->checkSecurity($newWidth*$newHeight,$this->security['min-dim'],$this->security['max-dim']);
        }
        elseif ($parameter[1] == 'exact')
        {
            $newWidth = $parameter[2];
            $newHeight = $parameter[3];

            // security check of dimensions
            $this->checkSecurity($newWidth,$this->security['min-width'],$this->security['max-width']);
            $this->checkSecurity($newHeight,$this->security['min-height'],$this->security['max-height']);
            $this->checkSecurity($newWidth*$newHeight,$this->security['min-dim'],$this->security['max-dim']);
        }
        elseif ($parameter[1] == 'min')
        {
            if ($oldWidth < $oldHeight)
            {
                $newWidth = $parameter[2];
                $newHeight = intval(($oldHeight/$oldWidth)*$newWidth);
            }
            else
            {
                $newHeight = $parameter[2];
                $newWidth = intval(($oldWidth/$oldHeight)*$newHeight);
            }
            // security check of dimensions
            $this->checkSecurity($newWidth,$this->security['min-width'],$this->security['max-width']);
            $this->checkSecurity($newHeight,$this->security['min-height'],$this->security['max-height']);
            $this->checkSecurity($newWidth*$newHeight,$this->security['min-dim'],$this->security['max-dim']);
        }
        elseif ($parameter[1] == 'max')
        {
            if ($oldWidth > $oldHeight)
            {
                $newWidth = $parameter[2];
                $newHeight = intval(($oldHeight/$oldWidth)*$newWidth);
            }
            else
            {
                $newHeight = $parameter[2];
                $newWidth = intval(($oldWidth/$oldHeight)*$newHeight);
            }
            // security check of dimensions
            $this->checkSecurity($newWidth,$this->security['min-width'],$this->security['max-width']);
            $this->checkSecurity($newHeight,$this->security['min-height'],$this->security['max-height']);
            $this->checkSecurity($newWidth*$newHeight,$this->security['min-dim'],$this->security['max-dim']);
        }
        elseif ($parameter[1] == 'width')
        {
            $newWidth = $parameter[2];
            $newHeight = intval(($oldHeight/$oldWidth)*$newWidth);
            // security check of dimensions
            $this->checkSecurity($newWidth,$this->security['min-width'],$this->security['max-width']);
            $this->checkSecurity($newHeight,$this->security['min-height'],$this->security['max-height']);
            $this->checkSecurity($newWidth*$newHeight,$this->security['min-dim'],$this->security['max-dim']);
        }
        elseif ($parameter[1] == 'height')
        {
            $newHeight = $parameter[2];
            $newWidth = intval(($oldWidth/$oldHeight)*$newHeight);
            // security check of dimensions
            $this->checkSecurity($newWidth,$this->security['min-width'],$this->security['max-width']);
            $this->checkSecurity($newHeight,$this->security['min-height'],$this->security['max-height']);
            $this->checkSecurity($newWidth*$newHeight,$this->security['min-dim'],$this->security['max-dim']);
        }
        elseif ($parameter[1] == 'dim')
        {
            $f = sqrt(($oldHeight*$oldWidth)/$parameter[2]);
            $newHeight = intval($oldHeight/$f);
            $newWidth = intval($oldWidth/$f);;
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
            if (!imagecopyresampled($this->dest, $source, 0, 0, $startX, $startY, $newWidth, $newHeight, $oldWidth, $oldHeight))
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

    function checkSecurity($value, $min, $max)
    {
        if ($min > 0 && $value < $min) $this->pushError('security issue, dimension fault ('.$value.' < '.$min.')');
        if ($max > 0 && $value > $max) $this->pushError('security issue, dimension fault ('.$value.' > '.$max.')');
    }
}
