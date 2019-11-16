<?php
require_once("gauthenticator/GoogleAuthenticator.php");

class TFAClass extends GoogleAuthenticator
{
    use NotifierClass;

    public
        $response,
        $error;

    public function isValidCode($enabled, $secretKey, $code) {
        if($enabled) {
            if(is_empty($code)) {
                return $this->failure(AUTHENTICATOR_CODE_REQUIRED);
            }
            if(!$this->checkCode($secretKey, (int)$code)) {
                return $this->failure(INVALID_AUTHENTICATOR_CODE);
            }
        }
        return true;
    }
}