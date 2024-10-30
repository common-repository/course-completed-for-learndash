<?php
/**
 * Plugin Name:  Course Completed for Learndash
 * Plugin URI: https://wptrat.com/learndash-course-completed /
 * Description: Course Completed for Learndash is the best way to prevent user from accessing course content after course completion.
 * Author: Luis Rock
 * Author URI: https://wptrat.com/
 * Version: 1.0.0
 * Text Domain: learndash-course-completed
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   Course Completed for Learndash
 */


if ( ! defined( 'ABSPATH' ) ) exit;
		
// //Solicitando outros arquivos do plugin
require_once('admin/trcc-settings.php');
require_once('includes/functions.php');

//Admin CSS
function trcc_enqueue_admin_script( $hook ) {
    global $trcc_settings_page;
    if( $hook != $trcc_settings_page ) {
        return;
    }
    wp_enqueue_style('trcc_admin_style', plugins_url('assets/css/trcc-admin.css',__FILE__ ));
}
add_action( 'admin_enqueue_scripts', 'trcc_enqueue_admin_script' );


//New message for lesson not available for user that has completed the course
add_filter('learndash_lesson_available_from_text', 'truc_lesson_not_available_text', 999, 2 );
//Filters content of lessons, topics and quizzes
add_filter( 'learndash_content', 'truc_hide_course_completed_lessons_topics_quizzes', 999, 2 );
