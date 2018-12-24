<?php

use Erbilen\YandexDisk;
require '../class.yandexdisk.php';

YandexDisk::setCredentials('KADI', 'ŞİFRE');

// ana dizindeki klasör ve dosyaları listele
echo YandexDisk::listFolder();

// test klasörü içindeki klasör ve dosyaları listele
//echo YandexDisk::listFolder('test');