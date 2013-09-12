# Instant Picture Creator

Instant Picture Creator is a wrapper to produce images on demand for
Web sites. It has filter management, and it's possible to add new
filters. It can cache filtered images, works independently (no backend
is needed), and is easy to integrate with existing Web sites.

It includes two basic filters for resizing images and manipulating color
palettes.

Currently it supports PNG, GIF, and JPEG.


## Easy to use

Copy Instant Picture Creator on your server, configure your .htaccess
(example is included in archive file) and start to us it:

    http://your.server.com/path/to/picture.png?filter=Resize-width-150

You can use shortcuts:

    http://your.server.com/path/to/picture.png?filter=thumbnail

Instant Picture Creator comes with cache handling and security helpers.


## Get help

* [How to use Resize filter?](http://eye48.com/dokuwiki/doku.php?id=en:projects:instant-picture-creator:filter:resize)
* [Mailing group](http://groups.google.com/group/instant-picture-creator)


## Dependencies

Instant Picture Creator needs minimally PHP 4.0.6 with gd and hash
extension. CompatInfo output is:

    +----------------------------------+---------+------------+------------------+
    | Path                             | Version | Extensions | Constants/Tokens |
    +----------------------------------+---------+------------+------------------+
    | [...]/*                          | 4.0.6   | hash       |                  |
    |                                  |         | gd         |                  |
    +----------------------------------+---------+------------+------------------+
    | [...]/filters/Resize.filter.php  | 4.0.6   | gd         |                  |
    +----------------------------------+---------+------------+------------------+
    | [...]/filters/Palette.filter.php | 4.0.6   | gd         |                  |
    +----------------------------------+---------+------------+------------------+
    | [...]/instantpicture.inc.php     | 4.0.6   | hash       |                  |
    |                                  |         | gd         |                  |
    +----------------------------------+---------+------------+------------------+
    | [...]/instantpicture.conf.php    | 3.0.0   |            |                  |
    +----------------------------------+---------+------------+------------------+
    | [...]/instantpicture.php         | 4.0.5   |            |                  |
    +----------------------------------+---------+------------+------------------+

