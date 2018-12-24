<?php

use Erbilen\YandexDisk;
require '../class.yandexdisk.php';

YandexDisk::setCredentials('KADI', 'ŞİFRE');

// abc.jpg dosyasını sil
echo YandexDisk::delete('abc.jpg');