<?php 
$url = "https://revenueapi.amkatek.com/flowengine/malipoapi/v1.0/payment/request/link" ;
                // Request data
                $data = array(
                    'phoneNumber' => $phonenumber,
                    'description' => $form->getFormName(),
                    'checkoutItems' => array(
                        array(
                            'name' => $form->getFormName(),
                            'unitPrice' => $invoice->getTotalAmount(),
                            'quantity' => 1,
                            'description' => $form->getFormName()
                        )
                    )
                );

                // Convert data to JSON
                $jsonData = json_encode($data);

                // cURL options
                $options = array(
                    CURLOPT_URL => $url,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $jsonData,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($jsonData)
                    )
                );

                // Initialize cURL session
                $curl = curl_init();
                curl_setopt_array($curl, $options);

                // Execute the request
                $response = curl_exec($curl);

                // Check for errors
                if ($response === false) {
                    $error = curl_error($curl);
                    // Handle the error accordingly
                    die("cURL Error: " . $error);
                }

                // Close cURL session
                curl_close($curl);
                error_log("------ Debug ---- Response ----- ".$response) ;
                exit;