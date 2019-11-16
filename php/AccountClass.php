<?php

class AccountClass {
    use NotifierClass;
    use TelegramNotifier;

    private
        $email,
        $db;

    public function __construct($email)
    {
        $this->email = $email;
        $this->db = new DBClass();
    }

    public function getEmail() {
        return $this->email;
    }

    public function getLoginHistory() {
        $arr = $this->getData(LOGIN_HISTORY_TABLE);
        array_reverse_sort($arr, 'timestamp');
        return $arr;
    }

    public function getAllTransactions() {
        $transactions = $this->getTransactions();
        $withdrawals = $this->getWithdrawals();
        $arr = array_merge($transactions, $withdrawals);
        array_reverse_sort($arr, 'time');
        return $arr;
    }

    public function getPreparedTransactions($limit = -1)
    {
        $userEmail = $this->getEmail();
        $transactionHistory = $this->getAllTransactions();
        $txList = array();
        if($limit == -1) {
            $limit = count($transactionHistory);
        }
        for ($i = 0; $i < $limit; $i++) {
            $item = $transactionHistory[$i];
            $type = $item['type'];

            $amount = Coin::toCoinPlainString($item['amount']);
            $isMoved = ($type == TX_MOVED);

            $address = "N/A";
            if($type != TX_SENT) {
                $fromAddress = $item["from_address"];
                $toAddress = $item["to_address"];
                if(compare($fromAddress, STAKING_ACCOUNT)) {
                    $type = TX_MINED;
                }
                else {
                    if (($isMoved && compare($userEmail, $fromAddress)) || ($type == TX_LEGACY_SENT)) {
                        $address = $toAddress;
                    } else if (($isMoved && compare($userEmail, $toAddress)) || ($type == TX_RECEIVED)) {
                        $address = $fromAddress;
                    }
                }
            }
            else {
                $address = $item["address"];
            }
            $item = array(
                "type" => getTransactionType($type),
                "date" => gdate($item['time']),
                "amount" => abs(($type == TX_SENT) ? $amount : $amount*SWAP_FACTOR),
                "address" => $address,
                "status" => getTransactionStatus($item['status'])
            );
            array_push($txList, $item);
        }
        return $txList;
    }

    public function getWithdrawals() {
        return $this->getData(WITHDRAWALS_TABLE);
    }

    public function getTransactions() {
        return $this->getData(TRANSACTIONS_TABLE);
    }

    public function changePassword() {
        $currentPassword = get_post_value('current_password');
        $newPassword = get_post_value('new_password');
        $confirmNewPassword = get_post_value('confirm_new_password');
        $code = get_post_value('authenticator_code');

        return $this->changePasswordByParams($currentPassword, $newPassword, $confirmNewPassword, $code);
    }

    private function changePasswordByParams($current_password, $new_password, $confirm_new_password, $code) {
        if(is_empty($current_password)) {
            return $this->failure(CURRENT_PASSWORD_REQUIRED);
        }
        else if(is_empty($new_password)) {
            return $this->failure(NEW_PASSWORD_REQUIRED);
        }
        else if(is_empty($confirm_new_password)) {
            return $this->failure(CONFIRM_PASSWORD_REQUIRED);
        }
        else if($new_password != $confirm_new_password) {
            return $this->failure(PASSWORDS_DONT_MATCH);
        }
        else if($current_password == $new_password) {
            return $this->failure(CURRENT_PASSWORD_AND_NEW_PASSWORD_MATCH);
        }
        else {
            $row = $this->getUserData('password');
            $tfa = $this->getTfa();
            if(!$tfa->isValid($code)) {
                return $this->failure($tfa->getError());
            }
            if(password_verify($current_password, $row['password'])) {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $this->db->updateValue(USERS_TABLE, 'password', $password_hash, 'email', $this->getEmail());
                return $this->success(PASSWORD_CHANGED);
            }
            else {
                return $this->failure(INVALID_CURRENT_PASSWORD);
            }
        }
    }

    public function tfaAction($code) {
        $tfa = $this->getTfa();
        $tfaEnabled = $tfa->isEnabled();

        if(!$tfa->isValid($code, true)) {
            return $this->failure($tfa->getError());
        }

        $updArray = array('tfa_status' => (($tfaEnabled) ? 'n' : 'y'));
        if($tfaEnabled) {
            $nSecret = $tfa->generateSecret();
            if(is_empty($nSecret)) {
                return $this->failure(UNKNOWN_ERROR);
            }
            $updArray = array_merge($updArray, array('secret_key' => $nSecret));
        }
        $this->db->updateValues(USERS_TABLE, $updArray, 'email', $this->getEmail());

        return $this->success((($tfaEnabled) ? TFA_DISABLED : TFA_ENABLED));
    }

    public function getTfa() {
        return new AccountTFA($this->getTfaData());
    }

    public function getSummary() {
        $received = $this->getReceived();
        $balance = (doubleval($received))*(doubleval(SWAP_FACTOR));
        $balance -= $this->getWithdrawn();
        return array(
            'balance' => Coin::toCoinPlainString($balance),
            'exchanged' => Coin::toCoinPlainString($received),
            'supply' => SUPPLY
        );
    }

    public function getWithdrawn() {
        $key = 'withdrawn';
        return $this->getUserData($key)[$key];
    }

    public function getReceived() {
        $key = 'received';
        return $this->getUserData($key)[$key];
    }

    private function getBalance() {
        $received = $this->getReceived();
        $balance = (doubleval($received))*(doubleval(SWAP_FACTOR));
        $balance -= $this->getWithdrawn();

        return $balance;
    }

    public function send() {
        $tfa = $this->getTfa();
        $amount = get_post_value('amount');
        $address = get_post_value('address');
        $code = get_post_value('authenticator_code');


        if(is_empty($address)) {
            return $this->failure(ADDRESS_REQUIRED);
        }
        else if(!isValidAddress($address)) {
            return $this->failure(INVALID_ADDRESS);
        }
        else if(!$tfa->isValid($code)) {
            return $this->failure($tfa->getError());
        }
        if(!ENABLED_WITHDRAW) {
            return $this->failure(SENDING_DISABLED);
        }

        if($amount < MIN_TX_AMOUNT) {
            return $this->failure(AMOUNT_MUST_BE_GREATER_THAN);
        }

        $amount *= 1e8;

        $balance = $this->getBalance();

        if($balance < $amount) {
            return $this->failure(INSUFFICIENT_FUNDS);
        }

        $this->sendToAddress($amount, $address);
        return $this->success(TOKENS_SENT);
    }

    public function sendToAddress($amount, $address) {
        $sendParams = array(
            'type' => TX_SENT,
            'amount' => $amount,
            'address' => $address,
            'email' => $this->getEmail(),
            'time' => time(),
            'status' => TX_PENDING
        );
        $this->db->insert(WITHDRAWALS_TABLE, $sendParams);
        $this->db->increaseValue(USERS_TABLE, 'email', $this->getEmail(), 'withdrawn', $amount);
        $this->sendNotification('New withdraw: '.$amount);
    }

    private function getTfaData() {
        return $this->getUserData('tfa_status secret_key');
    }

    private function getUserData($columns = '') {
        return $this->db->getRow(USERS_TABLE, 'email', $this->email, $columns);
    }

    private function getData($table) {
        return $this->db->getRows($table, 'email', $this->email);
    }
}
