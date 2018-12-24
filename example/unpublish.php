<?php

use Erbilen\YandexDisk;
require '../class.yandexdisk.php';

YandexDisk::setCredentials('KADI', 'ŞİFRE');

// abc.jpg dosyasını private yap
echo YandexDisk::unpublish('abc.jpg');