<?php

class AuthController extends Controller {	
	
	function logout() {
		SlideWikiAuth::logout();
	}
	
	
}