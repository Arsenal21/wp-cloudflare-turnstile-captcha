<?php

class WP_CFT_Dashboard_Menu extends WP_CFT_Admin_Menu
{
    var $dashboard_menu_page_slug = WP_CFT_MAIN_MENU_SLUG;
    
    /* Specify all the tabs of this menu in the following array */
    var $dashboard_menu_tabs = array('tab1' => 'Tab One', 'tab2' => 'Tab Two');

    function __construct() 
    {
        $this->render_dashboard_menu_page();
    }

    function get_current_tab() 
    {
        //Get the current tab (if any), otherwise default to the first tab.
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->dashboard_menu_tabs['tab1'];
        return $tab;
    }

    /*
     * Renders our tabs of this menu as nav items
     */
    function render_dashboard_menu_tabs() 
    {
        $current_tab = $this->get_current_tab();

        echo '<h2 class="nav-tab-wrapper">';
        foreach ( $this->dashboard_menu_tabs as $tab_key => $tab_caption ) 
        {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->dashboard_menu_page_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
        }
        echo '</h2>';
    }
    
    /*
     * The menu rendering goes here
     */
    function render_dashboard_menu_page() 
    {
        echo '<div class="wrap">';

        //Get the current tab
        $tab = $this->get_current_tab();

        //Render the menu tabe before poststuff (for the menu tabs to be correctly rendered withou CSS issue)
        $this->render_dashboard_menu_tabs();
        
        //Post stuff and body
        echo '<div id="poststuff"><div id="post-body">';

        //Switch based on the current tab
        $tab_keys = array_keys($this->dashboard_menu_tabs);
        switch ($tab)
        {
            case $tab_keys[0]:
                //include_once('file-to-handle-this-tab-rendering.php');
                //call_function_to_render_tab1();
                echo "<p>We are in tab1! Just showing that tab1 has been rendered.</p>";
                $this->postbox("test123", "Some Title for Test", "Some test content to show how this function can be used to output postbox");
                break;
            case $tab_keys[1]:
                //include_once('file-to-handle-this-tab-rendering.php');
                //call_function_to_render_tab2();
                echo "<p>We are in tab2! Just showing that tab2 has been rendered.</p>";
                break;            
            default :
                //call_default_tab();
                echo "<p>We are in default tab (which is the first 1)!</p>";
                $this->postbox("test123", "Some Title for Test", "Some test content to show how this function can be used to output postbox");
                break;
        }
        echo '</div></div>'; //end poststuff and post-body
        echo '</div>'; //<!-- end or wrap -->
    }
    
} //end class