<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 02/11/18
 * Time: 8:33 PM
 */
namespace App\Paypal;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
class CreatePayment extends Paypal
{
    public function create()
    {
        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku('123123') // Similar to `item_number` in Classic API
            ->setPrice(7.5);
        $item2 = new Item();
        $item2->setName('Granola bars')
            ->setCurrency('USD')
            ->setQuantity(5)
            ->setSku('321321') // Similar to `item_number` in Classic API
            ->setPrice(2);
        $itemList = new ItemList();
        $itemList->setItems([$item1, $item2]);
        $payment = $this->Payment($itemList);

        $payment->create($this->apiContext);
        return redirect($payment->getApprovalLink());
    }
    /**
     * @return Payer
     */
    protected function Payer(): Payer
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        return $payer;
    }
    /**
     * @param $itemList
     * @return Transaction
     */
    protected function Transaction( $itemList): Transaction
    {
        $transaction = new Transaction();
        $transaction->setAmount($this->Amount())
            ->setItemList($itemList)
            ->setDescription('Payment description')
            ->setInvoiceNumber(uniqid());
        return $transaction;
    }
    /**
     * @return RedirectUrls
     */
    protected function RedirectUrls(): RedirectUrls
    {
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("http://127.0.0.1:8000/execute-payment")
            ->setCancelUrl("http://127.0.0.1:8000/cancel");
        return $redirectUrls;
    }
    /**
     * @param $itemList
     * @return Payment
     */
    protected function Payment($itemList): Payment
    {
        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($this->payer())
            ->setRedirectUrls($this->RedirectUrls())
            ->setTransactions([$this->transaction($itemList)]);
        return $payment;
    }
    /**
     * @return Details
     */
    protected function details(): Details
    {
        $details = new Details();
        $details->setShipping(1.2)
            ->setTax(1.3)
            ->setSubtotal(17.50);
        return $details;
    }
    /**
     * @return Amount
     */
    protected function amount(): Amount
    {
        $amount = new Amount();
        $amount->setCurrency('USD');
        $amount->setTotal(20);
        $amount->setDetails($this->details());
        return $amount;
    }
}
