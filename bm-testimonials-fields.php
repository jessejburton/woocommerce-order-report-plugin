<?php

function bm_testimonials_url( $post ) {
    wp_nonce_field( basename(__FILE__), 'bm_testimonial_nonce' );
    $bm_testimonial_stored_meta = get_post_meta( $post->ID );

    if ( isset( $bm_testimonial_stored_meta['bm_testimonial_url'] ) ){
        $url = $bm_testimonial_stored_meta['bm_testimonial_url'][0];
    } else {
        $url = '';
    }

    ?>
        <p>
            <input type="text" name="bm_testimonial_url" id="bm_testimonial_url" style="width: 100%;" value="<?php echo $url; ?>" placeholder="Enter an optional URL for the project / website or company." />          
        </p>
    <?php
}