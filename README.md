SlideWiki -- Authoring platform for OpenCourseWare
=========
SlideWiki drives the OpenCourseWare authoring platform http://slidewiki.org and empowers communities of instructors, teachers, lecturers, academics to create, share and re-use sophisticated educational content in a truly collaborative way. In addition to importing PowerPoint presentations, it supports authoring of interactive online slides using HTML and LaTeX. Slides and their containers (called decks), are versioned, thereby enabling change tracking. Users can create their own themes on top of existing themes or re-use other's themes.

=========
REQUIREMENTS

1. Apache 2.2 or higher
2. PHP 5.3 (for now the PHP 5.4 and upper versions cause bugs)
3. MySQL 5.5 or higher
4. **Optional**: we recommend to use PhpMyAdmin to manage the database

=========
INSTALLATION GUIDE

1. Download and extract SlideWiki into your server document root folder (e.g. c:/wamp/www/). 

2. Create an empty database 

3. Import **slidewiki/db/slidewiki.sql.zip** into the recently created database

4. Configure the installation 
    + Copy the **slidewiki/application/config/config.php-example** file to **slidewiki/application/config/config.php**.   
    + Change the configuration accordingly to your database and site settings:
        You need to change the following lines:

            define('DB_DSN', 'mysql:dbname=YOUR_DATABASE_NAME;host=YOUR_HOST_NAME;charset=utf8'); //database and hostname of the database
            define('DB_NAME', 'YOUR_DATABASE_NAME'); //the name of recently created database for slidewiki
            define('DB_USER', 'YOUR_USER_NAME'); //the name of the user, who is granted ALL privileges for slidewiki database
            define('DB_PASSWORD', 'USER_PASSWORD'); //the password of the user
            define('BASE_PATH', 'YOUR_ROOT_LOCATION'); //URL path of index.php 
            
        Be sure to grant ALL privileges for the slidewiki database to the user specified in config.php 

5. Check the configuration.     
    Restart Apache service. For now the index page of slidewiki should be already shown. However, you will need to configure your Apache and PHP installations before SlideWiki will be ready to use.   

6. Configure Apache   
    To work properly, SlideWiki requires rewrite_module to be enabled. Thus, be sure to uncomment the following line in htppd.conf:  
    
        LoadModule rewrite_module modules/mod_rewrite.s

    Additionally, the server must allow to use directives from .htaccess files. For this, your httpd.conf should include 
the following lines (YOUR_DOCUMENT_ROOT means the root directory for slidewiki, for example, c:/wamp/www/slidewiki)

        DocumentRoot "YOUR_DOCUMENT_ROOT"      
        ...      
        \<Directory "YOUR_DOCUMENT_ROOT\>      
        AllowOverride all   
        ...   
        \</Directory\> 

7. Configure PHP   
    To work properly, SlideWiki requires php_curl and php_tidy modules to be enabled. For that, uncomment the following
lines in php.ini:

        extension=php_curl      
        extension=php_tidy.dll.dll    

    Additionaly, you need to copy **ssleay32.dll**, **libeay32.dll** and **php_curl.dll** from PHP directory to system directory (e.g. C:\WINDOWS\System32)

8. Restart Apache to complete the SlideWiki installation.    
    Your new SlideWiki includes default themes and transitions as well as documentation, that can be found on **http://YOUR_ROOT_LOCATION/documentation**



