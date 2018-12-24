<?php

use Erbilen\YandexDisk;
require '../class.yandexdisk.php';

YandexDisk::setCredentials('KADI', 'ŞİFRE');

// abc.jpg dosyasını adını yeni.jpg yap ve test klasörüne taşı
echo YandexDisk::move('abc.jpg', 'test/yeni.jpg');