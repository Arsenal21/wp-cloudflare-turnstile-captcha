<?php

/* 
* Parent class for all admin menu classes.
* It will contain helpful functions that are useful for all admin menus
*/
abstract class WP_CFT_Admin_Menu
{
    /**
     * Shows postbox for settings menu. Useful for showing a section of settings
     **/
    function postbox($id, $title, $content) 
    {
        ?>
        <div id="<?php echo esc_attr($id); ?>" class="postbox">
            <h3 class="hndle"><label for="title"><?php echo esc_attr($title); ?></label></h3>
            <div class="inside">
                <?php echo $content?>
            </div>
        </div>
        <?php
    }
    
}