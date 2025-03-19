<?php

class PasCreate {

    private $featured_image = null;

	public static $instance = null;

    public static function instance() {
        // Initialize the plugin
        if (null === self::$instance) {
            self::$instance = new self();
        } 
        return self::$instance;
    }

    private function __construct() {
        add_action( 'forminator_form_after_save_entry', [$this, 'connect_data_on_submit'], 20, 2 ); 
    }




    /**
     * Connect data to post on form submission
     *
     * @param int $form_id The ID of the form.
     * @param array $response The response from the form submission.
     */
    public function connect_data_on_submit( $form_id, $response ) { 

        if ( $form_id != 1170 ) { 
            return;
        }

        $entry = forminator_get_latest_entry_by_form_id( $form_id );
        $post_id = sanitize_text_field( $entry->meta_data['postdata-1']['value']['postdata'] );
        $cat = sanitize_text_field($entry->meta_data['select-4']['value']); // Izgubljeni / Vidjeni <- literaly - not slug
        
        if ( ! $post_id ) {
            $post_id = wp_insert_post(array(
                'post_title' => "Vidjen " . date('d.m.Y'),
                'post_type' => 'post',
                'post_status' => 'pending',
            ));
        }
        
        $post_content = get_post_field('post_content', $post_id);
    
        $post_content .= $this->make_columns( 
            $this->make_gallery( $entry ), 
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





    /**
     * Create columns with gallery and table
     *
     * @param string $gallery The HTML for the gallery.
     * @param string $table The HTML for the table.
     * @return string The combined HTML for the columns.
     */
    private function make_columns($gallery, $table) {
        return <<<COLUMNS
        <!-- wp:columns -->
        <div class="wp-block-columns">
            <!-- wp:column {"width":"60%"} -->
            <div class="wp-block-column" style="flex-basis:60%">
                $gallery
            </div>
            <!-- /wp:column -->
            <!-- wp:column {"width":"40%"} -->
            <div class="wp-block-column" style="flex-basis:40%">
                <!-- wp:table {"hasFixedLayout":false} -->
                <figure class="wp-block-table">
                    $table
                </figure>
                <!-- /wp:table -->
            </div>
            <!-- /wp:column -->
        </div>
        <!-- /wp:columns -->
        COLUMNS;
    }





    /**
     * Create gallery block
     *
     * @param object $entry The entry object.
     * @return string The HTML for the gallery block.
     */
    private function make_gallery( $entry ) {
        $img_urls = $entry->meta_data['upload-1']['value']['file']['file_url'];  //OLD
    
        // Form images block for gallery
        $images_block = '';
        if ( ! empty( $img_urls ) ) {
            foreach ($img_urls as $img_url) {
                $attachId = $this->ensure_make_attachments( $img_url );
                if ( $attachId ) {
                    $images_block .= $this->generate_image_html( $img_url, $attachId );
                }
            }
        }
    
        $gallery_id = '"'. uniqid() . '"';
        return <<<GALLERY
            <!-- wp:gallery {
                "linkTo": "file",
                "sizeSlug": "full",
                "className": "",
                "style": {
                    "border": {
                        "radius": "0px"
                    },
                    "spacing": {
                        "blockGap": {
                            "left": "0"
                        }
                    }
                },
                "masonryGutter": 0,
                "block_id": $gallery_id
            } -->
            <figure class="wp-block-gallery has-nested-images columns-default is-cropped" style="border-radius:0px">
                $images_block
            </figure>
            <!-- /wp:gallery -->
        GALLERY;
    }




    /**
     * Generate image HTML for gallery
     *
     * @param string $img_url The URL of the image.
     * @param int $attachId The attachment ID of the image.
     * @return string The HTML for the image.
     */
    private function generate_image_html( $img_url, $attachId ) {
        $image_html = '<!-- wp:image {"id":' . $attachId . ',"sizeSlug":"full","linkDestination":"media","className":"wp-block-gallery has-nested-images columns-default is-cropped"} -->';
        $image_html .= '<figure class="wp-block-image size-full wp-block-gallery has-nested-images columns-default is-cropped">';
        $image_html .= '<a href="' . $img_url . '"><img src="' . $img_url . '" alt="" class="wp-image-' . $attachId . '"/></a>';
        $image_html .= '</figure><!-- /wp:image -->';
        return $image_html;
    }




    /**
     * Ensure that the attachment is created and return its ID
     *
     * @param string $img_url The URL of the image.
     * @return int The attachment ID.
     */
    private function ensure_make_attachments( $img_url ) {

        sleep(1);
        $attachId = attachment_url_to_postid($img_url); // try to find attachment by url

        $retries = 0;
        while (empty($attachId) && ($retries < 3) ) {
            sleep(1);
            $attachId = $this->get_attachment_id( $img_url );
            $retries++;
        }

        if ( empty( $this->featured_image ) ) {
            $this->featured_image = $attachId;
        }

        return $attachId;
    }




    /**
     * Create table block
     *
     * @param object $entry The entry object.
     * @return string The HTML for the table block.
     */
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
        
        $table_rows = '';
        foreach ( $rows as $row_name => $row_value ) {
            if( ! empty( $row_value ) ) {
                $table_rows .= "<tr><td>{$row_name}</td><td>{$row_value}</td></tr>";
            }
        }

        return <<<TABLE
        <table>
            <tbody>
                {$table_rows}
            </tbody>
        </table>
        TABLE;
    }




    /**
     * Create text block
     *
     * @param object $entry The entry object.
     * @param string $cat The category of the post.
     * @return string The HTML for the text block.
     */
    private function make_text( $entry, $cat ){
        $content = '';
    
        $rows = [
            'Osobenost' => sanitize_text_field($entry->meta_data['textarea-1']['value']),
            'Ime vlasnika' => $cat=="Izgubljeni" ? sanitize_text_field($entry->meta_data['name-1']['value']) : sanitize_text_field($entry->meta_data['name-2']['value']),
            'Telefon' => sanitize_text_field($entry->meta_data['phone-1']['value']),
            'Email' => sanitize_text_field($entry->meta_data['email-1']['value']),
        ];
    
        $owner_label = $cat == "Izgubljeni" ? 'vlasnik' : 'pronalazač';
        return <<<HTML
        <!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->
        <!-- wp:heading {"level":6} --><h6 class="wp-block-heading">opis</h6><!-- /wp:heading -->
        <!-- wp:paragraph --><p>{$rows['Osobenost']}</p><!-- /wp:paragraph -->
        <!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->
        <!-- wp:heading {"level":6} --><h6 class="wp-block-heading">{$owner_label}</h6><!-- /wp:heading -->
        <!-- wp:paragraph --><p>{$rows['Ime vlasnika']}<br>{$rows['Telefon']}<br>{$rows['Email']}</p><!-- /wp:paragraph -->
        HTML;
    }




    /**
     * Update post meta and set featured image
     *
     * @param object $entry The entry object.
     * @param int $post_id The ID of the post.
     */
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
            $slug = strtolower( iconv('UTF-8', 'ASCII//TRANSLIT', $key) ); // make slug ascii only
            update_post_meta($post_id, $slug, $value);
        }

        // We want to store entry_id in postmeta to know which entry is connected to which post
        update_post_meta($post_id, 'entry_id', $entry->entry_id);
    
        // $img_urls = $entry->meta_data['upload-1']['value']['file']['file_url'];
        if ( ! empty( $this->featured_image ) ) {
            set_post_thumbnail( $post_id, $this->featured_image );
        }
    }




    /**
     * Get an attachment ID given a URL.
     * 
     * @param string $url
     *
     * @return int Attachment ID on success, 0 on failure
     */
    private function get_attachment_id( $url ) {

        $attachment_id = 0;
        $file = basename( $url );
        $query_args = array(
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
            'fields'      => 'ids',
            'meta_query'  => array(
                array(
                    'value'   => $file,
                    'compare' => 'LIKE',
                    'key'     => '_wp_attachment_metadata',
                ),
            )
        );

        $query = new WP_Query( $query_args );

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post_id ) {

                $meta = wp_get_attachment_metadata( $post_id );

                $original_file       = basename( $meta['original_image'] );
                $cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );

                if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
                    $attachment_id = $post_id;
                    break;
                }

            }
        }

        return $attachment_id;
    
    } 

}
