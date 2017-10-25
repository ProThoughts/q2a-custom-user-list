<?php

require_once CUL_DIR.'/cul-theme-main.php';

class qa_html_theme_layer extends qa_html_theme_base
{
    
    function __construct($template, $content, $rooturl, $request)
    {
        qa_html_theme_base::qa_html_theme_base($template, $content, $rooturl, $request);
    }
    
    function body_footer()
    {
        qa_html_theme_base::body_footer();
        if (qa_opt('site_theme') === CUL_TARGET_THEME_NAME && $this->template === 'users') {
            $cul_lang_json = json_encode (array(
              'read_next' => qa_lang_html('cul_lang/read_next'),
              'read_previous' => qa_lang_html('cul_lang/read_previous'),
            ));
            $this->output(
              '<SCRIPT TYPE="text/javascript">',
              'var action = "'.$action.'";',
              "var cul_lang = '".$cul_lang_json."';",
              '</SCRIPT>'
            );
            
            $cond_location = qa_get('location');
            $this->output('<SCRIPT>',' var cond_location = "'.$cond_location.'";','</SCRIPT>');
            $this->output('<SCRIPT TYPE="text/javascript" SRC="'. QA_HTML_THEME_LAYER_URLTOROOT.'js/custom-user-list.js"></SCRIPT>');
        }
    }

    public function main()
    {
        if (qa_opt('site_theme') === CUL_TARGET_THEME_NAME && $this->template === 'users') {
            cul_theme_main::main($this);
        } else {
            qa_html_theme_base::main();
        }
    }
    
}
