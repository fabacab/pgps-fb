<?php

/**
 * @return the value at $index in $array or $default if $index is not set.
 */
function idx(array $array, $key, $default = null) {
  return array_key_exists($key, $array) ? $array[$key] : $default;
}

function he ($str) {
    return htmlentities($str, ENT_QUOTES, 'UTF-8');
}

function fullUrl ($page, $encode = false) {
    $https = ($_SERVER['HTTPS']) ? 'https' : 'http';
    $ret = "$https://{$_SERVER['HTTP_HOST']}/$page";
    return ($encode) ? $encode($ret) : $ret;
}
function getFacebookAppToken () {
    $url = 'https://graph.facebook.com/oauth/access_token?'.
           'client_id=' . getenv('FACEBOOK_APP_ID') .
           '&client_secret=' . getenv('FACEBOOK_SECRET') .
           '&grant_type=client_credentials';
    $res = file_get_contents($url);
    list(, $token) = explode('=', $res);
    return $token;
}
function getFlashMessage ($output = 'html', $before = '<li>', $after = '</li>') {
    global $pgps_flashmsg;
    if (count($pgps_flashmsg)) {
        $out = '';
        foreach ($pgps_flashmsg as $msg) {
            switch ($output) {
                case 'HTML':
                case 'html':
                    $out .= '<ul>';
                    $out .= $before . he($msg) . $after;
                    $out .= '</ul>';
                default:
                    break;
            }
        }
        return $out;
    }
}
