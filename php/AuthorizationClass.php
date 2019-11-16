<?php
require_once("MailerClass.php");
require_once("TFAClass.php");
require_once("recaptcha/reCAPTCHA.php");

class AuthorizationClass
{
    use NotifierClass;
    private
        $dbClass,
        $encryptionClass,
        $mailerClass,
        $tfa,
        $recaptcha;

    /*
     * Public
     */
    public function __construct() {
        $this->encryptionClass = new EncryptionClass();
        $this->mailerClass = new MailerClass();
        $this->dbClass = new DBClass();
        $this->tfa = new TFAClass();
        if(!is_empty(RECAPTCHA_SITE_KEY)) {
            $this->recaptcha = new reCAPTCHA(RECAPTCHA_SITE_KEY, RECAPTCHA_SECRET_KEY);
        }
    }

    private function getSessionValue($key) {
        $value = $_SESSION[$this->encryptionClass->getSessionVar()][$key];
        if(is_empty($value)) {
            return '';
        }
        return $value;
    }

    public function getSessionEmail() {
        return $this->getSessionValue('email');
    }

    public function checkLogin()
    {
        if(!isset($_SESSION)){ session_start(); }

        $sessionVar = $this->encryptionClass->getSessionVar();

        return isset_session($sessionVar);
    }

    public function logout()
    {
        session_start();
        $sessionVar = $this->encryptionClass->getSessionVar();
        $_SESSION[$sessionVar] = null;
        unset($_SESSION[$sessionVar]);
    }

    public function isValidCaptcha() {
        if(!ENABLE_CAPTCHA) {
            return true;
        }
        return $this->recaptcha->isValid($_POST['g-recaptcha-response']);
    }

    public function loginToAccount() {

        if(!$this->isValidCaptcha()) {
            return $this->failure(INVALID_CAPTCHA);
        }

        $email = get_post_value('email', true);
        $password = get_post_value('password');

        if(is_empty($email)) {
            return $this->failure(EMAIL_REQUIRED);
        }
        if(is_empty($password)) {
            return $this->failure(PASSWORD_REQUIRED);
        }
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->failure(INVALID_EMAIL_ADDRESS);
        }

        $row = $this->dbClass->getRow(USERS_TABLE, 'email', $email);

        $pwdSalt = array_get_if_exist($row, 'salt');
        $pwdHash = array_get_if_exist($row, 'password');
        if(!is_array($row) || count($row) == 0 || !$this->checkPassword($pwdHash, $pwdSalt, $password)) {
            return $this->failure(INVALID_EMAIL_OR_PASSWORD);
        }
        if(!is_y($row['confirm_code'])) {
            return $this->failure(EMAIL_NOT_CONFIRMED);
        }

        $tfaAccount = new AccountTFA($row);
        if(!$tfaAccount->isValid(get_post_value('authenticator_code'))) {
            return $this->failure($tfaAccount->getError());
        }
        $loginMailSent = $this->insertToLoginHistoryDB($email);

        if($loginMailSent) {
            if (!isset($_SESSION)) {
                session_start();
            }
            set_session_value($this->encryptionClass->getSessionVar(), array('email' => $row['email']));
        }
        else {
            return $loginMailSent;
        }
        return true;
    }

    public function resetPasswordRequest() {
        return $this->resetPassword(true);
    }

    public function resetPassword($request = false) {
        if($request) {
            if(!$this->isValidCaptcha()) {
                return $this->failure(INVALID_CAPTCHA);
            }
            $email = get_post_value('email', true);

            if(!is_empty($email)) {
                if (!$this->emailExist($email)) {
                    return $this->failure(INVALID_EMAIL_ADDRESS);
                }

                if(!$this->emailConfirmed($email)) {
                    return $this->failure(EMAIL_NOT_CONFIRMED);
                }
                $code = $this->encryptionClass->generateRandomHash($email);//
                $hash = $this->encryptionClass->generateHash($code, 'sha256');//hash of code to dbClass
                $this->dbClass->updateValue(USERS_TABLE, 'reset_code', $hash, 'email', $email);

                $this->mailerClass->sendResetPasswordLink($email, $code);

                $this->response = get_string(RESET_PASSWORD_EMAIL_SENT);
                return true;
            }
            else {
                return $this->failure(EMAIL_REQUIRED);
            }
        }
        else {
            $code = get_get_value('reset_code');
            $hash = $this->encryptionClass->generateHash($code, 'sha256');
            $user = $this->dbClass->getRow(USERS_TABLE, 'reset_code', $hash, 'email salt');
            $email = $user["email"];
            $salt = $user["salt"];
            if(!is_empty($email) && $this->updateCode('reset_code', $code, get_string(RESET_CODE_REQUIRED), get_string(INVALID_RESET_CODE), get_string(NEW_PASSWORD_SENT))) {
                $password = random_string();
                $hash = base64_encode((sha1($password.$salt, true).$salt));
                $this->dbClass->updateValue(USERS_TABLE, 'password', $hash, 'email', $email);
                $this->mailerClass->sendNewPassword($email, $password);
                $this->response = get_string(NEW_PASSWORD_SENT);
            }
            else {
                return $this->failure(INVALID_RESET_CODE);
            }
        }
        return false;
    }

    /*
     * Private
     */
    private function checkPassword($originalHashedPassword, $salt, $password) {
        $hashedPassword = base64_encode((sha1($password.$salt, true).$salt));
        return ($hashedPassword == $originalHashedPassword);
    }

    private function updateCode($column, $code, $error1, $error2, $response = "") {
        if(is_empty($code)) {
            $this->error = $error1;
        }
        else {
            $hash = $this->encryptionClass->generateHash($code, 'sha256');

            if ($this->dbClass->checkValueInDB(USERS_TABLE, $column, $hash) && !empty($code)) {
                $this->dbClass->updateValueInKey(USERS_TABLE, $column, $hash, 'n');
                $this->response = $response;
                return true;
            } else {
                $this->error = $error2;
            }
        }
        return false;
    }

    private function emailConfirmed($email) {
        $confirmCode = $this->dbClass->getRow(USERS_TABLE, 'email', $email, "confirm_code");
        return is_y(array_get_if_exist($confirmCode, 'confirm_code'));
    }

    private function emailExist($email) {
        return $this->dbClass->checkValueInDB(USERS_TABLE, 'email', $email);
    }

    private function insertToLoginHistoryDB($email) {
        $browserData = get_browser_data();
        $ipAddress = get_ip();
        if(empty($ipAddress)) $ipAddress = "N/A";

        $visitArray = array(
            'email' => $email,
            'ip_address' => $ipAddress,
            'timestamp' => time(),
            'browser' => ($browserData['name']).' '.$browserData['version'],
            'platform' => $browserData['platform']
        );

        $this->dbClass->insert(LOGIN_HISTORY_TABLE, $visitArray);

        return $this->mailerClass->sendLoginMail($email, $ipAddress, $browserData);
    }
}