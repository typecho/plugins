<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Markdown 编辑器 <a href="https://pandao.github.io/editor.md/" target="_blank">Editor.md</a> for Typecho
 * 
 * @package EditorMD
 * @author DT27
 * @version 1.1.1
 * @link https://dt27.org
 */
class EditorMD_Plugin implements Typecho_Plugin_Interface
{
    public static $count = 0;
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('admin/write-post.php')->richEditor = array('EditorMD_Plugin', 'Editor');
        Typecho_Plugin::factory('admin/write-page.php')->richEditor = array('EditorMD_Plugin', 'Editor');

        Typecho_Plugin::factory('Widget_Abstract_Contents')->content = array('EditorMD_Plugin', 'content');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('EditorMD_Plugin','footerJS');
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
        $emoji = new Typecho_Widget_Helper_Form_Element_Radio('emoji',
            array(
                '1' => '是',
                '0' => '否',
            ),'1', _t('启用 Emoji 表情'), _t('启用后可在编辑器里插入 Emoji 表情符号，前台会加载13KB的js文件将表情符号转为表情图片(图片来自七牛云存储)'));
        $form->addInput($emoji);

        $isActive = new Typecho_Widget_Helper_Form_Element_Radio('isActive',
            array(
                '1' => '是',
                '0' => '否',
            ),'0', _t('接管前台Markdown解析并启用ToC、TeX科学公式、流程图 Flowchart、时序图 Sequence Diagram 等扩展'), _t('启用后，插件将接管前台 Markdown 解析，使用与后台编辑器一致的 <a href="https://github.com/chjj/marked" target="_blank">marked.js</a> 解析器，前台需要加载的依赖文件大约366KB(不包括jQuery)'));
        $form->addInput($isActive);
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
     * 插入编辑器
     */
    public static function Editor()
    {
        $options = Helper::options();
        $cssUrl = $options->pluginUrl.'/EditorMD/css/editormd.css';
        $jsUrl = $options->pluginUrl.'/EditorMD/js/editormd.js';
        $editormd = Typecho_Widget::widget('Widget_Options')->plugin('EditorMD');
        ?>
        <link rel="stylesheet" href="<?php echo $cssUrl; ?>" />
        <script>
            var emojiPath = '<?php echo $options->pluginUrl; ?>';
        </script>
        <script type="text/javascript" src="<?php echo $jsUrl; ?>"></script>
        <script>
            $(document).ready(function() {
                $('#text').wrap("<div id='text-editormd'></div>");
                postEditormd = editormd("text-editormd", {
                    width: "100%",
                    height: 640,
                    path: '<?php echo $options->pluginUrl ?>/EditorMD/lib/',
                    toolbarAutoFixed: false,
                    htmlDecode: true,
                    emoji: <?php echo $editormd->emoji ? 'true' : 'false'; ?>,
                    tex: <?php echo $editormd->isActive ? 'true' : 'false'; ?>,
                    toc: <?php echo $editormd->isActive ? 'true' : 'false'; ?>,
                    tocm: <?php echo $editormd->isActive ? 'true' : 'false'; ?>,    // Using [TOCM]
                    taskList: <?php echo $editormd->isActive ? 'true' : 'false'; ?>,
                    flowChart: <?php echo $editormd->isActive ? 'true' : 'false'; ?>,  // 默认不解析
                    sequenceDiagram: <?php echo $editormd->isActive ? 'true' : 'false'; ?>,
                    toolbarIcons: function () {
                        return ["undo", "redo", "|", "bold", "del", "italic", "quote", "h1", "h2", "h3", "h4", "|", "list-ul", "list-ol", "hr", "|", "link", "reference-link", "image", "code", "preformatted-text", "code-block", "table", "datetime"<?php echo $editormd->emoji ? ', "emoji"' : ''; ?>, "html-entities", "more", "|", "goto-line", "watch", "preview", "fullscreen", "clear", "|", "help", "info"]
                    },
                    toolbarIconsClass: {
                        more: "fa-newspaper-o"  // 指定一个FontAawsome的图标类
                    },
                    // 自定义工具栏按钮的事件处理
                    toolbarHandlers: {
                        /**
                         * @param {Object}      cm         CodeMirror对象
                         * @param {Object}      icon       图标按钮jQuery元素对象
                         * @param {Object}      cursor     CodeMirror的光标对象，可获取光标所在行和位置
                         * @param {String}      selection  编辑器选中的文本
                         */
                        more: function (cm, icon, cursor, selection) {
                            cm.replaceSelection("<!--more-->");
                        }
                    },
                    lang: {
                        toolbar: {
                            more: "插入摘要分隔符"
                        }
                    },
                });

                // 优化图片及文件附件插入 Thanks to Markxuxiao
                Typecho.insertFileToEditor = function (file, url, isImage) {
                    html = isImage ? '![' + file + '](' + url + ')'
                        : '[' + file + '](' + url + ')';
                    postEditormd.insertValue(html);
                };
            });
        </script>
        <?php
    }
    /**
     * emoji 解析器
     */
    public static function footerJS($conent)
    {
        $options = Helper::options();
        $pluginUrl = $options->pluginUrl.'/EditorMD';
    $editormd = Typecho_Widget::widget('Widget_Options')->plugin('EditorMD');
if($editormd->isActive == 1 && $conent->isMarkdown) {
?>
<link rel="stylesheet" href="<?php echo $pluginUrl; ?>/css/editormd.preview.min.css"/>
<?php
}
if($editormd->emoji){
?>
<link rel="stylesheet" href="<?php echo $pluginUrl; ?>/css/emojify.min.css" />
<?php
}
if($editormd->emoji || ($editormd->isActive == 1 && $conent->isMarkdown)){
?>
<script type="text/javascript">
    window.jQuery || document.write(unescape('%3Cscript%20type%3D%22text/javascript%22%20src%3D%22<?php echo $pluginUrl; ?>/lib/jquery.min.js%22%3E%3C/script%3E'));
</script>
<?php
}
if($editormd->isActive == 1 && $conent->isMarkdown) {
?>
<script src="<?php echo $pluginUrl; ?>/lib/marked.min.js"></script>
<script src="<?php echo $pluginUrl; ?>/lib/prettify.min.js"></script>
<script src="<?php echo $pluginUrl; ?>/lib/raphael.min.js"></script>
<script src="<?php echo $pluginUrl; ?>/lib/underscore.min.js"></script>
<script src="<?php echo $pluginUrl; ?>/lib/sequence-diagram.min.js"></script>
<script src="<?php echo $pluginUrl; ?>/lib/flowchart.min.js"></script>
<script src="<?php echo $pluginUrl; ?>/lib/jquery.flowchart.min.js"></script>
<script src="<?php echo $pluginUrl; ?>/js/editormd.min.js"></script>
<?php
}
if($editormd->emoji){
?>
<script src="<?php echo $pluginUrl; ?>/js/emojify.min.js"></script>
<?php
}
if($editormd->emoji||($editormd->isActive == 1 && $conent->isMarkdown)){
?>
<script type="text/javascript">
$(function() {
<?php
if($editormd->isActive == 1 && $conent->isMarkdown) {
?>
    var markdowns = document.getElementsByClassName("md_content");
    for(var i=1; i<=markdowns.length; i++) {
        var markdown = $('#md_content_'+ i + " #append-test").text();
        //$('#md_content_'+i).text('');
        var testEditormdView;
        testEditormdView = editormd.markdownToHTML("md_content_"+i, {
            markdown: markdown,//+ "\r\n" + $("#append-test").text(),
            toolbarAutoFixed : false,
            htmlDecode: true,
            emoji: <?php echo $editormd->emoji?'true':'false'; ?>,
            tex: <?php echo $editormd->isActive?'true':'false'; ?>,
            toc: <?php echo $editormd->isActive?'true':'false'; ?>,
            tocm: <?php echo $editormd->isActive?'true':'false'; ?>,
            taskList: <?php echo $editormd->isActive?'true':'false'; ?>,
            flowChart: <?php echo $editormd->isActive?'true':'false'; ?>,
            sequenceDiagram: <?php echo $editormd->isActive?'true':'false'; ?>,
        });
    }
<?php
}
if($editormd->emoji){
?>
    emojify.setConfig({
        img_dir: 'https:' == document.location.protocol ? "https://staticfile.qnssl.com/emoji-cheat-sheet/1.0.0" : "http://cdn.staticfile.org/emoji-cheat-sheet/1.0.0",
        blacklist: {
            'ids': [],
            'classes': ['no-emojify'],
            'elements': ['^script$', '^textarea$', '^pre$', '^code$']
        },
    });
    emojify.run();
<?php } ?>
});
</script>
<?php
    }
    }

    public static function content($text, $conent){
        self::$count++;
        $editormd = Typecho_Widget::widget('Widget_Options')->plugin('EditorMD');
        $text = $conent->isMarkdown ? ($editormd->isActive == 1?$text:$conent->markdown($text))
            : $conent->autoP($text);
        if($editormd->isActive == 1 && $conent->isMarkdown)
            return '<div id="md_content_'.self::$count.'" class="md_content" style="background-image:url('.Helper::options()->pluginUrl.'/EditorMD'.'/images/loading.gif);background-position: center;background-repeat: no-repeat; min-height: 50px;"><textarea id="append-test" style="display:none;">'.$text.'</textarea></div>';
        else
            return $text;
    }
}
