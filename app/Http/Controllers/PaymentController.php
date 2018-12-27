<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;
use PayPal\Api\Agreement;
use PayPal\Api\ShippingAddress;

class PaymentController extends Controller
{
    /**
     * @return array|Request|string
     */
    public function create()
    {

        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AVQkF-XQqCo0kYFLnpXi2IE0IO3iXGDicHTbKa1ksiElfmPVIG3xsgeXjy1ntgYVjTMsY-prC03ZL2Os',     // ClientID
                'EJ45rEsxD5VomRTeOrjcRrvRqYmwq6y8eIP5ZSO-6jyHMwRUs1ONiqVPD22rNjt061owRZ1bHUcfIjzN'      // ClientSecret
            )
        );
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku("123123") // Similar to `item_number` in Classic API
            ->setPrice(7.5);
        $item2 = new Item();
        $item2->setName('Granola bars')
            ->setCurrency('USD')
            ->setQuantity(5)
            ->setSku("321321") // Similar to `item_number` in Classic API
            ->setPrice(2);

        $itemList = new ItemList();
        $itemList->setItems(array($item1, $item2));

        $details = new Details();
        $details->setShipping(1.2)
            ->setTax(1.3)
            ->setSubtotal(17.50);

        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal(20)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment description")
            ->setInvoiceNumber(uniqid());


        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("http://127.0.0.1:8000/execute-payment")
            ->setCancelUrl("http://127.0.0.1:8000/cancel");

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        $payment->create($apiContext);
        return redirect($payment->getApprovalLink());
    }


    public function execute()
    {
        // After Step 1
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AYvwELv0oTaQs3xykoYtYGfP9_1vZDgqQdZaczaJqS7eq_mCb7L7VbPlCyRp5aBeI0AD_AvZcq22l01a',     // ClientID
                'EHCfa_K2dDtMVKXE56dE8pm2xVoZ-t_q2lz0IdERp9ACpYJSJpX7Dv7_1QnkQj-yKPKkUC8KYtm-YDUb'      // ClientSecret
            )
        );

        $paymentId = request('paymentId');
        $payment = Payment::get($paymentId, $apiContext);

        $execution = new PaymentExecution();
        $execution->setPayerId(request('PayerID'));

        $transaction = new Transaction();
        $amount = new Amount();
        $details = new Details();

        $details->setShipping(2.2)
            ->setTax(1.3)
            ->setSubtotal(17.50);

        $amount->setCurrency('USD');
        $amount->setTotal(21);
        $amount->setDetails($details);
        $transaction->setAmount($amount);

        $execution->addTransaction($transaction);
        $result = $payment->execute($execution, $apiContext);
        return $result;
    }

    /**
     *
     */
    /*subscription part*/
    public function executed()
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AYvwELv0oTaQs3xykoYtYGfP9_1vZDgqQdZaczaJqS7eq_mCb7L7VbPlCyRp5aBeI0AD_AvZcq22l01a',     // ClientID
                'EHCfa_K2dDtMVKXE56dE8pm2xVoZ-t_q2lz0IdERp9ACpYJSJpX7Dv7_1QnkQj-yKPKkUC8KYtm-YDUb'      // ClientSecret
            )
        );

        $plan = new Plan();
        $plan->setName('T-Shirt of the Month Club Plan')
            ->setDescription('Template creation.')
            ->setType('fixed');

        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
            ->setType('REGULAR')
            ->setFrequency('Month')
            ->setFrequencyInterval("2")
            ->setCycles("12")
            ->setAmount(new Currency(array('value' => 100, 'currency' => 'USD')));

        $chargeModel = new ChargeModel();
        $chargeModel->setType('SHIPPING')
            ->setAmount(new Currency(array('value' => 10, 'currency' => 'USD')));

        $paymentDefinition->setChargeModels(array($chargeModel));

        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences->setReturnUrl("http://127.0.0.1:8000/execute-agreement/true")
            ->setCancelUrl("http://127.0.0.1:8000/execute-agreement/false")
            ->setAutoBillAmount("yes")
            ->setInitialFailAmountAction("CONTINUE")
            ->setMaxFailAttempts("0")
            ->setSetupFee(new Currency(array('value' => 1, 'currency' => 'USD')));

        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);

        $output =$plan->create($apiContext);
        dd($output);

    }
    public function listPlan()
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AYvwELv0oTaQs3xykoYtYGfP9_1vZDgqQdZaczaJqS7eq_mCb7L7VbPlCyRp5aBeI0AD_AvZcq22l01a',     // ClientID
                'EHCfa_K2dDtMVKXE56dE8pm2xVoZ-t_q2lz0IdERp9ACpYJSJpX7Dv7_1QnkQj-yKPKkUC8KYtm-YDUb'      // ClientSecret
            )
        );
        $params = array('page_size' => '10');
        $planList = Plan::all($params, $apiContext);
        return $planList;
    }

   /* plan details*/
    public function planDetails($id)
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AYvwELv0oTaQs3xykoYtYGfP9_1vZDgqQdZaczaJqS7eq_mCb7L7VbPlCyRp5aBeI0AD_AvZcq22l01a',     // ClientID
                'EHCfa_K2dDtMVKXE56dE8pm2xVoZ-t_q2lz0IdERp9ACpYJSJpX7Dv7_1QnkQj-yKPKkUC8KYtm-YDUb'      // ClientSecret
            )
        );
        $plan = Plan::get($id, $apiContext);
        return $plan;
    }

    /*active the plan*/

    public function activePlan($id)
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AYvwELv0oTaQs3xykoYtYGfP9_1vZDgqQdZaczaJqS7eq_mCb7L7VbPlCyRp5aBeI0AD_AvZcq22l01a',     // ClientID
                'EHCfa_K2dDtMVKXE56dE8pm2xVoZ-t_q2lz0IdERp9ACpYJSJpX7Dv7_1QnkQj-yKPKkUC8KYtm-YDUb'      // ClientSecret
            )
        );
        $createdPlan=$this->planDetails($id);
        $patch = new Patch();

        $value = new PayPalModel('{
	       "state":"ACTIVE"
	     }');

        $patch->setOp('replace')
            ->setPath('/')
            ->setValue($value);
        $patchRequest = new PatchRequest();
        $patchRequest->addPatch($patch);

        $createdPlan->update($patchRequest, $apiContext);

        $plan = Plan::get($id, $apiContext);
        return $plan;
    }


    /*paypal Argument*/
    public function agrement($id)
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AYvwELv0oTaQs3xykoYtYGfP9_1vZDgqQdZaczaJqS7eq_mCb7L7VbPlCyRp5aBeI0AD_AvZcq22l01a',     // ClientID
                'EHCfa_K2dDtMVKXE56dE8pm2xVoZ-t_q2lz0IdERp9ACpYJSJpX7Dv7_1QnkQj-yKPKkUC8KYtm-YDUb'      // ClientSecret
            )
        );

        $agreement = new Agreement();

        $agreement->setName('Base Agreement')
            ->setDescription('Basic Agreement')
            ->setStartDate('2019-06-17T9:45:04Z');

        $plan = new Plan();
        $plan->setId($id);
        $agreement->setPlan($plan);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        $shippingAddress = new ShippingAddress();
        $shippingAddress->setLine1('111 First Street')
            ->setCity('Saratoga')
            ->setState('CA')
            ->setPostalCode('95070')
            ->setCountryCode('US');
        $agreement->setShippingAddress($shippingAddress);

        $agreement = $agreement->create($apiContext);
        $approvalUrl = $agreement->getApprovalLink();

        return redirect($approvalUrl);

    }

    public function executeagrement($status)
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AYvwELv0oTaQs3xykoYtYGfP9_1vZDgqQdZaczaJqS7eq_mCb7L7VbPlCyRp5aBeI0AD_AvZcq22l01a',     // ClientID
                'EHCfa_K2dDtMVKXE56dE8pm2xVoZ-t_q2lz0IdERp9ACpYJSJpX7Dv7_1QnkQj-yKPKkUC8KYtm-YDUb'      // ClientSecret
            )
        );
        if ($status == 'true')
        {
            $token = $_GET['token'];
            $agreement = new Agreement();
            $agreement->execute($token,$apiContext);
            return 'done';
        }
    }

}

