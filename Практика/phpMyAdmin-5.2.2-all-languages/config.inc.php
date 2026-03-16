<?php

declare(strict_types=1);

$cfg['blowfish_secret'] = 'k8F3zJ9mN2pQ7vR1wX4yB6cA0dE5gH8i';

$i = 0;

$i++;
$cfg['Servers'][$i]['auth_type'] = 'cookie';
$cfg['Servers'][$i]['host'] = '127.127.126.31';
$cfg['Servers'][$i]['port'] = '3306';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = true;

$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
$cfg['TempDir'] = '../temp/phpmyadmin';
$cfg['DefaultLang'] = 'ru';
$cfg['ShowPhpInfo'] = true;
$cfg['MaxRows'] = 50;
