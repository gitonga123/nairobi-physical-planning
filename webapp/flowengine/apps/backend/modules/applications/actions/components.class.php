<?php

class applicationsComponents extends sfComponents
{
	public function executeIfcview()
	{
		//NEEDED FOR VIEW
		$response = $this->getResponse();
		
		$csp = "default-src 'self'; frame-src 'self' http://localhost:*";
        $response->setHttpHeader("Content-Security-Policy", $csp);
        $response->setHttpHeader("X-Content-Security-Policy", $csp);
        $response->setHttpHeader("X-WebKit-CSP", $csp);
	}
}