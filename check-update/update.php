<?php
    $fs = new WP_Filesystem_Direct(false);

    $config = array(
        'slug' => 'stripe-hosted-gateway/stripe-hosted-gateway.php',
        'zip_name' => 'stripe-hosted-gateway.zip',
        'folder_name' => 'stripe-hosted-gateway',
        'github_url' => 'https://github.com/bhaskar-pandit/stripe-hosted-gateway/archive/refs/heads/main.zip',
        
    );

    $upload_dir = wp_upload_dir();
    $tmp_file = download_url( $config['github_url'] );
    $proper_destination = $upload_dir['basedir'].'/'.$config['zip_name'];
    $fs->move( $tmp_file, $proper_destination );
    $dest_path  = trailingslashit( $upload_dir['basedir'] ) . $config['zip_name'];
    

    $zip = new ZipArchive;
    $res = $zip->open( $dest_path );

    if ($res === TRUE) {

        
        $zip->extractTo( WP_PLUGIN_DIR );
        $mainFolderName = $zip->getNameIndex(0);      
        $mainFolderName = str_replace("/","",$mainFolderName);
        $zip->close();
        
        $fs->move( WP_PLUGIN_DIR.'/'.$mainFolderName, WP_PLUGIN_DIR.'/'.$config['folder_name'], true );
    }

    $activate = activate_plugin( WP_PLUGIN_DIR.'/'.$config['slug'] );

    // Output the update message
    $fail  = __( 'The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'github_plugin_updater' );
    $success = __( 'Plugin reactivated successfully.', 'github_plugin_updater' );
    

    wp_delete_file($dest_path);

    echo is_wp_error( $activate ) ? $fail : $success;

?>