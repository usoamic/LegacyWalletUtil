<?php

require_once("mailer/PHPMailerAutoload.php");

class MailerClass {
    private $smtp_host = SMTP_HOST;
    private $noreply_address = NOREPLY_ADDRESS;
    private $smtp_password = SMTP_PASSWORD;

    /*
     * Public
     */
    public function sendResetPasswordLink($email, $code)
    {
        $link = get_url().'/?reset_code='.urlencode($code);

        $subject = SITE_TITLE." password reset";

        $body = 'Hello!<br>Someone has requested a link to change your password, and you can do this through the link below:<br>'.$link."<br>
                If you didn't request this, please ignore this email<br>
                Your password won't change until you access the link above and create a new one<br>";

        return $this->sendMail($email, $subject, $body);
    }

    public function sendResetTfaMail($email)
    {
        $subject = SITE_TITLE." 2FA reset";

        $body = "Hello!<br>
                 Someone has requested a 2fa key reset.<br>
                 If you didn't request this, please urgently write to support@usoamic.io.";

        return $this->sendMail($email, $subject, $body);
    }

    public function sendLoginMail($email, $ip, $browserData) {
        $subject = "Successful login to ".SITE_TITLE;
        $browser = get_if_not_empty($browserData['name'])." ".$browserData['version'];
        $body = "Hello!<br>Successful login to ".SITE_TITLE." from IP ".$ip." through ".$browser.".<br>";

        return $this->sendMail($email, $subject, $body);
    }

    public function sendNewPassword($email, $password)
    {
        $body = "Hello!<br>Your password is reset successfully<br>
                 Here is your new password: ".$password."<br>";
        $subject = "Your new password for ".SITE_TITLE;

        return $this->sendMail($email, $subject, $body);
    }

    public function sendUserConfirmationEmail($email, $code)
    {
        $subject = SITE_TITLE." account confirmation";
        $confirm_url = get_url().'/?confirm_code='.$code;
        $body ="Hello!<br>Please confirm your email address by following link: <br>".$confirm_url;

        return $this->sendMail($email, $subject, $body);
    }

    public function sendClosingEmail($recipient)
    {
        $subject = SITE_TITLE." closing";
        $body = "Hey, $recipient!<br><a href='".SITE_URL."'>".SITE_TITLE."</a> will be closed on May 1, 2022. After this date, you will not be able to use the funds in your account, since all coins remaining in the account by this time will be burned. Please WITHDRAW your coins as soon as possible. If you have any problems with the exchange or withdrawal of funds, please immediately write to support@usoamic.io, or to the thread on BitcoinTalk, we will try to answer you within 2-3 days.";

        return $this->sendMail($recipient, $subject, $body);
    }

    /*
     * Private
     */
    private function sendMail($email, $subject, $content)
    {
        $mailer = new PHPMailer;
        $mailer->isSMTP();

        ///////
        $mailer->Host = $this->smtp_host;
        $mailer->Username = $this->noreply_address;
        $mailer->Password = $this->smtp_password;
        $mailer->From = $this->noreply_address;
        $mailer->FromName = SITE_TITLE;
        $mailer->CharSet = 'UTF-8';
        ///////

        $mailer->SMTPAuth = true;
        $mailer->SMTPSecure = 'ssl';
        $mailer->Port = 465;
        $mailer->isHTML(true);
        $mailer->addAddress($email);
        $mailer->Subject = $subject;
        $altbody = str_replace('<br>', '', $content);
        $mailer->Body = $content;
        $mailer->AltBody = $altbody;

        try {
            if (!$mailer->send()) {
                print_r("sendMailError: ".$mailer->ErrorInfo."\n");
                return false;
            }
        } catch (phpmailerException $e) {
            print_r("sendMailError: ".$e->getMessage()."\n");
            return false;
        }
        return true;
    }
}
