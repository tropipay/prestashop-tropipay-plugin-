<?php

require_once(dirname(__DIR__).'/Entities/Payment.php');
require_once(dirname(__DIR__).'/Exceptions/PaymentValidationException.php');

class ProcessPayment {

    public function parseRequest(string $requestBody): Payment 
    {
        $data = json_decode($requestBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PaymentValidationException("JSON inválido en la solicitud.");
        }
       ;

        if ($data['status'] !== 'OK') {
            throw new PaymentValidationException("Estado del pago no es válido.");
        }

        return new Payment($data['data']);
    }

    public function validatePayment(Payment $payment): void 
    {
        $localSignature = $this->generateSignature($payment);
        var_dump($payment->getSignature(), $localSignature);
        if ($payment->getSignature() !== $localSignature) {
            throw new PaymentValidationException("La firma no coincide.");
        }
    }

    private function generateSignature(Payment $payment): string {
        return hash(
            'sha256',
            $payment->getBankOrder() . Configuration::get('TROPIPAY_CLIENTID') .
            Configuration::get('TROPIPAY_CLIENTSECRET') . $payment->getAmount()
        );
    }
}