<?php
/**
 * Fired during plugin deactivation
 */
class Map_Drawing_Assessment_Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Remove capabilities
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->remove_cap('manage_map_assessment');
            $admin_role->remove_cap('view_map_assessment');
        }

        // Clear any scheduled hooks
        wp_clear_scheduled_hook('map_drawing_assessment_cleanup');

        // Clear rewrite rules
        flush_rewrite_rules();

        // Optionally, you can add code here to:
        // 1. Clean up temporary files
        // 2. Remove transients
        // 3. Clear any caches

        // Note: We don't delete tables or options here
        // They should only be removed when the plugin is uninstalled
    }
}