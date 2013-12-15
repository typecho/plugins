<?php

if(!defined('OSS_API_PATH'))
define('OSS_API_PATH', dirname(__FILE__));

//ACCESS_ID
define('OSS_ACCESS_ID', '');

//ACCESS_KEY
define('OSS_ACCESS_KEY', '');

//是否记录日志
define('ALI_LOG', FALSE);

//自定义日志路径，如果没有设置，则使用系统默认路径，在./logs/
define('ALI_LOG_PATH',OSS_API_PATH.'/logs/');

//是否显示LOG输出
define('ALI_DISPLAY_LOG', FALSE);

require_once 'lib/sdk.class.php';



/**
 * <a href="http://loftor.com/" target="_blank">Alioss</a>专用的文件上传插件。
 * 
 * @package AliUpload
 * @author loftor
 * @version 1.0.0 Beta
 * @link http://loftor.com/
 */
class AliUpload_Plugin implements Typecho_Plugin_Interface
{

    //上传文件目录
    const UPLOAD_PATH = 'usr/uploads';
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Upload')->uploadHandle = array('AliUpload_Plugin', 'uploadHandle');
        Typecho_Plugin::factory('Widget_Upload')->modifyHandle = array('AliUpload_Plugin', 'modifyHandle');
        Typecho_Plugin::factory('Widget_Upload')->deleteHandle = array('AliUpload_Plugin', 'deleteHandle');
        Typecho_Plugin::factory('Widget_Upload')->attachmentHandle = array('AliUpload_Plugin', 'attachmentHandle');
        Typecho_Plugin::factory('Widget_Upload')->attachmentDataHandle = array('AliUpload_Plugin', 'attachmentDataHandle');
        
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
        $buketName = new Typecho_Widget_Helper_Form_Element_Text('bucket', NULL, 'loftor',
            _t('Bucket名称'), _t('请填写Buket名称!'));
        $form->addInput($buketName);

        $accessID = new Typecho_Widget_Helper_Form_Element_Text('access_id', NULL, '',
            _t('ACCESS_ID'), _t('请填写ACCESS_ID!'));
        $form->addInput($accessID);

        $accessKEY = new Typecho_Widget_Helper_Form_Element_Text('access_key', NULL, '',
            _t('ACCESS_KEY'), _t('请填写请填写ACCESS_KEY!'));
        $form->addInput($accessKEY);

        $domianName = new Typecho_Widget_Helper_Form_Element_Text('domian', NULL, 'http://oss.loftor.com/',
            _t('域名名称'), _t('请填写域名名称!'));
        $form->addInput($domianName);
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
     * 上传文件处理函数
     *
     * @access public
     * @param array $file 上传的文件
     * @return mixed
     */
    public static function uploadHandle($file)
    {


        if (empty($file['name'])) {
            return false;
        }

        $fileName = preg_split("(\/|\\|:)", $file['name']);
        $file['name'] = array_pop($fileName);
        
        //获取扩展名
        $ext = '';
        $part = explode('.', $file['name']);
        if (($length = count($part)) > 1) {
            $ext = strtolower($part[$length - 1]);
        }

        if (!self::checkFileType($ext)) {
            return false;
        }

        $options = Typecho_Widget::widget('Widget_Options');
        $date = new Typecho_Date($options->gmtTime);
        $path = self::UPLOAD_PATH;


        $options = Typecho_Widget::widget('Widget_Options');

        $access_id=$options->plugin('AliUpload')->access_id;
        $access_key=$options->plugin('AliUpload')->access_key;
        $bucket = $options->plugin('AliUpload')->bucket;

        $oss_service = new ALIOSS($access_id,$access_key);

        $path = $path . '/' . $date->year . '/' . $date->month;

        //获取文件名
        $fileName = sprintf('%u', crc32(uniqid())) . '.' . $ext;
        $path = $path . '/' . $fileName;//add for mkdir

        $response;
        if (isset($file['tmp_name'])) {
            //移动上传文件
            $response = $oss_service->upload_file_by_file($bucket,$path,$file['tmp_name']);

            if (!$response->isOk()) {
                return false;
            }
        } else if (isset($file['bits'])) {
            //直接写入文件
            $upload_file_options = array(
                'content' => $file['bits'],
                'length' => strlen($file['bits'])
                );

            $response = $oss_service->upload_file_by_content($bucket,$path,$upload_file_options);

            if (!$response->isOk()) {
                return false;
            }
        } else {
            return false;
        }

        if (!isset($file['size'])) {

            $file['size'] = $response->header['_info']['size_upload'];
        }

        $path=$response->header['x-oss-request-url'];

        //返回相对存储路径
        return array(
            'name' => $file['name'],
            'path' => self::UPLOAD_PATH . '/' . $date->year . '/' . $date->month . '/' . $fileName,
            'size' => $file['size'],
            'type' => $ext,
            'mime' => self::mimeContentType($ext)
            );
    }

    /**
     * 修改文件处理函数
     *
     * @access public
     * @param array $content 老文件
     * @param array $file 新上传的文件
     * @return mixed
     */
    public static function modifyHandle($content, $file)
    {

        if (empty($file['name'])) {
            return false;
        }

        $fileName = preg_split("(\/|\\|:)", $file['name']);
        $file['name'] = array_pop($fileName);
        
        //获取扩展名
        $ext = '';
        $part = explode('.', $file['name']);
        if (($length = count($part)) > 1) {
            $ext = strtolower($part[$length - 1]);
        }

        if ($content['attachment']->type != $ext) {
            return false;
        }

        //获取文件名
        $fileName = $content['attachment']->path;
        $path = $path . '/' . $fileName;//add for mkdir
        $path=substr($path, 1);

        $options = Typecho_Widget::widget('Widget_Options');

        $access_id=$options->plugin('AliUpload')->access_id;
        $access_key=$options->plugin('AliUpload')->access_key;
        $bucket = $options->plugin('AliUpload')->bucket;

        $oss_service = new ALIOSS($access_id,$access_key);

        

        if (isset($file['tmp_name'])) {
            //移动上传文件
            $response = $oss_service->upload_file_by_file($bucket,$path,$file['tmp_name']);
            if (!$response->isOk()) {
                return false;
            }
        } else if (isset($file['bits'])) {
            //直接写入文件
            $upload_file_options = array(
                'content' => $file['bits'],
                'length' => strlen($file['bits'])
                );

            $response = $oss_service->upload_file_by_content($bucket,$path,$upload_file_options);

            if (!$response->isOk()) {
                return false;
            }
        } else {
            return false;
        }

        if (!isset($file['size'])) {
            $file['size'] = $response->header['_info']['size_upload'];
        }

        //返回相对存储路径
        return array(
            'name' => $content['attachment']->name,
            'path' => $content['attachment']->path,
            'size' => $file['size'],
            'type' => $content['attachment']->type,
            'mime' => $content['attachment']->mime
            );
    }

    /**
     * 删除文件
     *
     * @access public
     * @param array $content 文件相关信息
     * @return string
     */
    public static function deleteHandle(array $content)
    {
        $options = Typecho_Widget::widget('Widget_Options');

        $access_id=$options->plugin('AliUpload')->access_id;
        $access_key=$options->plugin('AliUpload')->access_key;
        $bucket = $options->plugin('AliUpload')->bucket;

        $oss_service = new ALIOSS($access_id,$access_key);

        $response = $oss_service->delete_object($bucket,$content['attachment']->path);
        return $response->isOk();
    }

    /**
     * 获取实际文件绝对访问路径
     *
     * @access public
     * @param array $content 文件相关信息
     * @return string
     */
    public static function attachmentHandle(array $content)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $domian = $options->plugin('AliUpload')->domian;
        return $domian.$content['attachment']->path;
    }

    /**
     * 获取实际文件数据
     *
     * @access public
     * @param array $content
     * @return string
     */
    public static function attachmentDataHandle(array $content)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $domian = $options->plugin('AliUpload')->domian;
        return file_get_contents($domian.$content['attachment']->path);
    }

    /**
     * 检查文件名
     *
     * @access private
     * @param string $ext 扩展名
     * @return boolean
     */
    public static function checkFileType($ext)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        return in_array($ext, $options->allowedAttachmentTypes);
    }

    /**
     * 获取图片
     *
     * @access public
     * @param string $fileName 文件名
     * @return string
     */
    public static function mimeContentType($ext)
    {
        $mimeTypes = array(
          'ez' => 'application/andrew-inset',
          'csm' => 'application/cu-seeme',
          'cu' => 'application/cu-seeme',
          'tsp' => 'application/dsptype',
          'spl' => 'application/x-futuresplash',
          'hta' => 'application/hta',
          'cpt' => 'image/x-corelphotopaint',
          'hqx' => 'application/mac-binhex40',
          'nb' => 'application/mathematica',
          'mdb' => 'application/msaccess',
          'doc' => 'application/msword',
          'dot' => 'application/msword',
          'bin' => 'application/octet-stream',
          'oda' => 'application/oda',
          'ogg' => 'application/ogg',
          'prf' => 'application/pics-rules',
          'key' => 'application/pgp-keys',
          'pdf' => 'application/pdf',
          'pgp' => 'application/pgp-signature',
          'ps' => 'application/postscript',
          'ai' => 'application/postscript',
          'eps' => 'application/postscript',
          'rss' => 'application/rss+xml',
          'rtf' => 'text/rtf',
          'smi' => 'application/smil',
          'smil' => 'application/smil',
          'wp5' => 'application/wordperfect5.1',
          'xht' => 'application/xhtml+xml',
          'xhtml' => 'application/xhtml+xml',
          'zip' => 'application/zip',
          'cdy' => 'application/vnd.cinderella',
          'mif' => 'application/x-mif',
          'xls' => 'application/vnd.ms-excel',
          'xlb' => 'application/vnd.ms-excel',
          'cat' => 'application/vnd.ms-pki.seccat',
          'stl' => 'application/vnd.ms-pki.stl',
          'ppt' => 'application/vnd.ms-powerpoint',
          'pps' => 'application/vnd.ms-powerpoint',
          'pot' => 'application/vnd.ms-powerpoint',
          'sdc' => 'application/vnd.stardivision.calc',
          'sda' => 'application/vnd.stardivision.draw',
          'sdd' => 'application/vnd.stardivision.impress',
          'sdp' => 'application/vnd.stardivision.impress',
          'smf' => 'application/vnd.stardivision.math',
          'sdw' => 'application/vnd.stardivision.writer',
          'vor' => 'application/vnd.stardivision.writer',
          'sgl' => 'application/vnd.stardivision.writer-global',
          'sxc' => 'application/vnd.sun.xml.calc',
          'stc' => 'application/vnd.sun.xml.calc.template',
          'sxd' => 'application/vnd.sun.xml.draw',
          'std' => 'application/vnd.sun.xml.draw.template',
          'sxi' => 'application/vnd.sun.xml.impress',
          'sti' => 'application/vnd.sun.xml.impress.template',
          'sxm' => 'application/vnd.sun.xml.math',
          'sxw' => 'application/vnd.sun.xml.writer',
          'sxg' => 'application/vnd.sun.xml.writer.global',
          'stw' => 'application/vnd.sun.xml.writer.template',
          'sis' => 'application/vnd.symbian.install',
          'wbxml' => 'application/vnd.wap.wbxml',
          'wmlc' => 'application/vnd.wap.wmlc',
          'wmlsc' => 'application/vnd.wap.wmlscriptc',
          'wk' => 'application/x-123',
          'dmg' => 'application/x-apple-diskimage',
          'bcpio' => 'application/x-bcpio',
          'torrent' => 'application/x-bittorrent',
          'cdf' => 'application/x-cdf',
          'vcd' => 'application/x-cdlink',
          'pgn' => 'application/x-chess-pgn',
          'cpio' => 'application/x-cpio',
          'csh' => 'text/x-csh',
          'deb' => 'application/x-debian-package',
          'dcr' => 'application/x-director',
          'dir' => 'application/x-director',
          'dxr' => 'application/x-director',
          'wad' => 'application/x-doom',
          'dms' => 'application/x-dms',
          'dvi' => 'application/x-dvi',
          'pfa' => 'application/x-font',
          'pfb' => 'application/x-font',
          'gsf' => 'application/x-font',
          'pcf' => 'application/x-font',
          'pcf.Z' => 'application/x-font',
          'gnumeric' => 'application/x-gnumeric',
          'sgf' => 'application/x-go-sgf',
          'gcf' => 'application/x-graphing-calculator',
          'gtar' => 'application/x-gtar',
          'tgz' => 'application/x-gtar',
          'taz' => 'application/x-gtar',
          'gz'  => 'application/x-gtar',
          'hdf' => 'application/x-hdf',
          'phtml' => 'application/x-httpd-php',
          'pht' => 'application/x-httpd-php',
          'php' => 'application/x-httpd-php',
          'phps' => 'application/x-httpd-php-source',
          'php3' => 'application/x-httpd-php3',
          'php3p' => 'application/x-httpd-php3-preprocessed',
          'php4' => 'application/x-httpd-php4',
          'ica' => 'application/x-ica',
          'ins' => 'application/x-internet-signup',
          'isp' => 'application/x-internet-signup',
          'iii' => 'application/x-iphone',
          'jar' => 'application/x-java-archive',
          'jnlp' => 'application/x-java-jnlp-file',
          'ser' => 'application/x-java-serialized-object',
          'class' => 'application/x-java-vm',
          'js' => 'application/x-javascript',
          'chrt' => 'application/x-kchart',
          'kil' => 'application/x-killustrator',
          'kpr' => 'application/x-kpresenter',
          'kpt' => 'application/x-kpresenter',
          'skp' => 'application/x-koan',
          'skd' => 'application/x-koan',
          'skt' => 'application/x-koan',
          'skm' => 'application/x-koan',
          'ksp' => 'application/x-kspread',
          'kwd' => 'application/x-kword',
          'kwt' => 'application/x-kword',
          'latex' => 'application/x-latex',
          'lha' => 'application/x-lha',
          'lzh' => 'application/x-lzh',
          'lzx' => 'application/x-lzx',
          'frm' => 'application/x-maker',
          'maker' => 'application/x-maker',
          'frame' => 'application/x-maker',
          'fm' => 'application/x-maker',
          'fb' => 'application/x-maker',
          'book' => 'application/x-maker',
          'fbdoc' => 'application/x-maker',
          'wmz' => 'application/x-ms-wmz',
          'wmd' => 'application/x-ms-wmd',
          'com' => 'application/x-msdos-program',
          'exe' => 'application/x-msdos-program',
          'bat' => 'application/x-msdos-program',
          'dll' => 'application/x-msdos-program',
          'msi' => 'application/x-msi',
          'nc' => 'application/x-netcdf',
          'pac' => 'application/x-ns-proxy-autoconfig',
          'nwc' => 'application/x-nwc',
          'o' => 'application/x-object',
          'oza' => 'application/x-oz-application',
          'pl' => 'application/x-perl',
          'pm' => 'application/x-perl',
          'p7r' => 'application/x-pkcs7-certreqresp',
          'crl' => 'application/x-pkcs7-crl',
          'qtl' => 'application/x-quicktimeplayer',
          'rpm' => 'audio/x-pn-realaudio-plugin',
          'shar' => 'application/x-shar',
          'swf' => 'application/x-shockwave-flash',
          'swfl' => 'application/x-shockwave-flash',
          'sh' => 'text/x-sh',
          'sit' => 'application/x-stuffit',
          'sv4cpio' => 'application/x-sv4cpio',
          'sv4crc' => 'application/x-sv4crc',
          'tar' => 'application/x-tar',
          'tcl' => 'text/x-tcl',
          'tex' => 'text/x-tex',
          'gf' => 'application/x-tex-gf',
          'pk' => 'application/x-tex-pk',
          'texinfo' => 'application/x-texinfo',
          'texi' => 'application/x-texinfo',
          '~' => 'application/x-trash',
          '%' => 'application/x-trash',
          'bak' => 'application/x-trash',
          'old' => 'application/x-trash',
          'sik' => 'application/x-trash',
          't' => 'application/x-troff',
          'tr' => 'application/x-troff',
          'roff' => 'application/x-troff',
          'man' => 'application/x-troff-man',
          'me' => 'application/x-troff-me',
          'ms' => 'application/x-troff-ms',
          'ustar' => 'application/x-ustar',
          'src' => 'application/x-wais-source',
          'wz' => 'application/x-wingz',
          'crt' => 'application/x-x509-ca-cert',
          'fig' => 'application/x-xfig',
          'au' => 'audio/basic',
          'snd' => 'audio/basic',
          'mid' => 'audio/midi',
          'midi' => 'audio/midi',
          'kar' => 'audio/midi',
          'mpga' => 'audio/mpeg',
          'mpega' => 'audio/mpeg',
          'mp2' => 'audio/mpeg',
          'mp3' => 'audio/mpeg',
          'm3u' => 'audio/x-mpegurl',
          'sid' => 'audio/prs.sid',
          'aif' => 'audio/x-aiff',
          'aiff' => 'audio/x-aiff',
          'aifc' => 'audio/x-aiff',
          'gsm' => 'audio/x-gsm',
          'wma' => 'audio/x-ms-wma',
          'wax' => 'audio/x-ms-wax',
          'ra' => 'audio/x-realaudio',
          'rm' => 'audio/x-pn-realaudio',
          'ram' => 'audio/x-pn-realaudio',
          'pls' => 'audio/x-scpls',
          'sd2' => 'audio/x-sd2',
          'wav' => 'audio/x-wav',
          'pdb' => 'chemical/x-pdb',
          'xyz' => 'chemical/x-xyz',
          'bmp' => 'image/x-ms-bmp',
          'gif' => 'image/gif',
          'ief' => 'image/ief',
          'jpeg' => 'image/jpeg',
          'jpg' => 'image/jpeg',
          'jpe' => 'image/jpeg',
          'pcx' => 'image/pcx',
          'png' => 'image/png',
          'svg' => 'image/svg+xml',
          'svgz' => 'image/svg+xml',
          'tiff' => 'image/tiff',
          'tif' => 'image/tiff',
          'wbmp' => 'image/vnd.wap.wbmp',
          'ras' => 'image/x-cmu-raster',
          'cdr' => 'image/x-coreldraw',
          'pat' => 'image/x-coreldrawpattern',
          'cdt' => 'image/x-coreldrawtemplate',
          'djvu' => 'image/x-djvu',
          'djv' => 'image/x-djvu',
          'ico' => 'image/x-icon',
          'art' => 'image/x-jg',
          'jng' => 'image/x-jng',
          'psd' => 'image/x-photoshop',
          'pnm' => 'image/x-portable-anymap',
          'pbm' => 'image/x-portable-bitmap',
          'pgm' => 'image/x-portable-graymap',
          'ppm' => 'image/x-portable-pixmap',
          'rgb' => 'image/x-rgb',
          'xbm' => 'image/x-xbitmap',
          'xpm' => 'image/x-xpixmap',
          'xwd' => 'image/x-xwindowdump',
          'igs' => 'model/iges',
          'iges' => 'model/iges',
          'msh' => 'model/mesh',
          'mesh' => 'model/mesh',
          'silo' => 'model/mesh',
          'wrl' => 'x-world/x-vrml',
          'vrml' => 'x-world/x-vrml',
          'csv' => 'text/comma-separated-values',
          'css' => 'text/css',
          '323' => 'text/h323',
          'htm' => 'text/html',
          'html' => 'text/html',
          'uls' => 'text/iuls',
          'mml' => 'text/mathml',
          'asc' => 'text/plain',
          'txt' => 'text/plain',
          'text' => 'text/plain',
          'diff' => 'text/plain',
          'rtx' => 'text/richtext',
          'sct' => 'text/scriptlet',
          'wsc' => 'text/scriptlet',
          'tm' => 'text/texmacs',
          'ts' => 'text/texmacs',
          'tsv' => 'text/tab-separated-values',
          'jad' => 'text/vnd.sun.j2me.app-descriptor',
          'wml' => 'text/vnd.wap.wml',
          'wmls' => 'text/vnd.wap.wmlscript',
          'xml' => 'text/xml',
          'xsl' => 'text/xml',
          'h++' => 'text/x-c++hdr',
          'hpp' => 'text/x-c++hdr',
          'hxx' => 'text/x-c++hdr',
          'hh' => 'text/x-c++hdr',
          'c++' => 'text/x-c++src',
          'cpp' => 'text/x-c++src',
          'cxx' => 'text/x-c++src',
          'cc' => 'text/x-c++src',
          'h' => 'text/x-chdr',
          'c' => 'text/x-csrc',
          'java' => 'text/x-java',
          'moc' => 'text/x-moc',
          'p' => 'text/x-pascal',
          'pas' => 'text/x-pascal',
          '***' => 'text/x-pcs-***',
          'shtml' => 'text/x-server-parsed-html',
          'etx' => 'text/x-setext',
          'tk' => 'text/x-tcl',
          'ltx' => 'text/x-tex',
          'sty' => 'text/x-tex',
          'cls' => 'text/x-tex',
          'vcs' => 'text/x-vcalendar',
          'vcf' => 'text/x-vcard',
          'dl' => 'video/dl',
          'fli' => 'video/fli',
          'gl' => 'video/gl',
          'mpeg' => 'video/mpeg',
          'mpg' => 'video/mpeg',
          'mpe' => 'video/mpeg',
          'qt' => 'video/quicktime',
          'mov' => 'video/quicktime',
          'mxu' => 'video/vnd.mpegurl',
          'dif' => 'video/x-dv',
          'dv' => 'video/x-dv',
          'lsf' => 'video/x-la-asf',
          'lsx' => 'video/x-la-asf',
          'mng' => 'video/x-mng',
          'asf' => 'video/x-ms-asf',
          'asx' => 'video/x-ms-asf',
          'wm' => 'video/x-ms-wm',
          'wmv' => 'video/x-ms-wmv',
          'wmx' => 'video/x-ms-wmx',
          'wvx' => 'video/x-ms-wvx',
          'avi' => 'video/x-msvideo',
          'movie' => 'video/x-sgi-movie',
          'ice' => 'x-conference/x-cooltalk',
          'vrm' => 'x-world/x-vrml',
          'rar' => 'application/x-rar-compressed',
          'cab' => 'application/vnd.ms-cab-compressed'
        );
            
            $ext=strtolower($ext);
            if (isset($mimeTypes[$ext])) {
                return $mimeTypes[$ext];
            }

        return 'application/octet-stream';
    }
}
