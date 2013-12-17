<?php

require_once "Spyc.php";

class GitHubGit_Action extends Widget_Abstract_Contents implements Widget_Interface_Do
{

    public function action()
    {

      $get_payload = function () {
      
      /** get added files

          GitHub webhooks
          https://help.github.com/articles/post-receive-hooks        
      **/

        $payload = ltrim(urldecode(file_get_contents('php://input')), 'payload='); 
        $data = json_decode($payload, true); //without `true`, json_decode will return object instead of array
        return $data;
      };

      $data = $get_payload();


      $get_added_files = function ($data) {
      
        $commits = $data['commits'];
        foreach ($commits as $commit) {
          foreach ($commit['added'] as $added_file) {
            if (preg_match('#^_posts/#', $added_file)) {
              $added_files[] = $added_file;
            }
          }
        }

        return $added_files;
      };

      $added_files = $get_added_files($data);


      $get_repository_url = function ($data) {
        return $repository_url = $data['repository']['url'];
      };

      $repository_prefix = preg_replace('#https://#', 'https://raw.', $get_repository_url($data));


      $login = function () {
            $master = $this->db->fetchRow($this->db->select()->from('table.users')
                ->where('group = ?', 'administrator')
                ->order('uid', Typecho_Db::SORT_ASC)
                ->limit(1));
            
            if (empty($master)) {
                return false;
            } else if (!$this->user->simpleLogin($master['uid'])) {
                return false;
            }
      
      };

      
      $prepare_post = function ($to_post_file) use ($login, $repository_prefix) {
            $input = array(
                'do'            =>  'publish',
                'allowComment'  =>  $this->options->defaultAllowComment,
                'allowPing'     =>  $this->options->defaultAllowPing,
                'allowFeed'     =>  $this->options->defaultAllowFeed
            );

            list($slug) = explode('.', basename($to_post_file));
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
            
            $url = $repository_prefix . '/master/' . $to_post_file;
            $post_text = file_get_contents($url);
            $post_text_array = Spyc::YAMLLoad($post_text);
            
            $post_sections = preg_split('/^---$/m', $post_text, 2, PREG_SPLIT_NO_EMPTY);
            if (sizeof($post_sections) == 2) {
              $post_body = $post_sections[1];
            } else {
              $post_body = $post_sections[0];
            }

            $input['title'] = $post_text_array['title'] ?: pathinfo($to_post_file)['filename'];
            $input['category']  = $post_text_array['category'] ?: 'default';
            $input['tags'] = implode(',', explode(' ', $post_text_array['tags'])) ?: '';
            $input['text'] = MarkdownExtraExtended::defaultTransform($post_body);

            return $input;
      };

      $post_to_typecho = function ($input) {
            if ($input) {
              // It seems that only the first added file get published.
              $this->widget('Widget_Contents_Post_Edit', NULL, $input, false)->action();
            }
      };
      

      if (isset($added_files) && is_array($added_files)) {
        $login();
        foreach ($added_files as $to_post_file) {
          $post_to_typecho($prepare_post($to_post_file));  
        }
      }   
    }
}
