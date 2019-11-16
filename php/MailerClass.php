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
        if(!$this->sendMail($email, $subject, $body))
        {
            die_redirect();
            return false;
        }
        return true;
    }

    public function sendLoginMail($email, $ip, $browserData) {
        $subject = "Successful login to ".SITE_TITLE;
        $browser = get_if_not_empty($browserData['name'])." ".$browserData['version'];
        $body = "Hello!<br>Successful login to ".SITE_TITLE." from IP ".$ip." through ".$browser.".<br>";

        if(!$this->sendMail($email, $subject,  $body))
        {
            return false;
        }
        return true;
    }

    public function sendNewPassword($email, $password)
    {
        $body = "Hello!<br>Your password is reset successfully<br>
                 Here is your new password: ".$password."<br>";
        $subject = "Your new password for ".SITE_TITLE;

        if(!$this->sendMail($email, $subject, $body))
        {
            die_redirect();
            return false;
        }
        return true;
    }

    public function sendUserConfirmationEmail($email, $code)
    {
        $subject = SITE_TITLE." account confirmation";
        $confirm_url = get_url().'/?confirm_code='.$code;
        $body ="Hello!<br>Please confirm your email address by following link: <br>".$confirm_url;
        if(!$this->sendMail($email, $subject,  $body))
        {
            die_redirect();
            return false;
        }
        return true;
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

        if (!$mailer->send()) {
            die_redirect();
            return false;
        }
        return true;
    }
}
