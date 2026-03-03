<?php
/**
 * Help actions.
 *
 * Displays help content for clients
 *
 * @package    frontend
 * @subpackage sharedapplication
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

class helpActions extends sfActions
{
	/**
	* Executes 'Faq' action
	*
	* Displays Frequently Asked Questions
	*
	* @param sfRequest $request A request object
	*/
	public function executeFaq(sfWebRequest $request)
	{
		$this->setLayout("layout-mentor");
	}
          
    /**
	* Executes 'Contact' action
	*
	* Displays contact form for clients
	*
	* @param sfRequest $request A request object
	*/
	public function executeContact(sfWebRequest $request)
	{
		$this->sent = false; 
		$this->form = new ContactForm();
		if ($request->isMethod('post'))
		{
			$this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));
			if ($this->form->isValid())
			{
				 $name = $this->form->getValue('name');
				 $email = $this->form->getValue('email');
				 $mobile = $this->form->getValue('mobile');
				 $subject = $this->form->getValue('subject');
				 $message = $this->form->getValue('message');
				 $body = "From: {$name} ({$email} / {$mobile}). <br><br> {$message}";
				 error_log('-------BODY------'.$body);
				 $site_settings = Functions::site_settings();
	 
				 $this->notifier = new mailnotifications();
				 $this->notifier->sendemail("",$site_settings->getOrganisationEmail(),$subject,$body);
				 $this->notifier->sendemail("","mutwiridanielsci@gmail.com",$subject,$body);
				 $this->notifier->sendemail("",$email,"Contact Us:  ".$subject,"Thank you!. {$name}<br /> Your message has been received. We will get back to you shortly");
	 
				 $this->sent = true;
			}
		}

		$this->setLayout("layout-mentor");
	}
}
