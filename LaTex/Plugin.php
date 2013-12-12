<?php
/**
 * LaTex 公式解析
 * 
 * @package LaTex
 * @author mutoo
 * @version 1.1.0
 * @link http://blog.mutoo.im/LaTex.html
 */
class LaTex_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->footer = array('LaTex_Plugin', 'footer');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){
        $mark = new Typecho_Widget_Helper_Form_Element_Text('mark', NULL, 'latex', _t('自定义标记'), _t('在 Markdown 语法环境下使用以下代码进行公式转换：<blockquote>```自定义标记<br/>公式<br/>```</blockquote>'));
        $form->addInput($mark);
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
     * 输出尾部js
     * 
     * @access public
     * @param unknown $footer
     * @return unknown
     */
    public static function footer() {
        $jsUrl = Helper::options()->pluginUrl . '/LaTex/latex.js';
        echo '<script type="text/javascript" src="'. $jsUrl .'"></script>';
        $mark = Typecho_Widget::widget('Widget_Options')->plugin('LaTex')->mark;
        echo '<script type="text/javascript">latex.parse("'. $mark. '");</script>';
    }
}
