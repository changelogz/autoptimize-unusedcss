<?php

/**
 * Class UnusedCSS
 */
class UnusedCSS {

    public $ran = false;
    public $url = null;

    /**
     * UnusedCSS constructor. 
     */
    public function __construct()
    {
        // load wp filesystem related files;
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();

        $this->url = UnusedCSS_Utils::get_current_url();

        add_action('init', function () {


            if (!function_exists('autoptimize')) {
                $this->show_notice();
            }


            if(!$this->ran) {
                $this->processCss();
            }


            $this->ran = true;


        });

        

        add_action('autoptimize_setup_done', function () {

           




        });
    }

    public function show_notice()
    {

        add_action('admin_notices', function () {

            echo '<div class="notice notice-error is-dismissible">
                    <p>Autoptimize UnusedCSS Plugin only works when autoptimize is installed and activated</p>
                 </div>';

        });

    }


    public function is_enabled()
    {
        if (is_user_logged_in()) {
            return true;
        }

        return true;
    }

    public function get_unusedCSS($url = null)
    {
        $css = (new UnusedCSS_Api())->get($this->url);

        return $css;
    }


    public function processCss(){

        if (!$this->is_enabled()) {
            return;
        }

        if(is_admin()) {
            return;
        }

        if(wp_doing_ajax()) {
            return;
        }


        if(UnusedCSS_Utils::is_cli()){
            return;
        }


        if ( defined( 'DOING_CRON' ) )
        {
            return;
        }

        if(isset($_GET['doing_unused_fetch'])) {
            return;
        }

        $url = UnusedCSS_Utils::get_current_url();

        $files = $this->get_unusedCSS($url);

        $this->store_files($files);

    }

    protected function store_files($files) {
        global $wp_filesystem;
        $base = $this->get_base_dir();

        foreach( $files as $file) {

            $_file = $base . '/' . $this->get_cache_source_hash() . '/' .basename($file->file);

            if(!$wp_filesystem->exists($_file)) {
                $wp_filesystem->put_contents($_file, $file->css, FS_CHMOD_FILE);
            }

        }
        
    }

    protected function get_base_dir(){
        global $wp_filesystem;
        
        $root = $wp_filesystem->wp_content_dir() . 'cache/autoptimize-uucss';

        if(!$wp_filesystem->exists($root)) {
            $wp_filesystem->mkdir($root);
        }

        return $root;
    }

    protected function get_cache_source_hash()
    {
        global $wp_filesystem;
        $base = $this->get_base_dir();

        $hash = $this->base64url_encode($this->url);

        if(!$wp_filesystem->exists($base . '/' . $hash)) {
            $wp_filesystem->mkdir($base . '/' . $hash);
        }

        return $hash;
    }


    function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }


}