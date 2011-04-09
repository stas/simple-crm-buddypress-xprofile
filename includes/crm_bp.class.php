<?php

/**
 * Class adds BuddyPress export/import logic to SCRM
 */
class SCRM_BP {
    /**
     * Static constructor
     */
    function init() {
        // Do this if we have WordPress with SCRM
        if( !defined( 'BP_VERSION' ) ) {
            add_action( 'scrm_options_screen_updated', array( __CLASS__, 'screen_update' ) );
            add_action( 'scrm_options_screen', array( __CLASS__, 'screen' ), 11 );
        } else {
        // Do this if only BuddyPress is installed
            add_action( 'bp_core_admin_screen', array( __CLASS__, 'screen_update' ) );
            add_action( 'bp_core_admin_screen', array(__CLASS__, 'screen' ) );
        }
    }
    
    /**
     * Options screen
     */
    function screen() {
        $filename = self::get_filename();
        $export_file = WP_CONTENT_DIR . '/' . $filename;
        
        if( file_exists( $export_file ) )
            $vars['export_file'] = content_url( $filename );
        else
            $vars['export_file'] = false;
        
        $vars['path'] = SCRM_BPU_ROOT . '/includes/templates/';
        $vars['bp_installed'] = defined( 'BP_VERSION' );
        self::template_render( 'bp_options', $vars );
    }
    
    /**
     * Update options
     */
    function screen_update() {
        if( isset( $_POST['scrm_bp_nonce'] ) && wp_verify_nonce( $_POST['scrm_bp_nonce'], 'scrm_bp' ) )
            if( isset( $_FILES['scrm_bp_import_filename'] ) && !empty( $_FILES['scrm_bp_import_filename']['name'] ) )
                self::import( $_FILES['scrm_bp_import_filename'] );
            elseif( isset( $_POST['scrm_bp_export'] ) )
                self::export();
            elseif( isset( $_POST['scrm_bp_delete'] ) )
                self::delete();
    }
    
    /**
     * Just a helper to make it easier to change the export filename
     */
    function get_filename() {
        $filename = apply_filters( 'scrm_bp_export_filename', 'scrm_buddypress_exported.json' );
        return $filename;
    }
    
    /**
     * Imports users with their xprofile fields data
     */
    function import( $import_file ) {
        $core_fields = array(
            'aim',
            'yim',
            'nickname',
            'first_name',
            'last_name',
            'jabber',
            'description'
        );
        $file_data = file_get_contents( $import_file['tmp_name'] );
        $data = json_decode( $file_data );
        
        if( !json_last_error() && is_object( $data ) )
            foreach( $data as $u ) {
                $user = get_userdatabylogin( $u->id );
                if( $user )
                    foreach( $u->fields as $f ) {
                        if( !empty( $f->value ) )
                            $is_field = SCRM::get_field( $f->name );
                            if( !empty( $is_field['name'] ) || in_array( $f->name, $core_fields ) )
                                update_user_meta( $user->ID, sanitize_key( $f->name ), sanitize_text_field( $f->value ) );
                            if( $f->name == 'user_url' )
                                wp_update_user( array ('ID' => $u->ID, 'user_url' => $f->value) );
                    }
            }
        
        unlink( $import_file['tmp_name'] );
    }
    
    /**
     * It will delete the exported file
     */
    function delete() {
        $path = WP_CONTENT_DIR . '/' . self::get_filename();
        unlink( $path );
    }
    
    /**
     * Exports users with their xprofile fields data
     */
    function export() {
        $path = WP_CONTENT_DIR . '/' . self::get_filename();
        $users = self::get_users();
        $exported_users = array();
        $xprofile_args = array(
            'fetch_fields' => true,
            'fetch_field_data' => true
        );
    
        foreach( $users as $u ) {
            $xprofile_args['user_id'] = $u->ID;
            $xprofiledata = BP_XProfile_Group::get( $xprofile_args );
            $exported_users[$u->user_login] = array();
            $fields = array();
            foreach( $xprofiledata as $xd ) {
                foreach( $xd->fields as $f )
                    $fields[] = array(
                        'name'  => $f->name,
                        'title' => $f->name,
                        'value' => $f->data->value
                    );
            }
            $exported_users[$u->user_login]['id'] = $u->user_login;
            $exported_users[$u->user_login]['fields'] = $fields;
        }
        
        file_put_contents( $path, json_encode( $exported_users ), FILE_APPEND | LOCK_EX );
    }
    
    /**
     * get_users() wrapper for older WordPress
     */
    function get_users() {
        if( function_exists( 'get_users' ) )
            return get_users();
        else {
            $users_search = new WP_User_Search( null, null, 'subscriber' );
            $users = $users_search->get_results();
            $data = array();
            foreach ( $users as $u )
                $data[] = get_userdata( $u );
            return $data;
        }
    }
    
    /**
     * template_render( $name, $vars = null, $echo = true )
     *
     * Helper to load and render templates easily
     * @param String $name, the name of the template
     * @param Mixed $vars, some variables you want to pass to the template
     * @param Boolean $echo, to echo the results or return as data
     * @return String $data, the resulted data if $echo is `false`
     */
    function template_render( $name, $vars = null, $echo = true ) {
        ob_start();
        if( !empty( $vars ) )
            extract( $vars );
        
        if( !isset( $path ) )
            $path = dirname( __FILE__ ) . '/templates/';
        
        include $path . $name . '.php';
        
        $data = ob_get_clean();
        
        if( $echo )
            echo $data;
        else
            return $data;
    }
}
?>
