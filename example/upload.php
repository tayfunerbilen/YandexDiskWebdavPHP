<?php

use Erbilen\YandexDisk;
require '../class.yandexdisk.php';

YandexDisk::setCredentials('KADI', 'ŞİFRE');

// abc.jpg olarak kaydet
echo YandexDisk::upload('dosya.jpg', 'abc.jpg');

// $_FILEs ile dosya yükleme
// YandexDisk::upload($_FILES['file']['tmp_name'], 'test.zip')