<?php
/**
 * 从数组取值，如果数组中无指定key，则返回默认值.
 *
 * @param string $name
 * @param array  $array
 * @param mixed  $default
 *
 * @return mixed
 */
function aryget($name, array $array, $default = '') {
    if (isset ($array [ $name ])) {
        return $array [ $name ];
    }

    return $default;
}

/**
 * 将以'，',' ','　','-',';','；','－'分隔的字符串转换成以逗号分隔的字符.
 *
 * @param string $string
 *
 * @return string
 */
function pure_comman_string($string) {
    if ($string) {
        $string = trim(trim(str_replace(['，', ' ', '　', '-', ';', '；', '－'], ',', $string)), ',');
        if ($string) {
            $strings = explode(',', $string);

            return implode(',', array_filter($strings));
        }
    }

    return '';
}

/**
 * 判断$tag是否在A标签中或是某个标签的属性.
 *
 * @param string $content
 * @param string $tag
 *
 * @return bool
 */
function in_atag($content, $tag) {
    $pos = strpos($content, $tag);
    if ($pos === false) {
        return false;
    }
    // 是否是某一个标签的属性
    $search = '`<[^>]*?' . preg_quote($tag, '`') . '[^>]*?>`ui';
    if (preg_match($search, $content)) {
        return true;
    }
    $pos  = strlen($content) - $pos;
    $spos = strripos($content, '<a', -$pos);
    $epos = strripos($content, '</a', -$pos);
    // 没有a标签
    if ($spos === false) {
        return false;
    }
    // 前边的a标签已经关掉
    if ($epos !== false && $epos > $spos) {
        return false;
    }

    return true;
}

/**
 * covert the charset of filename to UTF-8.
 *
 * @param string $filename
 *
 * @return string
 */
function thefilename($filename) {
    $encode = mb_detect_encoding($filename, "UTF-8,GBK,GB2312,BIG5,ISO-8859-1");
    if ($encode != 'UTF-8') {
        $filename = mb_convert_encoding($filename, "UTF-8", $encode);
    }

    return $filename;
}

/**
 * 媒体文件URL.
 *
 * @param string $url
 *
 * @return string
 */
function the_media_src($url) {
    if (!$url || preg_match('#^(/|https?://).+$#', $url)) {
        return $url;
    }
    $medias = apply_filter('get_media_domains', [WWWROOT_DIR]);

    return trailingslashit($medias[ array_rand($medias) ]) . $url;
}

/**
 * Set HTTP status header.
 *
 * @param int $header HTTP status code
 *
 * @since 1.0
 *
 */
function status_header($header) {
    $text = get_status_header_desc($header);

    if (empty ($text)) {
        return;
    }
    $protocol = $_SERVER ["SERVER_PROTOCOL"];
    if ('HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol) {
        $protocol = 'HTTP/1.0';
    }

    $status_header = "$protocol $header $text";

    @header($status_header, true, $header);
    if (php_sapi_name() == 'cgi-fcgi') {
        @header("Status: $header $text");
    }
}

/**
 * Retrieve the description for the HTTP status.
 *
 * @param int $code HTTP status code.
 *
 * @return string Empty string if not found, or description if found.
 * @since 1.0
 *
 */
function get_status_header_desc($code) {
    global $output_header_to_desc;

    $code = abs(intval($code));

    if (!isset ($output_header_to_desc)) {
        $output_header_to_desc = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Page Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            510 => 'Not Extended'
        ];
    }
    if (isset ($output_header_to_desc [ $code ])) {
        return $output_header_to_desc [ $code ];
    } else {
        return '';
    }
}

/**
 * 确保字符以'/'结尾.
 *
 * @param string $string
 *
 * @return string
 */
function trailingslashit($string) {
    return untrailingslashit($string) . '/';
}

/**
 * 去除字符尾部'/'与'\'字符.
 *
 * @param string $string
 *
 * @return string
 */
function untrailingslashit($string) {
    return rtrim($string, '/\\');
}

/**
 * 去除文件名中不合法的字符.
 *
 * @param string $filename
 *
 * @return string
 */
function sanitize_file_name($filename) {
    $special_chars = [
        "?",
        "[",
        "]",
        "/",
        "\\",
        "=",
        "<",
        ">",
        ":",
        ";",
        ",",
        "'",
        "\"",
        "&",
        "$",
        "#",
        "*",
        "(",
        ")",
        "|",
        "~",
        "`",
        "!",
        "{",
        "}",
        chr(0)
    ];
    $filename      = str_replace($special_chars, '', $filename);
    $filename      = preg_replace('/[\s-]+/', '-', $filename);
    $filename      = trim($filename, '.-_');
    $parts         = explode('.', $filename);
    if (count($parts) <= 2) return $filename;
    $filename  = array_shift($parts);
    $extension = array_pop($parts);
    $mimes     = ['tmp', 'txt', 'jpg', 'gif', 'png', 'rar', 'zip', 'gzip', 'ppt'];
    foreach (( array )$parts as $part) {
        $filename .= '.' . $part;
        if (preg_match('/^[a-zA-Z]{2,5}\d?$/', $part)) {
            $allowed = false;
            foreach ($mimes as $ext_preg => $mime_match) {
                $ext_preg = '!(^' . $ext_preg . ')$!i';
                if (preg_match($ext_preg, $part)) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed) $filename .= '_';
        }
    }
    $filename .= '.' . $extension;

    return $filename;
}

/**
 * 获取唯一文件名.
 *
 * @param string        $dir
 * @param string        $filename
 * @param callable|null $unique_filename_callback
 *
 * @return string
 */
function unique_filename($dir, $filename, $unique_filename_callback = null) {
    $filename = sanitize_file_name($filename);
    $info     = pathinfo($filename);
    $ext      = !empty ($info ['extension']) ? '.' . $info ['extension'] : '';
    $name     = $info['filename'];
    if ($name === $ext) {
        $name = '';
    }
    if ($unique_filename_callback && is_callable($unique_filename_callback)) {
        $filename = $unique_filename_callback ($dir, $name);
    } else {
        $number = '';
        if ($ext && strtolower($ext) != $ext) {
            $ext2      = strtolower($ext);
            $filename2 = preg_replace('|' . preg_quote($ext) . '$|', $ext2, $filename);

            while (file_exists($dir . "/$filename") || file_exists($dir . "/$filename2")) {
                $new_number = (int)$number + 1;
                $filename   = str_replace("$number$ext", "$new_number$ext", $filename);
                $filename2  = str_replace("$number$ext2", "$new_number$ext2", $filename2);
                $number     = $new_number;
            }

            return $filename2;
        }
        while (file_exists($dir . "/$filename")) {
            if ('' == "$number$ext") {
                $filename = $filename . ++$number . $ext;
            } else {
                $filename = str_replace("$number$ext", ++$number . $ext, $filename);
            }
        }
    }

    return $filename;
}

/**
 * 查找文件.
 *
 * @param string   $dir       起始目录
 * @param string   $pattern   合法的正则表达式,此表达式只用于文件名
 * @param array    $excludes  不包含的目录名
 * @param bool|int $recursive 是否递归查找
 * @param int      $stop      递归查找层数
 *
 * @return array 查找到的文件
 */
function find_files($dir = '.', $pattern = '', $excludes = [], $recursive = 0, $stop = 0) {
    $files = [];
    $dir   = trailingslashit($dir);
    if (is_dir($dir)) {
        $fhd = @opendir($dir);
        if ($fhd) {
            $excludes  = is_array($excludes) ? $excludes : [];
            $_excludes = array_merge($excludes, ['.', '..']);
            while (($file = readdir($fhd)) !== false) {
                if ($recursive && is_dir($dir . $file) && !in_array($file, $_excludes)) {
                    if ($stop == 0 || $recursive <= $stop) {
                        $files = array_merge($files, find_files($dir . $file, $pattern, $excludes, $recursive + 1, $stop));
                    }
                }
                if (is_file($dir . $file) && @preg_match($pattern, $file)) {
                    $files [] = $dir . $file;
                }
            }
            @closedir($fhd);
        }
    }

    return $files;
}

/**
 * 删除目录及其内容,如果$keep为true则将目录本身也删除.
 *
 * @param string $dir
 * @param bool   $keep
 *
 * @return bool
 */
function rmdirs($dir, $keep = true) {
    $hd = @opendir($dir);
    if ($hd) {
        while (($file = readdir($hd)) != false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($dir . DS . $file)) {
                rmdirs($dir . DS . $file, false);
            } else {
                @unlink($dir . DS . $file);
            }
        }
        closedir($hd);
        if (!$keep) {
            @rmdir($dir);
        }
    }

    return true;
}

/**
 * 只保留URL中部分参数.
 *
 * @param string $url
 * @param array  $include 要保留的参数
 *
 * @return string
 */
function keepargs($url, $include = []) {
    $urls = explode('?', $url);
    if (count($urls) < 2) {
        return $url;
    }
    $kargs = [];
    foreach ($include as $arg) {
        if (preg_match('/' . $arg . '=([^&]+)/', $urls [1], $m)) {
            $kargs [] = $m [0];
        }
    }
    if (!empty ($kargs)) {
        $urls [1] = implode('&', $kargs);

        return implode('?', $urls);
    } else {
        return $urls [0];
    }
}

/**
 * 删除URL中的参数.
 *
 * @param string $url
 * @param array  $exclude 要删除的参数
 *
 * @return string
 */
function unkeepargs($url, $exclude = []) {
    $regex = [];
    $rpm   = [];
    if (is_string($exclude)) {
        $exclude = [$exclude];
    }
    foreach ($exclude as $ex) {
        $regex [] = '/&?' . $ex . '=[^&]*/';
        $rpm []   = '';
    }

    return preg_replace($regex, $rpm, $url);
}

/**
 * 从SESSION中取值.
 *
 * 如果未设置,则返回默认值 $default
 *
 * @param string $name    值名
 * @param mixed  $default 默认值
 *
 * @return mixed SESSION中的值
 */
function sess_get($name, $default = "") {
    if (isset ($_SESSION [ $name ])) {
        return $_SESSION [ $name ];
    }

    return $default;
}

/**
 * 从SESSION中删除变量$name,并将该变量值返回.
 *
 * @param string $name
 * @param string $default
 *
 * @return mixed
 */
function sess_del($name, $default = '') {
    $value = sess_get($name, $default);
    if (isset ($_SESSION [ $name ])) {
        $_SESSION [ $name ] = null;
        unset ($_SESSION [ $name ]);
    }

    return $value;
}

/**
 * 安全ID.
 *
 * @param string  $ids   以$sp分隔的id列表,只能是大与0的整形.
 * @param string  $sp    分隔符.
 * @param boolean $array 是否返回数组.
 *
 * @return mixed
 */
function safe_ids($ids, $sp = ',', $array = false) {
    if (empty ($ids)) {
        return $array ? [] : '';
    }
    $_ids = explode($sp, $ids);
    $ids  = [];
    foreach ($_ids as $id) {
        if (preg_match('/^[1-9]\d*$/', $id)) {
            $ids [] = intval($id);
        }
    }
    if ($array === false) {
        return empty ($ids) ? '' : implode($sp, $ids);
    } else {
        return empty ($ids) ? [] : $ids;
    }
}

/**
 * 安全ID.
 *
 * @param string $ids 要处理的ids.
 * @param string $sp  分隔字符，默认为','.
 *
 * @return array
 */
function safe_ids2($ids, $sp = ',') {
    return safe_ids($ids, $sp, true);
}

/**
 * 可读的size.
 *
 * @param int $size
 *
 * @return string
 */
function readable_size($size) {
    $size = intval($size);
    if ($size < 1024) {
        return $size . 'B';
    } else if ($size < 1048576) {
        return number_format($size / 1024, 2) . 'K';
    } else if ($size < 1073741824) {
        return number_format($size / 1048576, 2) . 'M';
    } else {
        return number_format($size / 1073741824, 2) . 'G';
    }
}

/**
 * 可读数字.
 *
 * @param int $size
 *
 * @return int|string
 */
function readable_num($size) {
    $size = intval($size);
    if ($size < 1000) {
        return $size;
    } else if ($size < 10000) {
        return number_format($size / 1000, 2) . 'K';
    } else if ($size < 10000000) {
        return number_format($size / 10000, 2) . 'W';
    } else {
        return number_format($size / 10000000, 2) . 'KW';
    }
}

/**
 * 用户友好日期
 *
 * @param int   $sec
 * @param array $text
 *
 * @return string
 */
function readable_date($sec, $text = ['s' => '秒', 'm' => '分', 'h' => '小时', 'd' => '天']) {
    $size = intval($sec);
    if ($size == 0) {
        return '';
    } else if ($size < 60) {
        return $size . $text ['s'];
    } else if ($size < 3600) {
        return floor($size / 60) . $text ['m'] . readable_date(fmod($size, 60));
    } else if ($size < 86400) {
        return floor($size / 3600) . $text ['h'] . readable_date(fmod($size, 3600));
    } else {
        return floor($size / 86400) . $text ['d'] . readable_date(fmod($size, 86400));
    }
}

/**
 * 合并$base与$arr.
 *
 * @param mixed $base
 * @param array $arr
 *
 * @return array 如果$base为空或$base不是一个array则直接返回$arr,反之返回array_merge($base,$arr)
 */
function array_merge2($base, $arr) {
    if (empty ($base) || !is_array($base)) {
        return $arr;
    }
    if (empty ($arr) || !is_array($arr)) {
        return $base;
    }

    return array_merge($base, $arr);
}

/**
 * 获取QUERY_STRING.
 *
 * @return string
 */
function get_query_string() {
    $query_str = $_SERVER ['QUERY_STRING'];
    if ($query_str) {
        parse_str($query_str, $args);
        unset ($args ['preview']);
        $query_str = http_build_query($args);
    }

    return empty ($query_str) ? '' : '?' . rtrim($query_str, '=');
}

/**
 * 为url添加参数。
 *
 * @param string $url     url
 * @param array  $args    要添加的参数
 * @param bool   $replace 是否替换原有的参数
 *
 * @return string
 */
function url_append_args($url, $args = [], $replace = true) {
    if (empty($args)) {
        return $url;
    }
    if (strpos($url, '?') === false) {
        return $url . '?' . http_build_query($args);
    } else {
        $urls = explode('?', $url);
        if (isset($urls[1])) {
            @parse_str($urls[1], $oargs);
            if ($oargs) {
                $args = $replace ? array_merge($oargs, $args) : array_merge($args, $oargs);
                ksort($args);
            }
        }

        return $urls[0] . '?' . http_build_query($args);
    }
}

/**
 * 将array的key/value通过$sep连接成一个字符串.
 *
 * @param array  $ary
 * @param string $concat 连接符
 * @param bool   $quote  连接时值是否用双引号包裹.
 * @param string $sep    组连接符
 *
 * @return string
 */
function ary_kv_concat(array $ary, $concat = '=', $quote = true, $sep = ' ') {
    if (empty ($ary)) {
        return '';
    }
    $quote   = $quote ? '"' : '';
    $tmp_ary = [];
    foreach ($ary as $name => $val) {
        $name       = trim($name);
        $tmp_ary [] = $name . $concat . "{$quote}{$val}{$quote}";
    }

    return implode($sep, $tmp_ary);
}

/**
 * 合并二个数组，并将对应值通过$sep进行连结(concat).
 *
 * @param array  $ary1 被加数组.
 * @param array  $ary2 数组.
 * @param string $sep  相加时的分隔符.
 *
 * @return array 合并后的数组.
 */
function ary_concat(array $ary1, array $ary2, $sep = ' ') {
    foreach ($ary2 as $key => $val) {
        if (isset ($ary1 [ $key ])) {
            if (is_array($ary1 [ $key ]) && is_array($val)) {
                $ary1 [ $key ] = ary_concat($ary1 [ $key ], $val);
            } else if (is_array($ary1 [ $key ]) && !is_array($val)) {
                $ary1 [ $key ] [] = $val;
            } else if (!is_array($ary1 [ $key ]) && is_array($val)) {
                $val []        = $ary1 [ $key ];
                $ary1 [ $key ] = $val;
            } else {
                $ary1 [ $key ] = $ary1 [ $key ] . $sep . $val;
            }
        } else {
            $ary1 [ $key ] = $val;
        }
    }

    return $ary1;
}

/**
 * 生成随机字符串.
 *
 * @param int    $len
 * @param string $chars
 *
 * @return string
 */
function rand_str($len = 8, $chars = "a-z,0-9,$,_,!,@,#,=,~,$,%,^,&,*,(,),+,?,:,{,},[,],A-Z") {
    $characters  = explode(',', $chars);
    $num         = count($characters);
    $array_allow = [];
    for ($i = 0; $i < $num; $i++) {
        if (substr_count($characters [ $i ], '-') > 0) {
            $character_range = explode('-', $characters [ $i ]);
            $max             = ord($character_range [1]);
            for ($j = ord($character_range [0]); $j <= $max; $j++) {
                $array_allow [] = chr($j);
            }
        } else {
            $array_allow [] = $characters [ $i ];
        }
    }

    // 生成随机字符串
    mt_srand(( double )microtime() * 1000000);
    $code = [];
    $i    = 0;
    while ($i < $len) {
        $index   = mt_rand(0, count($array_allow) - 1);
        $code [] = $array_allow [ $index ];
        $i++;
    }

    return implode('', $code);
}

/**
 * 来自ucenter的加密解密函数.
 *
 * @param string $string    要解（加）密码字串
 * @param string $operation DECODE|ENCODE 解密|加密
 * @param string $key       密码
 * @param int    $expiry    超时
 *
 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    $ckey_length = 4;

    $key  = md5($key ? $key : rand_str(3));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey   = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box    = range(0, 255);

    $rndkey = [];
    for ($i = 0; $i <= 255; $i++) {
        $rndkey [ $i ] = ord($cryptkey [ $i % $key_length ]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j          = ($j + $box [ $i ] + $rndkey [ $i ]) % 256;
        $tmp        = $box [ $i ];
        $box [ $i ] = $box [ $j ];
        $box [ $j ] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a          = ($a + 1) % 256;
        $j          = ($j + $box [ $a ]) % 256;
        $tmp        = $box [ $a ];
        $box [ $a ] = $box [ $j ];
        $box [ $j ] = $tmp;
        $result     .= chr(ord($string [ $i ]) ^ ($box [ ($box [ $a ] + $box [ $j ]) % 256 ]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 解析版本号.
 *
 * @param string $versions format:[(min,max)]
 *
 * @return array array(min,minop,max,maxop)
 */
function parse_version_pair($versions) {
    $rst = [false, '', false, ''];
    if (preg_match('#^([\[\(])(.*?),(.*?)([\]\)])$#', $versions, $m)) {
        if ($m [2]) {
            $rst [0] = $m [2];
            if ($m [1] == '[') {
                $rst [1] = '<=';
            } else {
                $rst [1] = '<';
            }
        }
        if ($m [3]) {
            $rst [2] = $m [3];
            if ($m [4] == ']') {
                $rst [3] = '>=';
            } else {
                $rst [3] = '>';
            }
        }
    }

    return $rst;
}

/**
 * 从数据$ary取数据并把它从原数组中删除.
 *
 * @param array  $ary
 * @param string ...$keys
 *
 * @return array
 * @since 1.0.3
 */
function get_then_unset(&$ary, ...$keys) {
    $rtnAry = [];
    if (is_array($ary) && $ary && $keys > 1) {
        foreach ($keys as $arg) {
            if (isset ($ary [ $arg ])) {
                $rtnAry [ $arg ] = $ary [ $arg ];
                unset ($ary [ $arg ]);
            } else {
                $rtnAry [ $arg ] = '';
            }
        }
    }

    return $rtnAry;
}

/**
 * 从数据$ary取数据.
 *
 * @param array $ary
 * @param array $name 要取出来的数据
 *
 * @return array
 * @since 1.0.3
 */
function get_for_list($ary, ...$name) {
    $rtnAry = [];
    if (is_array($ary) && $ary && $name) {
        foreach ($name as $arg) {
            if (isset ($ary [ $arg ])) {
                $rtnAry [] = $ary [ $arg ];
            } else {
                $rtnAry [] = '';
            }
        }
    }

    return $rtnAry;
}

/**
 * 从$ary中获取$key对应的值并将其从$ary中删除.
 *
 * @param array      $ary
 * @param string|int $key
 *
 * @return mixed
 */
function unget(&$ary, $key) {
    $v = null;
    if (isset($ary[ $key ])) {
        $v = $ary[ $key ];
        unset($ary[ $key ]);
    }

    return $v;
}

/**
 * 转义HTML字符.
 *
 * @param string $string
 * @param string $esc_type
 * @param null   $char_set
 * @param bool   $double_encode
 *
 * @return mixed|null|string|string[]
 */
function html_escape($string, $esc_type = 'html', $char_set = null, $double_encode = true) {
    if (!$char_set) {
        $char_set = 'UTF-8';
    }

    switch ($esc_type) {
        case 'html' :
        case 'htmlall' :
            $string = htmlspecialchars($string, ENT_QUOTES, $char_set, $double_encode);

            return $string;
        case 'url' :
            return rawurlencode($string);

        case 'urlpathinfo' :
            return str_replace('%2F', '/', rawurlencode($string));

        case 'quotes' :
            // escape unescaped single quotes
            return preg_replace("%(?<!\\\\)'%", "\\'", $string);

        case 'hex' :
            // escape every byte into hex
            // Note that the UTF-8 encoded character ä will be represented as %c3%a4
            $return  = '';
            $_length = strlen($string);
            for ($x = 0; $x < $_length; $x++) {
                $return .= '%' . bin2hex($string [ $x ]);
            }

            return $return;

        case 'javascript' :
            // escape quotes and backslashes, newlines, etc.
            return strtr($string, [
                '\\' => '\\\\',
                "'"  => "\\'",
                '"'  => '\\"',
                "\r" => '\\r',
                "\n" => '\\n',
                '</' => '<\/'
            ]);

        default :
            return $string;
    }
}

/**
 * 时间差
 *
 * @param integer $time
 *
 * @return string
 */
function timediff($time) {
    static $ctime = false;
    if ($ctime === false) {
        $ctime = time();
    }
    $d = $ctime - $time;
    if ($d < 60) {
        return _('刚刚');
    } else if ($d < 3600) {
        $it = floor($d / 60);

        return _($it . '分钟前');
    } else if ($d < 86400) {
        $it = floor($d / 3600);

        return _($it . '小时前');
    } else if ($d < 604800) {
        $it = floor($d / 86400);

        return _($it . '天前');
    } else if ($d < 2419200) {
        $it = floor($d / 604800);

        return _($it . '周前');
    } else {
        $it = floor($d / 2592000);

        return _($it . '月前');
    }
}

/**
 * 将目录$path压缩到$zipFileName.
 *
 * @param string $zipFileName 文件名.
 * @param string $path        要压缩的路径.
 *
 * @return boolean Returns true on success or false on failure.
 */
function zipit($zipFileName, $path) {
    if (!file_exists($path)) {
        return false;
    }
    $zip = new ZipArchive ();
    if ($zip->open($zipFileName, ZipArchive::OVERWRITE)) {
        $dir_iterator = new RecursiveDirectoryIterator ($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator     = new RecursiveIteratorIterator ($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
        $success      = true;
        foreach ($iterator as $file) {
            if (is_dir($file)) {
                $dest = str_replace($path, '', $file);
                if (!$zip->addEmptyDir($dest)) {
                    $success = false;
                    break;
                }
            } else {
                $dest = str_replace($path, '', $file);
                if (!$zip->addFile($file, $dest)) {
                    $success = false;
                    break;
                }
            }
        }
        $zip->close();
        if (!$success) {
            @unlink($zipFileName);
        }

        return $success;
    }

    return false;
}

/**
 * 从$str中截取$str1与$str2之间的字符串.
 *
 * @param string  $str
 * @param string  $str1
 * @param string  $str2
 * @param boolean $include_str1
 *
 * @return string
 */
function inner_str($str, $str1, $str2, $include_str1 = true) {
    if (!$str || !$str1 || !$str2) {
        return null;
    }
    $s    = $str1;
    $e    = $str2;
    $pos1 = strpos($str, $s);
    $pos2 = strpos($str, $e, $pos1 + strlen($s) + 1);
    if ($pos1 !== false && !$include_str1) {
        $pos1 += strlen($s);
    }
    if ($pos1 !== false && $pos2 !== false && $pos2 > $pos1) {
        $cnt = substr($str, $pos1, $pos2 - $pos1);

        return $cnt;
    } else {
        return null;
    }
}

/**
 * 去除字符串中的所有html标签,换行,空格等.
 *
 * @param string $text
 *
 * @return string
 */
function cleanhtml2simple($text) {
    $text = str_ireplace(['[page]', ' ', '　', "\t", "\r", "\n", '&nbsp;'], '', $text);
    $text = preg_replace('#</?[a-z0-9][^>]*?>#umsi', '', $text);

    return $text;
}

/**
 * 抛出一个异常以终止程序运行.
 *
 * @param string $message
 *
 * @throws \Exception
 */
function throw_exception($message) {
    throw new Exception($message);
}

include __DIR__ . '/I18n.php';
// end of common.php