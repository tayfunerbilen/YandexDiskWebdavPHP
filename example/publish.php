<?php

use Erbilen\YandexDisk;
require '../class.yandexdisk.php';

YandexDisk::setCredentials('KADI', 'ŞİFRE');

// abc.jpg dosyasını publish et, geriye linki döner
echo YandexDisk::publish('abc.jpg');