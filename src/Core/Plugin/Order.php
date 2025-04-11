<?php

namespace LEXO\PO\Core\Plugin;

class Order
{
    private $renderWhiteList = [
        'page',
    ];

    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'renderMetabox']);
    }

    public function renderMetabox() : void
    {
        $whitelist = $this->renderWhiteList;
        $screen = get_current_screen();
    
        if (!in_array($screen->post_type, $whitelist)) {
            return;
        }
    
        add_meta_box(
            'subpage_settings_box',
            'Subpage Settings',
            [self::class, 'renderMetaboxContent'],
            'page',
            'normal',
            'default'
        );
    }

    public static function renderMetaboxContent($post) : void
    {
        if (!current_user_can('edit_posts')) { 
            return;
        }
    
        if (self::isHomepage($post)) {
            ?>
                <p><?php echo __('Subpage settings are disabled for this page.', 'po'); ?></p>
                <p><?php echo __('If you want to change the structure or order of subpages, you can do so ', 'po'); ?><a href="<?php echo admin_url('nav-menus.php'); ?>" target><?php echo __('here.', 'po'); ?></a>.</p>
            <?php 
            
            return;
        }
        
        self::displaySubpageSortingBox($post);
        self::displayExclusionBox($post);
        self::addHiddenFields();
    }

    public static function displaySubpageSortingBox($post) : void
    {
        $subpages = get_pages([
            'parent' => $post->ID,
            'sort_column' => 'menu_order',
            'sort_order' => 'ASC',
        ]);
    
        if (empty($subpages)) {
            ?>
                <p><?php echo __('No subpages found for this page.', 'po'); ?></p>
            <?php

            return;
        }
        
        ?>
            <ul id="sortable-subpages">
                <?php
                    foreach ($subpages as $subpage) {
                        ?>
                            <li class="sortable-item <?php echo self::printItemClass($subpage); ?>" data-id="<?php echo esc_attr($subpage->ID); ?>">
                                <?php echo esc_html($subpage->post_title); ?>
                                <a href="<?php echo get_edit_post_link($subpage->ID); ?>" target="_blank"><?php echo __('Go to page →', 'po') ?></a>
                            </li>
                        <?php
                    }
                ?>
            </ul>
        <?php
    }
    
    public static function displayExclusionBox($post) : void
    {
        if ($post->post_status != 'publish' || self::isPageInAnyMenu($post)) {
            return;
        }
    
        ?>
            <hr>
            <div class="exclusion-box" id="exclude-box">
                <input 
                    type="checkbox"
                    id="exclude-page"
                    name="custom_exclude"
                    data-page-id="<?php echo $post->ID; ?>"
                    <?php echo get_post_meta($post->ID, 'custom_exclude', true) ? 'checked' : ''; ?>
                >
                <label for="exclude-page"><?php echo __('Exclude page from menu', 'po'); ?></label>
            </div>
        <?php
    }
    
    public static function addHiddenFields() : void
    {
        wp_nonce_field('subpage_settings_action', 'subpage_settings_nonce');
    
        ?>
            <input 
                type="hidden" 
                name="subpage_order" 
                id="subpage_order" 
                value=""
            >
        <?php
    }

    public static function isPageInAnyMenu($post) : bool
    {
        $menus = wp_get_nav_menus();

        foreach ($menus as $menu) {
            $items = wp_get_nav_menu_items($menu->term_id);
            if ($items && in_array($post->ID, wp_list_pluck($items, 'object_id'))) {
                return true;
            }
        }

        return false;
    }

    public static function printItemClass($post) : string
    {
        return (get_post_meta($post->ID, 'custom_exclude', true) || $post->post_status != 'publish') 
            ? 'excluded' 
            : '';
    }

    public static function isHomepage($post) : bool
    {
        if (self::isMlAdminPageRegistered("lexo-ml-core.php") && empty($post->post_parent)) {
            return true;
        }

        return get_option('page_on_front') === $post->ID;
    }

    public static function isMlAdminPageRegistered($slug) : bool
    {
        global $menu, $submenu;
        
        foreach ($menu as $item) if (isset($item[2]) && $item[2] === $slug) return true;
        foreach ($submenu as $items) foreach ($items as $item) if (isset($item[2]) && $item[2] === $slug) return true;

        return false;
    }

    public function setNewPageMenuOrder($new_status, $old_status, $post) : void
    {
        remove_action('transition_post_status', [$this, 'setNewPageMenuOrder'], 10);

        if ($post->post_type !== 'page' || $new_status !== 'publish' || $old_status === 'publish') {
            return;
        }
    
        if (get_post_meta($post->ID, '_menu_order_set', true)) {
            return;
        }
            
        $siblings = get_pages([
            'parent' => $post->post_parent,
            'post_status' => 'publish',
            'hierarchical' => false,
            'exclude' => $post->ID
        ]);
    
        wp_update_post([
            'ID' => $post->ID,
            'menu_order' => count($siblings)
        ]);
        
        update_post_meta($post->ID, '_menu_order_set', true);
        add_action('transition_post_status', [$this, 'setNewPageMenuOrder'], 10, 3);
    }
    
    public function updateMenuOrderOnParentChange($post_ID, $post_after, $post_before) : void
    {
        if ($post_after->post_type !== 'page' || $post_after->post_parent == $post_before->post_parent) {
            return;
        }
        
        $siblings = get_pages([
            'parent' => $post_after->post_parent,
            'post_status' => 'publish',
            'hierarchical' => false,
            'exclude' => $post_ID
        ]);
    
        wp_update_post([
            'ID' => $post_ID,
            'menu_order' => count($siblings)
        ]);
    }

    public function saveSubpageSettingsData($post_id) : void
    {
        remove_action('save_post', [$this, 'saveSubpageSettingsData']);

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
    
        if (!isset($_POST['subpage_settings_nonce'])) {
            return;
        }
    
        if (!wp_verify_nonce($_POST['subpage_settings_nonce'], 'subpage_settings_action')) {
            return;
        }
    
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    
        if (!empty($_POST['subpage_order'])) {
            $order = explode(',', $_POST['subpage_order']);
            
            foreach ($order as $index => $page_id) {
                wp_update_post([
                    'ID' => (int)$page_id,
                    'menu_order' => $index
                ]);
            }
        }
    
        $exclude = isset($_POST['custom_exclude']) ? 'on' : '';
        if ($exclude) {
            update_post_meta($post_id, 'custom_exclude', $exclude);
        } else {
            delete_post_meta($post_id, 'custom_exclude');
        }

        add_action('save_post', [$this, 'saveSubpageSettingsData']);
    }
}
