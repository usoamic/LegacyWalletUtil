<?php
require_once("include.php");

$managerClass = new ManagerClass();
$action = $argv[1];

try {
    switch ($action) {
        case "help":
            $helpArr = array(
                'get_account EMAIL',
                'get_accounts',
                'get_withdrawal ID',
                'get_withdrawals STATUS {TX_PENDING = 1}, {TX_CONFIRMED = 2}, {TX_REJECTED = 3}',
                'reset_2fa EMAIL',
                'replace_mail OLD_EMAIL NEW_EMAIL',
                'count_balances'
            );
            print_r($helpArr);
            break;
        case "get_account":
            print_r($managerClass->getAccount($argv[2]));
            break;
        case "get_accounts":
            print_r($managerClass->getAccounts());
            break;
        case "get_withdrawal":
            print_r($managerClass->getWithdrawal($argv[2]));
            break;
        case "get_withdrawals":
            print_r($managerClass->getWithdrawals($argv[2]));
            break;
        case "reset_2fa":
            print_r($managerClass->resetTfa($argv[2]));
            break;
        case "replace_mail":
            print_r(
                $managerClass->replaceMail(
                    $argv[2],
                    $argv[3]
                )
            );
            break;
        case "count_balances":
            print_r(
                $managerClass->countBalances()
            );
            break;
        case 'send_closing':
            $managerClass->sendClosingEmails();
            break;
        default:
            println('Invalid command');
    }
    print_r("\n");
}
catch (Exception $e) {
    println('error: '.$e->getMessage());
}
