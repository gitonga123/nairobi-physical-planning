<?php
/**
 *
 * Cart class that will manage the creation and modification of carts
 *
 * Created by PhpStorm.
 * User: thomasjuma
 * Date: 11/19/14
 * Time: 12:28 AM
 */

class CartManager {

    protected $cart;
    protected $token_manager;
    protected $application_manager;

    //Public constructor for the cart manager class
    public function __construct($token, $user_id)
    {
      $this->token_manager = new TokenManager();
      $this->application_manager = new ApplicationManager();

      //Create a cart if it does not exist
      $q = Doctrine_Query::create()
         ->from("Cart a")
         ->where("a.user_id = ?", $user_id);

      if($q->count() == 0)
      {
        $this->cart = new Cart();
        $this->cart->setUserId($user_id);
        $this->cart->setToken($token);
        $this->cart->save();
      }
      else
      {
        $this->cart = $q->fetchOne();
      }

    }

    public function add_to_cart($invoice_id)
    {
      //Fetch check if invoice already exists in cart
      $q = Doctrine_Query::create()
         ->from("CartItem a")
         ->where("a.cart_id = ?", $this->cart->getId())
         ->andWhere("a.invoice_id = ?", $invoice_id);

      if($q->count() == 0)
      {
        $cart_item = new CartItem();
        $cart_item->setCartId($this->cart->getId());
        $cart_item->setInvoiceId($invoice_id);
        $cart_item->save();

        return true;
      }
      else
      {
        //Invoice is already in the cart
        return true;
      }
    }

    public function remove_from_cart($invoice_id)
    {
      //Fetch check if invoice already exists in cart
      $q = Doctrine_Query::create()
         ->from("CartItem a")
         ->where("a.cart_id = ?", $this->cart->getId())
         ->andWhere("a.invoice_id = ?", $invoice_id);

      if($q->count() == 0)
      {
        // Invoice already removed
        return true;
      }
      else {
        $cart_item = $q->fetchOne();
        $cart_item->delete();

        return true;
      }
    }

    public function clear_cart()
    {
      $q = Doctrine_Query::create()
         ->from("CartItem a")
         ->where("a.cart_id = ?", $this->cart->getId());
      $cart_items = $q->execute();

      foreach($cart_items as $cart_item)
      {
        $cart_item->delete();
      }

      return true;
    }

    public function clear_invalid_invoices()
    {
      $q = Doctrine_Query::create()
         ->from("CartItem a")
         ->where("a.cart_id = ?", $this->cart->getId());

      $items = $q->execute();
      foreach($items as $item)
      {
        $invoice = $item->getMfInvoice();

        if($invoice->getPaid() != 1 && $invoice->getPaid() != 15)
        {
          $this->remove_from_cart($invoice->getId());
        }
      }
    }

    public function get_items_count()
    {
      $this->clear_invalid_invoices();

      $q = Doctrine_Query::create()
         ->from("CartItem a")
         ->where("a.cart_id = ?", $this->cart->getId());

      return $q->count();
    }

    public function get_items()
    {
      $this->clear_invalid_invoices();

      $q = Doctrine_Query::create()
         ->from("CartItem a")
         ->where("a.cart_id = ?", $this->cart->getId());

      return $q->execute();
    }

    //Check if application is in the cart
    public function is_in_cart($application_id)
    {
      $application = $this->application_manager->get_application_by_id($application_id);

      foreach($application->getMfInvoice() as $invoice)
      {
        $q = Doctrine_Query::create()
           ->from("CartItem a")
           ->where("a.cart_id = ?", $this->cart->getId())
           ->andWhere("a.invoice_id = ?", $invoice->getId());
        if($q->count() > 0)
        {
          return true;
        }
        else
        {
          return false;
        }
      }
    }

    public function get_total_amount()
    {
      $q = Doctrine_Query::create()
         ->from("CartItem a")
         ->where("a.cart_id = ?", $this->cart->getId());

      $total = 0;

      foreach($q->execute() as $invoice)
      {
        $total = $total + $invoice->getTotalAmount();
      }

      return $total;
    }

    // Fetch list of invoices with their service codes and fees
    public function fetch_bill($token)
    {
      $bill = array();

      if($this->token_manager->authenticate($token))
      {
        $items = $this->get_items();
        foreach($items as $item)
        {
          $invoice = $item->getMfInvoice();
          $bill[] = array(
            "service_code" => $invoice->getFormEntry()->getForm()->getFormCode(),
            "amount" => $invoice->getTotalAmount(),
            "phone_number" => $invoice->getFormEntry()->getUser()->getMobile(),
            "merchant_reference" => $invoice->getFormEntry()->getFormId()."/".$invoice->getFormEntry()->getEntryId()."/".$invoice->getId()
          );
        }

        $token["invoices"] = $bill;

        $url = urlencode(openssl_encrypt(json_encode($token),"AES-128-ECB", sfConfig::get('app_sso_secret')));

        return $url;
      }
      else
      {
        return false;
      }
    }

    // Mark all the invoices as paid
    public function mark_as_paid($token)
    {
      //if($this->token_manager->authenticate($token))
      {
        $items = $this->get_items();
        foreach($items as $item)
        {
          $invoice = $item->getMfInvoice();

          $q = Doctrine_Query::create()
             ->from("ApFormPayments a")
             ->where("a.form_id = ? AND a.record_id = ?", array($invoice->getFormEntry()->getFormId(), $invoice->getFormEntry()->getEntryId()))
             ->andWhere("a.status <> ?", 2)
             ->andWhere("a.payment_amount = ?", $invoice->getTotalAmount());
          $transaction = $q->fetchOne();

          if($transaction)
          {
              //Update transaction details
              $transaction->setBillingState($data['reference']);
              $transaction->setPaymentDate(date("Y-m-d H:i:s"));

              $transaction->setStatus(2);
              $transaction->setPaymentStatus("paid");

              $transaction->save();
          }
          else
          {
              $application = $invoice->getFormEntry();

              $user = Doctrine_Core::getTable('SfGuardUser')->find(array($application->getUserId()));

              $fullname = $user->getProfile()->getFullname();

              //Add a new transaction if one doesn't exist
              $transaction = new ApFormPayments();
              $transaction->setFormId($invoice->getFormEntry()->getFormId());
              $transaction->setRecordId($invoice->getFormEntry()->getEntryId());
              $transaction->setPaymentId($invoice->getFormEntry()->getFormId()."/".$invoice->getFormEntry()->getEntryId()."/".$invoice->getId());
              $transaction->setDateCreated(date("Y-m-d H:i:s"));
              $transaction->setPaymentFullname($fullname);
              $transaction->setPaymentAmount($invoice->getTotalAmount());
              $transaction->setPaymentCurrency($invoice->getCurrency());
              $transaction->setPaymentMerchantType('Dalapay');
              $transaction->setPaymentTestMode("0");

              $transaction->setBillingState($data['reference']);
              $transaction->setPaymentDate(date("Y-m-d H:i:s"));

              $transaction->setStatus(2);
              $transaction->setPaymentStatus("paid");

              $transaction->save();
          }

          $invoice->setPaid(2);
          $invoice->save();
        }
      }
    }

}
