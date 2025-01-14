<?php

require_once(dirname(__DIR__).'/Entities/Payment.php');
require_once(dirname(__DIR__).'/Exceptions/PaymentValidationException.php');

class CompletePayment {

    public function parseRequest(string $requestBody): Payment 
    {
        $data = json_decode($requestBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PaymentValidationException("JSON inválido en la solicitud.");
        }
       ;

        if (!$data['status'] !== 'OK') {
            throw new PaymentValidationException("Estado del pago no es válido.");
        }

        return new Payment($data);
    }
}