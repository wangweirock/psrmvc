<?php
include BASE_PATH.'bootstrap/Psr4AutoloadClass.php';
$load = new Psr4AutoloadClass();

$load->addNamespace('Controller', 'app/controller');
$load->addNamespace('Model', 'app/model');
$load->addNamespace('Framework', 'vendor/bob/framework/src');

$load->register();
