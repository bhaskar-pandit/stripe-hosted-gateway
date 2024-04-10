<style>
    .card_container {
        background-color: #FFF;
        margin: 20px 0;
        border-radius: 6px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: start;
        justify-content: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }

    .card_container h2 {
        font-size: 18px;
        margin: 0 0 10px;
    }

    .card_container .btn_elmnt {
        background-color: #17a2b8;
        color: #FFF;
        padding: 8px 30px;
        border-radius: 4px;
        cursor: pointer;
        border: none;
        transition: all 0.5s;
    }
    .card_container .btn_elmnt:hover {
        background-color: #16889b;
    }
</style>

<div class="card_container">
    <h2>Welcome Lorem ipsum doler sit amet. demo title goes here</h2>
    <button type="button" class="btn_elmnt" onclick="addUpdateParam()">Download update</button>
</div>
<script>
    function addUpdateParam() {
        var currentUrl = window.location.href;
        var currentUrlObj = new URL(currentUrl);
        // console.log(currentUrl);
        currentUrlObj.searchParams.set('download-plugin', 'yes');
        var newURL = currentUrlObj.href;
        // console.log(newURL);
        window.location.href = newURL;
    }
</script>

<?php
    if($_GET['download-plugin'] == 'yes') {
        echo "hello";
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

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
    }

?>