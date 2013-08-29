SlideWiki -- Authoring platform for OpenCourseWare
=========
SlideWiki empowers communities of instructors, teachers, lecturers, academics to create, share and re-use sophisticated educational content in a truly collaborative way. In addition to importing PowerPoint presentations, it supports authoring of interactive online slides using HTML and LaTeX. Slides and their containers (called decks), are versioned, thereby enabling change tracking. Users can create their own themes on top of existing themes or re-use other's themes.

=========
REQUIREMENTS

1. Apache 2.2 or higher
2. PHP 5.3 (for now the PHP 5.4 and upper versions cause bugs)
3. MySQL 5.5 or higher

=========
INSTALLATION GUIDE

1. Download and extract SlideWiki
2. Create an empty database
3. Import slidewiki.sql.zip in the recently created database
4. Configure config.php   
Copy the config.php-example file to config.php.   
Change the configuration accordingly to your database and site settings.   
Be sure to grant ALL privileges to slidewiki database for the user specified in config.php   

5. Check the configuration.   
For now the index page of slidewiki should be already shown.   
However, you will probably need to configure your Apache and PHP installations before SlideWiki will be ready to use.   

5. Configure Apache   
To work properly, SlideWiki requires mod_rewrite module to be enabled. Thus, be sure to uncomment the following line in htppd.conf:  
LoadModule rewrite_module modules/mod_rewrite.s  

Additionally, the server must allow to overwrite the configuration with .htaccess files:

> DocumentRoot "c:/wamp/www/"   
> ...   
> <Directory "c:/wamp/www/">
>     AllowOverride all
> </Directory>



6. Configure PHP   
To work properly, SlideWiki requires php_curl and php_tidy modules to be enabled.   
