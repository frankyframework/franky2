<?php
ini_set('ignore_repeated_errors',true);
define ("_PHP_ERROR_LOG_", PROJECT_DIR."/logs/error_php.log");
ini_set('error_log',_PHP_ERROR_LOG_);
date_default_timezone_set('America/Mexico_City');

require_once(PROJECT_DIR.'/php-gettext/gettext.inc');
include_once(PROJECT_DIR."/modulos/base/loads/util.php");
include_once(PROJECT_DIR."/modulos/base/loads/util.shell.php");
include_once(PROJECT_DIR."/modulos/base/loads/constantes.php");
include_once(PROJECT_DIR."/modulos/base/loads/autoload.php");

$MyConfigure        = new \Franky\Core\configure();


$available_debug_ip = explode(",",getCoreConfig('base/debug/ip'));
$enable_debug_php = getCoreConfig('base/debug/display_errors');
$enable_debug_site = getCoreConfig('base/debug/debug');
$session_time = getCoreConfig('base/server/session_time');
$session_autorenew = getCoreConfig('base/server/session_renew');

$enable_ip = 0;

if(!empty($session_time)) {
    // Set the maxlifetime of session
    ini_set( "session.gc_maxlifetime", $session_time );
    // Also set the session cookie timeout
    ini_set( "session.cookie_lifetime", $session_time );
}

session_start();

$sessionName = session_name();

if($session_autorenew == 1 && isset( $_COOKIE[ $sessionName ] ) ) {

	setcookie( $sessionName, $_COOKIE[ $sessionName ], time() + $session_time, '/' );
}

if(empty($available_debug_ip)):
    $available_debug_ip = array('%');
endif;

foreach($available_debug_ip as $debug_ip):
    if(in_array($debug_ip,array($_SERVER['REMOTE_ADDR'],'%'))):
        $enable_ip = 1;
        break;
    endif;
endforeach;
if($enable_debug_php== 1 && $enable_ip == 1):
    $enable_debug_php = 1;
else:
    $enable_debug_php = 0;
endif;
if($enable_debug_site== 1 && $enable_ip == 1):
    $enable_debug_site = 1;
else:
    $enable_debug_site = 0;
endif;

ini_set('display_errors',$enable_debug_php);

$MyDebug = new \Franky\Core\MYDEBUG();
$MyDebug->SetDebug($enable_debug_site);

$MySession          = new \Franky\Core\MYSESSION("auth");
$MyMessageAlert     = new \Franky\Core\MessageAlert();
$MyFrankyMonster    = new \Franky\Core\FRANKY();
$MyMetatag          = new \Franky\Core\Metatags();
$MyFlashMessage     = new \Franky\Core\flashMessages($CONTEXT);
$MyRequest          = new \Franky\Core\request();
$Mobile_detect      = new \Mobile_Detect();
$MyRedireccion      = new \Base\model\redireccionesModel();
$ObserverManager    = new \Franky\Core\ObserverManager();
$RoleModel          = new \Base\model\RoleModel;
$RoleEntity         = new \Base\entity\RoleEntity;
$MyAccessList       = new \Franky\Core\AccessList();

$seccion = $MyRequest->getRequest('my_url_friendly');

$_seccion = explode("/",$seccion);
				
if($_seccion[0] == PATH_ADMIN)
{
    
    $catalogo_idiomas  = include(PROJECT_DIR.'/modulos/base/configure/idiomas_admin.php');
    $idioma_base = getCoreConfig('base/theme/baselang-admin');
    define('DEFAULT_LOCALE',$idioma_base);

    $idiomas = getCoreConfig('base/theme/langs-admin');
    $locale = DEFAULT_LOCALE;
    if($_SESSION['lang_admin'])
    {
        $idioma_encontrado = false;
        foreach ($catalogo_idiomas as $idioma => $path_idioma)
        {
            if(in_array($_SESSION["lang_admin"],$idiomas))
            {
                $idioma_encontrado = true;
            }

        }
        if($idioma_encontrado)
        {
            $locale = $_SESSION["lang_admin"];
        }
    }
}
elseif($_seccion[0] == PATH_ACCOUNT)
{
    
    $catalogo_idiomas  = include(PROJECT_DIR.'/modulos/base/configure/idiomas.php');
    $idioma_base = getCoreConfig('base/theme/baselang');
    define('DEFAULT_LOCALE',$idioma_base);

    $idiomas = getCoreConfig('base/theme/langs');
    $locale = DEFAULT_LOCALE;
    if($_SESSION['lang'])
    {
        $idioma_encontrado = false;
        foreach ($catalogo_idiomas as $idioma => $path_idioma)
        {
            if(in_array($_SESSION["lang"],$idiomas))
            {
                $idioma_encontrado = true;
            }

        }
        if($idioma_encontrado)
        {
            $locale = $_SESSION["lang"];
        }
    }
}
else
{
    $catalogo_idiomas  = include(PROJECT_DIR.'/modulos/base/configure/idiomas.php');
    $idioma_base = getCoreConfig('base/theme/baselang');
    define('DEFAULT_LOCALE',$idioma_base);

    $idiomas = getCoreConfig('base/theme/langs');

    $locale = DEFAULT_LOCALE;
    if(!isset($_SESSION['lang']))
    {
        $_SESSION['lang'] = DEFAULT_LOCALE;
    }
    if($MyRequest->getRequest("lang") != "" && in_array($MyRequest->getRequest("lang"),$idiomas))
    {
        $locale = $MyRequest->Sanitizacion($MyRequest->getRequest("lang"));
        $path_idioma =  $catalogo_idiomas[$MyRequest->getRequest("lang")];
        $_SESSION["lang"] = $locale;
    }
    else
    {
        $idioma_encontrado = false;
        foreach ($catalogo_idiomas as $idioma => $path_idioma)
        {

            $is_idioma = substr($seccion, 0,strlen($path_idioma)+1);
            if(!empty($path_idioma) && $is_idioma == $path_idioma."/" && in_array($idioma,$idiomas))
            {
                $locale = $idioma;

                $_SESSION["lang"] = $idioma;

                $idioma_encontrado = true;
            }

        }
        if(!$idioma_encontrado)
        {
            $locale = $_SESSION["lang"];
        }

    }
}
if($locale != DEFAULT_LOCALE)
{
    define("PREFIDIOMA", $catalogo_idiomas[$locale]);
}
else
{
    define("PREFIDIOMA", "");
}


$domain = 'messages';

__bindtextdomain($domain, "base");


if (function_exists('bind_textdomain_codeset'))
{
    bind_textdomain_codeset($domain, 'UTF-8');
}

T_setlocale(LC_MESSAGES, $locale);
putenv("LC_ALL=$locale"); //windows, MAC


textdomain($domain);

$lang_root = (!empty($catalogo_idiomas[$locale]) ? $catalogo_idiomas[$locale] : $catalogo_idiomas[$idioma_base]);

include_once(PROJECT_DIR."/modulos/base/loads/llenaMensajes.php");

include_once(PROJECT_DIR."/modulos/base/loads/core_config.php");

?>