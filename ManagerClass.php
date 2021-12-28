<?php

class ManagerClass
{
    private $db;
    private $tfa;
    private $mailer;

    public function __construct() {
        $this->db = new DBClass();
        $this->tfa = new TFAClass();
        $this->mailer = new MailerClass();
    }

    public function getAccount($email) {
        $account = $this->getAccounts("email", $email);
        $account['transactions'] = $this->getTransactions($email);
        return $account;
    }

    private function getTransactions($email) {
        $accountClass = new AccountClass($email);
        $transactions = $accountClass->getAllTransactions();
        return $this->prepareTransactions($transactions);
    }

    public function getAllAccounts($key = "", $value = "") {
        return $this->db->getRows(USERS_TABLE, $key, $value, "email received withdrawn tfa_status");
    }

    public function getAccounts($key = "", $value = "") {
        $accounts = $this->getAllAccounts($key, $value);
        $arr = array();
        $balance = 0;
        foreach ($accounts as $item) {
            if(!empty($item['received'])) {
                $received = $item['received'];
                $userBalance = Coin::toCoin($received);
                if($userBalance > 1) {
                    $balance += $received;
                    array_push(
                        $arr,
                        array(
                            'email' => $item['email'],
                            'received' => $userBalance,
                            'balance' => $userBalance * SWAP_FACTOR,
                            'tfa_status' => $item["tfa_status"],
                            'withdrawn' => Coin::toCoin($item['withdrawn'])
                        )
                    );
                }
            }
        }
        array_push($arr, Coin::toCoin($balance));
        return $arr;
    }

    public function getWithdrawal($id) {
        return $this->db->getRows(WITHDRAWALS_TABLE, 'id', $id);
    }

    public function getWithdrawals($status) {
        if(!is_numeric($status)) {
            throw new Exception('Invalid status');
        }
        $withdrawals = $this->db->getRows(WITHDRAWALS_TABLE, 'status', $status);
        return $this->prepareTransactions($withdrawals);
    }

    public function resetTfa($email) {
        $accountClass = new AccountClass($email);

        $this->mailer->sendResetTfaMail($email);

        if (!$accountClass->getTfa()->isEnabled()) {
            throw new Exception("2FA is disabled for $email");
        }

        $secretKey = $this->getUserData($email, "secret_key")['secret_key'];
        $code = $this->tfa->getCode($secretKey);

        return $accountClass->tfaAction($code);
    }


    public function replaceMail($oldEmail, $newMail) {
        $this->db->updateValueInKey(USERS_TABLE, 'email', $oldEmail, $newMail);
        return "Mail replaced from $oldEmail to $newMail";
    }

    public function countBalances() {
        $accounts = $this->getAccounts();
        $allBalance = 0.0;

        foreach ($accounts as $account) {
            $allBalance += $account['balance'];
        }

        return $allBalance;
    }

    public function sendClosingEmails() {
        $accounts = $this->getAllAccounts();

        for($i = 0; $i < count($accounts); $i++) {
            $acc = $accounts[$i];
            $email = $acc["email"];
            $this->mailer->sendClosingEmail($email);
            print_r("$i. Sent mail to $email.\n");
            $sleep = rand(150, 1200);
            print_r("Waiting $sleep...\n");
            sleep($sleep);
        }
        $count = count($accounts);

        print_r("Accounts finished($count)\n");
    }

    private function prepareTransactions($withdrawals) {
        foreach ($withdrawals as &$item) {
            $status = $item['status'];
            $time = $item['time'];
            $item['status'] = getTransactionStatus($status);
            $item['time'] = gdate($time);
        }
        return $withdrawals;
    }

    private function getUserData($email, $columns = '*')
    {
        return $this->db->getRow(USERS_TABLE, 'email', $email, $columns);
    }
}