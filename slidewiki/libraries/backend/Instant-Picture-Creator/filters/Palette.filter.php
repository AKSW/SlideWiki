<?php

class Palette
{

    var $oldWidth = 0;
    var $oldHeight = 0;
    var $source;

    function Palette($source,$parameter)
    {
        $this->oldWidth = imagesx ($source);
        $this->oldHeight = imagesy ($source);
        $this->source = $source;

        if ($parameter[1] == 'gray')
        {
            if ($parameter[2] > 1 && $parameter[2] < 257)
            {
                $this->PaletteGray($parameter[2]);
            }
            else
            {
                $this->PaletteGray(64);
            }
        }
        elseif ($parameter[1] == '')
        {
            $this->error[] = 'empty attribute';
        }
        else
        {
            $this->error[] = 'do not know attribute "'.$parameter[1].'"';
        }

        if ($this->error)
        {
            $this->dest = false;
        }
    }

    function PaletteGray($colors)
    {
        $colors = round(256 / ($colors-1));

        $this->dest = imagecreate($this->oldWidth,$this->oldHeight);

        for ($y = 0; $y <$this->oldHeight; $y++)
            for ($x = 0; $x <$this->oldWidth; $x++) {
                $rgb = imagecolorat($this->source, $x, $y);
                $red  = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8)  & 0xFF;
                $blue  = $rgb & 0xFF;

                $gray = round((.299*$red + .587*$green + .114*$blue) / $colors) * $colors;
                if ($gray > 255) $gray = 255;

                // shift gray level to the left
                $grayR = $gray << 16;  // R: red
                $grayG = $gray << 8;    // G: green
                $grayB = $gray;        // B: blue

                // OR operation to compute gray value
                $grayColor = $grayR | $grayG | $grayB;

                // set the pixel color
                imagesetpixel ($this->source, $x, $y, $grayColor);
                imagecolorallocate ($this->source, $grayR, $grayG, $grayB);
            }


        if (!imagecopyresampled($this->dest, $this->source, 0, 0, 0, 0, $this->oldWidth, $this->oldHeight, $this->oldWidth, $this->oldHeight))
            $this->dest = false;
    }

}

