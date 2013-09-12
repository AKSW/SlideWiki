<?php

class Scorm extends Model {

    public $manifest;
    public $deck_id;
    public $metadata_course;
    public $format;
    public $first_page;
    public $deck;
    public $title;
    public $deck_name;
    public $root_deck_name;
    public $css;
    public $resources;
    public $common_resources;
    public $slides;
    public $slide_file_names = array();

    function create($deck_id, $format){        
        $deck = new Deck;
        $deck->createFromID($deck_id);
        $this->deck_id = $deck_id;
        $this->deck = $deck;
        $this->title = $deck->title;
        $this->root_deck_name = $this->sluggify($this->title);
        $this->deck_name = $this->sluggify($this->title);
        mkdir(ROOT . DS . 'tmp' . DS . $this->root_deck_name);
        $this->format = $format;        
        $this->resources = $this->createResources($this->resources, $deck);
        $this->createManifest();
        //$this->createCourseMetadata();
    }
    function createStyle($deck){
        $style = new Style();
        $this->css = $style->getStyle($deck->default_theme);
        $deck_name = $this->sluggify($deck->title);
        $css = ROOT . DS . 'tmp' . DS . $this->root_deck_name . DS . $deck_name . DS . 'default.css';
        if( !file_exists($css)) {
            $fp = fopen($css, "wb");
            fwrite($fp, $this->css['css']);
            fclose ($fp);
        }else{
            unlink($css);
            $fp = fopen($css, "wb");
            fwrite($fp, $this->css['css']);
            fclose ($fp);
        }
        $this->common_resources[] = $deck_name . DS . 'default.css';        
    }
    function addSlide($deck_path, $element){
        $resource = array();
        $resource['type'] = 'slide';
//        //images
        $image_paths = array();
        $img_pattern = '/\.\/upload\/media\/images\/\d+\/\d+\..../';
        preg_match_all($img_pattern, $element->content, $matches);
        if(count($matches[0])){
            foreach($matches[0] as $img_path) {
                $image_paths[] = $img_path;
            }
        }
//        //adding images
        foreach($image_paths as $im){
            $fileNameArray = split('\/',$im);
            $fileName = $fileNameArray[count($fileNameArray)-1];
            copy($im, ROOT . DS . 'tmp' . DS . $this->root_deck_name . DS . $deck_path. DS .$fileName);
            $resource['resources'][] = $deck_path . DS . $fileName;
        }
//        //change urls
        $img_pattern_current = '/\.\/upload\/media\/images\/\d+\//';
        $element->content = preg_replace($img_pattern_current, '', $element->content);
        
        
        //slide html
            $resource['html'] = "
            <!DOCTYPE html>
            <!--[if lt IE 7]> <html class=\"no-js ie6\" lang=\"en\"> <![endif]-->
            <!--[if IE 7]>    <html class=\"no-js ie7\" lang=\"en\"> <![endif]-->
            <!--[if IE 8]>    <html class=\"no-js ie8\" lang=\"en\"> <![endif]-->
            <!--[if gt IE 8]><!-->  <html class=\"no-js\" lang=\"en\"> <!--<![endif]-->
            <head>

            <meta charset=\"utf-8\">
            <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">

            <title>". $element->title ."</title>

            <meta name=\"author\" content=". $element->user->username .">
            <meta name=\"slide_id\" content=". $element->slide_id .">
            <meta name=\"slide_revision_id\" content=". $element->id .">
            <meta name=\"viewport\" content=\"width=1024, user-scalable=no\">


            <!-- Theme CSS files (menu swaps these out) -->
            <link rel=\"stylesheet\" id=\"style-theme-link\" href=\"default.css\">

            <!-- Custom CSS just for this page -->
            <link rel=\"stylesheet\" href=\"deck.js/introduction/introduction.css\">
            <link rel=\"stylesheet\" href=\"local.css\">

            <script src=\"deck.js/modernizr.custom.js\"></script>
            <script src=\"deck.js/jquery.js\"></script>

            <!-- client syntax highlightning -->
            <link   href=\"deck.js/syntax/shThemeDefault.css\" rel=\"stylesheet\" type=\"text/css\" />
            <script src=\"deck.js/syntax/shCore.js\" type=\"text/javascript\"></script>
            <script src=\"deck.js/syntax/shAutoloader.js\" type=\"text/javascript\"></script>
            <script src=\"deck.js/syntax/shBrushTurtle.js\" type=\"text/javascript\"></script>
            <script type=\"text/javascript\" src=\"deck.js/MathJax.js\"></script>

            <script type=\"text/javascript\">
            function htmlDecode(input){
                      var e = document.createElement(\"div\");
                      e.innerHTML = input;
                      return e.childNodes.length === 0 ? \"\" : e.childNodes[0].nodeValue;
            }
            function removeBRs(input){
                    var r=input.replace(/<br>/gi,\"\");
                    r=r.replace(/<br[0-9a-zA-Z]+>/gi,\"\");
                    return r;
            }
                    SyntaxHighlighter.defaults['toolbar'] = false;
                    SyntaxHighlighter.all();
                    </script>
            <script type=\"text/x-mathjax-config\">
            $(function() {
              MathJax.Hub.Config({
                            skipStartupTypeset: true,
                            extensions: [\"tex2jax.js\"],
                            jax: [\"input/TeX\", \"output/HTML-CSS\"],
                            tex2jax: {
                            inlineMath: [ ['$','$'], [\"\\(\",\"\\)\"] ],
                            displayMath: [ ['$$','$$'], [\"\\[\",\"\\]\"] ],
                            processEscapes: true
                            },
                            \"HTML-CSS\": { availableFonts: [\"TeX\"] }
              });
                    $.deck('.slide');
                    MathJax.Hub.Queue([\"Typeset\",MathJax.Hub,'slide-area']);
                    });
                    </script>
        </head>

        <body>
        <div class='deck-container'><div class='slide'> " .
                    $element->content
        . "</div></div></body>
        </html>";
        
        //unification of file names
        $slide_name = $this->sluggify($element->title);
        $i = '';
        if (in_array($slide_name, $this->slide_file_names)){
            $i = 1;
            while(in_array($slide_name.'_'.$i, $this->slide_file_names)){
                $i++;
            }
            $slide_name .= '_'.$i;
        }
        $this->slide_file_names [] = $slide_name;

        //creating resource
        $resource['href'] = $deck_path . DS . $slide_name .  '.html';
        $resource['id'] = $slide_name;
        $resource['title'] = $element->title;

        //adding slide
        $page = ROOT . DS . 'tmp' . DS . $this->root_deck_name . DS . $resource['href'];
        if( !file_exists($page)) {
            $fp = fopen($page, "wb");
            fwrite($fp, $resource['html']);
            fclose ($fp);
        }else{
            unlink($page);
            $fp = fopen($page, "wb");
            fwrite($fp, $resource['html']);
            fclose ($fp);
        }
        return $resource;
    }
    function createResources($resources,$deck){
        
        $deck_name = $this->sluggify($deck->title);
        
        mkdir(ROOT . DS . 'tmp' . DS . $this->root_deck_name . DS . $deck_name);
        $this->createStyle($deck);
        foreach($deck->content as $element){
            $resource = array();
            if (get_class ( $element ) == "Deck"){ 
                $resource['type'] = 'deck';
                $resource['id'] = $this->sluggify($element->title);
                $resource['title'] = $element->title;
                $resource['resources'] = $this->createResources($resource['resources'], $element);
                $resources[] = $resource;
            }else{
                $resource = $this->addSlide($deck_name, $element);
                $resources[] = $resource;
                //first page
//                if (!strlen($this->first_page)){
//                    $this->first_page = $resource['href'];
//                }
            }
           
        }
        
        return $resources;
    }
    function xml_entities($string) {
    return strtr(
        $string, 
        array(
            "<" => "&lt;",
            ">" => "&gt;",
            '"' => "&quot;",
            "'" => "&apos;",
            "&" => "&amp;",
        )
    );
}
    function addResourceToManifest($resources){        
        $manifest_string = '';         
        foreach ($resources as $inner_resource){          
            if (isset($inner_resource['type']) && $inner_resource['type'] == 'slide'){
                $string= '                <item identifier="i_'.$inner_resource['id'].'" identifierref="r_'.$inner_resource['id'].'" isvisible="true">'.chr(13);
                $string.= '                    <title>'. $this->xml_entities($inner_resource['title']) .'</title>'.chr(13);
                $string.= '                </item>'.chr(13);  
                $manifest_string .= $string;
            }elseif (isset($inner_resource['type']) && $inner_resource['type'] == 'deck'){  
                $string='                <item identifier="i_'.$inner_resource['id'].'_deck" isvisible="true">'.chr(13);
                $string.='                    <title>'. $this->xml_entities($inner_resource['title']) .'</title>'.chr(13);
                $string.= $this->addResourceToManifest($inner_resource['resources']);
                $string.='</item>';                 
                $manifest_string .= $string;
            }            
        }
        return $manifest_string;
    }
    function addReferencesToManifest($resources){
        $manifest_string_ref = '';
        foreach ($resources as $inner_resource){            
            if (isset($inner_resource['type']) && $inner_resource['type'] == 'slide'){
                    $string = '';
                    if ($this->format == 'scorm12'){
                        $string.='        <resource identifier="r_'.$inner_resource['id'].'" type="webcontent" adlcp:scormtype="asset" href="'.$inner_resource['href'].'">'.chr(13);
                    }else{
                        $string.='        <resource identifier="r_'.$inner_resource['id'].'" type="webcontent" adlcp:scormType="asset" href="'.$inner_resource['href'].'">'.chr(13);
                    }
                    $string.= '           <file href="' . $inner_resource['href'] .'" />'.chr(13);
                    foreach ($inner_resource['resources'] as $file_resource){
                        $string.= '           <file href="' . $file_resource .'" />'.chr(13);
                    }
                    $string.='        </resource>'.chr(13);
            }
            if (isset($inner_resource['type']) && $inner_resource['type'] == 'deck'){
                $string = '';
                $string.= $this->addReferencesToManifest($inner_resource['resources']);
            }
            $manifest_string_ref.= $string;
        }
        return $manifest_string_ref;
    }
    function createManifest(){
        $image_paths = array();
        $resources = array();
        
        
        //manifest
            switch($this->format){
                    case 'scorm12':
                        $schema = '1.2';
                        $identifier = 'com.scorm.manifesttemplates.scorm12';
                        $location = 'http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd';
                        break;
                    case 'scorm2004_2nd':
                        $schema = 'CAM 1.3';
                        $identifier = 'com.scorm.manifesttemplates.scorm2004.2ndEd';
                        $location = 'http://www.imsglobal.org/xsd/imscp_v1p1 imscp_v1p1.xsd'.chr(13).'http://www.adlnet.org/xsd/adlcp_v1p3 adlcp_v1p3.xsd'.chr(13).'http://www.adlnet.org/xsd/adlseq_v1p3 adlseq_v1p3.xsd'.chr(13).'http://www.adlnet.org/xsd/adlnav_v1p3 adlnav_v1p3.xsd'.chr(13).'http://www.imsglobal.org/xsd/imsss imsss_v1p0.xsd'.chr(13).'http://ltsc.ieee.org/xsd/LOM lom.xsd';
                        break;
                    case 'scorm2004_3rd':
                        $schema = '2004 3rd Edition';
                        $identifier = 'com.scorm.manifesttemplates.scorm2004.3rdEd';
                        $location = 'http://www.imsglobal.org/xsd/imscp_v1p1 imscp_v1p1.xsd'.chr(13).'http://www.adlnet.org/xsd/adlcp_v1p3 adlcp_v1p3.xsd'.chr(13).'http://www.adlnet.org/xsd/adlseq_v1p3 adlseq_v1p3.xsd'.chr(13).'http://www.adlnet.org/xsd/adlnav_v1p3 adlnav_v1p3.xsd'.chr(13).'http://www.imsglobal.org/xsd/imsss imsss_v1p0.xsd'.chr(13).'http://ltsc.ieee.org/xsd/LOM lom.xsd';
                        break;
                    case 'scorm2004_4th':
                        $identifier = 'com.scorm.manifesttemplates.scorm2004.4thEd';
                        $location = 'http://www.imsglobal.org/xsd/imscp_v1p1 imscp_v1p1.xsd'.chr(13).'http://www.adlnet.org/xsd/adlcp_v1p3 adlcp_v1p3.xsd'.chr(13).'http://www.adlnet.org/xsd/adlseq_v1p3 adlseq_v1p3.xsd'.chr(13).'http://www.adlnet.org/xsd/adlnav_v1p3 adlnav_v1p3.xsd'.chr(13).'http://www.imsglobal.org/xsd/imsss imsss_v1p0.xsd'.chr(13).'http://ltsc.ieee.org/xsd/LOM lom.xsd';
                        $schema = '2004 4th Edition';
                        break;
                    case 'tincan':
                        $schema = 'Tin Can';
                        $identifier = 'com.scorm.manifesttemplates.tincan';
                        $location = 'http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd'.chr(13).'http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd'.chr(13).'http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd';
                        break;
                    default:
                        $schema = '2004 3rd Edition';
                        $identifier = 'com.scorm.manifesttemplates.scorm2004.3rdEd';
                        $location = 'http://www.imsglobal.org/xsd/imscp_v1p1 imscp_v1p1.xsd'.chr(13).'http://www.adlnet.org/xsd/adlcp_v1p3 adlcp_v1p3.xsd'.chr(13).'http://www.adlnet.org/xsd/adlseq_v1p3 adlseq_v1p3.xsd'.chr(13).'http://www.adlnet.org/xsd/adlnav_v1p3 adlnav_v1p3.xsd'.chr(13).'http://www.imsglobal.org/xsd/imsss imsss_v1p0.xsd'.chr(13).'http://ltsc.ieee.org/xsd/LOM lom.xsd';
                }
                
                //references to schemas
                $manifest = '<?xml version="1.0" standalone="no" ?>'.chr(13);
                $manifest.='    <manifest identifier="'.$identifier.'" version="1"'.chr(13);
                $manifest.='        xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2"'.chr(13); 
		$manifest.='				          xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2"'.chr(13);
		$manifest.='				          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.chr(13);
                $manifest.='        xsi:schemaLocation="'.$location.'">'.chr(13);
                //manifest metadata
                $manifest.='    <metadata>'.chr(13);
                $manifest.='        <schema>ADL SCORM</schema>'.chr(13);
                $manifest.='        <schemaversion>'.$schema.'</schemaversion>'.chr(13);
                $manifest.='        <adlcp:location>metadata_course.xml</adlcp:location>'.chr(13);
                $manifest.='    </metadata>'.chr(13);

                //organization
                $manifest.='    <organizations default="B0">'.chr(13);
                $manifest.='            <organization identifier="B0">'.chr(13);
                $manifest.='                <title>'. $this->xml_entities($this->title). '</title>'.chr(13);
                $manifest.='                <item identifier="i_' . $this->xml_entities($this->deck_name) . '_deck" isvisible="true">'.chr(13);
                $manifest.='                    <title>'. $this->xml_entities($this->title) .'</title>'.chr(13);
                $manifest.='                    <metadata>'.chr(13);
                $manifest.='                        <adlcp:location>metadata_course.xml</adlcp:location>'.chr(13);
                $manifest.='                    </metadata>'.chr(13);
                
                //content
                $manifest.= $this->addResourceToManifest($this->resources);                 
                $manifest.='                </item>'.chr(13);
                $manifest.='        </organization>'.chr(13);
                $manifest.='    </organizations>'.chr(13);

                //resources
                $manifest.='    <resources>'.chr(13);
                $manifest.= $this->addReferencesToManifest($this->resources);
                                                
                if ($format == 'scorm12'){
                    $manifest.='<resource identifier="common_files" type="webcontent" adlcp:scormtype="asset">'.chr(13);
                }else{
                    $manifest.='<resource identifier="common_files" type="webcontent" adlcp:scormType="asset">'.chr(13);
                }
                foreach($this->common_resources as $resource){
                    $manifest.= '           <file href="' . $this->xml_entities($resource) .'" />'.chr(13);
                }                
                $manifest.='        </resource>'.chr(13);
                $manifest.='    </resources>'.chr(13);
                $manifest.='</manifest>';
                $this->manifest = $manifest;
                $page = ROOT . DS . 'tmp' . DS . $this->root_deck_name . DS . 'imsmanifest.xml';
                if( !file_exists($page)) {
                    $fp = fopen($page, "wb");
                    fwrite($fp, $this->manifest);
                    fclose ($fp);
                }else{
                    unlink($page);
                    $fp = fopen($page, "wb");
                    fwrite($fp, $this->manifest);
                    fclose ($fp);
                }
                
    }

    function createCourseMetadata(){
        //course metadata
        $deck = $this->deck;
            $language_arr = $deck->getLanguage();
            $language = $language_arr['id'];
            $metadata_course = '<?xml version="1.0" ?>'.chr(13);
            $metadata_course .= '<lom xmlns="http://ltsc.ieee.org/xsd/LOM" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ltsc.ieee.org/xsd/LOM lom.xsd">'.chr(13);
            //general
            $metadata_course .= '<general>'.chr(13);
            $metadata_course .= '<identifier>'.chr(13);
            $metadata_course .= '<catalog>URI</catalog>'.chr(13);
            $metadata_course .= '<entry>slidewiki.metadata</entry>'.chr(13);
            $metadata_course .= '</identifier>'.chr(13);

            $metadata_course .= '<title>'.chr(13);
            $metadata_course .= '<string language="'.$language.'">'.$deck->title.'</string>'.chr(13);
            $metadata_course .= '</title>'.chr(13);

            $metadata_course .= '<language>'.$language.'</language>'.chr(13);
            if (strlen($deck->abstract)){
                $metadata_course .= '<description>'.chr(13);
                $metadata_course .= '    <string language="'.$language.'">'.$deck->abstract.'</string>'.chr(13);
                $metadata_course .= '</description>'.chr(13);
            }
            $metadata_course .= '<keyword>'.chr(13);
            $metadata_course .= '<string language="'.$language.'">'.$deck->title.'</string>'.chr(13);
            $metadata_course .= '</keyword>'.chr(13);
            if (strlen($deck->tags)){
                $tags = array();
                $tags = explode(',',$deck->tags);
                foreach($tags as $tag){
                    $metadata_course .= '<keyword>'.chr(13);
                    $metadata_course .= '<string language="'.$language.'">'.$tag.'</string>'.chr(13);
                    $metadata_course .= '</keyword>'.chr(13);
                }
            }
            $metadata_course .= '<structure>'.chr(13);
            $metadata_course .= '    <source>LOMv1.0</source>'.chr(13);
            $metadata_course .= '    <value>hierarchical</value>'.chr(13);
            $metadata_course .= '</structure>'.chr(13);

            $metadata_course .= '<aggregationLevel>'.chr(13);
            $metadata_course .= '    <source>LOMv1.0</source>'.chr(13);
            $metadata_course .= '    <value>1</value>'.chr(13);
            $metadata_course .= '</aggregationLevel>'.chr(13);
            $metadata_course .= '</general>'.chr(13);
            //lifecycle
            $metadata_course .= '<lifeCycle>'.chr(13);
            $metadata_course .= '<version>'.chr(13);
            $metadata_course .= '<langstring>1</langstring>'.chr(13);
            $metadata_course .= '</version>'.chr(13);
            $metadata_course .= '<status>'.chr(13);
            $metadata_course .= '<source>'.chr(13);
            $metadata_course .= '<langstring xml:lang="x-none">LOMv1.0</langstring>'.chr(13);
            $metadata_course .= '</source>'.chr(13);
            $metadata_course .= '<value>'.chr(13);
            $metadata_course .= '<langstring xml:lang="x-none">Final</langstring>'.chr(13);
            $metadata_course .= '</value>'.chr(13);
            $metadata_course .= '</status>'.chr(13);
            $metadata_course .= '</lifeCycle>'.chr(13);

            $metadata_course .= '<metametadata>'.chr(13);
            $metadata_course .= '    <metadatascheme>ADL SCORM 1.2</metadatascheme>'.chr(13);
            $metadata_course .= '</metametadata>'.chr(13);
            //technical
            $metadata_course .= '<technical>'.chr(13);
            $types = array();
            foreach($this->resources as $resource){
                $type_arr = explode('.',$resource['href']);
                $type = $type_arr[count($type_arr)-1];
                if (!in_array($type, $types)){
                    $types[] = $type;
                }
            }
            foreach($this->common_resources as $resource){
                $type_arr = explode('.',$resource);
                $type = $type_arr[count($type_arr)-1];
                if (!in_array($type, $types)){
                    $types[] = $type;
                }
            }
            foreach($types as $type){
                switch($type){
                    case 'html' :
                        $file_format .= '   <format>text/html</format>'.chr(13);
                        break;
                    case 'js' :
                         $file_format .= '   <format>application/x-javascript</format>'.chr(13);
                         break;
                     case 'css' :
                         $file_format .= '   <format>text/css</format>'.chr(13);
                         break;
                     case 'jpg' :
                         $file_format .= '   <format>image/jpeg</format>'.chr(13);
                         break;
                     case 'png' :
                         $file_format .= '   <format>image/png</format>'.chr(13);
                         break;
                     default :
                         $file_format .= '   <format>'.$type.'</format>'.chr(13);
                }
            }
            $metadata_course .= $file_format;
            $metadata_course .= '<location>'.$this->first_page.'</location>'.chr(13);
            $metadata_course .= '<pkgprop:ScormEnginePackageProperties xmlns="http://www.scorm.com/ScormEnginePackageProperties">'.chr(13);
            $metadata_course .= '        <appearance>'.chr(13);
            $metadata_course .= '            <displayStage>'.chr(13);
            $metadata_course .= '                <desired>'.chr(13);
            $metadata_course .= '                    <width>750</width>'.chr(13);
            $metadata_course .= '                    <height>550</height>'.chr(13);
            $metadata_course .= '                    <fullscreen>no</fullscreen>'.chr(13);
            $metadata_course .= '                </desired>'.chr(13);
            $metadata_course .= '                <required>'.chr(13);
            $metadata_course .= '                    <width>0</width>'.chr(13);
            $metadata_course .= '                    <height>0</height>'.chr(13);
            $metadata_course .= '                    <fullscreen>no</fullscreen>'.chr(13);
            $metadata_course .= '                </required>'.chr(13);
            $metadata_course .= '            </displayStage>'.chr(13);
            $metadata_course .= '        </appearance>'.chr(13);
            $metadata_course .= '        <behavior>'.chr(13);
            $metadata_course .= '            <alwaysFlowToFirstSco>yes</alwaysFlowToFirstSco>'.chr(13);
            $metadata_course .= '            <scoreOverridesStatus>no</scoreOverridesStatus>'.chr(13);
            $metadata_course .= '            <validateInteractionTypes>yes</validateInteractionTypes>'.chr(13);
            $metadata_course .= '        </behavior>'.chr(13);
            $metadata_course .= '    </pkgprop:ScormEnginePackageProperties>'.chr(13);
            $metadata_course .= '</technical>'.chr(13);
            $metadata_course .= '<rights>'.chr(13);
            $metadata_course .= '<cost>'.chr(13);
            $metadata_course .= '            <source>'.chr(13);
            $metadata_course .= '                <langstring xml:lang="x-none">LOMv1.0</langstring>'.chr(13);
            $metadata_course .= '            </source>'.chr(13);
            $metadata_course .= '            <value>'.chr(13);
            $metadata_course .= '                <langstring xml:lang="x-none">yes</langstring>'.chr(13);
            $metadata_course .= '            </value>'.chr(13);
            $metadata_course .= '        </cost>'.chr(13);
            $metadata_course .= '        <copyrightandotherrestrictions>'.chr(13);
            $metadata_course .= '            <source>'.chr(13);
            $metadata_course .= '                <langstring xml:lang="x-none">LOMv1.0</langstring>'.chr(13);
            $metadata_course .= '            </source>'.chr(13);
            $metadata_course .= '            <value>'.chr(13);
            $metadata_course .= '                <langstring xml:lang="x-none">yes</langstring>'.chr(13);
            $metadata_course .= '            </value>'.chr(13);
            $metadata_course .= '        </copyrightandotherrestrictions>'.chr(13);
            $metadata_course .= '    </rights>'.chr(13);
            $metadata_course .= '    <classification>'.chr(13);
            $metadata_course .= '        <purpose>'.chr(13);
            $metadata_course .= '            <source>'.chr(13);
            $metadata_course .= '                <langstring xml:lang="x-none">LOMv1.0</langstring>'.chr(13);
            $metadata_course .= '            </source>'.chr(13);
            $metadata_course .= '            <value>'.chr(13);
            $metadata_course .= '                <langstring xml:lang="x-none">Educational Objective</langstring>'.chr(13);
            $metadata_course .= '            </value>'.chr(13);
            $metadata_course .= '        </purpose>'.chr(13);
            if (strlen($deck->abstract)){
                $metadata_course .= '<description>'.chr(13);
                $metadata_course .= '    <string language="'.$language.'">'.$deck->abstract.'</string>'.chr(13);
                $metadata_course .= '</description>'.chr(13);
            }
            $metadata_course .= '<keyword>'.chr(13);
            $metadata_course .= '<string language="'.$language.'">'.$deck->title.'</string>'.chr(13);
            $metadata_course .= '</keyword>'.chr(13);
            if (strlen($deck->tags)){
                $tags = array();
                $tags = explode(',',$deck->tags);
                foreach($tags as $tag){
                    $metadata_course .= '<keyword>'.chr(13);
                    $metadata_course .= '<string language="'.$language.'">'.$tag.'</string>'.chr(13);
                    $metadata_course .= '</keyword>'.chr(13);
                }
            }
            $metadata_course .= '    </classification>'.chr(13);
            $metadata_course .= '</lom>'.chr(13);
            $this->metadata_course = $metadata_course;
            $page = ROOT . DS . 'tmp' . DS . $this->root_deck_name . DS . 'metadata_course.xml';
                if( !file_exists($page)) {
                    $fp = fopen($page, "wb");
                    fwrite($fp, $this->metadata_course);
                    fclose ($fp);
                }else{
                    unlink($page);
                    $fp = fopen($page, "wb");
                    fwrite($fp, $this->metadata_course);
                    fclose ($fp);
                }
    }

    
}

?>
