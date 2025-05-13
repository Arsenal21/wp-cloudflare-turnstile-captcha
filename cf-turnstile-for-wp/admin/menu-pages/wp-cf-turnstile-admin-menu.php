<?php

/* 
* Parent class for all admin menu classes.
* It will contain helpful functions that are useful for all admin menus
*/
abstract class WP_CFT_Admin_Menu
{
    /**
     * Shows postbox for settings menu. Useful for showing a section of settings
     *
     * @param string $id css ID for postbox
     * @param string $title title of the postbox section
     * @param string $content the content of the postbox
     **/
    function postbox($id, $title, $content) 
    {
        ?>
        <div id="<?php echo $id; ?>" class="postbox">
            <h3 class="hndle"><label for="title"><?php echo $title; ?></label></h3>
            <div class="inside">
                <?php echo $content; ?>
            </div>
        </div>
        <?php
    }
    
}