<?php
require_once("php/other/defines.php");
require_once("php/other/consts.php");
require_once("php/libs/cfunctions.php");
require_once("php/libs/Telegram.php");
require_once("php/libs/TelegramNotifier.php");
require_once("php/libs/Coin.php");
require_once("php/libs/EncryptionClass.php");
require_once("php/libs/DBClass.php");
require_once("php/libs/NotifierClass.php");
require_once("php/TFAClass.php");
require_once("php/AuthorizationClass.php");
require_once("php/AccountTFA.php");
require_once("php/AccountClass.php");
require_once('ManagerClass.php');

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
        default:
            println('Invalid command');
    }
    print_r("\n");
}
catch (Exception $e) {
    println('error: '.$e->getMessage());
}
