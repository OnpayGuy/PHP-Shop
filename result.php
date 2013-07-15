<?

/*
  ##############################################
  # Onpay payment module for PHPShop           #
  # Copyright (c) 2011 Norgen                  #
  # Exclusively for Onpay.ru                   #
  # mailto:vasily.norman@gmail.com             #
  ##############################################
 */

function UpdateNumOrder($uid) {
    $last_num = substr($uid, -2);
    $total = strlen($uid);
    $ferst_num = substr($uid, 0, ($total - 2));
    return $ferst_num . "-" . $last_num;
}

//Функция выдает ответ для сервиса onpay в формате XML на чек запрос
function answer($type, $code, $pay_for, $order_amount, $order_currency, $text) {
    global $key;
    //echo ("$type;$pay_for;$order_amount;$order_currency;$code;$key");
    $md5 = strtoupper(md5("$type;$pay_for;$order_amount;$order_currency;$code;$key"));
    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result>\n<code>$code</code>\n<pay_for>$pay_for</pay_for>\n<comment>$text</comment>\n<md5>$md5</md5>\n</result>";
}

//Функция выдает ответ для сервиса onpay в формате XML на pay запрос
function answerpay($type, $code, $pay_for, $order_amount, $order_currency, $text, $onpay_id) {
    global $key;
    $md5 = strtoupper(md5("$type;$pay_for;$onpay_id;$pay_for;$order_amount;$order_currency;$code;$key"));
    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result>\n<code>$code</code>\n <comment>$text</comment>\n<onpay_id>$onpay_id</onpay_id>\n <pay_for>$pay_for</pay_for>\n<order_id>$pay_for</order_id>\n<md5>$md5</md5>\n</result>";
}

// Парсим установочный файл
$SysValue = parse_ini_file("../../phpshop/inc/config.ini", 1);
while (list($section, $array) = each($SysValue))
    while (list($key, $value) = each($array))
        $SysValue['other'][chr(73) . chr(110) . chr(105) . ucfirst(strtolower($section)) . ucfirst(strtolower($key))] = $value;

if (isset($_REQUEST['type'])) {

    // perform some action (change order state to paid)
    // Подключаем базу MySQL
    @mysql_connect($SysValue['connect']['host'], $SysValue['connect']['user_db'], $SysValue['connect']['pass_db']) or
            @die("" . PHPSHOP_error(101, $SysValue['my']['error_tracer']) . "");
    mysql_select_db($SysValue['connect']['dbase']) or
            @die("" . PHPSHOP_error(102, $SysValue['my']['error_tracer']) . "");
    $pay_for = $_REQUEST['pay_for'];
    $new_uid = UpdateNumOrder($pay_for);
    $key = $SysValue['onpay']['onpay_key'];
    if ($_REQUEST['type'] == 'check') {
        $order_amount = $_REQUEST['order_amount'];
        $order_currency = $_REQUEST['order_currency'];
        $md5 = $_REQUEST['md5'];
        
// Приверяем сущ. заказа
        $sql = "select uid from " . $SysValue['base']['table_name1'] . " where uid='$new_uid'";
        $result = mysql_query($sql);
        $row = mysql_fetch_array($result);
        $uid = $row['uid'];

        if ($uid == $new_uid) {
            echo (answer($_REQUEST['type'], 0, $pay_for, $order_amount, $order_currency, 'OK')); //Отвечаем серверу OnPay, что все хорошо, можно принимать деньги
        }
        else {
            echo 'Bad check request';
        }
    }
    if ($_REQUEST['type'] == 'pay') {
        $onpay_id = $_REQUEST['onpay_id'];
        $pay_for = $_REQUEST['pay_for'];
        $order_amount = $_REQUEST['order_amount'];
        $order_currency = $_REQUEST['order_currency'];
        $balance_amount = $_REQUEST['balance_amount'];
        $balance_currency = $_REQUEST['balance_currency'];
        $exchange_rate = $_REQUEST['exchange_rate'];
        $paymentDateTime = $_REQUEST['paymentDateTime'];
        $md5 = $_REQUEST['md5'];


        $md5fb = strtoupper(md5($_REQUEST['type'] . ";" . $pay_for . ";" . $onpay_id . ";" . $order_amount . ";" . $order_currency . ";" . $key . ""));
//Сверяем строчки хеша (присланную и созданную нами)
        if ($md5fb != $md5) {
            echo (answerpay($_REQUEST['type'], 7, $pay_for, $order_amount, $order_currency, 'Md5 signature is wrong', $onpay_id));
        }
        else {
            $sql = "INSERT INTO " . $SysValue['base']['table_name33'] . " VALUES ('$pay_for','Onpay Cash Register','$order_amount','" . date("U") . "')";
            $result = mysql_query($sql);
            echo (answerpay($_REQUEST['type'], 0, $pay_for, $order_amount, $order_currency, 'OK', $onpay_id));
        }
    }
}
else {
    echo 'Bad request!';
}
?>
