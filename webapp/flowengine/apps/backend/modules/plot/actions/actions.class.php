<?php

/**
 * House the validation actions performed in the various matchform data
 * 
 * @version   1.0.0
 */
class plotActions extends sfActions {
    /**
     * Called to render the list of plots that are available in the system
     */
    public function executeIndex(sfWebRequest $request) {
        // When processing ajax requests
        if ( $request->isXmlHttpRequest() ) {
			error_log('--------XML-----');
            // The response to send
            $response = $this->getResponse();

            // When fetching table information
            if ( "GET" === $request->getMethod() ) {
                $dt = new SDataTables($request);
                $dt ->select("
                a.owner_name,a.owner_phone,a.plot_no,a.block_number,a.ward,a.plot_size, a.plot_size_ha, a.property_usage, a.amount_land_rates,a.id
                ")
                    ->from("plot", "a");
                
                $response->setContent( json_encode (
                    $dt->showTable()
                ));
            }

            // Fetch the requested record
            else {
                // If the request is not valid, end here
                if ( !$this->validateRequest() ) {
                    $response->setStatusCode(500);
                    $response->setContent( json_encode ( array (
                        'status' => false,
                        'msg'    => 'Your request is not valid'
                    )));

                    // End here
                    return sfView::NONE;
                }


                // Fetch one record from the db
                $row = Doctrine_Core::getTable('Plot')->find($request->getParameter('index'));
                if ( $row !== false ) {
                    $row = array (
                        'id'           => $row->getId(),
                        'upn'       => $row->getUpn(),
                       /*  'parentUpn'       => $row->getParentUpn(), */
                        'physicalAddress'       => $row->getPhysicalAddress(),
                        /* 'measurements'       => $row->getMeasurements(), */
                       /*  'poBox'       => $row->getPoBox(), */
                       /*  'postalCode'       => $row->getPostalCode(),
                        'email'       => $row->getEmail(), */
                        'amountLandRates'       => $row->getAmountLandRates(),
                       /*  'customerSupplierId'       => $row->getCustomerSupplierId(), */
                      /*   'town'       => $row->getTown(), */
                        'ownerName'       => $row->getOwnerName(),
                        'ownerPhone'       => $row->getOwnerPhone(),
                        'plotNo'       => $row->getPlotNo(),
                        'blockNumber'       => $row->getBlockNumber(),
                        'plotSizeHa'       => $row->getPlotSizeHa(),
                        'ward'       => $row->getWard(),
                        'propertyUsage' => $row->getPropertyUsage(),
                        'plotSize'     => $row->getPlotSize(),
                        
                    );
                }

                // Send the csrf token
                $response->setHttpHeader("X-Code", $this->getToken());

                // Send the response
                $response->setContent( json_encode ( array (
                    'status' => is_array($row),
                    'row'    => $row
                )));
            }

            // All ajax requests end without rendering the view
            return sfView::NONE;
        }

        // Set the layout to use
        $this->setLayout("layout-settings");
        $this->csrfToken = $this->getToken();
    }

    /**
     * Called to save the plot details
     * 
     * @param   request
     * @param   form
     */
    public function executeSave(sfWebRequest $request) {
        // Only ajax posts are allowed
        if ( !$request->isXmlHttpRequest() ) {
            $this->redirect('/backend.php/plot/index');
            return sfView::NONE;
        }

        // The response to send
        $response = $this->getResponse();

        // Get the post data
        $form = $request->getParameter("plot");
        if ( $form['id'] !== '' ) {
            $form = Doctrine_Core::getTable('Plot')->find($form['id']);
            if ( $form !== false ) $form = new PlotForm($form);
        }
        else {
            $form = new PlotForm();
        }

        // When the record could not be found
        if ( is_bool($form) ) {
            $response->setContent( json_encode ( array (
                'status' => false,
                'msg'    => 'The specified record could not be located in the database.'
            )));

            return sfView::NONE;
        }

        // Fetch the form data
        $form->bind($request->getParameter($form->getName()));

        // Save the form if the form is valid
        if ($form->isValid()) {
            // Save the form 
            $plot = $form->save();

            // Update the Audit log
            (new Audit())->saveAudit("", "updated plot of id ".$plot->getId());

            // Set the response
            $response->setContent( json_encode ( array (
                'status' => true,
                'msg'    => 'You have successfully updated the specified plot details.'
            )));
        }

        // When an error occurs while saving the form details
        else {
            $error = array ();
            foreach ( $form as $key=>$field ) {
                if ( $field->hasError() ) {
                    $str = $field->renderError();
                    if ( !empty($str) && $str !== "" )
                        $error[$key] = $str;
                }
            }

            $response->setContent( json_encode ( array (
                'status' => false,
                'msg'    => 'An error occured while trying to update the specified plot details.',
                'error'  => $error
            )));
        }

        // Send the csrf token
        $response->setHttpHeader("X-Code", $this->getToken());

        // Show no page content
        return sfView::NONE;
    }

    /**
     * Called to delete a given plot from the db
     */
    public function executeDelete(sfWebRequest $request) {
        // Only ajax posts are allowed
        if ( !$request->isXmlHttpRequest() ) {
            $this->redirect('/backend.php/plot/index');
            return sfView::NONE;
        }

        // The response to send
        $response = $this->getResponse();

        // If the request is not valid, end here
        if ( !$this->validateRequest() ) {
            $response->setStatusCode(500);
            $response->setContent( json_encode ( array (
                'status' => false,
                'msg'    => 'Your request is not valid'
            )));

            // End here
            return sfView::NONE;
        }

        // Fetch one record from the db
        $plot = Doctrine_Core::getTable('Plot')->find($request->getParameter('index'));

        // When the record could not be found
        if ( is_bool($plot) ) {
            $response->setContent( json_encode ( array (
                'status' => false,
                'msg'    => 'The specified record could not be located in the database.'
            )));

            return sfView::NONE;
        }

        // Delete the record after performing an audit log
        $msg = "You have successfully deleted the plot identified by '{$plot->getPlotNo()}'";
        (new Audit())->saveAudit("", "deleted plot of id ".$plot->getId());
        $plot->delete();

        // Send the csrf token
        $response->setHttpHeader("X-Code", $this->getToken());

        // Send the success message and end
        $response->setContent( json_encode ( array (
            'status' => true,
            'msg'    => $msg
        ))); 

        return sfView::NONE;
    }

    /**
     * Called to validate the plot number
     * 
     * [host]/backend.php/plot/validate?q=<plot number>
     */
    public function executeValidate(sfWebRequest $request) {
        // If there is a parameter to indicate that we are to work with response
        $plot = preg_replace('/[^\d]/S', '', $request->getParameter('q'));
        $message = "The plot number has not been specified.";
        $color = "#f29b11";

        // If there is no plot information
        if ( '' !== $plot ) {
            // Run the db query to check if the record is active or not
            $row = Doctrine_Query::create()->getConnection()->execute(
                "SELECT * FROM plot a WHERE a.plot_no = :plot",
                array('plot'=>$plot)
            )->fetch(\PDO::FETCH_ASSOC);

            // If the plot does not exist
            if ( false === $row ) {
                $message = "This plot does not exist.";
            }

            // If the plot is black listed
            else if ( 'Black-Listed' == $row['plot_status'] ) {
                $message = "This plot has been black-listed.";
                $color = "#e86051";
            }

            // All is well
            else {
                $message = "This is a valid plot number.";
                $color = "#29ba9b";
            }
        }

        // Set the content
       // $this->getResponse()->setContent("<span style='color:{$color}'>{$message}</span>");
        $this->getResponse()->setContent(json_encode($row));
       

        // Disable page rendering
        return sfView::NONE;
    }

    /**
     * Called to validate the ajax request made
     * 
     * @return  Boolean
     */
    private function validateRequest() {
        // Get the headers
        $headers = getallheaders();

        // Use the symfony base form the validate the token
        $form = new PlotForm();
        $form->bind( array (
            $form->getCSRFFieldName() => $headers['X-Code']
        ));

        // Check the token
        return $form->isValid();
    }

    /**
     * Get a valid token to use with subsequent requests
     * 
     * @return  String
     */
    private function getToken() {
        $form = new PlotForm();
        return $form[$form->getCSRFFieldName()]->getValue();
    }
}