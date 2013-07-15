<?
/*
##############################################
# Onpay payment module for PHPShop           #
# Copyright (c) 2011 Norgen                  #
# Exclusively for Onpay.ru                   #
# mailto:vasily.norman@gmail.com             #
##############################################
*/
if (empty($GLOBALS['SysValue']))
    exit(header("Location: /"));

$cart_list = Summa_cart();
$ChekDiscount = ChekDiscount($cart_list[1]);
$GetDeliveryPrice = GetDeliveryPrice($_POST['dostavka_metod'], $cart_list[1], $cart_list[2]);
$sum_pol = (ReturnSummaNal($cart_list[1], $ChekDiscount[0]) + $GetDeliveryPrice);
$price = number_format($sum_pol, 1, ".", "");

// регистрационная информация
$onpay_login = $SysValue['onpay']['onpay_login'];    
$secret_key = $SysValue['onpay']['onpay_key'];
$onpay_currency = $SysValue['onpay']['onpay_currency'];
$onpay_convert = $SysValue['onpay']['onpay_convert'];

//параметры магазина
$mrh_ouid = explode("-", $_POST['ouid']);
$pay_for = $mrh_ouid[0] . "" . $mrh_ouid[1];

$SuccesUrl="http://".$_SERVER['SERVER_NAME']."/success/";
$FailedUrl="http://".$_SERVER['SERVER_NAME']."/fail/";
// формирование подписи
$crc = md5("fix;$price;$onpay_currency;$pay_for;yes;$secret_key");

// вывод HTML страницы с кнопкой для оплаты
$disp = "
<div align=\"center\">
<img src=\"http://wiki.onpay.ru/lib/exe/fetch.php?media=onpay_logo.jpg\">
<p>
Платежный агрегатор «OnPay» предлагает услугу по организации приема электронных платежей на вашем сайте всеми наиболее распространенными платежными системами интернета. </p>
</br>
<p>
Собранные электронные деньги могут быть автоматически отконвертированы и выведены в другие, нужные вам электронные системы платежей или выведены на расчетный счет. Перечисление собранной выручки осуществляется в рублях (для иностранных компаний перечисление производится в долларах США или в иной валюте). 
<br></p>

      <form action='http://secure.onpay.ru/pay/$onpay_login' method=GET name=\"pay\">
      <input type=hidden name=pay_mode value=\"fix\">
      <input type=hidden name=pay_for value=$pay_for>
      <input type=hidden name=price value=$price>
      <input type=hidden name=currency value=$onpay_currency>
      <input type=hidden name=convert value=$onpay_convert>
      <input type=hidden name=md5 value=$crc>
      <input type=hidden name=url_success value=$SuccesUrl>
      <input type=hidden name=url_fail value=$FailedUrl>
    <table>
<tr><td><img src=\"images/shop/icon-setup.gif\" width=\"16\" height=\"16\" border=\"0\"></td>
	<td align=\"center\"><a href=\"javascript:history.back(1)\"><u>
	Вернуться к оформлению<br>
	покупки</u></a></td>
	<td width=\"20\"></td>
	<td><img src=\"images/shop/icon-client-new.gif\" alt=\"\" width=\"16\" height=\"16\" border=\"0\" align=\"left\">
	<a href=\"javascript:pay.submit();\">Оплатить через платежную систему</a></td>
</tr>
</table>
      </form>
</div>";
?>
