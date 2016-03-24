<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 16/3/24
 * Time: 下午4:15
 */
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
$extract = \Amlun\TLD\Extract::instance();
$tld = $extract->domain('www.amlun.com');
print_r($tld);