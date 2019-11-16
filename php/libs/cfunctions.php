<?php
function get_ip() {
    if (!empty($_SERVER['HTTP_X_REAL_IP']))   //check ip from share internet
    {
        return $_SERVER['HTTP_X_REAL_IP'];
    }
    else if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    return $_SERVER['REMOTE_ADDR'];
}

function gdate($timestamp, $format = FORMAT_OF_DATE, $default = 'N/A') {
    if(!is_numeric($timestamp) || $timestamp < 0) return $default;
    $dt = new DateTime('@'.$timestamp);
    return $dt->format($format);
}


function array_get_if_exist($arr, $key, $default = "") {
    return ((array_key_exists($key, $arr)) ? $arr[$key] : $default);
}

function get_if_not_empty($str, $default = "") {
    return ((is_empty($str)) ? $default : $str);
}

function get_qr_code($str, $size = '150x150') {
    return ("https://chart.googleapis.com/chart?chs=".$size."&chld=M|0&cht=qr&chl=".$str);
}

function get_browser_data()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?

    if (preg_match('/iPad|iPhone|iPod/i', $u_agent)) {
        $platform = 'iOS';
    }
    elseif (preg_match('/linux/i', $u_agent)) {
        $platform = 'Linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'Macintosh';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'Windows';
    }
    elseif (preg_match('/windows phone/i', $u_agent)) {
        $platform = 'Windows Phone';
    }
    elseif (preg_match('/android/i', $u_agent)) {
        $platform = 'Android';
    }


    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif(preg_match('/Firefox/i',$u_agent))
    {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif(preg_match('/Chrome/i',$u_agent))
    {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif(preg_match('/Safari/i',$u_agent))
    {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    elseif(preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Opera';
        $ub = "Opera";
    }
    elseif(preg_match('/Netscape/i',$u_agent))
    {
        $bname = 'Netscape';
        $ub = "Netscape";
    }

    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }

    // check if we have a number
    if ($version==null || is_empty($version)) { $version = "";}

    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}

function is_empty($var) {
    if(is_array($var)) {
        return (count($var) == 0);
    };
    return (((strlen($var) == 0) || is_null($var)));
}

function random_string($length = 24) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function number_to_string($number, $precision = 8) {
    return sprintf("%.".$precision."f", $number);
}

function get_text_and_qrcode($text, $image = '', $size = "219x219") {
    if(is_empty($image)) $image = get_qr_code($text, $size);
    return '<div align="center"><p><img src="'.$image.'"></p><p><h6 class="qr-text">'.$text.'</h6></p></div><br>';
}

function str_to_lad($str) { //delete spaces and string to lower
    return strtolower(remove_spaces($str));
}

function is_y($var) {
    if(!isset($var)) return false;
    if(empty($var)) return false;
    return (compare($var, 'y'));
}

function is_n($var) {
    if(!isset($var)) return false;
    if(empty($var)) return false;
    return (compare($var, 'n'));
}

function get_post_value($name, $tolower = false)
{
    return get_vars($name, 'post', $tolower);
}

function get_get_value($name, $tolower = false)
{
    return get_vars($name, 'get', $tolower);
}

function set_session_value($key, $value) {
    $_SESSION[$key] = $value;
}

function get_vars($name, $type, $tolower = false, $destroy = false) {
    if(isset_get($name) && (compare($type, 'get'))) {
        $getVal = $_GET[$name];
        return htmlentities((($tolower) ? strtolower($getVal) : $getVal));
    }
    else if(isset_post($name) && (compare($type, 'post'))) {
        $postVal = $_POST[$name];
        return htmlentities((($tolower) ? strtolower($postVal) : $postVal));
    }
    else if(isset_session($name) && (compare($type, 'session'))) {
        $sessionVal = $_SESSION[$name];
        if($destroy) unset($_SESSION[$name]);
        return htmlentities((($tolower) ? strtolower($sessionVal) : $sessionVal));
    }
    return '';
}

function replace_spaces($str, $replacement = "") {
    return preg_replace('/\s+/', $replacement, $str);
}

function remove_spaces($str) {
    return replace_spaces($str);
}

function array_reverse_sort(&$array, $key) {
    usort($array, function($a, $b) use ($key) {
        if(!array_key_exists($key, $a) || !array_key_exists($key, $b)) return null;
        return $b[$key] - $a[$key];
    });
}

function isset_get($var) {
    return isset($_GET[$var]);
}

function isset_post($var) {
    return isset($_POST[$var]);
}

function isset_session($var) {
    return (isset($_SESSION[$var]) && !empty($_SESSION[$var]));
}

function die_redirect($error = UNKNOWN_ERROR) {
    die('error: '.get_string($error));
}

function get_url() {
    $scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
    return ($scriptFolder.$_SERVER['HTTP_HOST']);
}

function println($str) {
    print_r($str."\n");
}

function get_url_content($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}


function redirect_to_url($url, $error = 2)
{
    if(!headers_sent()) {
        header("Location: $url");
        exit;
    }
    else {
        echo '<script>alert("'.get_string($error).'");</script>';
    }
}

function compare($str1, $str2, $case_sensitive = false) {
    $str1 = remove_spaces($str1);
    $str2 = remove_spaces($str2);
    return ((($case_sensitive) ? strcmp($str1, $str2) : strcasecmp($str1, $str2)) == 0);
}

function isValidAddress($address) {
    return preg_match('/^(0x)?[0-9a-f]{40}$/i', strtolower($address));
}
?>
