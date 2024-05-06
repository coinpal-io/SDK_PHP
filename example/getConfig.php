<?php
include_once "./config.php";
header('Content-Type: application/json');
global $config;
echo json_encode($config, true);
return;

