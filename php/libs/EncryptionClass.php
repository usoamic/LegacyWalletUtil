<?php

class EncryptionClass {
    private
        $rand_key,
        $encryptionSalt,
        $encryptMethod,
        $encryptionExceptions,
        $iv;

    /*
     * Public
     */

    public function __construct($method = ENCRYPTION_METHOD, $rand_key = ENCRYPTION_RANDOM_KEY, $salt = ENCRYPTION_SALT, $iv = ENCRYPTION_IV, $exceptions = ENCRYPTION_EXCEPTIONS) {
        $this->rand_key = $rand_key;
        $this->encryptionSalt = $salt;
        $this->encryptMethod = $method;
        $this->iv = $iv;
        $this->encryptionExceptions = explode(" ", $exceptions);
    }

    public function generateRandomHash($str = "", $algo = 'sha256') {
        $h_str = $str.''.rand().''.rand().''.time();
        return $this->generateHash($h_str, $algo);
    }

    public function getSessionVar()
    {
        return 'usr_'.$this->generateHash();
    }

    public function stringHash($str, $algo = 'sha256') {
        return hash($algo, $str);
    }

    public function generateHash($str = "", $algo = 'sha256') {
        $h_str = str_to_lad($str.SITE_TITLE.$this->rand_key);
        $hash = hash($algo, $h_str);
        if(is_empty($h_str) || is_empty($hash)) die_redirect();
        return $hash;
    }

    public function encryptString($text) {
        return $this->cryptString($text, true);
    }

    public function decryptString($text) {
        return $this->cryptString($text, false);
    }

    public function encryptArrayElement($key, $text) {
        if($text == NULL) return $text;
        return $this->cryptArrayElement($key, $text, true);
    }

    public function decryptArrayElement($key , $text) {
        return $this->cryptArrayElement($key, $text, false);
    }

    public function encryptArray($arr) {
        return $this->cryptArr($arr, true);
    }

    public function decryptArray($arr) {
        return $this->cryptArr($arr, false);
    }

    /*
     * Private
     */
    private function cryptString($text, $encrypt) {
        $iv = base64_decode($this->iv);
        if($encrypt) {
            return trim(base64_encode(openssl_encrypt($text, $this->encryptMethod, $this->encryptionSalt, OPENSSL_RAW_DATA, $iv)));
        }
        else {
            $decodeText = base64_decode($text);
            return trim((openssl_decrypt($decodeText, $this->encryptMethod, $this->encryptionSalt, OPENSSL_RAW_DATA, $iv)));
        }
    }

    private function cryptArrayElement($key, $element, $encrypt) {
        if(is_empty($element)) return $element;
        return ((!in_array($key, $this->encryptionExceptions)) ? $this->cryptString($element, $encrypt) : $element);
    }

    private function cryptArr($arg, $encrypt, $status = true) {
        $result = ($status) ? null : $arg;

        if($status) {
            if (is_array($arg)) {
                $result = array();
                $keys = array_keys($arg);

                foreach ($keys as $key) {
                    $element = $arg[$key];
                    if (is_array($element)) {
                        $sub_keys = array_keys($element);
                        foreach ($sub_keys as $sub_key) {
                            $result[$key][$sub_key] = $this->cryptArrayElement($sub_key, $element[$sub_key], $encrypt);
                        }
                    } else $result[$key] = $this->cryptArrayElement($key, $element, $encrypt);

                }

            } else {
                $result = $this->cryptString($arg, $encrypt);
            }
        }

        return $result;
    }
}

?>