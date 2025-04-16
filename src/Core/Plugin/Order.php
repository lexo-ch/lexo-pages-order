<?php

namespace LEXO\PO\Core\Plugin;

use WP_Post;

use const LEXO\PO\{
    DOMAIN
};

class Order
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'renderMetabox']);
    }

    public function renderMetabox(): void
    {
        add_meta_box(
            'subpage_settings_box',
            __('Subpage Settings', 'po'),
            [self::class, 'renderMetaboxContent'],
            'page',
            'normal',
            'default'
        );
    }

    public static function renderMetaboxContent(WP_Post $post): void
    {
        if (self::isHomepage($post)) {
            echo self::getHomepageMessage();
            return;
        }

        self::displaySubpageSortingBox($post);
        self::displayExclusionBox($post);
        self::addHiddenFields();
    }

    public static function getHomepageMessage(): string
    {
        ob_start(); ?>
            <p><?php echo __('Subpage settings are disabled for this page because its subpages are the part of the main menu.', 'po'); ?></p>
            <p>
                <?php echo sprintf(
                    __('If you want to change the order of the pages in the main menu, you can do so %s.', 'po'),
                    '<a href="' . admin_url('nav-menus.php') . '" target="_blank">' . __('here', 'po') . '</a>'
                ); ?>
            </p>
        <?php return ob_get_clean();
    }

    public static function displaySubpageSortingBox(WP_Post $post): void
    {
        $subpages = get_pages([
            'parent' => $post->ID,
            'sort_column' => 'menu_order',
            'sort_order' => 'ASC',
        ]);

        if (empty($subpages)) { ?>
            <p><?php echo __('No subpages found for this page.', 'po'); ?></p>
            <?php return;
        } ?>

        <ul id="po-sortable-subpages">
            <?php foreach ($subpages as $subpage) {
                $is_page_excluded = self::isPageExcluded($subpage);
                $parent_excluded = self::isAnyParentExcluded($subpage);

                $classes = ['po-sortable-item'];

                if ($is_page_excluded || $parent_excluded) {
                    $classes[] = 'excluded';
                } ?>

                <li
                    class="<?php echo implode(' ', $classes); ?>"
                    data-id="<?php echo esc_attr($subpage->ID); ?>"
                    <?php if ($is_page_excluded) { ?>
                        title="<?php echo __('This page and all it\'s subpages are excluded from the main menu.', 'po'); ?>"
                    <?php } elseif ($parent_excluded !== false) {
                        $title = sprintf(
                            __('This page and all it\'s subpages are excluded from the main menu because its parent page %s is excluded.', 'po'),
                            get_the_title($parent_excluded)
                        ); ?>
                        title="<?php echo $title; ?>"
                    <?php } ?>
                >
                    <div class="page-title"><?php echo esc_html($subpage->post_title); ?></div>
                    <div class="controls">
                        <a
                            class="po-sortable-url"
                            href="<?php echo get_edit_post_link($subpage->ID); ?>"
                            target="_blank"
                        >&rightarrow;</span></a>
                    </div>
                </li>
            <?php } ?>
        </ul>
        <small class="post-attributes-help-text">
            <?php echo __('Change the order of the subpages by clicking and dragging the respective page to the desired location.', 'po'); ?>
        </small>
        <?php
    }

    public static function displayExclusionBox(WP_Post $post): void
    {
        if ($post->post_status != 'publish' || self::isPageInAnyMenu($post)) {
            return;
        } ?>

        <div id="po-exclusion-box">
            <?php $parent_excluded = self::isAnyParentExcluded($post);

            if ($parent_excluded !== false) {
                echo sprintf(
                    __('This page and all it\'s subpages are excluded from the main menu because its parent page %s is excluded.', 'po'),
                    '<a href="' . get_edit_post_link($parent_excluded) . '" target="_blank">' . get_the_title($parent_excluded) . '</a>'
                );
            } else { ?>
                <label
                    for="po-exclude-page"
                >
                    <input
                        type="checkbox"
                        id="po-exclude-page"
                        name="custom_exclude"
                        data-page-id="<?php echo $post->ID; ?>"
                        <?php checked(self::isPageExcluded($post), true, true); ?>
                    />
                    <?php echo __('Exclude page from menu<br><small>(and all it\'s subpages)</small>', 'po'); ?>
                </label>
            <?php } ?>
        </div>
        <?php
    }

    public static function addHiddenFields(): void
    {
        wp_nonce_field('subpage_settings_action', 'subpage_settings_nonce'); ?>

        <input
            type="hidden"
            name="po-subpage-order"
            id="po-subpage-order"
            value=""
        >
        <?php
    }

    public static function isPageInAnyMenu(WP_Post $post): bool
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

    public static function isPageExcluded($post): bool
    {
        if (!($post instanceof WP_Post)) {
            $post = get_post($post);
        }

        return get_post_meta($post->ID, 'custom_exclude', true) || $post->post_status != 'publish';
    }

    public static function isHomepage(WP_Post $post): bool
    {
        if (self::isMlAdminPageRegistered("lexo-ml-core.php") && empty($post->post_parent)) {
            return true;
        }

        return get_option('page_on_front') === $post->ID;
    }

    public static function isMlAdminPageRegistered(string $slug): bool
    {
        global $menu, $submenu;

        foreach ($menu as $item) {
            if (isset($item[2]) && $item[2] === $slug) {
                return true;
            }
        }

        foreach ($submenu as $items) {
            foreach ($items as $item) {
                if (isset($item[2]) && $item[2] === $slug) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function isAnyParentExcluded(WP_Post $post)
    {
        $ancestors = get_post_ancestors($post->ID);

        foreach ($ancestors as $ancestor) {
            if (self::isPageExcluded($ancestor)) {
                return $ancestor;
            }
        }

        return false;
    }

    public function setMenuOrderForNewPage(string $new_status, string $old_status, WP_Post $post): void
    {
        remove_action('transition_post_status', [$this, 'setMenuOrderForNewPage'], 10);

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
        add_action('transition_post_status', [$this, 'setMenuOrderForNewPage'], 10, 3);
    }

    public function updateMenuOrderOnParentChange(int $post_id, WP_Post $post_after, WP_Post $post_before): void
    {
        if ($post_after->post_type !== 'page' || $post_after->post_parent == $post_before->post_parent) {
            return;
        }

        $siblings = get_pages([
            'parent' => $post_after->post_parent,
            'post_status' => 'publish',
            'hierarchical' => false,
            'exclude' => $post_id
        ]);

        wp_update_post([
            'ID' => $post_id,
            'menu_order' => count($siblings)
        ]);
    }

    public function saveSubpageSettingsData(int $post_id): void
    {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

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

        if (!empty(trim($_POST['po-subpage-order']))) {
            $order = explode(',', $_POST['po-subpage-order']);

            foreach ($order as $index => $subpost_id) {
                wp_update_post([
                    'ID' => $subpost_id,
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

    public static function addExcludedColumn(array $columns): array
    {
        $customColumn = [
            'excluded_pages_column' => __('Excluded from main menu', 'po')
        ];

        $columns = array_merge($columns, $customColumn);

        return $columns;
    }

    public static function printExcludedColumn(string $column_name, int $post_id): void
    {
        if ($column_name === 'excluded_pages_column') {
            $post = get_post();

            if (self::isAnyParentExcluded($post) !== false || self::isPageExcluded($post)) {
                $excluded_message = apply_filters(
                    DOMAIN . '/message/column_excluded',
                    '<span class="excluded-page true"></span>'
                );
            } else {
                $excluded_message = apply_filters(
                    DOMAIN . '/message/column_not_excluded',
                    '<span class="excluded-page false"></span>'
                );
            }

            echo $excluded_message;
        }
    }
}
