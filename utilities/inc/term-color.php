<?php

add_action( 'registered_taxonomy', function( $taxonomy ) {
    if ( !in_array( $taxonomy, apply_filters( 'register-term-color', [] ) ) ) return;

    // enqueue color picker js
    add_action( 'admin_enqueue_scripts', function() use ( $taxonomy ) {
        $screen = get_current_screen();
        if ( !$screen || $screen->id !== "edit-$taxonomy" ) return;

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_add_inline_script( 'wp-color-picker', "jQuery( '.colorpicker' ).wpColorPicker()" );
    } );

    // add color picker to term form
    add_action( "{$taxonomy}_add_form_fields", function( $taxonomy ) { ?>
        <div class="form-field term-color-wrap">
            <label for="term-color"><?php _e( 'Color' ) ?></label>
            <input name="term-color" class="colorpicker" id="term-color" />
        </div>
    <?php } );

    // add color picker to term form
    add_action( "{$taxonomy}_edit_form_fields", function( $term, $taxonomy ) { ?>
        <tr class="form-field term-color-wrap">
            <th scope="row"><label for="term-color"><?php _e( 'Color' ) ?></label></th>
            <td><input name="term-color" class="colorpicker" id="term-color"
                       value="<?php echo esc_attr( get_term_meta( $term->term_id, 'color', true ) ) ?>" /></td>
        </tr>
    <?php }, 10, 2 );

    // save term's color
    add_action( "created_$taxonomy", 'save_term_color', 10, 3 );
    add_action( "edited_$taxonomy", 'save_term_color', 10, 3 );

    // taxonomy table display
    add_filter( "manage_edit-{$taxonomy}_columns", 'term_color_columns' );
    add_filter( "manage_{$taxonomy}_custom_column", 'term_color_column', 10, 3 );

    add_action( 'admin_head', function() use ( $taxonomy ) {
        $screen = get_current_screen();
        if ( !$screen || $screen->id !== "edit-$taxonomy" ) return;

        echo minify_css( "<style>
            .column-color {
                width: 2em;
            }

            .column-color span {
                display: block;
                border-radius: .2em;
                width: 2em;
                aspect-ratio: 1/1;
            }
        </style>" )?>
    <?php } );

} );

function save_term_color( $term_id, $tt_id, $taxonomy  ): void {
    if ( $color = $_POST['term-color'] ?? '' )
        update_term_meta( $term_id, 'color', $color );
    else delete_term_meta( $term_id, 'color' );
}

/**
 * @param array $columns
 *
 * @return array
 */
function term_color_columns( array $columns ): array {
    $new_columns = [
        'cb' => $columns['cb'],
        'color' => '<span class="screen-reader-text">' . __( 'Color' ) . '</span>',
    ];

    unset( $columns['cb'] );

    return array_merge( $new_columns, $columns );
}

/**
 * @param string $output $
 * @param string $column_name
 * @param int $term_id
 *
 * @return string
 */
function term_color_column( string $output, string $column_name, int $term_id ): string {
    if ( $column_name == 'color' ) {
        if ( $color = get_term_meta( $term_id, 'color', true ) ) {
            $output .= "<span style='background-color:$color' title='$color'></span>";
        }
    }

    return $output;
}

// auto load color
add_filter( 'get_term', function( $term ) {
    if ( in_array( $term->taxonomy, apply_filters( 'register-term-color', [] ) ) )
        $term->color = get_term_meta( $term->term_id, 'color', true );

    return $term;
} );
