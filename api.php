<?php
require_once 'AppInfo.php';
require_once 'utils.php';
require_once 'lib/pgps-fb.php';

// Enforce HTTPS on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1')) {
    header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Load requested data.
$person = new PersonWithPronouns((int) $_GET['id']);
// Return requested data in JSON format.
print $person->jsonSerialize();
