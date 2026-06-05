<?php

if ( ! function_exists( 'vdgallery_get_aspect_ratio_options' ) ) {
    function vdgallery_get_aspect_ratio_options() {
        return array(
            'none' => 'Tidak Aktif',
            '1/1'  => '1:1',
            '4/3'  => '4:3',
            '3/2'  => '3:2',
            '16/9' => '16:9',
        );
    }
}

if ( ! function_exists( 'vdgallery_get_gallery_option_fields' ) ) {
    function vdgallery_get_gallery_option_fields() {
        return array(
            'size' => array(
                'label'   => 'Ukuran gambar',
                'type'    => 'select',
                'default' => 'large',
                'options' => array(
                    'thumbnail' => 'Thumbnail',
                    'medium'    => 'Medium',
                    'large'     => 'Large',
                    'full'      => 'Full',
                ),
            ),
            'kolom' => array(
                'label'   => 'Baris tampil',
                'type'    => 'number',
                'default' => 3,
                'min'     => 1,
            ),
            'kolomresponsif' => array(
                'label'   => 'Baris tampil responsif',
                'type'    => 'number',
                'default' => 2,
                'min'     => 1,
                'max'     => 2,
            ),
            'galericaption' => array(
                'label'   => 'Tampilkan Caption',
                'type'    => 'select',
                'default' => 'below',
                'options' => array(
                    'tidak'  => 'Tidak',
                    'hover'  => 'Saat Hover',
                    'below'  => 'Di Bawah',
                    'inside' => 'Di Dalam',
                ),
            ),
            'aspectratio' => array(
                'label'   => 'Aspect Ratio',
                'type'    => 'select',
                'default' => '1/1',
                'options' => vdgallery_get_aspect_ratio_options(),
            ),
            'pagination' => array(
                'label'   => 'Pagination',
                'type'    => 'select',
                'default' => 1,
                'options' => array(
                    1 => 'Ya',
                    0 => 'Tidak',
                ),
            ),
            'paginationitem' => array(
                'label'   => 'Jumlah gambar per Pagination',
                'type'    => 'number',
                'default' => 6,
                'min'     => 1,
            ),
        );
    }
}

if ( ! function_exists( 'vdgallery_get_slideshow_option_fields' ) ) {
    function vdgallery_get_slideshow_option_fields() {
        return array(
            'slidesize' => array(
                'label'   => 'Ukuran gambar',
                'type'    => 'select',
                'default' => 'full',
                'options' => array(
                    'full'      => 'Full',
                    'medium'    => 'Medium',
                    'large'     => 'Large',
                    'thumbnail' => 'Thumbnail',
                ),
            ),
            'perslide' => array(
                'label'   => 'Tampil per Slide',
                'type'    => 'number',
                'default' => 1,
                'min'     => 1,
                'max'     => 50,
            ),
            'persliderespon' => array(
                'label'   => 'Tampil per Slide responsif',
                'type'    => 'number',
                'default' => 1,
                'min'     => 1,
                'max'     => 3,
            ),
            'slideaspectratio' => array(
                'label'   => 'Aspect Ratio',
                'type'    => 'select',
                'default' => '4/3',
                'options' => vdgallery_get_aspect_ratio_options(),
            ),
            'slidecaption' => array(
                'label'   => 'Tampilkan Caption',
                'type'    => 'select',
                'default' => 'tidak',
                'options' => array(
                    'tidak'  => 'Tidak',
                    'hover'  => 'Saat Hover',
                    'below'  => 'Di Bawah',
                    'inside' => 'Di Dalam',
                ),
                'legacy_values' => array(
                    0 => 'tidak',
                    1 => 'inside',
                ),
            ),
            'navbtn' => array(
                'label'   => 'Navigasi',
                'type'    => 'select',
                'default' => 1,
                'options' => array(
                    1 => 'Ya',
                    0 => 'Tidak',
                ),
            ),
            'navdots' => array(
                'label'   => 'Dots',
                'type'    => 'select',
                'default' => 1,
                'options' => array(
                    1 => 'Ya',
                    0 => 'Tidak',
                ),
            ),
            'autoplay' => array(
                'label'   => 'Autoplay',
                'type'    => 'select',
                'default' => 1,
                'options' => array(
                    1 => 'Ya',
                    0 => 'Tidak',
                ),
            ),
            'infinite' => array(
                'label'   => 'Infinite',
                'type'    => 'select',
                'default' => 1,
                'options' => array(
                    1 => 'Ya',
                    0 => 'Tidak',
                ),
            ),
        );
    }
}

if ( ! function_exists( 'vdgallery_get_option_defaults' ) ) {
    function vdgallery_get_option_defaults( $fields ) {
        $defaults = array();

        foreach ( $fields as $key => $field ) {
            $defaults[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
        }

        return $defaults;
    }
}

if ( ! function_exists( 'vdgallery_get_all_option_defaults' ) ) {
    function vdgallery_get_all_option_defaults() {
        return array_merge(
            vdgallery_get_option_defaults( vdgallery_get_gallery_option_fields() ),
            vdgallery_get_option_defaults( vdgallery_get_slideshow_option_fields() )
        );
    }
}

if ( ! function_exists( 'vdgallery_get_global_options' ) ) {
    function vdgallery_get_global_options() {
        $options = get_option( 'vdgallery_global_options', array() );
        $options = is_array( $options ) ? $options : array();

        return array(
            'gallery'   => vdgallery_sanitize_options(
                isset( $options['gallery'] ) && is_array( $options['gallery'] ) ? $options['gallery'] : array(),
                vdgallery_get_gallery_option_fields()
            ),
            'slideshow' => vdgallery_sanitize_options(
                isset( $options['slideshow'] ) && is_array( $options['slideshow'] ) ? $options['slideshow'] : array(),
                vdgallery_get_slideshow_option_fields()
            ),
        );
    }
}

if ( ! function_exists( 'vdgallery_sanitize_option_value' ) ) {
    function vdgallery_sanitize_option_value( $value, $field ) {
        $type = isset( $field['type'] ) ? $field['type'] : 'text';

        if ( 'number' === $type ) {
            $value = absint( $value );

            if ( isset( $field['min'] ) ) {
                $value = max( (int) $field['min'], $value );
            }

            if ( isset( $field['max'] ) ) {
                $value = min( (int) $field['max'], $value );
            }

            return $value;
        }

        if ( 'select' === $type ) {
            $value   = (string) $value;
            $options = isset( $field['options'] ) ? $field['options'] : array();

            if ( isset( $field['legacy_values'] ) && is_array( $field['legacy_values'] ) ) {
                foreach ( $field['legacy_values'] as $legacy_value => $mapped_value ) {
                    if ( (string) $legacy_value === $value ) {
                        return $mapped_value;
                    }
                }
            }

            foreach ( $options as $option_value => $option_label ) {
                if ( (string) $option_value === $value ) {
                    return $option_value;
                }
            }

            return isset( $field['default'] ) ? $field['default'] : '';
        }

        return sanitize_text_field( $value );
    }
}

if ( ! function_exists( 'vdgallery_sanitize_options' ) ) {
    function vdgallery_sanitize_options( $values, $fields ) {
        $values  = is_array( $values ) ? $values : array();
        $options = array();

        foreach ( $fields as $key => $field ) {
            $value           = array_key_exists( $key, $values ) ? $values[ $key ] : ( isset( $field['default'] ) ? $field['default'] : '' );
            $options[ $key ] = vdgallery_sanitize_option_value( $value, $field );
        }

        return $options;
    }
}

if ( ! function_exists( 'vdgallery_normalize_options' ) ) {
    function vdgallery_normalize_options( $values, $fields ) {
        return wp_parse_args( is_array( $values ) ? $values : array(), vdgallery_get_option_defaults( $fields ) );
    }
}

if ( ! function_exists( 'vdgallery_render_option_fields' ) ) {
    function vdgallery_render_option_fields( $fields, $values, $name_prefix = 'vdgaleri-post[option]' ) {
        $values = vdgallery_normalize_options( $values, $fields );

        foreach ( $fields as $key => $field ) :
            $type  = isset( $field['type'] ) ? $field['type'] : 'text';
            $value = isset( $values[ $key ] ) ? $values[ $key ] : ( isset( $field['default'] ) ? $field['default'] : '' );
            $value = vdgallery_sanitize_option_value( $value, $field );
            $field_id = sanitize_key( str_replace( array( '[', ']' ), '_', $name_prefix . '_' . $key ) );
            ?>
            <tr>
                <th scope="row">
                    <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                </th>
                <td>
                    <?php if ( 'select' === $type ) : ?>
                        <select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name_prefix . '[' . $key . ']' ); ?>" class="regular-text">
                            <?php foreach ( $field['options'] as $option_value => $option_label ) : ?>
                                <option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ( 'number' === $type ) : ?>
                        <input id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name_prefix . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" type="number" <?php echo isset( $field['min'] ) ? 'min="' . esc_attr( $field['min'] ) . '"' : ''; ?> <?php echo isset( $field['max'] ) ? 'max="' . esc_attr( $field['max'] ) . '"' : ''; ?> class="small-text">
                    <?php else : ?>
                        <input id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name_prefix . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" type="text" class="regular-text">
                    <?php endif; ?>
                </td>
            </tr>
            <?php
        endforeach;
    }
}
