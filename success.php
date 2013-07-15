<?
/*
##############################################
# Onpay payment module for PHPShop           #
# Copyright (c) 2011 Norgen                  #
# Exclusively for Onpay.ru                   #
# mailto:vasily.norman@gmail.com             #
##############################################
*/
if(empty($GLOBALS['SysValue'])) exit(header("Location: /"));

if(isset($_GET['pay_for'])){
$order_metod="onpay";
$success_function=true; // Включаем функцию обновления статуса заказа
$my_crc = "NoN";
$crc = "NoN";
$inv_id = $_GET['pay_for'];
}
?>
