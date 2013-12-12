<?php
class GitHubGit_Action extends Widget_Abstract_Contents implements Widget_Interface_Do
{

    public function action()
    {
        /** get added files */

//         GitHub webhooks
//         https://help.github.com/articles/post-receive-hooks        
        $payload = ltrim(urldecode(file_get_contents('php://input')), 'payload='); 
        $data = json_decode($payload, true); //without `true`, json_decode will return object instead of array
        $repository_url = $data['repository']['url'];
        $commits = $data['commits'];
        foreach ($commits as $commit) {
          foreach ($commit['added'] as $added_file) {
            $added_files[] = $added_file;
          }
        }


        /** login */
        $master = $this->db->fetchRow($this->db->select()->from('table.users')
            ->where('group = ?', 'administrator')
            ->order('uid', Typecho_Db::SORT_ASC)
            ->limit(1));
        
        if (empty($master)) {
            return false;
        } else if (!$this->user->simpleLogin($master['uid'])) {
            return false;
        }

        /** add article */ 
        if (isset($added_files) && is_array($added_files)) {
          foreach ($added_files as $added_file) {

            $input = array(
                'do'            =>  'publish',
                'allowComment'  =>  $this->options->defaultAllowComment,
                'allowPing'     =>  $this->options->defaultAllowPing,
                'allowFeed'     =>  $this->options->defaultAllowFeed
            );

            list($slug) = explode('.', basename($added_file));
            $input['slug'] = $slug;

            $post = $this->db->fetchRow($this->db->select()
            ->from('table.contents')->where('slug = ?', $slug)->limit(1));
            if (!empty($post)) {
                if ('post' != $post['type']) {
                    return false;
                } else {
                    $input['cid'] = $post['cid'];
                }
            }

            $input['category'] = 'default';
            $input['title'] = pathinfo($added_file)['filename'];
            $url = preg_replace('#https://#', 'https://raw.', $repository_url) . '/master/' . $added_file;
            $input['text'] = file_get_contents($url);
            if ($input) {
              $this->widget('Widget_Contents_Post_Edit', NULL, $input, false)->action();
            }

          }
        }   
    }
}
<?php
class GitHubGit_Action extends Widget_Abstract_Contents implements Widget_Interface_Do
{

    public function action()
    {
        /** get added files */

//         GitHub webhooks
//         https://help.github.com/articles/post-receive-hooks        
        $payload = ltrim(urldecode(file_get_contents('php://input')), 'payload='); 
        $data = json_decode($payload, true); //without `true`, json_decode will return object instead of array
        $repository_url = $data['repository']['url'];
        $commits = $data['commits'];
        foreach ($commits as $commit) {
          foreach ($commit['added'] as $added_file) {
            $added_files[] = $added_file;
          }
        }


        /** login */
        $master = $this->db->fetchRow($this->db->select()->from('table.users')
            ->where('group = ?', 'administrator')
            ->order('uid', Typecho_Db::SORT_ASC)
            ->limit(1));
        
        if (empty($master)) {
            return false;
        } else if (!$this->user->simpleLogin($master['uid'])) {
            return false;
        }

        /** add article */ 
        if (isset($added_files) && is_array($added_files)) {
          foreach ($added_files as $added_file) {

            $input = array(
                'do'            =>  'publish',
                'allowComment'  =>  $this->options->defaultAllowComment,
                'allowPing'     =>  $this->options->defaultAllowPing,
                'allowFeed'     =>  $this->options->defaultAllowFeed
            );

            list($slug) = explode('.', basename($added_file));
            $input['slug'] = $slug;

            $post = $this->db->fetchRow($this->db->select()
            ->from('table.contents')->where('slug = ?', $slug)->limit(1));
            if (!empty($post)) {
                if ('post' != $post['type']) {
                    return false;
                } else {
                    $input['cid'] = $post['cid'];
                }
            }

            $input['category'] = 'default';
            $input['title'] = pathinfo($added_file)['filename'];
            $url = preg_replace('#https://#', 'https://raw.', $repository_url) . '/master/' . $added_file;
            $input['text'] = file_get_contents($url);
            if ($input) {
              $this->widget('Widget_Contents_Post_Edit', NULL, $input, false)->action();
            }

          }
        }   
    }
}
