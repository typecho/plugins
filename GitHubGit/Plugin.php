<?php
/**
 * GitHub git 同步文章
 * 
 * @package GitHub Git Transmit
 * @author weakish
 * @version 0.0.1
 * @dependence 10.6.24-*
 * @link http://typecho.org
 */
class GitHubGit_Plugin implements Typecho_Plugin_Interface
{
    // Use your own random code.
    // Keep your code secret!
    // Anyone knowing the code can post articles on your blog!
    const github_git = 'curtseyingpiddlesMiguelyeshivahsclarinettists';
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        if (false == Typecho_Http_Client::get()) {
            throw new Typecho_Plugin_Exception(_t('对不起, 您的主机不支持 php-curl 扩展而且没有打开 allow_url_fopen 功能, 无法正常使用此功能'));
        }
    
        Helper::addAction(github_git, 'GitHubGit_Action');
        return _t('请在插件设置里设置 GitHub 的Git参数') . $error;
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
    {
        Helper::removeAction(github_git);
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $basePath = new Typecho_Widget_Helper_Form_Element_Text('basePath', NULL, '/_posts',
        _t('Git目录'), _t('填写需要监控的Git目录')); // 默认为Jekyll的_posts目录
        $form->addInput($basePath->addRule('required', _t('必须填写数据库用户名')));
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
}
<?php
/**
 * GitHub git 同步文章
 * 
 * @package GitHub Git Transmit
 * @author weakish
 * @version 0.0.1
 * @dependence 10.6.24-*
 * @link http://typecho.org
 */
class GitHubGit_Plugin implements Typecho_Plugin_Interface
{
    // Use your own random code.
    // Keep your code secret!
    // Anyone knowing the code can post articles on your blog!
    const github_git = 'curtseyingpiddlesMiguelyeshivahsclarinettists';
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        if (false == Typecho_Http_Client::get()) {
            throw new Typecho_Plugin_Exception(_t('对不起, 您的主机不支持 php-curl 扩展而且没有打开 allow_url_fopen 功能, 无法正常使用此功能'));
        }
    
        Helper::addAction(github_git, 'GitHubGit_Action');
        return _t('请在插件设置里设置 GitHub 的Git参数') . $error;
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
    {
        Helper::removeAction(github_git);
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $basePath = new Typecho_Widget_Helper_Form_Element_Text('basePath', NULL, '/_posts',
        _t('Git目录'), _t('填写需要监控的Git目录')); // 默认为Jekyll的_posts目录
        $form->addInput($basePath->addRule('required', _t('必须填写数据库用户名')));
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
}
<?php
/**
 * GitHub git 同步文章
 * 
 * @package GitHub Git Transmit
 * @author weakish
 * @version 0.0.1
 * @dependence 10.6.24-*
 * @link http://typecho.org
 */
class GitHubGit_Plugin implements Typecho_Plugin_Interface
{
    // Use your own random code.
    // Keep your code secret!
    // Anyone knowing the code can post articles on your blog!
    const github_git = 'curtseyingpiddlesMiguelyeshivahsclarinettists';
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        if (false == Typecho_Http_Client::get()) {
            throw new Typecho_Plugin_Exception(_t('对不起, 您的主机不支持 php-curl 扩展而且没有打开 allow_url_fopen 功能, 无法正常使用此功能'));
        }
    
        Helper::addAction(github_git, 'GitHubGit_Action');
        return _t('请在插件设置里设置 GitHub 的Git参数') . $error;
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
    {
        Helper::removeAction(github_git);
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $basePath = new Typecho_Widget_Helper_Form_Element_Text('basePath', NULL, '/_posts',
        _t('Git目录'), _t('填写需要监控的Git目录')); // 默认为Jekyll的_posts目录
        $form->addInput($basePath->addRule('required', _t('必须填写数据库用户名')));
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
}
