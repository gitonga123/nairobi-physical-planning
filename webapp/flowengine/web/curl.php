<?php

        // create curl resource
        $ch = curl_init();
        // set url
        curl_setopt($ch, CURLOPT_URL, "https://integrations-test.zizi.co.ke/api/v1/login"); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$encoded=base64_encode('XZERLCZFLGVQJCMG:BK#&U=QUUSYD&EXVKGDHFZCR&VRTANHT'); 
		curl_setopt($ch, CURLOPT_HTTPHEADER,array(
			"Authorization: Basic {$encoded}"
		));
		
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);
		$error='';
		if (curl_errno($ch)) {
		   $error = curl_error($ch);
		}
		echo $output.'\n';
		echo $error.'\n';
        // close curl resource to free up system resources
        curl_close($ch);      