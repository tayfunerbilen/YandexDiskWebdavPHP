<?php

use Erbilen\YandexDisk;
require '../class.yandexdisk.php';

YandexDisk::setCredentials('KADI', 'ŞİFRE');

// disk'teki abc.jpg dosyasını test.jpg olarak kaydet
echo YandexDisk::download('abc.jpg', 'test.jpg');