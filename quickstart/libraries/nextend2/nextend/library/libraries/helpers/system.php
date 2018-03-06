<?php

class N2SystemHelper
{

    public static function testMemoryLimit() {
        static $works = null;
        if ($works === null) {
            $works = true;
            if (function_exists('ini_get')) {

                $memory_limit = @ini_get('memory_limit');
                if (!empty($memory_limit) && $memory_limit != '-1') {
                    $ok = self::settingToBytes($memory_limit) >= 0x3C00000;
                    if (!$ok) {
                        $works = false;
                    }
                }
            }
        }
        return $works;
    }

    private static function settingToBytes($setting) {
        static $short = array(
            'k' => 0x400,
            'm' => 0x100000,
            'g' => 0x40000000
        );

        $setting = (string)$setting;
        if (!($len = strlen($setting))) return NULL;
        $last    = strtolower($setting[$len - 1]);
        $numeric = intval($setting);
        $numeric *= isset($short[$last]) ? $short[$last] : 1;

        return $numeric;
    }

    /**
     * @param bool $error error message
     * @param int  $limit
     */
    public static function getDebugTrace($error = false, $limit = 5) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $outrace = array();
        if ($trace) {
            foreach ($trace as $k => $trow) {
                if ($k > 0 && $k <= ($limit + 1)) {
                    $outrace[] = $trow;
                }
            }
        }

        if ($error) echo "<p><strong>{$error}</strong></p>";
        echo N2Html::openTag("pre");
        print_r($outrace);
        echo N2Html::closeTag("pre");
        n2_exit(true);
    }

} 