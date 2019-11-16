<?php

require_once("../php/other/defines.php");
require_once("../php/other/consts.php");
require_once("../php/libs/cfunctions.php");
require_once("../php/libs/Telegram.php");
require_once("../php/libs/TelegramNotifier.php");
require_once("../php/libs/EncryptionClass.php");
require_once("../php/libs/DBClass.php");
require_once("../php/libs/NotifierClass.php");
require_once("../php/other/dfunctions.php");
require_once("../php/daemon/jsonRPCClient.php");
require_once("../php/daemon/UCoinRPC.php");
require_once("../php/TFAClass.php");
require_once("../php/AuthorizationClass.php");
require_once("../php/AccountTFA.php");
require_once("../php/LastClass.php");
require_once("../php/AccountClass.php");
require_once('ProcessClass.php');

$processClass = new ProcessClass();
while (true) {
    echo "Processing...\n";
    $time = time();
    $processClass->processTx();
    echo "Processed: ".(time() - $time)." Secs\n";
    sleep(60);
}
