<?php
namespace Utils;

require_once 'includes.inc';

/**
 *
 * @author Administrator
 *        
 */
class General {
    // TODO - Insert your code here

    /**
     */
    public function __construct() {
        // TODO - Insert your code here
    }

    /**
     */
    function __destruct() {
        // TODO - Insert your code here
    }

    static /* string */ function cleanGlyphs(/*string*/ $strOrg) {
        $accents = '/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig|tilde);/';

        $string_encoded = htmlentities($strOrg, ENT_NOQUOTES, 'UTF-8');

        $mtdRet = preg_replace($accents, '$1', $string_encoded);

        return $mtdRet;
    }

    /**
     *
     * @param string $strIn
     * @param boolean $treatSlashes
     * @param boolean $treatQuotes
     * @return object|string
     */
    static function    clean ($strIn, $treatSlashes = TRUE, $treatQuotes = TRUE): string {
        if( empty ($strIn) || ( ! is_string($strIn) ) ) {
            return $strIn;
        }

        $str = trim ($strIn);

        if ( $treatSlashes /*&& get_magic_quotes_gpc ()*/ ) {
            $str = stripslashes ($str);
        }

        $arrToSearchFor = array("\0", "\n", "\r", "\x1a");

        $arrReplace     = array('\\0', '\\n', '\\r', '\\Z');

        if ($treatSlashes) {
            $arrToSearchFor[] = '\\';

            $arrReplace[] = '\\\\';
        }

        if ($treatSlashes) {
            $arrToSearchFor[] = "'";
            $arrToSearchFor[] = '"';

            $arrReplace[] = "\\'";
            $arrReplace[] = '\\"';
        }

        $str = str_replace($arrToSearchFor, $arrReplace, $str);

        return htmlentities ($str/*, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'ISO-8859-1'/*'cp1252'*/);
    }

    /**
     * @param String $varName
     * @return String
     */
    static function getHTTPVar($varName): string {
        $mtdRet = "";

        for ($cont = 0; $cont < 2; $cont++) {
            $mtdRet = filter_input( ($cont == 0) ? INPUT_POST : INPUT_GET, $varName );

            if ( ! is_null($mtdRet) ) {
                break;
            }
        }

        if ($mtdRet == NULL) {
            $mtdRet = "";
        }

        return /*clean(*/$mtdRet/*)*/;
    }

    /**
     * Validates the entered user credentials, case it's valid.
     * @return String ''|The logged user name.
     */
    static function /*String*/ _checkLogon(): string {
        $appl_usr   = '';

        if ( ! array_key_exists(\_SESSION_LOGIN_NAME, $_SESSION) ) {
            $appl_usr   = (isset ($_POST[\FLD_USRNAME]) ) ? $_POST[\FLD_USRNAME] : '';
            $appl_passwd = (isset ($_POST[\FLD_USRPWD]) ) ? $_POST[\FLD_USRPWD] : NULL;
        }
        else {
            $appl_usr   = $_SESSION[\_SESSION_LOGIN_NAME];
            $appl_passwd = $_SESSION[\_SESSION_LOGIN_PWD];
        }

        // Verificar se ja' estamos processando o login ...
        if ($appl_usr && $appl_passwd) {
            // Conectando, escolhendo o banco de dados
            $objUsr = new \User($appl_usr);

            // Tem alguma coisa errada: tenta de novo, peao!
            if ( ! $objUsr->compare_Pwd($appl_passwd) ) {
                die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . " result = senha nao bate");
            }
            else {
                $_SESSION[\_SESSION_LOGIN_NAME] = $appl_usr;
                $_SESSION[\_SESSION_LOGIN_PWD] = $appl_passwd;

                if ( isset ($_POST[\FLD_USRDATEZN]) ) {
                    $timezone_offset_minutes = $_POST[\FLD_USRDATEZN];

                    $timezone_offset_minutes = $timezone_offset_minutes == 0 ? 0 : -$timezone_offset_minutes;

                    $timezone_name = timezone_name_from_abbr("", $timezone_offset_minutes*60, false);

                    $_SESSION[\_SESSION_DATEZONE] = $timezone_name;
                }

                date_default_timezone_set($_SESSION[\_SESSION_DATEZONE]);
            }
        }

        return $appl_usr;
    }

    /**
     * Logs off the current validated user.
     * @return boolean TRUE if there was a logged user.
     * @return boolean FALSE if there wasn't a logged user.
     */
    static function /*boolean*/ _logOff(): bool {
        $fRet = FALSE;

        if (isset($_SESSION[\_SESSION_LOGIN_NAME])) {
            unset($_SESSION[\_SESSION_LOGIN_NAME]);
            unset($_SESSION[\_SESSION_LOGIN_PWD]);

            $fRet = TRUE;
        }

        return $fRet;
    }
}
