<?php

namespace wulaphp\i18n {
    /**
     * 多语言支持类.
     *
     * @package wulaphp\i18n
     */
    class I18n {
        private static $languages = [];

        /**
         * 添加语言目录.
         *
         * @param string $dir
         *
         * @return int 添加的主词条数量
         */
        public static function addLang($dir) {
            $cnt  = 0;
            $dir  = untrailingslashit($dir);
            $lang = defined('LANGUAGE') ? LANGUAGE : 'en';
            $lf   = $dir . DIRECTORY_SEPARATOR . $lang . '.php';
            if (!is_file($lf) && ($pos = strpos($lang, '-', 1))) {
                $lf = $dir . DIRECTORY_SEPARATOR . substr($lang, 0, $pos) . '.php';
            }
            if (is_file($lf)) {
                $language = include $lf;
                if (is_array($language)) {
                    $cnt             = count($language);
                    self::$languages = array_merge(self::$languages, $language);
                }
            }

            return $cnt;
        }

        /**
         * 基于域进行翻译.
         *
         * @param string $text
         * @param array  $args
         * @param string $domain default is 'core'.
         *
         * @return string
         */
        public static function translate1($text, array $args = [], $domain = '') {
            if ($domain) {
                if (isset(self::$languages[ $domain ][ $text ])) {
                    return @vsprintf(self::$languages[ $domain ][ $text ], $args);
                }
            } else if (isset(self::$languages[ $text ])) {
                return @vsprintf(self::$languages[ $text ], $args);
            }

            return @vsprintf($text, $args);
        }

        /**
         * 取原生字段.
         *
         * @param string $text
         * @param string $domain
         *
         * @return mixed
         */
        public static function getText($text, $domain = '') {
            if ($domain) {
                if (isset(self::$languages[ $domain ][ $text ])) {
                    return self::$languages[ $domain ][ $text ];
                }
            } else if (isset(self::$languages[ $text ])) {
                return self::$languages[ $text ];
            }

            return $text;
        }

        /**
         * 翻译字符.
         *
         * @param $text
         * @param $args
         *
         * @return string
         */
        public static function translate($text, array $args = []) {
            if (isset(self::$languages[ $text ])) {
                return @vsprintf(self::$languages[ $text ], $args);
            }

            return @vsprintf($text, $args);
        }
    }
}

namespace {

    use wulaphp\i18n\I18n;

    /**
     * 翻译.
     * @see I18n::translate()
     * @see I18n::translate1()
     *
     * @param string $text 要翻译的字符串,不同域的字符串用@分隔.
     * @param array  $args 参数.
     *
     * @return string
     */
    function __($text, ...$args) {
        $texts = explode('@', $text);
        if (count($texts) > 1) {
            return I18n::translate1($texts[0], $args, '@' . $texts[1]);
        } else {
            return I18n::translate($text, $args);
        }
    }

    /**
     * 基于域进行翻译.字符和域用'@'分隔,如: 'abc@dashboard'.
     *
     * @see I18n::translate1()
     *
     * @param string $text 要翻译的字符串.
     * @param array  $args 参数.
     *
     * @return string
     */
    function _t($text, ...$args) {
        $text   = explode('@', $text);
        $str    = $text[0];
        $domain = isset($text[1]) ? '@' . $text[1] : '';

        return I18n::translate1($str, $args, $domain);
    }

    /**
     * alias of _t
     * @see I18n::translate1()
     *
     * @param string $text
     * @param mixed  ...$args
     *
     * @return string
     */
    function _tr($text, ...$args) {
        return _t($text, ...$args);
    }

    /**
     * 无参数翻译
     * @see I18n::getText()
     *
     * @param $text
     *
     * @return string
     */
    function _tt($text) {
        $text   = explode('@', $text);
        $str    = $text[0];
        $domain = isset($text[1]) ? '@' . $text[1] : '';

        return I18n::getText($str, $domain);
    }

    /**
     * 加载对应语言资源文件.
     *
     * @param string $file
     * @param string $ext
     *
     * @return string
     */
    function _i18n($file, $ext = '.js') {
        if (!$file) {
            return '';
        }
        $lang = defined('LANGUAGE') ? LANGUAGE : 'en';
        $rf   = substr($file, strlen(WWWROOT_DIR));
        $ext  = strtolower($ext);
        if (!is_file(WWWROOT . $rf . DS . $lang . $ext) && ($pos = strpos($lang, '-', 1))) {
            $lang = substr($lang, 0, $pos);
        }
        if (is_file(WWWROOT . $rf . DS . $lang . $ext)) {
            if ($ext == '.js') {
                return "<script type=\"text/javascript\" src=\"{$file}/{$lang}{$ext}\"></script>";
            } else if ($ext == '.css') {
                return "<link rel=\"stylesheet\" href=\"{$file}/{$lang}{$ext}\"/>";
            } else {
                return "{$file}/{$lang}{$ext}";
            }
        } else {
            if ($ext == '.js') {
                return "<script type=\"text/javascript\" src=\"{$file}_{$lang}{$ext}\"></script>";
            } else if ($ext == '.css') {
                return "<link rel=\"stylesheet\" href=\"{$file}_{$lang}{$ext}\"/>";
            } else {
                return "{$file}_{$lang}{$ext}";
            }
        }
    }

    // 翻译
    function smarty_modifiercompiler_t($params) {
        $str  = array_shift($params);
        $args = smarty_vargs($params);
        if ($args) {
            return "__({$str},$args)";
        } else {
            return "__({$str})";
        }
    }

    //带域翻译
    function smarty_modifiercompiler_tf($params) {
        $str  = array_shift($params);
        $args = smarty_vargs($params);
        if ($args) {
            return "_t($str,$args)";
        } else {
            return "_t($str)";
        }
    }

    // 加载语言相关资源使用
    function smarty_modifiercompiler_i18n($params) {
        $ext = isset($params[1]) ? $params[1] : "'.js'";

        return "_i18n({$params[0]},$ext)";
    }
}
