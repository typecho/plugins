<?php
/**
 * Highlight.JS插件，智能实现代码高亮
 * 
 * @package Highlight Js
 * @author qining
 * @version 1.0.1
 * @dependence 13.11.24-*
 * @link http://70.io
 */
class HighlightJs_Plugin implements Typecho_Plugin_Interface
{
    /**
     *   
     */
    private static $_isMarkdown = false;

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('HighlightJs_Plugin', 'parse');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('HighlightJs_Plugin', 'parse');
        Typecho_Plugin::factory('Widget_Abstract_Comments')->contentEx = array('HighlightJs_Plugin', 'parse');
        Typecho_Plugin::factory('Widget_Archive')->header = array('HighlightJs_Plugin', 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('HighlightJs_Plugin', 'footer');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $compatibilityMode = new Typecho_Widget_Helper_Form_Element_Radio('compatibilityMode', array(
            0   =>  _t('不启用'),
            1   =>  _t('启用')
        ), 0, _t('兼容模式'), _t('兼容模式一般用于对以前没有使用Markdown语法解析的文章'));
        $form->addInput($compatibilityMode->addRule('enum', _t('必须选择一个模式'), array(0, 1)));

        $styles = array_map('basename', glob(dirname(__FILE__) . '/res/styles/*.css'));
        $styles = array_combine($styles, $styles);
        $style = new Typecho_Widget_Helper_Form_Element_Select('style', $styles, 'default.css',
            _t('代码配色样式'));
        $form->addInput($style->addRule('enum', _t('必须选择配色样式'), $styles));
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 输出头部css
     * 
     * @access public
     * @param unknown $header
     * @return unknown
     */
    public static function header() {
        $cssUrl = Helper::options()->pluginUrl . '/HighlightJs/res/styles/' . Helper::options()->plugin('HighlightJs')->style;
        echo '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '" />';
    }
    
    /**
     * 输出尾部js
     * 
     * @access public
     * @param unknown $header
     * @return unknown
     */
    public static function footer() {
        $jsUrl = Helper::options()->pluginUrl . '/HighlightJs/res/highlight.pack.js';
        echo '<script type="text/javascript" src="'. $jsUrl .'"></script>';
        echo '<script type="text/javascript">window.onload = function () {
var codes = document.getElementsByTagName("pre"),
    hlNames = {
        actionscript : /^as[1-3]$/i,
        cmake : /^(make|makefile)$/i,
        cs : /^csharp$/i,
        css : /^css[1-3]$/i,
        delphi : /^pascal$/i,
        javascript : /^js$/i,
        markdown : /^md$/i,
        objectivec : /^objective\-c$/i,
        php  : /^php[1-6]$/i,
        sql : /^mysql$/i,
        xml : /^(html|html5|xhtml)$/i
    }, hlLangs = hljs.LANGUAGES;

for (var i = 0; i < codes.length; i ++) {
    var children = codes[i].getElementsByTagName("code"), highlighted = false;

    if (children.length > 0) {
        var code = children[0], className = code.className;

        if (!!className) {
            if (0 == className.indexOf("lang-")) {
                var lang = className.substring(5).toLowerCase(), finalLang;
            
                if (hlLangs[lang]) {
                    finalLang = lang;
                } else {
                    for (var l in hlNames) {
                        if (lang.match(hlNames[l])) {
                            finalLang = l;
                        }
                    }
                }

                if (!!finalLang) {
                    var result = hljs.highlight(finalLang, code.textContent, true);
                    code.innerHTML = result.value;
                    highlighted = true;
                }
            }
        }

        if (!highlighted) {
            var html = code.innerHTML;
            code.innerHTML = html.replace(/<\/?[a-z]+[^>]*>/ig, "");
            hljs.highlightBlock(code, "", false);
        }
    }
}
}</script>';
    }
    
    /**
     * 解析
     * 
     * @access public
     * @param array $matches 解析值
     * @return string
     */
    public static function parseCallback($matches)
    {
        if ('code' == $matches[1] && !self::$_isMarkdown) {
            $language = $matches[2];

            if (!empty($language)) {
                if (preg_match("/^\s*(class|lang|language)=\"(?:lang-)?([_a-z0-9-]+)\"$/i", $language, $out)) {
                    $language = ' class="' . trim($out[2]) . '"';
                } else if (preg_match("/\s*([_a-z0-9]+)/i", $language, $out)) {
                    $language = ' class="lang-' . trim($out[1]) . '"';
                }
            }
            
            return "<pre><code{$language}>" . htmlspecialchars(trim($matches[3])) . "</code></pre>";
        }

        return $matches[0];
    }
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function parse($text, $widget, $lastResult)
    {
        $text = empty($lastResult) ? $text : $lastResult;

        if (!Helper::options()->plugin('HighlightJs')->compatibilityMode) {
            return $text;
        }
        
        if ($widget instanceof Widget_Archive || $widget instanceof Widget_Abstract_Comments) {
            self::$_isMarkdown = $widget instanceof Widget_Abstract_Comments ? Helper::options()->commentsMarkdown : $widget->isMarkdown;
            return preg_replace_callback("/<(code|pre)(\s*[^>]*)>(.*?)<\/\\1>/is", array('HighlightJs_Plugin', 'parseCallback'), $text);
        } else {
            return $text;
        }
    }
}
