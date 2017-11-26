<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define("PARANOID", 1);
//define("SQL", 2);
define("SYSTEM", 4);
define("HTML", 8);
define("INT", 16);
define("FLOAT", 32);
define("LDAP", 64);
define("UTF8", 128);

// get magic_quotes_gpc ini setting - jp
$magic_quotes = (bool) @ini_get('magic_quotes_gpc');
if ($magic_quotes == TRUE) { define("MAGIC_QUOTES", 1); } else { define("MAGIC_QUOTES", 0); }

// addslashes wrapper to check for gpc_magic_quotes - gz
function nice_addslashes($string)
{
    // if magic quotes is on the string is already quoted, just return it
    if(MAGIC_QUOTES)
    return $string;
    else
    return addslashes($string);
}



function sanitize_filename($filename, $force_lowercase = true, $alphanumeric = false, $beautify=true) {
    // sanitize filename
    $filename = preg_replace(
       '~
        [<>:"/\\|?*]|
        [\x00-\x1F]|
        [\x7F\xA0\xAD]|
        [#\[\]@!$&\'()+,;=]|
        [{}^\~`]
        ~x',
        '-', $filename);
    // avoids ".", ".." or ".hiddenFiles"
    $filename = ltrim($filename, '.-');
    // optional beautification
    if ($beautify) $filename = beautify_filename($filename);
    // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
    $filename = ($alphanumeric) ? preg_replace("/[^a-zA-Z0-9]/", "", $filename) : $filename ;
    
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($filename, 'UTF-8') :
            strtolower($filename) :
        $filename;
}

function beautify_filename($filename) {
    // reduce consecutive characters
    $filename = preg_replace(array(
        // "file   name.zip" becomes "file-name.zip"
        '/ +/',
        // "file___name.zip" becomes "file-name.zip"
        '/_+/',
        // "file---name.zip" becomes "file-name.zip"
        '/-+/'
    ), '-', $filename);
    $filename = preg_replace(array(
        // "file--.--.-.--name.zip" becomes "file.name.zip"
        '/-*\.-*/',
        // "file...name..zip" becomes "file.name.zip"
        '/\.{2,}/'
    ), '.', $filename);
    // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
    $filename = mb_strtolower($filename, mb_detect_encoding($filename));
    // ".file-name.-" becomes "file-name"
    $filename = trim($filename, '.-');
    return $filename;
}





function sanitize_dirname($string, $force_lowercase = false, $alphanumeric = false) {
    $string = str_replace(".", "", $string);
    return sanitize_filename($string, $force_lowercase, $alphanumeric,false);
}


// paranoid sanitization -- only let the alphanumeric set through
function sanitize_paranoid_string($string, $min='', $max='')
{
    if (isset($string))
    {
        $string = preg_replace("/[^_.a-zA-Z0-9]/", "", $string);
        $len = strlen($string);
        if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
        return FALSE;
        return $string;
    }
}

function sanitize_cquestions($string, $min='', $max='')
{
    if (isset($string))
    {
        $string = preg_replace("/[^_.a-zA-Z0-9+#]/", "", $string);
        $len = strlen($string);
        if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
        return FALSE;
        return $string;
    }
}

// sanitize a string in prep for passing a single argument to system() (or similar)
function sanitize_system_string($string, $min='', $max='')
{
    if (isset($string))
    {
        $pattern = '/(;|\||`|>|<|&|^|"|'."\n|\r|'".'|{|}|[|]|\)|\()/i'; // no piping, passing possible environment variables ($),
        // separate commands, nested execution, file redirection,
        // background processing, special commands (backspace, etc.), quotes
        // newlines, or some other special characters
        $string = preg_replace($pattern, '', $string);
        $string = '"'.preg_replace('/\$/', '\\\$', $string).'"'; //make sure this is only interpretted as ONE argument
        $len = strlen($string);
        if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))	return FALSE;
        return $string;
    }
}

function sanitize_xss_string($string)
{
    if (isset($string))
    {
        $bad = array ('*','^','&',';','\"','(',')','%','$','?');
        return str_replace($bad, '',$string);
    }
}



// sanitize a string for SQL input (simple slash out quotes and slashes)
function sanitize_sql_db_tablename($string)
{
    $bad = array ('*','^','&','\'','-',';','\"','(',')','%','$','?');
    return str_replace($bad, "",$string);
}

// sanitize a string for SQL input (simple slash out quotes and slashes)
function sanitize_ldap_string($string, $min='', $max='')
{
    $pattern = '/(\)|\(|\||&)/';
    $len = strlen($string);
    if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
    return preg_replace($pattern, '', $string);
}


// sanitize a string for HTML (make sure nothing gets interpretted!)
function sanitize_html_string($string)
{
    $pattern[0] = '/\&/';
    $pattern[1] = '/</';
    $pattern[2] = "/>/";
    $pattern[3] = '/\n/';
    $pattern[4] = '/"/';
    $pattern[5] = "/'/";
    $pattern[6] = "/%/";
    $pattern[7] = '/\(/';
    $pattern[8] = '/\)/';
    $pattern[9] = '/\+/';
    $pattern[10] = '/-/';
    $replacement[0] = '&amp;';
    $replacement[1] = '&lt;';
    $replacement[2] = '&gt;';
    $replacement[3] = '<br />';
    $replacement[4] = '&quot;';
    $replacement[5] = '&#39;';
    $replacement[6] = '&#37;';
    $replacement[7] = '&#40;';
    $replacement[8] = '&#41;';
    $replacement[9] = '&#43;';
    $replacement[10] = '&#45;';
    return preg_replace($pattern, $replacement, $string);
}

// make int int!
function sanitize_int($integer, $min='', $max='')
{
    $int = preg_replace("#[^0-9]#", "", $integer);
    if((($min != '') && ($int < $min)) || (($max != '') && ($int > $max)))
    {
        return FALSE;
    }
    if ($int=='')
    {
        return null;
    }
    return $int;
}

// sanitize a username
// TODO: define the exact format of the username
// allow for instance 0-9a-zA-Z@_-.
/**
 * @param string $string
 */
function sanitize_user($string)
{
    $username_length=64;
    $string=mb_substr($string,0,$username_length);
    return $string;
}

// sanitize a username
// TODO: define the exact format of the username
// allow for instance 0-9a-zA-Z@_-.
function sanitize_userfullname($string)
{
    $username_length=50;
    $string=mb_substr($string,0,$username_length);
    return $string;
}

function sanitize_labelname($string)
{
    $labelname_length=100;
    $string=mb_substr($string,0,$labelname_length);
    return $string;
}

// make float float!
function sanitize_float($float, $min='', $max='')
{
    $float = str_replace(',','.',$float);
    // GMP library allows for high precision and high value numbers
    if (function_exists('gmp_init') && defined('GMP_VERSION') && version_compare(GMP_VERSION,'4.3.2')==1)
    {
        $gNumber = gmp_init($float);
        if(($min != '' && gmp_cmp($gNumber,$min)<0) || ($max != '' && gmp_cmp($gNumber,$max)>0))
        {
            return FALSE;
        }
        else
        {
            return gmp_strval($gNumber);
        }
    }
    else
    {
        $fNumber = str_replace(',','.',$float);
        $fNumber = floatval($fNumber);
        if((($min != '') && ($fNumber < $min)) || (($max != '') && ($fNumber > $max)))
            return FALSE;
        return $fNumber;
    }
}


// glue together all the other functions
function sanitize($input, $flags, $min='', $max='')
{
    if($flags & PARANOID) $input = sanitize_paranoid_string($input, $min, $max);
    if($flags & INT) $input = sanitize_int($input, $min, $max);
    if($flags & FLOAT) $input = sanitize_float($input, $min, $max);
    if($flags & HTML) $input = sanitize_html_string($input, $min, $max);
    if($flags & LDAP) $input = sanitize_ldap_string($input, $min, $max);
    if($flags & SYSTEM) $input = sanitize_system_string($input, $min, $max);
    return $input;
}

function check_paranoid_string($input, $min='', $max='')
{
    if($input != sanitize_paranoid_string($input, $min, $max))
    return FALSE;
    return TRUE;
}

function check_int($input, $min='', $max='')
{
    if($input != sanitize_int($input, $min, $max))
    return FALSE;
    return TRUE;
}

function check_float($input, $min='', $max='')
{
    if($input != sanitize_float($input, $min, $max))
    return FALSE;
    return TRUE;
}

function check_html_string($input, $min='', $max='')
{
    if($input != sanitize_html_string($input, $min, $max))
    return FALSE;
    return TRUE;
}


function check_ldap_string($input, $min='', $max='')
{
    if($input != sanitize_string($input, $min, $max))
    return FALSE;
    return TRUE;
}

function check_system_string($input, $min='', $max='')
{
    if($input != sanitize_system_string($input, $min, $max, TRUE))
    return FALSE;
    return TRUE;
}

// glue together all the other functions
function check($input, $flags, $min='', $max='')
{
    $oldput = $input;
    if($flags & UTF8) $input = my_utf8_decode($input);
    if($flags & PARANOID) $input = sanitize_paranoid_string($input, $min, $max);
    if($flags & INT) $input = sanitize_int($input, $min, $max);
    if($flags & FLOAT) $input = sanitize_float($input, $min, $max);
    if($flags & HTML) $input = sanitize_html_string($input, $min, $max);
    if($flags & LDAP) $input = sanitize_ldap_string($input, $min, $max);
    if($flags & SYSTEM) $input = sanitize_system_string($input, $min, $max, TRUE);
    if($input != $oldput)
    return FALSE;
    return TRUE;
}

function sanitize_languagecode($codetosanitize) {
    return preg_replace('/[^a-z0-9-]/i', '', $codetosanitize);
}

function sanitize_languagecodeS($codestringtosanitize) {
    $codearray=explode(" ",trim($codestringtosanitize));
    $codearray=array_map("sanitize_languagecode",$codearray);
    return implode(" ",$codearray);
}

/**
 * @deprecated use Token::sanitizeToken($codetosanitize);
 */
function sanitize_token($codetosanitize) {
    return Token::sanitizeToken($codetosanitize);
}

function sanitize_signedint($integer, $min='', $max='')
{
    $int  = (int) $integer;

    if((($min != '') && ($int < $min)) || (($max != '') && ($int > $max)))
    {
        return FALSE;                              // Oops! Outside limits.
    }

    return $int;
};
