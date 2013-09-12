<?php

# Security Configuration --- Begin

$nofiltername = 'nofilter'; // filter dummy name for unfiltered image
$nofilter = true; // is the filter dummy for unfiltered image allowed as filter in url

// array for all allowed filters, it's recommended to use that for security issues
// empty array: all requested filters will be used
// non-empty array: only that filters are allowed to be used
// $filtersallowed[] = 'filter-attr1-attr2';

# Security Configuration --- End

# System Configuration --- Begin

$pathToCache = "cache/"; // path to cache files, relative to location of instantpicture.php
$pathToFilters = "filters/"; // path to filters, relative to location of instantpicture.php

# System Configuration --- End

# User Preferences --- Begin

$quality = 80; // quality for Jpeg images: 1 (worst) to 99 (best)
$debug = 1; // debug level: 0 (off) | 1 (on, showed in picture) | 2 (debuginfo)
$debugtype = 'png'; // image type for debug info
$cache = 'hash'; // off | hash | long | mixed
$cachefilterorder = false; // order of requested filters belongs to cache name
$defaultfilter = 'nofilter'; // default filter used for images requested without filter

// shortcuts for filters and combined filters
# $shortcuts['shortcut'] = 'anothershortcut01/anothershortcut02';
# $shortcuts['anothershortcut01'] = 'filter1-attr1-attr2';
# $shortcuts['anothershortcut02'] = 'filter2-attr1/filter3-attr1-attr2-attr3';

# User Preferences --- End

