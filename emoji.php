<?php
/*
Plugin Name: Open Source Emoji
Plugin URI: http://wordpress.org/extend/plugins/open-source-emoji/
Description: Open Source Emoji - add Android and Phantom Emoji to your blog for all devices.
Author: Tim Whitlock
Version: 1.0.6
Author URI: http://timwhitlock.info/
*/



define( 'OS_EMOJI_VERSION', '1.0.6' );



/**
 * Get plugin local base directory in case __DIR__ isn't available (php<5.3)
 */
function os_emoji_basedir( $path = '' ){
    static $dir;
    if( ! isset($dir) ){
        $dir = dirname(__FILE__);
        if( 0 !== strpos( $dir, WP_PLUGIN_DIR ) ){
            // something along this path has been symlinked into the document path.
            // this fix assumes we've not been linked into a sub-directory of plugins:
            foreach( array('open-source-emoji', 'wp-emoji', basename($dir) ) as $name ){
                $dir = WP_PLUGIN_DIR.'/'.$name;
                if( is_dir($dir) ){
                    break;
                }
            }
        }
    }
    return $dir.$path;    
}    



/**
 * Get plugin base URL path.
 */
function os_emoji_baseurl( $path = '' ){
    static $url;
    if( ! isset($url) ){
        $url = plugins_url( '', os_emoji_basedir('/emoji.php') );
    }
    return $url.$path;
}



/** 
 * Include a component from the lib directory.
 * @param string $component e.g. "admin"
 * @return mixed value from last included file
 */
function os_emoji_include(){
    $dir = os_emoji_basedir();
    $ret = '';
    foreach( func_get_args() as $component ){
        $ret = include_once $dir.'/lib/emoji-'.$component.'.php';
    }
    return $ret;
} 



/**
 * Get config options from DB
 * @param array any new options to update
 */
function _os_emoji_config( array $update = array() ){
    static $conf;
    if( ! isset($conf) ){
        $conf = array (
            'theme' => 'android',
        );
        foreach( $conf as $key => $val ){
            $conf[$key] = get_option('os_emoji_'.$key) or
            $conf[$key] = $val;
        }
    }
    foreach( $update as $key => $val ){
        if( isset($conf[$key]) ){
            update_option( 'os_emoji_'.$key, $val );
            $conf[$key] = $val;
        }
    }
    return $conf;
}



/**
 * Convert emoji name to utf-8 character sequence
 */
function os_emoji_string( $name ){
    static $map;
    if( ! isset($map) ){
        $map = os_emoji_include('named');
    }
    $key = strtr( strtoupper($name),'_',' ');
    if( ! isset($map[$key]) ){
        // get closest match
        $grep = '/^'.preg_replace('/[^A-Z]+/','[^A-Z]+', preg_quote($key,'/') ).'/';
        foreach( preg_grep( $grep, array_keys($map) ) as $key ){
            return $map[$key];
        }
        return '';
    }
    return $map[$key];
}



/**
 * Convert [emoji ..] short code into HTML
 */
function os_emoji_shortcode( $atts ){
    $names = implode( ' ', $atts );
    if( $content = os_emoji_string( $names ) ){
        static $hooked;
        if( ! isset($hooked) ){
            $hooked = os_emoji_enqueue_scripts();
        }
    }
    else {
        $content = '<!-- invalid [emoji '.$names.'] -->';
    }
    return $content;
}

add_shortcode( 'emoji', 'os_emoji_shortcode' );




/**
 * Add JavaScript API to current page footer
 * @return string current emoji theme
 */
function os_emoji_enqueue_scripts(){
    static $hook, $vers, $script;
    if( isset($hook) ){
        wp_dequeue_script($hook); // <- permits theme change
    }
    else {
        $hook = 'os-emoji';
        $vers = WP_DEBUG ? time() : OS_EMOJI_VERSION;
        $script = os_emoji_baseurl('/pub/js/emoji.'.( WP_DEBUG ? 'min.js' : 'js' ) );
    }
    extract( _os_emoji_config() );
    $theme = apply_filters( 'emoji_theme', $theme );
    wp_enqueue_script( $hook, $script.'?theme='.$theme, array(), $vers, true );
    return $theme;
}




/**
 * Inialize admin screen
 */
if( is_admin() ){
    os_emoji_include('admin');
}
