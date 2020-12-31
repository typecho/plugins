## 系统要求
- [CentOS7.0][1]及以后版本(其他Linux系统未验证可用性)
- [宝塔面板][2]
- [PHP7.4][3]及以后版本(PHP8.0未验证可用性)
- Typecho1.1及以后版本
## 插件必要设置
1.打开宝塔面板，点击‘网站’，点击对应网站的‘设置’
2.点击‘伪静态’，左上角选择‘typecho’，点击‘保存’

![伪静态配置][5]

3.进入typecho后台，点击‘设置->永久链接’
4.启用‘地址重写功能’
## 插件安装
1.在GitHub[下载][6]插件
2.将‘sitemap’目录上传到网站服务器‘/usr/plugins’目录下
3.进入Typecho后台启用插件
## 插件使用
**访问http(s)://你的域名/sitemap-maker.xml即可生成XML sitemap**
> Demo:[https://tyblog.com.cn/sitemap-maker.xml][7]
## 支持
在[GitHub][8]提issue


  [1]: https://www.centos.org
  [2]: https://bt.cn
  [3]: https://www.php.net
  [5]: http://image-cdn-tyblog.test.upcdn.net/sitemap-plugin/1.png
  [6]: https://github.com/ty-yqs/Typecho-Sitemap-Plugin
  [7]: https://tyblog.com.cn/sitemap-maker.xml
  [8]: https://github.com/ty-yqs/Typecho-Sitemap-Plugin/issues/new
