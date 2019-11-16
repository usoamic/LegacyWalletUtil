<?php
class ProcessClass
{
    private
        $rpc,
        $db,
        $last;

    public function __construct()
    {
        $this->rpc = new UCoinRPC();
        $this->db = new DBClass();
        $this->last = new LastClass();
    }

    public function processTx() {
        $lastTimestamp = (int)$this->last->getTxTimestamp();
        $txList = $this->rpc->getListTransactions();
        sleep(2);
        $height = $this->rpc->getCurrentBlock();
        $this->last->setHeight($height);

        foreach ($txList as $tx) {
            $toAddress = $tx['address'];
            $email = $this->db->getValue(USERS_TABLE, 'address', $toAddress, 'email');
            $confirmations = $tx['confirmations'];
            $category = $tx['category'];

            if($confirmations >= TX_CONFIRMATION_AMOUNT && !is_empty($email) && compare($category, "receive")) {
                $timestamp = $tx['time'];
                if($timestamp > $lastTimestamp) {
                    $txId = $tx['txid'];
                    $txData = $this->rpc->getTransaction($txId);
                    $fromAddress = $txData['vout'][0]['scriptPubKey']['addresses'][0];
                    if(!isset($fromAddress)) {
                        $fromAddress = "N/A";
                    }
                    $amount = $tx['amount']*1e8;

                    $txArr = array(
                        'amount' => $amount,
                        'time' => $timestamp,
                        'timereceived' => $tx['timereceived'],
                        'email' => $email,
                        'type' => 'received',
                        'txid' => $tx['txid'],
                        'blockhash' => $tx['blockhash'],
                        'ticker' => COIN_TICKER,
                        'from_address' => $fromAddress,
                        'to_address' => $toAddress,
                        'status' => TX_CONFIRMED
                    );
                    $this->db->insert(TRANSACTIONS_TABLE, $txArr);
                    $this->db->changeValue(USERS_TABLE, 'email', $email, 'received', $amount, DBClass::INCREASE);
                    $this->last->setTxTimestamp($timestamp);
                    print_r("Added TX: ".json_encode($txArr)."\n");
                }
            }
        }
    }
}
/*

 */