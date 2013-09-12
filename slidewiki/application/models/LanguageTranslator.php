<?php

class LanguageTranslator
    {
        // this is the API endpoint, as specified by Google
        const ENDPOINT = 'https://www.googleapis.com/language/translate/v2';
        const ENDPOINTDETECT = 'https://www.googleapis.com/language/translate/v2/detect';
        const ENDPOINTLANG = 'https://www.googleapis.com/language/translate/v2/languages';
 
        // holder for you API key, specified when an instance is created
        protected $_apiKey;
        public $languages;
 
        // constructor, accepts Google API key as its only argument
        public function __construct($apiKey)
        {
            $this->_apiKey = $apiKey;
            $this->languages = $this->languages();
        }
        
        public function detect($data){
            if (strlen($data) >= 100){
                $length = 100;
            }else{
                $length = strlen($data);
            }
            $small_data = substr($data, 0, $length);
            $values = array(
                'key'    => $this->_apiKey,
                'q'      => $small_data
            );
            $language = 'en';
            // turn the form data array into raw format so it can be used with cURL
            $formData = http_build_query($values);
 
            // create a connection to the API endpoint
            $ch = curl_init(self::ENDPOINTDETECT);
 
            // tell cURL to return the response rather than outputting it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            //for the problems of SSL
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
            // write the form data to the request in the post body
            curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
 
            // include the header to make Google treat this post request as a get request
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
 
            // execute the HTTP request
            $json = curl_exec($ch);
           
            curl_close($ch);
            
            // decode the response data
            $data = json_decode($json, true);
            // ensure the returned data is valid
            if (!is_array($data) || !array_key_exists('data', $data)) {
                throw new Exception('Unable to find data key');
            }
 
            // ensure the returned data is valid
            if (!array_key_exists('detections', $data['data'])) {
                throw new Exception('Unable to find detections key');
            }
 
            if (!is_array($data['data']['detections'])) {
                throw new Exception('Expected array for detections');
            }
            // loop over the translations and return the first one.
            // if you wanted to handle multiple translations in a single call
            // you would need to modify how this returns data
            $conf = $data['data']['detections'][0][0]['confidence'];
            foreach ($data['data']['detections'] as $detection) {
                if ($detection[0]['confidence'] >= $conf){
                    $conf = $detection[0]['confidence'];
                    $language = $detection[0]['language'];
                }
            }
            return $language;
            // assume failure since success would've returned just above
            throw new Exception('Translation failed');
            
        }
        public function getLanguageName($language){
            $languages = array();
            $values = array(
                'key'    => $this->_apiKey,
                'target' => 'en'
            );
            
            // turn the form data array into raw format so it can be used with cURL
            $formData = http_build_query($values);
 
            // create a connection to the API endpoint
            $ch = curl_init(self::ENDPOINTLANG);
 
            // tell cURL to return the response rather than outputting it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            //for the problems of SSL
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
            // write the form data to the request in the post body
            curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
 
            // include the header to make Google treat this post request as a get request
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
 
            // execute the HTTP request
            $json = curl_exec($ch);
           
            curl_close($ch);
            
            // decode the response data
            $data = json_decode($json, true);
            // ensure the returned data is valid
            if (!is_array($data) || !array_key_exists('data', $data)) {
                throw new Exception('Unable to find data key');
            }else{
                $languages = $data['data']['languages']; 
                foreach ($languages as $full_lang){
                    if ($full_lang['language'] == $language){
                        return $full_lang['name'];
                    }
                }
            }            
        }
        public function languages(){
            $languages = array();
            $values = array(
                'key'    => $this->_apiKey
            );
            
            // turn the form data array into raw format so it can be used with cURL
            $formData = http_build_query($values);
 
            // create a connection to the API endpoint
            $ch = curl_init(self::ENDPOINTLANG);
 
            // tell cURL to return the response rather than outputting it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            //for the problems of SSL
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
            // write the form data to the request in the post body
            curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
 
            // include the header to make Google treat this post request as a get request
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
 
            // execute the HTTP request
            $json = curl_exec($ch);
           
            curl_close($ch);
            
            // decode the response data
            $data = json_decode($json, true);
            // ensure the returned data is valid
            if (!is_array($data) || !array_key_exists('data', $data)) {
                throw new Exception('Unable to find data key');
            }else{
                foreach ($data['data']['languages'] as $language) {
                    $languages[] = $language['language'];
                }
                return $languages;
            }            
        }
 
        // translate the text/html in $data. Translates to the language
        // in $target. Can optionally specify the source language
        public function translate($data, $target, $source = ''){
            if (!$source || !in_array($source, $this->languages)){
                return -1;
            }    
            // this is the form data to be included with the request
            $values = array(
                'key'    => $this->_apiKey,
                'target' => $target,
                'q'      => $data
            );
            // only include the source data if it's been specified
            if (strlen($source) > 0) {
                $values['source'] = $source;
            }
 
            // turn the form data array into raw format so it can be used with cURL
            $formData = http_build_query($values);
 
            // create a connection to the API endpoint
            $ch = curl_init(self::ENDPOINT);
 
            // tell cURL to return the response rather than outputting it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            //for the problems of SSL
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
            // write the form data to the request in the post body
            curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
 
            // include the header to make Google treat this post request as a get request
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
 
            // execute the HTTP request
            $json = curl_exec($ch);
            curl_close($ch);
            
            // decode the response data
            $data = json_decode($json, true);
            // ensure the returned data is valid
            if (!is_array($data) || !array_key_exists('data', $data)) {
                
                throw new Exception('Unable to find data key');
            }
 
            // ensure the returned data is valid
            if (!array_key_exists('translations', $data['data'])) {
                throw new Exception('Unable to find translations key');
            }
 
            if (!is_array($data['data']['translations'])) {
                throw new Exception('Expected array for translations');
            }
 
            // loop over the translations and return the first one.
            // if you wanted to handle multiple translations in a single call
            // you would need to modify how this returns data
            foreach ($data['data']['translations'] as $translation) {
                return $translation['translatedText'];
            }
 
            // assume failure since success would've returned just above
            throw new Exception('Translation failed');
        }
    }
?>
