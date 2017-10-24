<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Extend core List Table class
 */
class WooCommerce_Custom_Availability_Table extends WP_List_Table {


    public function __construct() {
        parent::__construct(
            [
            'singular' => __( 'Custom Availability', 'woocommerce-custom-availability' ),
            'plural'   => __( 'Custom Availabilities', 'woocommerce-custom-availability' ),
            'ajax'     => false,

            ]
        );
    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $per_page     = $this->get_items_per_page( 'woocommerce_custom_availability_per_page' );
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();
        $this->set_pagination_args(
            [
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page,     //WE have to determine how many items to show on a page
            ]
        );
        $this->items = $this->get_products( $per_page, $current_page );
    }

    /**
     * Get simple and variable products
     *
     * @param  integer $per_page [description]
     * @param  integer $page_no  [description]
     * @return Array
     */
    public function get_products( $per_page = 20, $page_no = 1 ) {
        global $wpdb;
        $order_by = "{$wpdb->prefix}posts.ID";
        if ( ( isset( $_GET['orderby'] ) && ! empty( $_GET['orderby'] ) ) ) {
            $order_by = esc_sql( $_GET['orderby'] );
        }
        $order = ( isset( $_GET['order'] ) && ! empty( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'DESC';
        $offset = ( ( $page_no - 1 ) * $per_page );
        return $wpdb->get_results(
                                $wpdb->prepare(
                                        "SELECT {$wpdb->prefix}posts.ID,{$wpdb->prefix}posts.post_title FROM
                                        {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type
                                        IN ('product','product_variation')
                                        AND {$wpdb->prefix}posts.post_status
                                        IN ('draft','publish')
                                        AND {$wpdb->prefix}posts.ID NOT IN ( SELECT DISTINCT(post_parent)
                                        FROM {$wpdb->prefix}posts WHERE post_type='product_variation'
                                        UNION ALL SELECT DISTINCT(post_id)
                                        FROM {$wpdb->prefix}postmeta
                                        WHERE ( meta_key = '_downloadable' AND meta_value = 'yes' )
                                        OR ( meta_key = '_virtual' AND meta_value = 'yes' ) )
                                        ORDER BY %s %s LIMIT %d OFFSET %d",
                                array( $order_by, $order, $per_page, $offset )
                                ),
                            'ARRAY_A'
            );
    }

    /**
     * Get simple and variable products count
     *
     * @return Integer
     */
    public function record_count() {
        global $wpdb;
        return $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT count(*) FROM {$wpdb->prefix}posts
                    WHERE {$wpdb->prefix}posts.post_type
                    IN ('product','product_variation') AND {$wpdb->prefix}posts.post_status
                    IN ('draft','publish') AND {$wpdb->prefix}posts.ID NOT
                    IN ( SELECT DISTINCT(post_parent)
                    FROM {$wpdb->prefix}posts
                    WHERE post_type='product_variation'
                    UNION ALL SELECT DISTINCT(post_id)
                    FROM {$wpdb->prefix}postmeta
                    WHERE ( meta_key = '_downloadable' AND meta_value = 'yes' )
                    OR ( meta_key = '_virtual' AND meta_value = 'yes' ) ) -- %d", array(1)
                )
            );
    }

    /**
     * Text displayed when no product data is available
     */
    public function no_items() {
        esc_html_e( 'No products avaliable.', 'woocommerce-custom-availability' );
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = array(
        'post_title'  => __( 'Name', 'woocommerce-custom-availability' ),
        'description' => __( 'Custom Availability', 'woocommerce-custom-availability' ),
        );
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns() {
        return array(
                    'post_title' => array( 'post_title', true ),
                    );
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param Array  $item        Data
     * @param String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
        case 'post_title':
            $edit_link = get_edit_post_link( $item['ID'] );
            if ( ! empty( $edit_link ) ) { // if product is simple
                return '<a href="' . esc_url( $edit_link ) . '">' . $item[ $column_name ] . '</a>';
            } else { // if product is varible
                $parent_id = wp_get_post_parent_id( $item['ID'] );
                return '<a href="' . get_edit_post_link( $parent_id ) . '">
                        ' . esc_html( get_the_title( $parent_id ) ) . '</a> - ' . $item[ $column_name ];
            }
        case 'description':
            $meta_value = get_post_meta( $item['ID'], '_custom_availability', true );
                return '<input class="regular-text"
                                type="text"
                                name="_custom_availability[' . $item['ID'] . ']"
                                value="' . esc_attr( $meta_value ) . '" >';
        default:
                return '';
        }
    }
}
