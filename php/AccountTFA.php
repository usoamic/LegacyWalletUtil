<?php

class AccountTFA extends TFAClass
{
    private
        $secretKey,
        $status;

    public function __construct($data)
    {
        parent::__construct();
        $this->secretKey = $data['secret_key'];
        $this->status = $data['tfa_status'];
    }

    public function getQrCode($size = '220x220') {
        $encoderURL = sprintf("otpauth://totp/%s?secret=%s", SITE_TITLE, $this->secretKey);
        return get_qr_code($encoderURL, $size);
    }

    public function getQrCodeAndText($size = '220x220') {
        return get_text_and_qrcode($this->secretKey, $this->getQrCode($size));
    }

    public function getSecretKey()
    {
        return $this->secretKey;
    }

    public function isEnabled()
    {
        return !is_n($this->status);
    }

    public function isValid($code, $enabled = null) {
        return $this->isValidCode(($enabled == null) ? $this->isEnabled() : $enabled, $this->getSecretKey(), $code);
    }
}