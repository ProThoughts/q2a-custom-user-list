<?php 

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

// require_once CUL_DIR.'/cul-html-builder.php';

class cul_theme_main
{
    protected $context = array();
    
    public static function main($theme_obj)
    {
      $content = $theme_obj->content;
      $theme_obj->output('<main class="mdl-layout__content">');
      $theme_obj->output('<section>');
      $theme_obj->output('<div class="qa-main'.(@$theme_obj->content['hidden'] ? ' qa-main-hidden' : '').'">');
      if($theme_obj->template !== 'login')
        $theme_obj->output(
          '<div class="mdl-grid mdl-grid--no-spacing">',
          '<div class="mdl-cell mdl-cell--12-col">'
          );
      //横幅を640pxに収める
      if($theme_obj->template !== 'question'&&$theme_obj->template !== 'login'&&$theme_obj->template !== 'register')
        $theme_obj->output('<div class="centering__width-640">');

      $theme_obj->widgets('main', 'top');


      qa_html_builder::user_list();

      $no_page_title_template = array('unanswered', 'user', 'users','login', 'blogs');
      if(!in_array($theme_obj->template, $no_page_title_template)) {
        $theme_obj->page_title_error();
      }

      $theme_obj->widgets('main', 'high');

      if($theme_obj->template !== 'user'){
        $theme_obj->main_parts($content);
      }

      if($theme_obj->template !== 'question' && $theme_obj->template !== 'login') {
        $theme_obj->output('</div><!-- END centering__width-640 -->');
      }

      $theme_obj->widgets('main', 'low');
      $theme_obj->page_links();
      $theme_obj->suggest_next();
      $theme_obj->widgets('main', 'bottom');

      if($theme_obj->template !== 'login') {
        $theme_obj->output(
          '</div><!-- END mdl-cell--12-col -->',
          '</div> <!-- END mdl-grid -->'
          );
      }

      $theme_obj->output('</div> <!-- END qa-main -->', '');
      $theme_obj->output('</section>');
      $theme_obj->output('</div> <!-- END mdl-layout__content -->', '');
    }
    
    private static function output_user_detail($theme_obj) {
        $path = CUD_DIR . '/html/main_user_detail.html';
        $html = file_get_contents($path);
        $buttons = '';
        $params = self::create_params($theme_obj->content);
        $theme_obj->output( strtr($html, $params) );
    }
    
    private static function create_params($content)
    {
        $raw = $content['raw'];
        $points = $raw['points']['points'];
        $points = $points ? number_format($points) : 0;
        $buttons = cud_html_builder::create_buttons($raw['account']['userid'], $raw['account']['handle'], $content['favorite']);
        return array(
            '^site_url' => qa_opt('site_url'),
            '^blobid' => $raw['account']['avatarblobid'],
            '^handle' => $raw['account']['handle'],
            '^location' => $raw['profile']['location'],
            '^groups' => $raw['profile']['飼-育-群-数'],
            '^years' => $raw['profile']['ニホンミツバチ-飼-育-歴'],
            '^hivetype' => $raw['profile']['使-用-している-巣-箱'],
            '^about' => $raw['profile']['about'],
            '^points' => $points,
            '^ranking' => $raw['rank'],
            '^buttons' => $buttons,
        );
    }
    
    private static function output_q_list_tab_header($theme_obj)
    {
        $active_tab = self::set_active_tab($theme_obj);
        $html = cud_html_builder::crate_tab_header($active_tab);
        $theme_obj->output($html);
    }
    
    private static function output_q_list_tab_footer($theme_obj)
    {
        $theme_obj->output('</div>');
    }
    
    private static function output_q_list_panels($theme_obj)
    {
        $active_tab = self::set_active_tab($theme_obj);
        self::output_q_list($theme_obj, 'activities', $active_tab['activities']);
        self::output_q_list($theme_obj, 'questions', $active_tab['questions']);
        self::output_q_list($theme_obj, 'answers', $active_tab['answers']);
        self::output_q_list($theme_obj, 'blogs', $active_tab['blogs']);
    }
    
    private static function output_q_list($theme_obj, $list_type, $active_tab)
    {
        $html = cud_html_builder::create_tab_panel($list_type, $active_tab);
        $theme_obj->output($html);
        if (isset($theme_obj->content['q_list'][$list_type])) {
            $q_items = $theme_obj->content['q_list'][$list_type];
            $theme_obj->q_list_items($q_items);
            if (isset($theme_obj->content['page_links_'.$list_type])) {
                self::page_links($theme_obj, $list_type);
            }
            $html = cud_html_builder::create_spinner();
            $theme_obj->output($html);
        } else {
            $list_name = qa_lang_html('cud_lang/'.$list_type);
            $html = cud_html_builder::create_no_item_list($list_name);
            $theme_obj->output($html);
        }
        
        $theme_obj->output('</div>','</div>');
    }
    
    static function q_item_title($theme_obj, $q_item)
    {
        $search = '/.*>(.*)<.*/';
        $replace = '$1';
        $q_item_title = preg_replace($search, $replace, $q_item['title']);
        self::get_thumbnail($q_item['raw']['postid']) ? $theme_obj->output('<div class="mdl-card__title">') : $theme_obj->output('<div class="mdl-card__title no-thumbnail">');
        $theme_obj->output(
            '<h1 class="mdl-card__title-text qa-q-item-title">',
            '<a href="'.$q_item['url'].'">'.$q_item_title.'</a>',
            // add closed note in title
            empty($q_item['closed']['state']) ? '' : ' ['.$q_item['closed']['state'].']',
            '</h1>',
            '</div>'
        );
        
        $blockwordspreg = qa_get_block_words_preg();
        if (isset($q_item['raw']['content'])) {
            $text = qa_viewer_text($q_item['raw']['content'], 'html', array('blockwordspreg' => $blockwordspreg));
        } else {
            $text = '';
        }
        $q_item_content = mb_strimwidth($text, 0, 150, "...", "utf-8");
        $theme_obj->output('<div class="qa-item-content">');
        $theme_obj->output($q_item_content);
        $theme_obj->output('</div>');
    }
    
    private static function get_thumbnail($postid)
    {
        $post = qa_db_single_select(qa_db_full_post_selectspec(null, $postid));
        $ret = preg_match("/<img(.+?)>/", $post['content'], $matches);
        if ($ret === 1) {
            return $matches[1];
        } else {
            return '';
        }
    }
    
    private static function page_links($theme_obj, $list_type)
    {
        $page_links = @$theme_obj->content['page_links_'.$list_type];
        if (!empty($page_links)) {
            $theme_obj->output('<div class="qa-page-links-'.$list_type.'">');
            $theme_obj->page_links_list(@$page_links['items']);
            $theme_obj->page_links_clear();
            $theme_obj->output('</div>');
        }
    }
    
    private static function set_active_tab($theme_obj)
    {
        $action = isset($theme_obj->content['raw']['action']) ? $theme_obj->content['raw']['action'] : 'activities';
        $active_tab = array(
            'activities' => '',
            'questions' => '',
            'answers' => '',
            'blogs' => ''
        );
        $active_tab[$action] = 'is-active';
        return $active_tab;
    }
}
