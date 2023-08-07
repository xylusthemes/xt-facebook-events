<?php
/**
 *  List table for Shortcode Lost
 *
 * @link       http://xylusthemes.com/
 * @since      1.1.5
 *
 * @package    XT_Facebook_Events
 * @subpackage XT_Facebook_Events/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class XT_Facebook_Shortcode_List_Table extends WP_List_Table {

    public function prepare_items() {

        $columns 	= $this->get_columns();
        $hidden 	= $this->get_hidden_columns();
        $sortable 	= $this->get_sortable_columns();
        $data 		= $this->table_data();

        $perPage 		= 10;
        $currentPage 	= $this->get_pagenum();
        $totalItems 	= count( $data );

        $data = array_slice( $data, ( ( $currentPage-1 ) * $perPage ), $perPage );
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = array(
            'id'            => __( 'ID', 'xt-facebook-events' ),
            'how_to_use'    => __( 'Title', 'xt-facebook-events' ),
            'shortcode'     => __( 'Shortcode', 'xt-facebook-events' ),
			'action'    	=> __( 'Action', 'xt-facebook-events' ),
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
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        $data = array();

        $data[] = array(
                    'id'            => 1,
                    'how_to_use'    => 'Grid View Shortcode<span style="color:green;font-weight: 900;"> ( PRO )</span>',
                    'shortcode'     => '<p class="xtfe_short_code">[wpfb_events page_id="YOUR_OWN_FACEBOOK_PAGE_ID" col="3" max_events="10"]</p>',
                    'action'        => "<button class='xtfe-btn-copy-shortcode button-primary' data-value='[wpfb_events page_id=\"YOUR_OWN_FACEBOOK_PAGE_ID\" col=\"3\" max_events=\"10\"]'>Copy</button>",
                    );
        $data[] = array(
                    'id'            => 2,
                    'how_to_use'    => 'New Grid Layouts<span style="color:green;font-weight: 900;"> ( PRO )</span>',
                    'shortcode'     => '<p class="xtfe_short_code">[wpfb_events page_id="YOUR_OWN_FACEBOOK_PAGE_ID" col="3" max_events="10" layout="style2"]</p>',
                    'action'        => "<button class='xtfe-btn-copy-shortcode button-primary' data-value='[wpfb_events page_id=\"YOUR_OWN_FACEBOOK_PAGE_ID\" col=\"3\" max_events=\"10\" layout=\"style2\"]'>Copy</button>",
                    );
        return $data;
    }

    protected function display_tablenav( $which ) {
        if ( 'top' === $which ) { return false; }
        if ( 'bottom' === $which ) { return false; }
    }
	
    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item Data
     * @param  String $column_name - Current column name
     *
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'how_to_use':
            case 'shortcode':
			case 'action':
                return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }
}