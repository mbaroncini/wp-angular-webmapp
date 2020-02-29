<?php
// This file enqueues the apps.
defined( 'ABSPATH' ) or die( 'Direct script access disallowed.' );

class WpAngularAppLoader {

    protected $appsDir = [];
    protected $appsJs = [];
    protected $apps = [];

    protected $allowedJs = [
        'polyfills',
        'runtime',
        'main',
        'styles',
        'vendor'
    ];

    function __construct()
    {
        $this->init();
    }

    protected function init() {
        $this->loadFiles();
        //add_action( 'init' , array( $this , 'registerScripts') , 10 );
        add_action( 'init' , array( $this , 'registerShortcodes') , 10 );
        add_action( 'wp_ajax_getAngularApp' , array( $this , 'ajax_handler') , 10 );
        
    }

    protected function loadFiles(){
        $this->appsDir = glob( WpAngular_APPS_PATH . "/*/dist/");
        if ( $this->appsDir !== FALSE )
            foreach ( $this->appsDir as $appDir )
            {
                $appName = basename( dirname($appDir) );

                $appJss = glob( $appDir . '*.js');
                if ( $appJss !== FALSE )
                {
                    $this->appsJs[$appName] = [];
                    foreach ( $appJss as $js )
                    {
                        $allowed = false; $i = 0;
                        
                
                        while( $i < count($this->allowedJs) && ! $allowed )
                        {
                            if ( strrpos( $js , $this->allowedJs[$i] ) !== FALSE )
                            {
                                $allowed = true;
                                $key = $this->getJsHandler( $appName , $js );
                                $this->appsJs[$appName][$key] = plugin_dir_url($js) . basename( $js );
                            }
                            $i++;
                        } 
                        
                           
                    }
                    
                }
                    
                $this->apps[$appName] = array(
                    'path' => $appDir,
                    'jss'   =>  $this->appsJs[$appName],
                    'url' => plugin_dir_url($appDir) . 'dist/'
                );
            }
    }

    public function ajax_handler(){
        $appName = $_POST['name'];
        $conf = $_POST['conf'];
        echo do_shortcode( "[$appName conf='$conf']" );
        die();
    }

    public function registerScripts()
    {
        foreach( $this->appsJs as $appName => $jss )
        {
            foreach ( $jss as $key => $js )
                wp_register_script( $key , $js , array() , false, true );
        }
            
            
    }

    public function registerShortcodes()
    {
        foreach($this->apps as $name => $details )
        {
            add_shortcode( $name , function( $atts ) use ($name){
                $jss = $this->appsJs[$name];
                //$i = 0;
                foreach ( $jss as $handle => $js )
                {
                    /**
                    if ( $i == 0 )
                        wp_localize_script($handle,str_replace('-','',$name),
                        apply_filters( "wp-angular_shortcode_atts_$name" , (array) $atts )
                    ); */
                    //wp_enqueue_script($handle);
                    //$i++;
                }
                   
                $conf = $atts['conf'];
                $confArr = WebMapp_getWizardConfiguration($conf);
                $confJson = json_encode( array( 'conf' => json_encode( $confArr ) ) );
                ob_start();
                ?>
                <script type="text/javascript">
                (function($){
                    const $head = $('head');
                    const $base = $head.find('base');
                    const appUrl = "<?= $this->apps[$name]['url']?>";
                    $head.append($('<base href="">'));

                })(jQuery)   
                </script>
                <app-root data-conf='<?php echo $confJson?>' data-appName="<?php echo $name ?>"></app-root>
                <?php
                $tmp = $this->apps[$name]['jss'];
                foreach ( array_reverse($tmp) as $js )
                {
                    $filename = basename($js);
                    $module = 'nomodule';
                    if ( strrpos($filename, 'es2015') )
                    {
                        $module = 'type="module"';
                    }
                    echo "<script src=\"$js\" $module></script>";
                }
    
                return ob_get_clean();
            } );
        }

    }

    protected function getJsHandler( $appName , $jsFile )
    {
        $pathInfo = pathinfo($jsFile);
        return WpAngular_SCRIPTS_HANDLER_KEY . '-' . $appName . '-' . $pathInfo['filename'];
    }
    

    public function getApps(){
        return $this->apps;
    }

    public function getAppsJs(){
        return $this->appsJs;
    }
}

