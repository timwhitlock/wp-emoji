<?php
/**
 * Open Source Emoji TinyMCE plugin window
 */
 
if( ! defined('DOING_AJAX') || ! DOING_AJAX || ! current_user_can('manage_options') ){
    return wp_die( 'Permission denied', array( 'response' => 403 ) );
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Insert Emoji Shortcode</title>
    <link href="<?php echo os_emoji_baseurl('/pub/css/emoji-admin.css')?>" rel="stylesheet" />
</head>
<body class="os-emoji-popup emoji-25">
    
    <div id="os-emoji-wrap" style="display:none;">
    <?php os_emoji_include('table'); ?> 
    </div>
    <script type="text/javascript" src="<?php echo includes_url('/js/tinymce/tiny_mce_popup.js')?>"></script>
    <script>
        // steal parent scripts
        var $       = parent.jQuery,
            tinyMCE = parent.tinyMCE,
            OSEmoji = parent.OSEmoji;
            
        // run Emoji convertion with current theme
        OSEmoji.run( window );            

        // wrapper may now be made visible
        var div = document.getElementById('os-emoji-wrap');
        div.style.display = 'block';
        
        // initialize tinyMCE callback to insert shortcode
        $(div).click( function(event){
            event.preventDefault();
            event.stopPropagation();
            var a = $(event.target).closest('a'),
                t = a.attr('title').replace(/_/g,' ').toLowerCase();
            insertShortcode(t);
            return false;
        } );
        
        // callback to TinyMCE with shortcode insertion        
        function insertShortcode( text ){
            var editor = tinyMCE.activeEditor;
            if ( tinyMCE.isIE ) {
                editor.focus();
                editor.selection.moveToBookmark(tinyMCE.EditorManager.activeEditor.windowManager.bookmark);
            }
            text = ' [emoji '+text+'] ';
            tinyMCE.execInstanceCommand( 'content', 'mceInsertContent', false, text );
            editor.execCommand('mceRepaint');
            tinyMCEPopup.close();
        }
    </script>

</body>
</html>