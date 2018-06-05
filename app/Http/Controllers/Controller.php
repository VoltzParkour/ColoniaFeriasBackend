<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use GuzzleHttp\Client;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function Session(Request $request) {
    	$client = new Client([
   	 // Base URI is used with relative requests
    		'base_uri' => 'https://ws.sandbox.pagseguro.uol.com.br/v2/',
		]);
		$response = $client->request('POST', 'sessions?email=luisfnicolau@hotmail.com&token=503F25BCA32146728390BA730AA376F1');
		$xml = simplexml_load_string($response->getBody());
		$json = json_encode($xml);
    	return json_decode($json,TRUE);
    }

    public function BoletoPayment(Request $request) {
		  //   	$client = new Client([
		  //  	 // Base URI is used with relative requests
		  //   		'base_uri' => 'https://ws.sandbox.pagseguro.uol.com.br/v2/',
				// ]);
				// $response = $client->request('POST', 'transactions?email=luisfnicolau@hotmail.com&token=503F25BCA32146728390BA730AA376F1', [
				// 	$request]);
				// $xml = simplexml_load_string($response->getBody());
				// $json = json_encode($xml);
		  //   	return json_decode($json,TRUE);

		// require_once "../../vendor/autoload.php";
		\PagSeguro\Library::initialize();
		\PagSeguro\Library::cmsVersion()->setName("Nome")->setRelease("1.0.0");
		\PagSeguro\Library::moduleVersion()->setName("Nome")->setRelease("1.0.0");
		\PagSeguro\Configuration\Configure::setEnvironment("sandbox"); 
		\PagSeguro\Configuration\Configure::setAccountCredentials("luisfnicolau@hotmail.com", "503F25BCA32146728390BA730AA376F1"); 
		\PagSeguro\Configuration\Configure::setCharset('UTF-8'); 
		//Instantiate a new Boleto Object
		$boleto = new \PagSeguro\Domains\Requests\DirectPayment\Boleto();
		// Set the Payment Mode for this payment request
		$boleto->setMode('DEFAULT');
		/**
		 * @todo Change the receiver Email
		 */
		$boleto->setReceiverEmail('luisfnicolau@hotmail.com'); 
		// Set the currency
		$boleto->setCurrency("BRL");
		// Add an item for this payment request
		// Add an item for this payment request
		$boleto->addItems()->withParameters(
		    '1',
		    'Colonia Ferias',
		    1,
		    $request->get('amount')
		    // 110.00
		);
		// Set a reference code for this payment request. It is useful to identify this payment
		// in future notifications.
		$boleto->setReference("LIBPHP000001-boleto");
		//set extra amount
		$boleto->setExtraAmount(0.00);
		// Set your customer information.
		// If you using SANDBOX you must use an email @sandbox.pagseguro.com.br
		$boleto->setSender()->setName($request->get('name'));
		// $boleto->setSender()->setName('abigail freitas');
		// $boleto->setSender()->setEmail($request->get('email'));
		$boleto->setSender()->setEmail('fsdsdas@sandbox.pagseguro.com.br');
		$boleto->setSender()->setPhone()->withParameters(
		    $request->get('phone_code'),
		    $request->get('phone')
		);
		$boleto->setSender()->setDocument()->withParameters(
		    'CPF',
		    $request->get('cpf')
		     // '01212944208'
		);
		$boleto->setSender()->setHash($request->get('hash'));
		// $boleto->setSender()->setIp('127.0.0.0');
		// Set shipping information for this payment request
		$boleto->setShipping()->setAddressRequired()->withParameters('FALSE');
		// If your payment request don't need shipping information use:
		// $boleto->setShipping()->setAddressRequired()->withParameters('FALSE');
		try {
		    //Get the crendentials and register the boleto payment
		    $result = $boleto->register(
		        \PagSeguro\Configuration\Configure::getAccountCredentials()
			);

		    // You can use methods like getCode() to get the transaction code and getPaymentLink() for the Payment's URL.
		    // echo "<pre>";
		    // print_r($result);
		    return [$result->getCode(), $result->getPaymentLink()];
		} catch (Exception $e) {
		    echo "</br> <strong>";
		    die($e->getMessage());
		    return $e;
		}
	}

	public function CardPayment(Request $request) {
		\PagSeguro\Library::initialize();
		\PagSeguro\Library::cmsVersion()->setName("Nome")->setRelease("1.0.0");
		\PagSeguro\Library::moduleVersion()->setName("Nome")->setRelease("1.0.0");
		\PagSeguro\Configuration\Configure::setEnvironment("sandbox"); 
		\PagSeguro\Configuration\Configure::setAccountCredentials("luisfnicolau@hotmail.com", "503F25BCA32146728390BA730AA376F1"); 
		\PagSeguro\Configuration\Configure::setCharset('UTF-8'); 
		//Instantiate a new direct payment request, using Credit Card
		$creditCard = new \PagSeguro\Domains\Requests\DirectPayment\CreditCard();
		/**
		 * @todo Change the receiver Email
		 */
		$creditCard->setReceiverEmail('luisfnicolau@hotmail.com');
		// Set a reference code for this payment request. It is useful to identify this payment
		// in future notifications.
		$creditCard->setReference("LIBPHP000001");
		// Set the currency
		$creditCard->setCurrency("BRL");
		// Add an item for this payment request
		$creditCard->addItems()->withParameters(
		    '1',
		    'Colonia Ferias',
		    1,
		    $request->get('amount')
		    // 110.00
		);
		// Set your customer information.
		// If you using SANDBOX you must use an email @sandbox.pagseguro.com.br
		$creditCard->setSender()->setName($request->get('name'));
		$creditCard->setSender()->setEmail('fsdsdas@sandbox.pagseguro.com.br');
		$creditCard->setSender()->setPhone()->withParameters(
		    $request->get('phone_code'),
		    $request->get('phone')
		);
		$creditCard->setSender()->setDocument()->withParameters(
		    'CPF',
		    $request->get('cpf')
		     // '01212944208'
		);
		$creditCard->setSender()->setHash($request->get('hash'));
		$creditCard->setSender()->setIp('127.0.0.0');
		// Set shipping information for this payment request
		$creditCard->setShipping()->setAddressRequired()->withParameters('FALSE');

		//Set billing information for credit card
		$creditCard->setBilling()->setAddress()->withParameters(
		    $request->get('street'),
		    $request->get('number'),
		    'bairro',
		    // 'Jardim Paulistano',
		    $request->get('cep'),
		    $request->get('city'),
		    $request->get('estate'),
		    'BRA',
		    $request->get('complement')
		);
		// Set credit card token
		$creditCard->setToken($request->get('token'));
		// Set the installment quantity and value (could be obtained using the Installments
		// service, that have an example here in \public\getInstallments.php)
		$creditCard->setInstallment()->withParameters(1, $request->get('amount'));
		// Set the credit card holder information
		$creditCard->setHolder()->setBirthdate($request->get('card_holder_birth_date'));
		$creditCard->setHolder()->setName($request->get('card_holder_name')); // Equals in Credit Card
		// $creditCard->setHolder()->setPhone()->withParameters(
		//     $request->get('card_holder_phone_code'),
		//     $request->get('card_holder_phone')
		// );
		$creditCard->setHolder()->setDocument()->withParameters(
		    'CPF',
		    $request->get('card_holder_cpf')
		);
		// Set the Payment Mode for this payment request
		$creditCard->setMode('DEFAULT');
		// Set a reference code for this payment request. It is useful to identify this payment
		// in future notifications.
		try {
		    //Get the crendentials and register the boleto payment
		    $result = $creditCard->register(
		        \PagSeguro\Configuration\Configure::getAccountCredentials()
			);			
		    echo "<pre>";
		    print_r($result);
		    return $result->getCode();
		} catch (Exception $e) {
		    echo "</br> <strong>";
		    die($e->getMessage());
		}
	}

}
