<?php


/**
 * 头像代理修改插件
 * 
 * @package Avartar
 * @author loftor
 * @version 1.0.0 Beta
 * @link http://loftor.com/
 */
class Avartar implements Typecho_Plugin_Interface
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
        Typecho_Plugin::factory('Widget_Abstract_Comments')->gravatar = array('Avartar', 'gravatar');
        return _t('启用成功，请进行相应设置！');
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
        $http = new Typecho_Widget_Helper_Form_Element_Text('http', NULL, 'http://gravatar.duoshuo.com',
            _t('http代理地址'), _t('请填写http代理地址!'));
        $form->addInput($http);
        
        $https = new Typecho_Widget_Helper_Form_Element_Text('https', NULL, 'https://secure.gravatar.com',
            _t('https代理地址'), _t('请填写https代理地址!'));
        $form->addInput($https);
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
     * 输出用户头像
     *
     * @access public
     * @param array $file 上传的文件
     * @return mixed
     */
    public static function gravatar($size, $rating, $default, $ctx)
    {
        $mailHash = NULL;
        if (!empty($ctx->mail)) {
            $mailHash = md5(strtolower($ctx->mail));
        }
        $options = Typecho_Widget::widget('Widget_Options');
        if ($ctx->request->isSecure()) {
            $host=$options->plugin('Avartar')->https;
        } else {
            $host=$options->plugin('Avartar')->http;
        }

        $url = $host . '/avatar/';

        if (!empty($ctx->mail)) {
            $url .= $mailHash;
        }

        $url .= '?s=' . $size;
        $url .= '&amp;r=' . $rating;
        $url .= '&amp;d=' . $default;

        echo '<img class="avatar" src="' . $url . '" alt="' .
        $ctx->author . '" width="' . $size . '" height="' . $size . '" />';
    }
}