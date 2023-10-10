<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$success = true;
try {
    $dbConfig = $options->plugin('WordpressToTypecho');

    /** 初始化一个db */
    if (Typecho_Db_Adapter_Mysql::isAvailable()) {
        $wordpressDb = new Typecho_Db('Mysql', $dbConfig->prefix);
    } else {
        $wordpressDb = new Typecho_Db('Pdo_Mysql', $dbConfig->prefix);
    }

    /** 只读即可 */
    $wordpressDb->addServer(array (
      'host' => $dbConfig->host,
      'user' => $dbConfig->user,
      'password' => $dbConfig->password,
      'charset' => 'utf8',
      'port' => $dbConfig->port,
      'database' => $dbConfig->database
    ), Typecho_Db::READ);
    
    $rows = $wordpressDb->fetchAll($wordpressDb->select()->from('table.options'));
    $static = array();
    foreach ($rows as $row) {
        $static[$row['option_name']] = $row['option_value'];
    }
} catch (Typecho_Db_Exception $e) {
    $success = false;
}

include 'header.php';
include 'menu.php';
?>
<div class="main">
    <div class="body container">
        <div class="colgroup">
            <?php include 'page-title.php'; ?>
        </div>
        <div class="colgroup typecho-page-main" role="main">
            <div class="com-mb-12">
                <?php if ($success): ?>
                <div class="message notice">
                <form action="<?php $options->index('/action/wordpress-to-typecho'); ?>" method="post">
                    <?php _e('我们检测到了 WordPress 系统信息, 点击下方的按钮开始数据转换, 数据转换可能会耗时较长.'); ?>
                    <ul>
                        <li>网站名：<strong><?php echo $static['blogname']; ?></strong></li>
                        <li>描述：<?php echo $static['blogdescription']; ?></li>
                        <li>网址：<code><?php echo $static['siteurl']; ?></code></li>
                    </ul>
                    <button type="submit" class="btn primary"><?php _e('开始数据转换 &raquo;'); ?></button>
                </form>
                </div>
                <?php else: ?>
                <div class="message error">
                    <?php _e('我们在连接到 WordPress 的数据库时发生了错误, 请<a href="%s">重新设置</a>你的信息.', 
                    Typecho_Common::url('options-plugin.php?config=WordpressToTypecho', $options->adminUrl)); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
include 'copyright.php';
include 'common-js.php';
include 'footer.php';
?>
