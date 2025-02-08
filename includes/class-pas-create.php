<?php

class PasCreate {

	public static $instance = null;

    public static function instance() {
        // Initialize the plugin
        if (null === self::$instance) {
            self::$instance = new self();
        } 
        return self::$instance;
    }

    private function __construct() {
        // add_action ( 'forminator_form_after_handle_submit', [$this, 'connect_data_on_submit'], 10, 2 );
        add_action( 'forminator_form_after_save_entry', [$this, 'connect_data_on_submit'], 20, 2 ); 
    }

    public function connect_data_on_submit( $form_id, $response ) { 
        $entry = forminator_get_latest_entry_by_form_id( $form_id );
        $post_id = sanitize_text_field( $entry->meta_data['postdata-1']['value']['postdata'] );
        $cat = sanitize_text_field($entry->meta_data['select-4']['value']); // Izgubljeni / Vidjeni <- literaly - not slug
        

        error_log( print_r( [ 'entry' => $entry, 'post_id' => $post_id], true ), 3, ACF_SEARCHER_PATH . 'log.txt' );
        die;

        if ( ! $post_id ) {
            $post_id = wp_insert_post(array(
                'post_title' => "Vidjen " . date('d.m.Y'),
                'post_type' => 'post',
                'post_status' => 'pending',
            ));
        }
        
        $post_content = get_post_field('post_content', $post_id);
    
        $post_content .= $this->make_columns( 
            $this->make_images( $entry ), 
            $this->make_table( $entry ) 
        );
    
        $post_content .= $this->make_text( $entry, $cat );
    
        $this->update_postmeta_and_thumb( $entry, $post_id );
    
        wp_update_post(array(
            'ID'           => $post_id,
            'post_content' => $post_content,
            'post_category' => array( get_cat_ID( $cat ) ),
        ));
    
    }

    private function make_columns($gallery, $table) {
        return <<<COLUMNS
        <!-- wp:columns --><div class="wp-block-columns"><!-- wp:column {"width":"60%"} --><div class="wp-block-column" style="flex-basis:60%">$gallery</div><!-- /wp:column --><!-- wp:column {"width":"40%"} --><div class="wp-block-column" style="flex-basis:40%"><!-- wp:table {"hasFixedLayout":false} --><figure class="wp-block-table">$table</figure><!-- /wp:table --></div><!-- /wp:column --></div><!-- /wp:columns -->
        COLUMNS;
    }

    private function make_images( $entry ) {
        $img_urls = $entry->meta_data['upload-1']['value']['file']['file_url'];  //OLD

        // global $wpdb;
        // $entry_id = $entry->entry_id;
        // $meta_key = 'upload-1';

        // $query = $wpdb->prepare(
        //     "SELECT meta_value FROM wp_frmt_form_entry_meta WHERE entry_id = %d AND meta_key = %s",
        //     $entry_id,
        //     $meta_key
        // );
        // $meta_value = $wpdb->get_var($query);
        // $img_urls = unserialize($meta_value);
        // $img_urls = $img_urls['file']['file_url'];
    
        $images_block = '';
        if ( ! empty( $img_urls ) ) {
            foreach ($img_urls as $img_url) {
                $attachId = attachment_url_to_postid( $img_url );
                if ( $attachId ) {
                    $images_block .= '<!-- wp:image {"id":' . $attachId . ',"sizeSlug":"full","linkDestination":"media","className":"wp-block-gallery has-nested-images columns-default is-cropped"} --><figure class="wp-block-image size-full wp-block-gallery has-nested-images columns-default is-cropped"><a href="' . $img_url . '"><img src="' . $img_url . '" alt="" class="wp-image-' . $attachId . '"/></a></figure><!-- /wp:image -->';
                }
            }
        }
    
        $gallery_id = '"'. uniqid() . '"';
        return <<<GALLERY
        <!-- wp:gallery {"linkTo":"file","sizeSlug":"full","className":"","style":{"border":{"radius":"0px"},"spacing":{"blockGap":{"left":"0"}}},"masonryGutter":0,"block_id":$gallery_id} --><figure class="wp-block-gallery has-nested-images columns-default is-cropped" style="border-radius:0px">$images_block</figure><!-- /wp:gallery -->
        GALLERY;
    }

    private function make_table($entry) {
        $rows = [
            'Rasa' => sanitize_text_field($entry->meta_data['select-1']['value']),
            'Pol' => sanitize_text_field($entry->meta_data['radio-1']['value']),
            'Veličina' => sanitize_text_field($entry->meta_data['select-2']['value']),
            'Boja' => sanitize_text_field($entry->meta_data['select-3']['value']),
            'Čip' => sanitize_text_field($entry->meta_data['radio-2']['value']) != 'Ne znam' ? sanitize_text_field( $entry->meta_data['radio-2']['value']) : 0,
            'Broj čipa' => sanitize_text_field($entry->meta_data['number-1']['value']),
            'Datum' => sanitize_text_field($entry->meta_data['date-1']['value']),
            'Lokacija' => sanitize_text_field($entry->meta_data['text-1']['value']),
        ];
        
        $table = '<table><tbody>';
        foreach ( $rows as $row_name => $row_value ) {
            if( ! empty( $row_value ) ) {
                $table .= '<tr><td>' . $row_name . '</td><td>' . $row_value . '</td></tr>';
            }
        }
        $table .= '</tbody></table>';
        return  $table;
    }

    private function make_text( $entry, $cat ){
        $content = '';
    
        $rows = [
            'Osobenost' => sanitize_text_field($entry->meta_data['textarea-1']['value']),
            'Ime vlasnika' => $cat=="Izgubljeni" ? sanitize_text_field($entry->meta_data['name-1']['value']) : sanitize_text_field($entry->meta_data['name-2']['value']),
            'Telefon' => sanitize_text_field($entry->meta_data['phone-1']['value']),
            'Email' => sanitize_text_field($entry->meta_data['email-1']['value']),
        ];
    
        $content .= '<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->';
    
        $content .= '<!-- wp:heading {"level":6} --><h6 class="wp-block-heading">opis</h6><!-- /wp:heading -->';
    
        $content .= '<!-- wp:paragraph --><p>' . $rows['Osobenost'] . '</p><!-- /wp:paragraph -->';
    
        $content .= '<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->';
    
        $content .= '<!-- wp:heading {"level":6} --><h6 class="wp-block-heading">'; 
            $cat == "Izgubljeni" ? $content .= 'vlasnik' : $content .= 'pronalazač';
        $content .= '</h6><!-- /wp:heading -->';
    
        $content .= '<!-- wp:paragraph --><p>' . $rows['Ime vlasnika'] . '<br>' . $rows['Telefon'] . '<br>' . $rows['Email']  . '</p><!-- /wp:paragraph -->';
    
        return $content;
    }

    private function update_postmeta_and_thumb( $entry, $post_id ) {
	
        $paragraphs = [
            'Rasa' => $entry->meta_data['select-1']['value'],
            'Pol' => $entry->meta_data['radio-1']['value'],
            'Veličina' => $entry->meta_data['select-2']['value'],
            'Boja' => $entry->meta_data['select-3']['value'],
            'Čip' => $entry->meta_data['radio-2']['value'] != 'Ne znam' ? $entry->meta_data['radio-2']['value'] : 0,
            'Datum' => sanitize_text_field($entry->meta_data['date-1']['value']),
            'Lokacija' => sanitize_text_field($entry->meta_data['text-1']['value']),
            'Email' => sanitize_text_field($entry->meta_data['email-1']['value']),
        ];

        foreach ( $paragraphs as $key => $value ) {
                $slug = strtolower( iconv('UTF-8', 'ASCII//TRANSLIT', $key) );
                update_post_meta($post_id, $slug, $value);
        }

        // We want to store entry_id in postmeta for connection which entry is connected to which post
        update_post_meta($post_id, 'entry_id', $entry->entry_id);
    
        $img_urls = $entry->meta_data['upload-1']['value']['file']['file_url'];
        if ( ! empty( $img_urls ) ) {
            $attachId = attachment_url_to_postid( $img_urls[0] );
            if ( $attachId ) {
                set_post_thumbnail( $post_id, $attachId );
            }
        }
    }

}
