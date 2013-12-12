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

- Add new posts in your git repository.
- Commit and push to GitHub.

Done. Your new post will be published in typecho automatically:

- Use your file name as post title.
- Use your file content as post text.
- Under the default category (which you can change it later).

Note: if you want some format, you need to use html tags.
Markdown support may be added in future version.
