<?php

/**
 * Instant Picture Creator
 * version 0.1
 *
 * @author Michael Haschke @ eye48.com
 * @version $Id: $
 *
 * Instant Picture Creator is an image wrapper to produce
 * images on demand for the web (resizing, changing colour palettes, etc)
 *
 * o works with gif, png and jpeg
 * o variable filter management
 * o you can add your own filters
 * o can cache filtered images
 * o works independent
 * o is easy to deploy
 * o is easy to use like "http://yourserver.com/picture.png?filter=thumbnail"
 *
 * project website:
 * @link http://eye48.com/dokuwiki/doku.php?id=en:projects:instant-picture-creator
 *
 * CHANGE LOG version 0.1
 * + initial release
 *
 * TODO
 * + support for more image type (maybe svg, tiff, bmp, ... send your wishes)
 * + make it easier to use it in backends
 * + trash management for cached images
 *
 * LICENCE
 * Libary or Lesser Gnu Public Licence (LGPL)
 *
 * This library is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; either version 2.1 of the License, or (at your option)
 * any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with this library; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @link http://www.opensource.org/licenses/lgpl-license.php
 *
 **/

### InstantImage starts here

// load configuration
require_once('instantpicture.conf.php');
// load base classes and functions
require_once('instantpicture.inc.php');

$debugmsg = array();
$errormsg = array();

// ERROR HANDLIND: show errors in image
error_reporting(E_ALL);
set_error_handler('instantErrorHandler');

// system paths
$pathToWrapper = dirname($_SERVER['SCRIPT_FILENAME']).'/';
$pathToFilters = realpath($pathToWrapper.$pathToFilters).'/';
$pathToCache = realpath($pathToWrapper.$pathToCache).'/';

// save debug info for paths
if ($debug == 2)
{
    $debugmsg[] = 'PATH TO InstantImage: '.$pathToWrapper;
    $debugmsg[] = 'PATH TO FILTERS: '.$pathToFilters;
    $debugmsg[] = 'PATH TO CACHE: '.$pathToCache;
}

// get name and filters for requested picture
if (isset($_SERVER['QUERY_STRING']))
{
    $query = explode('?',$_SERVER['QUERY_STRING']);
    parse_str($query[0]); // $picture
    parse_str($query[1]); // $filter
    // small hack that instant picture works with redirect
    // fallback if mod_rewrite or mod_mime/mod_actions is not available
    if ((!isset($picture) || $picture=='') && substr($_SERVER['REQUEST_URI'],0,strlen($_SERVER['SCRIPT_NAME'])) == $_SERVER['SCRIPT_NAME'])
    {
        $queryredirect = explode('?','picture='.substr($_SERVER['REQUEST_URI'],strlen($_SERVER['SCRIPT_NAME'])));
        parse_str($queryredirect[0]); // $picture
        parse_str($queryredirect[1]); // $filter
    }
    $picture = $_SERVER['DOCUMENT_ROOT'].$picture;
}

// save debug info for picture
if ($debug == 2)
{
    $debugmsg[] = 'PICTURE: '.$picture;
}

// preference array
$pref = array("quality"=>$quality,"debug"=>$debug,"debugtype"=>$debugtype);

// is nofilter in url possible
if ($nofilter !== true && $filter == $nofiltername) $filter = '';

// no filters, take default filter
if ($filter == '') $filter = $defaultfilter;

// save debug info for requested filters
if ($debug == 2)
{
    $debugmsg[] = 'FILTER (request): '.$filter;
}

// transform filter string to array
$filter = explode('/',$filter);

// check for shortcuts and replace it
if (isset($shortcuts) && count($shortcuts) > 0)
{
    foreach ($shortcuts as $shortcut => $value)
    {
        if (in_array($shortcut,$filter))
        {
            $fkey = array_search($shortcut,$filter);
            array_splice($filter,$fkey,1,explode('/',$value));
        }
    }
    // save debug info for plain filters
    if ($debug == 2)
    {
        $debugmsg[] = 'FILTER (plain): '.implode('/',$filter);
    }
}

// check availability for requested filters
$filters = array();
if (implode('/',$filter) != $nofiltername)
{
    foreach($filter as $filterItem)
    {
        $filterInfos = explode('-',$filterItem);
        if ((!isset($filtersallowed) || count($filtersallowed) == 0 || (count($filtersallowed) > 0 && in_array($filterItem,$filtersallowed))) && file_exists($pathToFilters.$filterInfos[0].'.filter.php'))
        {
            require_once($pathToFilters.$filterInfos[0].'.filter.php');
            $filters[] = $filterItem;
        }
    }
}
// save debug info for available filters
if ($debug == 2)
{
    $debugmsg[] = 'FILTER (checked): '.implode('/',$filters);
}


// check for wrong filters
if (implode('/',$filter) != $nofiltername && count($filter) != count($filters))
{
    // requested filters are wrong or not available
    $errormsg[] = 'InstantImage: requested filters are wrong or not available';
}

// create empty image object
$IMG = new InstantPicture($pref);
$show = null;

if ($debug == 2)
{
    $IMG->error = $debugmsg;
}
elseif (implode('/',$filter) != $nofiltername && count($errormsg) == 0)
{
    // default: create image
    $instant = true;

    // name of cache file
    $cachename = cacheName($picture,$filters,$cache,$cachefilterorder);

    // check for cache
    if ($cache != 'off' && is_file($pathToCache.$cachename))
    {
        // image must be older than cache
        if (filemtime($picture) < filemtime($pathToCache.$cachename))
        {
            $instant = false;
            $show = $pathToCache.$cachename;
        }
    }

    if ($instant === true)
    {
        $IMG->apply($picture,$filters);
        if ($cache != 'off' && count($errormsg) == 0) $IMG->imageSaveToFile($pathToCache.$cachename);
    }
}
else
{
    // send original image
    $show = $picture;
}

// flush filtered image or errors/faults
if (count($errormsg) > 0) $IMG->error = array_merge($errormsg,$IMG->error);
$IMG->flush($show);

