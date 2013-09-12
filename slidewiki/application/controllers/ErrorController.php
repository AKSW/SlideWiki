<?php
class ErrorController extends Controller {
	
	public function show() {
		$err_code= @$_GET ['code'];
		switch($err_code){
			case 400: //Bad Request
			    $error_title="The request could not be understood by the server due to malformed syntax. The client SHOULD NOT repeat the request without modifications.";
				$error_response="";
				header('HTTP/1.0 400 Bad Request');
				break;
			case 401: //Unauthorized
			    $error_title="The request requires user authentication. ";
				$error_response="";
				header('HTTP/1.0 401 Unauthorized');
				break;
			case 403: //Forbidden
			    $error_title="The server understood the request, but is refusing to fulfill it.";
				$error_response="";
				header('HTTP/1.0 403 Forbidden');
				break;	
			case 404: //Not Found
			    $error_title="The server has not found any resources matching your request.";
				$error_response="";
				header('HTTP/1.0 404 Not Found');
				break;		
			case 405: //Method Not Allowed
			    $error_title="The method specified in the Request-Line is not allowed for the resource identified by the Request-URI.";
				$error_response="";
				header('HTTP/1.0 405 Method Not Allowed');
				break;	
			case 406: //Not Acceptable
			    $error_title="The resource identified by the request is only capable of generating response entities which have content characteristics not acceptable according to the accept headers sent in the request.";
				$error_response="";
				header('HTTP/1.0 406 Not Acceptable');
				break;																			
			default:
				$error_title="An unknown error happened when handling your request!";
				$error_response="";
		}
		$this->set ( 'error_title', $error_title );
		$this->set ( 'error_response', $error_response );
	}

}