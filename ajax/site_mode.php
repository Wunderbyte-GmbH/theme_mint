<?php

require(dirname(__DIR__, 3) . '/config.php');

$targetedmode = required_param('targetedmode', PARAM_TEXT);
$_SESSION['currentmode'] = $targetedmode;
echo json_encode(array('currentmode' => $targetedmode));