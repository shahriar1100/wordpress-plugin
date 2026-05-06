<?php
/*
Plugin Name: Student Result Pro
Description: Dynamic student result management system.
Version: 4.0
Author: AL SHAHRIAR
*/

if (!defined('ABSPATH')) {
    exit;
}

class Student_Result_Pro {

    public function __construct() {

        register_activation_hook(__FILE__, array($this, 'create_tables'));

        add_action('admin_menu', array($this, 'admin_menu'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        add_shortcode('student_result_search', array($this, 'search_shortcode'));

        add_action('wp_ajax_srp_search_student', array($this, 'search_student'));

        add_action('wp_ajax_nopriv_srp_search_student', array($this, 'search_student'));
    }

    public function create_tables() {

        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $students_table = $wpdb->prefix . 'srp_students';

        $courses_table = $wpdb->prefix . 'srp_courses';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $students_sql = "CREATE TABLE $students_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            roll VARCHAR(50) NOT NULL UNIQUE,
            student_name VARCHAR(255) NOT NULL,
            father_name VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($students_sql);

        $courses_sql = "CREATE TABLE $courses_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            student_id BIGINT UNSIGNED NOT NULL,
            course_name VARCHAR(255) NOT NULL,
            mark_obtained VARCHAR(50) NOT NULL,
            grade VARCHAR(50) NOT NULL,
            certificate_url TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($courses_sql);
    }

    public function enqueue_assets() {

    wp_enqueue_style(
        'srp-style',
        plugin_dir_url(__FILE__) . 'assets/style.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/style.css')
    );

    wp_enqueue_script(
        'srp-script',
        plugin_dir_url(__FILE__) . 'assets/script.js',
        array('jquery'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/script.js'),
        true
    );

    wp_localize_script(
        'srp-script',
        'srp_ajax_obj',
        array(
            'ajax_url' => admin_url('admin-ajax.php')
        )
    );
}

    public function admin_menu() {

        add_menu_page(
            'Student Result Pro',
            'Student Result',
            'manage_options',
            'student-result-pro',
            array($this, 'admin_page'),
            'dashicons-welcome-learn-more',
            20
        );
    }

    public function admin_page() {

        require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';
    }

    public function search_shortcode() {

        ob_start();

        include plugin_dir_path(__FILE__) . 'templates/search-form.php';

        return ob_get_clean();
    }

    public function search_student() {

        global $wpdb;

        $roll = sanitize_text_field($_POST['roll']);

        $students_table = $wpdb->prefix . 'srp_students';
        $courses_table = $wpdb->prefix . 'srp_courses';

        $student = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $students_table WHERE roll = %s",
                $roll
            )
        );

        if (!$student) {
            wp_send_json_error('Student not found');
        }

        $courses = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $courses_table WHERE student_id = %d",
                $student->id
            )
        );

        wp_send_json_success(array(
            'student' => $student,
            'courses' => $courses
        ));
    }
}

new Student_Result_Pro();