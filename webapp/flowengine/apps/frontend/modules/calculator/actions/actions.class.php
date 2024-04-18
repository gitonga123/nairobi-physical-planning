<?php
class calculatorActions extends sfActions
{
	  public function executeIndex(sfWebRequest $request)
	  {
		  $this->filter = $request->getParameter("filter");
		  $this->appform = $request->getParameter("appform");
		  $this->application_form = array();
		  foreach($request->getPostParameters() as $key => $value){
				$this->application_form[$key] = $value;
		  }
		  $this->setLayout("layoutdash");
	  }
}
