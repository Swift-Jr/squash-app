<?php

$config['protocol'] = 'smtp';
$config['smtp_host'] = 'smtp.sendgrid.net';
$config['smtp_port'] = 25;//465;
$config['smtp_user'] = 'apikey';
$config['smtp_pass'] = ENV_SENDGRID_API_KEY;
//$config['crlf'] = '\r\n';
//$config['newline'] = '\r\n';
//$config['wordwrap'] = false;
