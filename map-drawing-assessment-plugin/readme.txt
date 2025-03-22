=== Map Drawing Assessment ===
Contributors: SERU
Tags: assessment, map, drawing, education, training
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A comprehensive map drawing assessment system with multiple question types including map route drawing, MCQ, and fill in the blanks.

== Description ==

Map Drawing Assessment is a powerful WordPress plugin designed for creating and managing map-based assessments. It's particularly useful for training organizations that need to assess route knowledge and understanding of topographical elements.

= Features =

* **Map Drawing Questions**
  * Custom map tiles integration
  * Start and end point markers
  * Route drawing with undo/redo functionality
  * Map brightness control
  * Auto-save progress

* **Multiple Choice Questions (MCQ)**
  * Support for multiple correct answers
  * Weighted scoring (1-3 points per question)
  * Clear feedback system

* **Fill in the Blanks Questions**
  * Fixed set of 6 options per question
  * No repeat answers within same question
  * Automatic scoring system

* **User Management**
  * Temporary access codes
  * Time-limited access
  * Access revocation capability
  * Email notifications

* **Assessment Management**
  * Real-time progress tracking
  * Question flagging system
  * Review mode for checking answers
  * Comprehensive submission system

* **Admin Features**
  * Detailed submission review
  * Route correction tools
  * Bulk question management
  * User access control
  * Result email system

= Use Cases =

1. **Transport Training**
   * Assess drivers' route knowledge
   * Test understanding of traffic regulations
   * Evaluate navigation skills

2. **Geographic Education**
   * Test topographical knowledge
   * Assess route planning abilities
   * Evaluate spatial awareness

3. **Professional Certification**
   * Conduct route competency tests
   * Verify navigation skills
   * Assess regulatory knowledge

== Installation ==

1. Upload the `map-drawing-assessment` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings under 'Map Assessment' in the admin menu
4. Use the shortcode `[map_drawing_assessment]` to display the assessment in any post or page

= Minimum Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher

== Frequently Asked Questions ==

= How do I create a new map question? =

1. Go to Map Assessment > Create Map Question
2. Enter the question title and instructions
3. Place start (green) and end (red) markers on the map
4. Save the question

= How do users access the assessment? =

1. Create user access from the User Management page
2. Users receive an email with access code
3. Users enter the code when accessing the assessment
4. Access is valid for the specified period

= Can I customize the map tiles? =

Yes, you can change the map tiles URL in the plugin settings. The default tiles are from SERU's custom map service.

= How are assessments scored? =

* Map Questions: Admin reviews and scores manually
* MCQ: Automatic scoring based on correct answers (1-3 points)
* Fill in Blanks: 2 points if all correct, 0 if any wrong

== Screenshots ==

1. Map Drawing Interface
2. Admin Dashboard
3. Question Creation
4. User Management
5. Submission Review

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of Map Drawing Assessment plugin.

== Additional Information ==

= Custom Development =

For custom development or specific feature requests, please contact SERU support.

= Documentation =

Detailed documentation is available at: https://theseru.co.uk/docs

= Support =

For support inquiries, please visit: https://theseru.co.uk/support