<?php

define( 'SRS_PLAYER_VERSION', '1.0.3' );

/**
 * SRS Player is a video streaming player, supports HLS/HTTP-FLV/WebRTC etc.
 *
 * @package SrsPlayer
 * @author Winlin Yang
 * @version 1.0.3
 * @link https://github.com/ossrs/Typecho-Plugin-SrsPlayer
 */
class SrsPlayer_Plugin implements Typecho_Plugin_Interface
{
    public static function activate() {
        Typecho_Plugin::factory('Widget_Archive')->header = array(__CLASS__, 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'footer');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->content = array('SrsPlayer_Plugin', 'parse');
    }

    public static function deactivate() {
    }

    public static function config(Typecho_Widget_Helper_Form $form) {
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form) {
    }

    public static function render() {
    }

    public static function header(){
        echo "\n";
        $cssUrl = Helper::options()->pluginUrl . '/SrsPlayer/public/css/srs-player-public.css?v=' . SRS_PLAYER_VERSION;
        echo '<link rel="stylesheet" href="' . $cssUrl . '">' . "\n";

        // We must preload the jQuery and SrsPlayer.
        $urls = array(
            Helper::options()->pluginUrl . '/SrsPlayer/public/js/jquery-1.10.2.min.js?v=' . SRS_PLAYER_VERSION,
            Helper::options()->pluginUrl . '/SrsPlayer/public/js/srs.player.js?v=' . SRS_PLAYER_VERSION,
        );
        foreach ($urls as $url) {
            echo "<script src='${url}'></script>\n";
        }
    }

    public static function footer() {
        // Lazy load after page is loaded.
        $urls = array(
            Helper::options()->pluginUrl . '/SrsPlayer/public/js/flv-1.5.0.min.js?v=' . SRS_PLAYER_VERSION,
            Helper::options()->pluginUrl . '/SrsPlayer/public/js/hls-0.14.17.min.js?v=' . SRS_PLAYER_VERSION,
            Helper::options()->pluginUrl . '/SrsPlayer/public/js/adapter-7.4.0.js?v=' . SRS_PLAYER_VERSION,
            Helper::options()->pluginUrl . '/SrsPlayer/public/js/srs.sdk.js?v=' . SRS_PLAYER_VERSION,
        );
        foreach ($urls as $url) {
            echo "<script src='${url}'></script>\n";
        }
    }

    public static function parse($text, $widget, $lastResult) {
        if (!($widget instanceof Widget_Archive)) {
            return $text;
        }

        $matches = array();
        preg_match_all('/\[(srs_player).*\]/', $text, $matches);
        if (empty($matches) || empty($matches[0])) {
            return $text;
        }

        $o = $text;
        foreach ($matches[0] as $match) {
            // The $match is the player instance, for example,
            //      [srs_player url='https://r.ossrs.net/live/livestream.m3u8' muted width="720"]
            // We parse $match to $obj as:
            //      url: https://r.ossrs.net/live/livestream.m3u8
            //      muted: muted
            //      width: 720
            $obj = SrsPlayer_Plugin::toObject($match);

            // Build $obj to $replace, the new H5 element.
            $replace = SrsPlayer_Plugin::buildReplacement($match, $obj);

            // Replace the $match to the new H5 object.
            $o = str_replace($match, $replace, $o);
        }

        return $o;
    }

    private static function toObject($match) {
        $input = trim(rtrim(ltrim(ltrim(ltrim($match, "[")), "srs_player"), "]"));

        $attrs = array(
            'url' => '',
            'controls' => 'controls',
            'autoplay' => 'autoplay',
            'muted' => 'muted',
            'width' => '',
        );

        foreach (explode(" ", $input) as $e) {
            $kv = explode("=", $e);
            if (count($kv) == 0) {
                continue;
            }

            $k = $kv[0];
            if (count($kv) == 1) {
                $attrs[$k] = $k;
            } else {
                $attrs[$k] = $kv[1];
            }
        }

        foreach ($attrs as $k => $v) {
            $attrs[$k] = trim(trim($v, '"'), "'");
        }

        return $attrs;
    }

    private static function buildReplacement($match, $q) {
        if (empty($q)) {
            return "Invalid ${match}";
        }

        if (empty($q['url'])) {
            return "No URL of ${match}";
        }

        $url = $q['url'];
        $id = 'srs-player-' . SrsPlayer_Plugin::random_str(32);

        $controls = ' controls=' . $q['controls'];
        if ($q['controls'] != 'true' && $q['controls'] != 'controls') $controls = '';

        $autoplay = ' autoplay=' . $q['autoplay'];
        if ($q['autoplay'] != 'true' && $q['autoplay'] != 'autoplay') $autoplay = '';

        $muted = ' muted=' . $q['muted'];
        if ($q['muted'] != 'true' && $q['muted'] != 'muted') $muted = '';

        $width = ' width="' . $q['width'] . '"';
        if (empty($q['width'])) $width = '';

        $o = <<<EOT
    <div class="srs-player-wrapper">
        <video id="${id}" ${controls}${autoplay}${muted}${width}></video>
        <script>(function($) { new SrsPlayer("#${id}", "${url}").play(); })(jQuery);</script>
    </div>
EOT;
        return $o;
    }

    private static function random_str($length, $keyspace = NULL) {
        if (empty($keyspace)) {
            $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($length < 1) {
            throw new RangeException("Invalid length ${length}");
        }

        $pieces = array();
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[rand(0, $max)];
        }
        return implode('', $pieces);
    }
}

