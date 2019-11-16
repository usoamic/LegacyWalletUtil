<?php
require_once("FixedBitNotation.php");
class GoogleAuthenticator
{
    static
        $CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567',
        $PASS_CODE_LENGTH = 6,
        $PIN_MODULO,
        $SECRET_LENGTH = 10;

    public function __construct()
    {
        self::$PIN_MODULO = pow(10, self::$PASS_CODE_LENGTH);
    }

    public function checkCode($secret, $code) {
        $time = floor(time() / 30);
        $arr = array();

        for ( $i = -1; $i <= 1; $i++) {

            if ($this->getCode($secret,$time + $i) == $code) {
                return true;
            }
            array_push($arr, $this->getCode($secret,$time + $i));

        }

        return false;
    }

    public function getCode($secret,$time = null) {

        if (!$time) {
            $time = floor(time() / 30);
        }
        $base32 = new FixedBitNotation(5, $this::$CHARACTERS, TRUE, TRUE);
        $secret = $base32->decode($secret);

        $time = pack("N", $time);
        $time = str_pad($time,8, chr(0), STR_PAD_LEFT);

        $hash = hash_hmac('sha1',$time,$secret,true);
        $offset = ord(substr($hash,-1));
        $offset = $offset & 0xF;

        $truncatedHash = self::hashToInt($hash, $offset) & 0x7FFFFFFF;
        $pinValue = str_pad($truncatedHash % self::$PIN_MODULO,6,"0",STR_PAD_LEFT);;
        return $pinValue;
    }

    public function generateSecret() {
        $secret = "";
        for($i = 1;  $i<= self::$SECRET_LENGTH;$i++) {
            $c = rand(0,255);
            $secret .= pack("c",$c);
        }
        $base32 = new FixedBitNotation(5, $this::$CHARACTERS, TRUE, TRUE);
        return $base32->encode($secret);
    }

    protected function hashToInt($bytes, $start) {
        $input = substr($bytes, $start, strlen($bytes) - $start);
        $val2 = unpack("N",substr($input,0,4));
        return $val2[1];
    }
}