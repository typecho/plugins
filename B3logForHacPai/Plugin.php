<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
//如果没有json库，加载兼容包
! extension_loaded('json') and include('libs/compat_json.php');
/**
 * 黑客派社区实时同步插件
 * 
 * @package B3log for HacPai
 * @author DT27
 * @version 1.0.0
 * @link https://dt27.org/B3log-for-HacPai/
 */
class B3logForHacPai_Plugin implements Typecho_Plugin_Interface
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
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish =
            array('B3logForHacPai_Plugin', 'finishPublish');
        Typecho_Plugin::factory('Widget_Feedback')->finishComment =
            array('B3logForHacPai_Plugin', 'finishComment');

        // 创建路由
        // from HacPai
        Helper::addRoute('b3log.hacpai.article', '/b3log-hacpai/article', 'B3logForHacPai_Action', 'articleReceiver');
        Helper::addRoute('b3log.hacpai.comment', '/b3log-hacpai/comment', 'B3logForHacPai_Action', 'commentReceiver');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){
        Helper::removeRoute('b3log.hacpai.article');
        Helper::removeRoute('b3log.hacpai.comment');
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
        $b3logKey = new Typecho_Widget_Helper_Form_Element_Text('b3logKey', NULL, NULL,
            _t('B3log Key'), _t('请填写黑客派社区中设置的 B3log Key，并在社区中设置接收接口。<a href="https://hacpai.com/settings#soloKey" target="_blank">点此设置</a><br>
客户端收文及更新接口：<strong style="color: red;">'.Helper::options()->siteUrl.'b3log-hacpai/article</strong><br>客户端收评接口：<strong style="color: red;">'.Helper::options()->siteUrl.'b3log-hacpai/comment</strong>'));
        $form->addInput($b3logKey->addRule('required', _t('必须填写 B3log Key')));

        $b3logTitle = new Typecho_Widget_Helper_Form_Element_Text('b3logTitle', NULL, Helper::options()->title,
            _t('博客标题'), _t('请填写本博客标题'));
        $form->addInput($b3logTitle);

        $b3logHost = new Typecho_Widget_Helper_Form_Element_Text('b3logHost', NULL, Helper::options()->siteUrl,
            _t('博客地址'), _t('请填写本博客地址，需包括 http 且末尾无斜杠，例如：<strong style="color: red;">https://dt27.org</strong>'));
        $form->addInput($b3logHost);
        Typecho_Widget::widget('Widget_User')->to($user);
        $b3logEmail = new Typecho_Widget_Helper_Form_Element_Text('b3logEmail', NULL, $user->mail,
            _t('博客邮箱'), _t('请填写本博客邮箱'));
        $form->addInput($b3logEmail);


        $isHacPai = new Typecho_Widget_Helper_Form_Element_Radio('isHacPai',
            array(
                '1' => '是',
                '0' => '否',
            ),'1', _t('是否启用同步功能'), NULL);
        $form->addInput($isHacPai);
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
     * 发布文章
     * 
     * @access public
     * @return void
     */
    public static function finishPublish($contents, $edit)
    {
        $b3log = Typecho_Widget::widget('Widget_Options')->plugin('B3logForHacPai');
        if($b3log->isHacPai == 1) {
            $postData = array(
                "article" => array(
                    "id" => $edit->cid,
                    "title" => $contents['title'],
                    "permalink" => substr($edit->permalink,strlen($b3log->b3logHost)),//substr($str,4) [article.permalink] should start with /, for example, /hello-world
                    "tags" => $contents['tags'],
                    "content" => $contents['text'],
                ),
                "client" => array(
                    "title" => $b3log->b3logTitle,
                    "host" => $b3log->b3logHost,
                    "email" => $b3log->b3logEmail,
                    "key" => $b3log->b3logKey,
                ));
            $postString = json_encode($postData);
            $ch = curl_init('http://rhythm.b3log.org/api/article');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS,$postString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($postString))
            );
            $result = curl_exec($ch);
        }
        return $contents;
    }
    /**
     * 发布评论
     *
     * @access public
     * @return void
     */
    public static function finishComment($comment)
    {
        $b3log = Typecho_Widget::widget('Widget_Options')->plugin('B3logForHacPai');
        if ($b3log->isHacPai == 1) {
            $postData = array(
                "comment" => array(
                    "id" => $comment->coid,
                    "articleId" => $comment->cid,
                    "content" => $comment->text,
                    "authorName" => $comment->author,
                    "authorEmail" => $comment->mail,
                ),
                "client" => array(
                    "title" => $b3log->b3logTitle,
                    "host" => $b3log->b3logHost,
                    "email" => $b3log->b3logEmail,
                    "key" => $b3log->b3logKey,
                ));
            $postString = json_encode($postData);
            $ch = curl_init('http://rhythm.b3log.org/api/comment');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content -Length: ' . strlen($postString))
            );
            $result = curl_exec($ch);
            //print_r($result);exit;
        }
        return $comment;
    }
}
