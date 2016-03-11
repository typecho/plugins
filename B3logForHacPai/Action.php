<?php ! defined('__TYPECHO_ROOT_DIR__') and exit();

class B3logForHacPai_Action extends Typecho_Widget
{

    /**
     * 构造函数
     *
     * @param mixed $request
     * @param mixed $response
     * @param null $params
     */
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);


    }

    /**
     * Article receiver (from B3log Symphony).
     *
     */
    public function articleReceiver(){

        //print_r($_POST);
    }

    /**
     * Comment receiver (from B3log Symphony).
     *
     */
    public function commentReceiver(){
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents("php://input");
        }
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $GLOBALS['HTTP_RAW_POST_DATA'] = trim($GLOBALS['HTTP_RAW_POST_DATA']);
        }

        $result = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);
        if($result->client->key == Typecho_Widget::widget('Widget_Options')->plugin('B3logForHacPai')->b3logKey) {
            $post = Typecho_Db::get()->fetchRow(Typecho_Db::get()->select('authorId')->from('table.contents')->where('cid = ?', $result->comment->articleId));

            if ($post) {
                $comment = array(
                    'cid' => $result->comment->articleId,
                    'created' => Helper::options()->gmtTime,
                    'text' => $result->comment->content,
                    'author' => $result->comment->authorName,
                    'mail' => $result->comment->authorEmail,
                    'url' => $result->comment->authorURL,
                    'agent' => $this->request->getAgent(),
                    'ip' => $this->request->getIp(),
                    'ownerId' => $post['authorId'],
                    'type' => 'comment',
                    'status' => 'approved',
                );
                //print_r($result->comment->articleid);
                //$article = Typecho_Widget::widget('Widget_Users_Author@' . $this->cid, array('cid' => $result->comment->articleId));
                Typecho_Widget::widget('Widget_Feedback')->insert($comment);
            }
        }
    }



}
