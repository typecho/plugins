Configuration
-------------

### configure security code

Open `Plugin.php` in your editor, find this line:

```php
const github_git = 'curtseyingpiddlesMiguelyeshivahsclarinettists'    ;
```

Change `github_git` value to something else.

You can make up anything you like, but please use a long and hard to guess one.

If the value of `github_git` be guessed by someone else, they can post articles to your blog, if they know how to add GitHub web hooks.

After editing, save `Plugin.php`.

### setup GitHub web hook

In your repository, click `Settings` -> `Service Hooks` -> `WebHook URLs`, add the action url, e.g.

```
http://typecho.example.com/action/curtseyingpiddlesMiguelyeshivahsclarinettists
```

Install
-------

Same as other typecho plugins.

That is:

- Upload the `GitHubGit` diretory to `usr/plugins` of your typecho directory.
- Enable the plugin at your dashboard.

Usage
-----

This plugin is roughly jekyll compatible.
You just need to do as you normally do in jekyll.

- Add a new post in the `_posts` directory of your git repository.
- Commit and push to GitHub.
 
Your new post will be published in typecho automatically.

Post format
-----------

Example:

```yaml
---
title: your blog title
tags: apple orange
categroy: life
---

Write you posts in *markdown*.
```

If you have used jekyll before, you will find this format familiar.

But there are some differencs:

- Only support markdown markup.
- No self defined field.
- No support for `layout`, `published` and `permalink`. (We will use filename as permalink slug.)
- `category` only allows one value, since Typecho only allows one.
- `tags` only support space-separated strings. YAML list is not supported.


Bugs
----

[#9](https://github.com/weakish/plugins/issues/9) `git add` multiple files to repostiory, then push. Only the first file will be published into typecho. 

