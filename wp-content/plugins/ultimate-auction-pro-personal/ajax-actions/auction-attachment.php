<?php $wp_upload_dir = wp_upload_dir(); ?>

<script type="text/javascript">
    jQuery( document ).ready( function ( $ ) {

        $( '.wdm_product_attach_btn' ).click( function () {
            $( '#wdm_product_attach_file' ).trigger( 'click' );
        } );

        $( '#wdm_product_attach_file' ).change( function () {

            var fn = $( this ).val();

            if ( null != fn && fn.length > 0 ) {

                var endIndex = fn.lastIndexOf( "\\" ) + 1;

                if ( endIndex != -1 ) {
                    var fn = fn.substring( endIndex, fn.length );
                }
            }

            //$( '.wdm_product_file_name' ).html( fn );
	    var temp_url = '<?php echo $wp_upload_dir[ 'url' ]; ?>/' + fn;
            $( '#wdm_product_attachment' ).val( temp_url.replace(/\s/g, "-") );

        } );
    } );
</script>
<?php
if ( ! function_exists( 'wp_handle_upload' ) )
	require_once( ABSPATH . 'wp-admin/includes/file.php' );

if ( isset( $_FILES[ 'wdm_product_attach_file' ] ) )
	$uploadedfile = $_FILES[ 'wdm_product_attach_file' ];
        
if ( ! empty( $_FILES[ 'wdm_product_attach_file' ][ 'name' ] ) ) {

	$upload_overrides = array( 'test_form' => false );

	$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

	if ( $movefile ) {

		$wp_filetype = $movefile[ 'type' ];

		$filename = $movefile[ 'file' ];

		$attachment = array(
			'guid'		 => $wp_upload_dir[ 'url' ] . '/' . basename( $filename ),
			'post_mime_type' => $wp_filetype,
			'post_title'	 => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'	 => '',
			'post_status'	 => 'inherit'
		);

		$attach_id	= wp_insert_attachment( $attachment, $filename );
		$file_type_array[]	 = array( "application/x-compressed",
			"application/x-zip-compressed",
			"application/zip",
			"multipart/x-zip",
			"application/pdf",
			"application/msword",
			"application/vnd.openxmlformats-officedocument.wordprocessingml.template",
			"application/vnd.openxmlformats-officedocument.wordprocessingml.document",
			"image/png",
			"image/jpeg",
			"image/pjpeg",
			"image/x-jps",);
		if ( in_array($movefile[ 'type' ], $file_type_array) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );
		}

		$attach_file = wp_get_attachment_url( $attach_id );
		update_post_meta( $post_id, "wdm_product_attachment", $_POST['wdm_product_attachment']);
		
    } else {
	    _e( "Sorry, this file can not be uploaded", "wdm-ultimate-auction" );
    }
}
?>