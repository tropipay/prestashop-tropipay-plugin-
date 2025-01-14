<?php

class Payment
{
    private string $status;
    private float $amount;
    private string $reference;
    private string $bankOrderCode;
    private string $currency;
    private string $signature;

    public function __construct(array $data) 
    {
        $this->status = $data["status"];
        $data = $data;
        $this->amount = abs($data["originalCurrencyAmount"]);
        $this->reference = $data["reference"];
        $this->bankOrderCode = $data["bankOrderCode"];
        $this->currency = $data["paymentcard"]["currency"];
        $this->signature = $data["signaturev2"];
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getBankOrder()
    {
        return $this->bankOrderCode;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getReference(): string 
    {
        return $this->reference;
    }

    public function getCurrency(): Currency
    {
        return new Currency((int)$this->currency);
    }

}