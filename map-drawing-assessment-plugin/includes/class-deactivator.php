<?php
/**
 * Fired during plugin deactivation
 */
class Map_Drawing_Assessment_Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Clear any scheduled hooks
        wp_clear_scheduled_hook('map_drawing_assessment_cleanup');

        // Clear any transients
        delete_transient('map_drawing_assessment_cache');

        // Clear rewrite rules
        flush_rewrite_rules();

        // Note: We don't delete tables or options here
        // They should only be removed during uninstallation
    }
}