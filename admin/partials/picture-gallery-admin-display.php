<?php
    global $wpdb;
    $table_name = $wpdb->prefix . "picture_category";

    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    if(isset($_POST['delete_category']))
    {
        $wpdb->delete( $table_name, array( 'id' => $_POST['delete_category'] ), array( '%d' ) );
    }

    //Grabs categories data from the database
    $query_categories = $wpdb->get_results( 'SELECT id, name FROM ' . $table_name);

    if(isset($_POST['category']))
    {   
        $uploaddir = wp_upload_dir();
        $file = $_FILES["fileToUpload"]["name"];
        $uploadfile = $uploaddir['path'] . '/' . basename( $file );

        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        $uploadedfile = $_FILES['fileToUpload'];

        $upload_overrides = array( 'test_form' => false );

        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

        $error_text = 'No upload';

        if ( $movefile && ! isset( $movefile['error'] ) ) {
            $error_text = "File is valid, and was successfully uploaded.\n";
            var_dump( $movefile );
        } else {
            /**
             * Error generated by _wp_handle_upload()
             * @see _wp_handle_upload() in wp-admin/includes/file.php
             */
            $error_text = "There was an error: " . $movefile['error'];
        }



        $filename = basename( $uploadfile );

        $wp_filetype = wp_check_filetype(basename($filename), null );

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit',
            'menu_order' => $_i + 1000
        );
        $attach_id = wp_insert_attachment( $attachment, $uploadfile );

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        update_post_meta( $attach_id, 'Category', $_POST['category'] );
        
        set_post_thumbnail( $attach_id );


        $wpdb->insert( 
            $table_name, 
                array( 
                    'time' => current_time( 'mysql' ), 
                    'name' => strtoupper($_POST['category']),
                ) 
        );
    }
?>

<div class="wrap">
    <form method="post">
        <h3>Select a category you wish to delete</h3>
        <select name="delete_category">
            <?php foreach ( $query_categories as $key=>$category ) : ?>
            <option value='<?= $category->id ?>'><?= $category->name?></option>
            <? endforeach ?>
        </select>
        <input type="submit" value="Delete Category">
    </form>
    <div><?= $error_text; ?></div>
    <h2>Upload a new image</h2>
    <form id="featured_upload" method="post" enctype="multipart/form-data">
        
        <input type="file" name="fileToUpload" id="fileToUpload" />
        <input id="fileName" type="text" />
        <select>
            <?php foreach ( $query_categories as $key=>$category ) : ?>
            <option value='<?= $category->name ?>'><?= $category->name?></option>
            <? endforeach ?>
        </select>
        <input type="text" name="category" />
        <input type="submit" value="Submit">
    </form>
</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
