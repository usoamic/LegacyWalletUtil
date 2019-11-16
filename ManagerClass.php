<?php
class ManagerClass
{
    private $db;

    public function __construct()
    {
        $this->db = new DBClass();
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

    public function getAccounts($key = "", $value = "") {
        $accounts = $this->db->getRows(USERS_TABLE, $key, $value, "email received withdrawn");
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

    private function prepareTransactions($withdrawals) {
        foreach ($withdrawals as &$item) {
            $status = $item['status'];
            $time = $item['time'];
            $item['status'] = getTransactionStatus($status);
            $item['time'] = gdate($time);
        }
        return $withdrawals;
    }
}