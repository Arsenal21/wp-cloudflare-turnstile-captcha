<?php

class WP_CFT_Settings_Menu extends WP_CFT_Admin_Menu {
    var $menu_page_slug = WP_CFT_SETTINGS_MENU_SLUG;
    
    /* Specify all the tabs of this menu in the following array */
    var $menu_tabs = array('tab1' => 'Tab One', 'tab2' => 'Tab Two');

    function __construct() 
    {
        $this->render_settings_menu_page();
    }

    function get_current_tab() 
    {
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->menu_tabs[0];
        return $tab;
    }

    /*
     * Renders our tabs of this menu as nav items
     */
    function render_menu_tabs() 
    {
        $current_tab = $this->get_current_tab();

        echo '<h2 class="nav-tab-wrapper">';
        foreach ( $this->menu_tabs as $tab_key => $tab_caption ) 
        {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->menu_page_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
        }
        echo '</h2>';
    }
    
    /*
     * The menu rendering goes here
     */
    function render_settings_menu_page() 
    {
        $tab = $this->get_current_tab();
        ?>
        <div class="wrap">
        <div id="poststuff"><div id="post-body">
        <?php 
        $this->render_menu_tabs();
        $tab_keys = array_keys($this->menu_tabs);
        switch ($tab)
        {
            case $tab_keys[0]:
                //include_once('file-to-handle-this-tab-rendering.php');
                //call_function_to_render_tab1();
                echo "<br />We are in tab1!";
                $this->postbox("test123", "General Settings Options", "Some test content to show how this function can be used to output postbox");
                break;
            case $tab_keys[1]:
                //include_once('file-to-handle-this-tab-rendering.php');
                //call_function_to_render_tab2();
                echo "<br />We are in tab2!";
                break;            
            default :
                //call_default_tab();
                echo "<br />We are in default tab (which is the first 1)!";
                $this->postbox("test123", "General Settings Options Default", "Some test content to show how this function can be used to output postbox");
                break;
        }
        ?>
        </div></div>
        </div><!-- end or wrap -->
        <?php
    }
    
} //end class