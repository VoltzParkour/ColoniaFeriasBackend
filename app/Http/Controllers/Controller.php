<?php

namespace App\Http\Controllers;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function enviroment() {
//       return $enviroment = 'sandbox';
      return $enviroment = 'production';
    }

    public function Session(Request $request) {
    	$client = new Client([
   	 // Base URI is used with relative requests
        'base_uri' => $this->baseUriWithEnviroment()

		]);
		$response = $client->request('POST', 'sessions?email='.$this->getEmailWithEnviroment().'&token='.$this->getTokenWithEnviroment(), [
      'headers' => [
            'Access-Control-Allow-Origin' => '*',
        ]
    ]);
		$xml = simplexml_load_string($response->getBody());
		$json = json_encode($xml);
    return response(json_decode($json,TRUE), 200);
    	// return json_decode($json,TRUE);
    }

    public function getEmailWithEnviroment() {
      if ($this->enviroment() == 'sandbox') {
        return 'luisfnicolau@hotmail.com';
      }
      return 'gianpomposelli@gmail.com';
    }

    public function getTokenWithEnviroment() {
      if ($this->enviroment() == 'sandbox') {
        return '503F25BCA32146728390BA730AA376F1';
      }
      return '89EE1C909073473692979E098163D221';
    }

    public function baseUriWithEnviroment() {
      if ($this->enviroment() == 'sandbox') {
        return 'https://ws.sandbox.pagseguro.uol.com.br/v2/';
      }
      return 'https://ws.pagseguro.uol.com.br/v2/';
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
		\PagSeguro\Configuration\Configure::setEnvironment($this->enviroment());
		\PagSeguro\Configuration\Configure::setAccountCredentials($this->getEmailWithEnviroment(), $this->getTokenWithEnviroment());
		\PagSeguro\Configuration\Configure::setCharset('UTF-8');
		//Instantiate a new Boleto Object
		$boleto = new \PagSeguro\Domains\Requests\DirectPayment\Boleto();
		// Set the Payment Mode for this payment request
		$boleto->setMode('DEFAULT');
		/**
		 * @todo Change the receiver Email
		 */
		$boleto->setReceiverEmail($this->getEmailWithEnviroment());
		// Set the currency
		$boleto->setCurrency("BRL");
		// Add an item for this payment request
		// Add an item for this payment request
		$boleto->addItems()->withParameters(
		    '1',
        $request->description ? $request->description : 'Colonia Ferias',
		    1,
		    $request->get('amount')
		    // 110.00
		);
		// Set a reference code for this payment request. It is useful to identify this payment
		// in future notifications.
		$boleto->setSender()->setHash($request->get('hash'));

		$boleto->setReference("LIBPHP000001-boleto");
		//set extra amount
		$boleto->setExtraAmount(0.00);
		// Set your customer information.
		// If you using SANDBOX you must use an email @sandbox.pagseguro.com.br
		$boleto->setSender()->setName($request->get('name'));
		// $boleto->setSender()->setName('abigail freitas');
		$boleto->setSender()->setEmail($request->get('email'));
		// $boleto->setSender()->setEmail('lusifnicolau@gmail.com.br');
		$boleto->setSender()->setPhone()->withParameters(
		    $request->get('phone_code'),
		    $request->get('phone')
		);
		$boleto->setSender()->setDocument()->withParameters(
		    'CPF',
		    $request->get('cpf')
		     // '01212944208'
		);
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
    $data = [
      'transactionCode' => $result->getCode(),
      'paymentLink' => $result->getPaymentLink()
    ];
    return response()->json($data);
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
		\PagSeguro\Configuration\Configure::setEnvironment($this->enviroment());
		\PagSeguro\Configuration\Configure::setAccountCredentials($this->getEmailWithEnviroment(), $this->getTokenWithEnviroment());
		\PagSeguro\Configuration\Configure::setCharset('UTF-8');
		//Instantiate a new direct payment request, using Credit Card
		$creditCard = new \PagSeguro\Domains\Requests\DirectPayment\CreditCard();
		/**
		 * @todo Change the receiver Email
		 */
		$creditCard->setReceiverEmail($this->getEmailWithEnviroment());
		$creditCard->setHolder()->setPhone()->withParameters(
    	21,
    	995403334
		);
		// Set a reference code for this payment request. It is useful to identify this payment
		// in future notifications.
		$creditCard->setReference("LIBPHP000001");
		// Set the currency
		$creditCard->setCurrency("BRL");
		// Add an item for this payment request
		$creditCard->addItems()->withParameters(
		    '1',
        $request->description ? $request->description : 'Colonia Ferias',
		    1,
		    $request->get('amount')
		    // 110.00
		);
		// Set your customer information.
		// If you using SANDBOX you must use an email @sandbox.pagseguro.com.br
		$creditCard->setSender()->setName($request->get('name'));
		// $creditCard->setSender()->setEmail('fsdsdas@sandbox.pagseguro.com.br');
		$creditCard->setSender()->setEmail($request->get('email'));
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
		// $creditCard->setSender()->setIp('127.0.0.0');
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
		    // echo "<pre>";
		    // print_r($result);
        $data = [
          'transactionCode' => $result->getCode(),
        ];
		    return $data;
		} catch (Exception $e) {
		    // echo "</br> <strong>";
		    return $e->getMessage();
		}
	}

	public function Check(Request $request) {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/coloniaferiasvoltz-firebase-adminsdk-qgbr2-a62e750848.json');

        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            // The following line is optional if the project id in your credentials file
            // is identical to the subdomain of your Firebase project. If you need it,
            // make sure to replace the URL with the URL of your project.
            ->withDatabaseUri('https://coloniaferiasvoltz.firebaseio.com')
            ->create();
        $database = $firebase->getDatabase();
        $reference = $database
            // ->getReference('colony_buyers_by_payment/'.$transactionCode);
            ->getReference('colony_buyers/');
        $values = $reference->getValue();

        $array = [];
        foreach ($values as $key => $value) {
            if ($key == '-LEgM90slRNpURqOZ9e3') {
                continue;
            }
            foreach ($value as $middleKey => $middleValue) {
                foreach ($middleValue as $finalKey => $finalValue) {
                    $database->getReference('colony_buyers/-LQdwFFfKio_UTfCuxUo/' . $finalKey)
                        // return $user->getChild($userIds[$j])->getValue();
                        ->set($finalValue);
                }
            }
//            array_push($array, $value);
        }


//        $database->getReference('colony_buyers/-LQdwFFfKio_UTfCuxUo')
//            // return $user->getChild($userIds[$j])->getValue();
//            ->set($array);
        return 'Sucesso';
    }

	public function Confirm(Request $request) {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/coloniaferiasvoltz-firebase-adminsdk-qgbr2-a62e750848.json');

        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            // The following line is optional if the project id in your credentials file
            // is identical to the subdomain of your Firebase project. If you need it,
            // make sure to replace the URL with the URL of your project.
            ->withDatabaseUri('https://coloniaferiasvoltz.firebaseio.com')
            ->create();
        $database = $firebase->getDatabase();
        $reference = $database
            // ->getReference('colony_buyers_by_payment/'.$transactionCode);
            ->getReference('colony_buyers_by_payment/'.$request->transactionCode);
        // $snapshot = $reference->getSnapshot();
        // ->push([
        //     'title' => 'Post title',
        //     'body' => 'This should probably be longer.'
        // ]);
        // return $reference->getValue();
        if (!$reference->getValue()) {
            return response('Not found', 201);
        }
        $coloniesIds = $reference->getValue();
        foreach ($coloniesIds as $colony) {
            foreach ($colony as $colonyKey => $subscription) {
                foreach ($subscription as $key => $users) {
                    $database->getReference('colony_buyers/'.$colonyKey.'/'.$key)
                        // return $user->getChild($userIds[$j])->getValue();
                        ->set($users);                }
            }
        }
//        for ($i=0; $i < sizeof($coloniesIds); $i++) {
//            $user = $reference->getChild($coloniesIds[$i]);
//            $userIds = array_keys($user->getValue());
//            for ($j=0; $j < sizeof($userIds); $j++) {
//                $database->getReference('colony_buyers/'.$coloniesIds[$i].'/'.$userIds[$j])
//                    // return $user->getChild($userIds[$j])->getValue();
//                    ->set($user->getChild($userIds[$j])->getValue());
//            }
//        }
//        $reference->remove();
        return response('Added', 200);
    }

    public function Test(Request $request) {
        return 'Teste Ok';
    }

  public function Confirmation(Request $request) {
    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/coloniaferiasvoltz-firebase-adminsdk-qgbr2-a62e750848.json');

    $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        // The following line is optional if the project id in your credentials file
        // is identical to the subdomain of your Firebase project. If you need it,
        // make sure to replace the URL with the URL of your project.
        ->withDatabaseUri('https://coloniaferiasvoltz.firebaseio.com')
        ->create();

    $database = $firebase->getDatabase();

    $uri = '';
    if ($this->enviroment() == 'sandbox') {
      $uri = 'https://ws.sandbox.pagseguro.uol.com.br/v3/';
    } else {
      $uri = 'https://ws.pagseguro.uol.com.br/v3/';
    }

    $client = new Client([
     // Base URI is used with relative requests
        // 'base_uri' => 'https://ws.pagseguro.uol.com.br/v3/',
        'base_uri' => $uri,
    ]);
    if (!$request->notificationCode) {
        return 'Sem código de notificacao';
    }
    $response = $client->request('GET', 'transactions/notifications/'.$request->notificationCode.'?email='.$this->getEmailWithEnviroment().'&token='.$this->getTokenWithEnviroment());
    // return $response->getBody();
    $transaction = simplexml_load_string($response->getBody());
    $transactionCode = $transaction->code;
    $transactionStatus = $transaction->status;
    $transactionDescription = $transaction->items->item->description;
    // return $transactionDescription;
    if ($transactionDescription == 'Colonia Ferias') {
    switch ($transactionStatus) {
      case 3:
        $reference = $database
          // ->getReference('colony_buyers_by_payment/'.$transactionCode);
          ->getReference('colony_buyers_by_payment/'.$transactionCode);
        // $snapshot = $reference->getSnapshot();
          // ->push([
          //     'title' => 'Post title',
          //     'body' => 'This should probably be longer.'
          // ]);
          // return $reference->getValue();
          if (!$reference->getValue()) {
            return response('Not found', 201);
          }
          $coloniesIds = array_keys($reference->getValue());
          for ($i=0; $i < sizeof($coloniesIds); $i++) {
              $user = $reference->getChild($coloniesIds[$i]);
              $userIds = array_keys($user->getValue());
              for ($j=0; $j < sizeof($userIds); $j++) {
                $database->getReference('colony_buyers/'.$coloniesIds[$i].'/'.$userIds[$j])
                // return $user->getChild($userIds[$j])->getValue();
                ->set($user->getChild($userIds[$j])->getValue());
              }
          }
//          $reference->remove();
          return response('Added', 200);
        break;

      case 6:
		$reference = $database
          // ->getReference('colony_buyers_by_payment/'.$transactionCode);
          ->getReference('colony_buyers_by_payment/'.$transactionCode);
          $reference->remove();
          return response('Removed', 202);
        break;
      case 7:
      	$reference = $database
          // ->getReference('colony_buyers_by_payment/'.$transactionCode);
          ->getReference('colony_buyers_by_payment/'.$transactionCode);
          $reference->remove();
          return response('Removed', 202);
      default:
      return response('Not a payment confirmation', 201);
        break;
    }
  } else if ($transactionDescription == 'Desafio Voltz Kids 2018') {
    switch ($transactionStatus) {
      case 3:

        $reference = $database
          // ->getReference('colony_buyers_by_payment/'.$transactionCode);
          ->getReference('events/kids/'.$transactionCode.'/transaction_status');
        // $snapshot = $reference->getSnapshot();
          // ->push([
          //     'title' => 'Post title',
          //     'body' => 'This should probably be longer.'
          // ]);
          // return $reference->getValue();
          if (!$reference->getValue()) {
            return response('Not found', 201);
          }
          $reference->set('paga');
          return response('Added', 200);
        break;

      case 6:
		$reference = $database
          // ->getReference('colony_buyers_by_payment/'.$transactionCode);
          ->getReference('events/kids/'.$transactionCode.'/transaction_status');
          $reference->set('devolvida');
          return response('Removed', 202);
        break;
      case 7:
      	$reference = $database
          // ->getReference('colony_buyers_by_payment/'.$transactionCode);
          ->getReference('events/kids/'.$transactionCode.'/transaction_status');
          $reference->set('cancelada');
          return response('Removed', 202);
      default:
      return response('Not a payment confirmation', 201);
        break;
    }
  } else if ($transactionDescription == 'Oficina Voltz Kids 2018') {
    switch ($transactionStatus) {
      case 3:

        $reference = $database
          // ->getReference('colony_buyers_by_payment/'.$transactionCode);
          ->getReference('events/oficina/'.$transactionCode.'/transaction_status');
        // $snapshot = $reference->getSnapshot();
          // ->push([
          //     'title' => 'Post title',
          //     'body' => 'This should probably be longer.'
          // ]);
          // return $reference->getValue();
          if (!$reference->getValue()) {
            return response('Not found', 201);
          }
          $reference->set('paga');
          return response('Added', 200);
        break;

      case 6:
		$reference = $database
          // ->getReference('colony_buyers_by_payment/'.$transactionCode);
          ->getReference('events/oficina/'.$transactionCode.'/transaction_status');
          $reference->set('devolvida');
          return response('Removed', 202);
        break;
      case 7:
      	$reference = $database
          // ->getReference('colony_buyers_by_payment/'.$transactionCode);
          ->getReference('events/oficina/'.$transactionCode.'/transaction_status');
          $reference->set('cancelada');
          return response('Removed', 202);
      default:
      return response('Not a payment confirmation', 201);
        break;
    }
  }
}
}
