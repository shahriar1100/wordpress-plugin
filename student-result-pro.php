<?php
/*
Plugin Name: Student Result Pro
Description: Dynamic student result management system with certificate verification URL.
Version: 9.0
Author: AL SHAHRIAR
Text Domain: student-result-pro
*/

if (!defined('ABSPATH')) {
    exit;
}

class Student_Result_Pro
{

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {

        /*
        |--------------------------------------------------------------------------
        | Activation Hook
        |--------------------------------------------------------------------------
        */

        register_activation_hook(
            __FILE__,
            array($this, 'activate_plugin')
        );

        register_deactivation_hook(
            __FILE__,
            array($this, 'deactivate_plugin')
        );

        /*
        |--------------------------------------------------------------------------
        | Admin Menu
        |--------------------------------------------------------------------------
        */

        add_action(
            'admin_menu',
            array($this, 'admin_menu')
        );

        /*
        |--------------------------------------------------------------------------
        | Frontend Assets
        |--------------------------------------------------------------------------
        */

        add_action(
            'wp_enqueue_scripts',
            array($this, 'enqueue_assets')
        );

        /*
        |--------------------------------------------------------------------------
        | Shortcode
        |--------------------------------------------------------------------------
        */

        add_shortcode(
            'student_result_search',
            array($this, 'search_shortcode')
        );

        /*
        |--------------------------------------------------------------------------
        | AJAX Search
        |--------------------------------------------------------------------------
        */

        add_action(
            'wp_ajax_srp_search_student',
            array($this, 'search_student')
        );

        add_action(
            'wp_ajax_nopriv_srp_search_student',
            array($this, 'search_student')
        );

        /*
        |--------------------------------------------------------------------------
        | Student Verification URL
        |--------------------------------------------------------------------------
        */

        add_action(
            'init',
            array($this, 'custom_rewrite_rule')
        );

        add_filter(
            'query_vars',
            array($this, 'query_vars')
        );

        add_action(
            'template_redirect',
            array($this, 'custom_template')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Plugin Activation
    |--------------------------------------------------------------------------
    */

    public function activate_plugin()
    {

        $this->create_tables();
    }

    /*
    |--------------------------------------------------------------------------
    | Plugin Deactivation
    |--------------------------------------------------------------------------
    */

    public function deactivate_plugin()
    {
        // Nothing for now
    }

    /*
    |--------------------------------------------------------------------------
    | Create Database Tables
    |--------------------------------------------------------------------------
    */

    public function create_tables()
    {

        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $students_table = $wpdb->prefix . 'srp_students';

        $courses_table = $wpdb->prefix . 'srp_courses';

        require_once(
            ABSPATH . 'wp-admin/includes/upgrade.php'
        );

        /*
        |--------------------------------------------------------------------------
        | Students Table
        |--------------------------------------------------------------------------
        */

        $students_sql = "
            CREATE TABLE $students_table (

                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

                roll VARCHAR(100) NOT NULL UNIQUE,

                student_name VARCHAR(255) NOT NULL,

                father_name VARCHAR(255) NOT NULL,

                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

                PRIMARY KEY (id)

            ) $charset_collate;
        ";

        dbDelta($students_sql);

        /*
        |--------------------------------------------------------------------------
        | Courses Table
        |--------------------------------------------------------------------------
        */

        $courses_sql = "
            CREATE TABLE $courses_table (

                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

                student_id BIGINT UNSIGNED NOT NULL,

                course_name VARCHAR(255) NOT NULL,

                mark_obtained VARCHAR(100) NOT NULL,

                grade VARCHAR(100) NOT NULL,

                certificate_url TEXT NOT NULL,

                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

                PRIMARY KEY (id)

            ) $charset_collate;
        ";

        dbDelta($courses_sql);
    }

    /*
    |--------------------------------------------------------------------------
    | Enqueue CSS & JS
    |--------------------------------------------------------------------------
    */

    public function enqueue_assets()
    {

        wp_enqueue_style(
            'srp-style',
            plugin_dir_url(__FILE__) . 'assets/style.css',
            array(),
            filemtime(
                plugin_dir_path(__FILE__) . 'assets/style.css'
            )
        );

        wp_enqueue_script(
            'srp-script',
            plugin_dir_url(__FILE__) . 'assets/script.js',
            array('jquery'),
            filemtime(
                plugin_dir_path(__FILE__) . 'assets/script.js'
            ),
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

    /*
    |--------------------------------------------------------------------------
    | Admin Menu
    |--------------------------------------------------------------------------
    */

    public function admin_menu()
    {

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

    /*
    |--------------------------------------------------------------------------
    | Admin Page
    |--------------------------------------------------------------------------
    */

    public function admin_page()
    {

        require_once plugin_dir_path(__FILE__) .
            'admin/admin-page.php';
    }

    /*
    |--------------------------------------------------------------------------
    | Frontend Shortcode
    |--------------------------------------------------------------------------
    */

    public function search_shortcode()
    {

        ob_start();

        include plugin_dir_path(__FILE__) .
            'templates/search-form.php';

        return ob_get_clean();
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Student Search
    |--------------------------------------------------------------------------
    */

    public function search_student()
    {

        global $wpdb;

        $roll = isset($_POST['roll'])
            ? sanitize_text_field($_POST['roll'])
            : '';

        if (empty($roll)) {

            wp_send_json_error(
                'Roll number is required.'
            );
        }

        $students_table = $wpdb->prefix . 'srp_students';

        $courses_table = $wpdb->prefix . 'srp_courses';

        /*
        |--------------------------------------------------------------------------
        | Get Student
        |--------------------------------------------------------------------------
        */

        $student = $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT * FROM {$students_table}
                WHERE roll = %s
                ",
                $roll
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Student Not Found
        |--------------------------------------------------------------------------
        */

        if (!$student) {

            wp_send_json_error(
                'Student not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Courses
        |--------------------------------------------------------------------------
        */

        $courses = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT * FROM {$courses_table}
                WHERE student_id = %d
                ORDER BY id DESC
                ",
                $student->id
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Student Verification URL
        |--------------------------------------------------------------------------
        */

        $student_url = home_url(
            '/students-certificate/' . $student->roll
        );

        /*
        |--------------------------------------------------------------------------
        | Return Response
        |--------------------------------------------------------------------------
        */

        wp_send_json_success(
            array(

                'student' => $student,

                'courses' => $courses,

                'student_url' => $student_url

            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Rewrite Rule
    |--------------------------------------------------------------------------
    */

    public function custom_rewrite_rule()
    {

        add_rewrite_rule(

            '^students-certificate/([^/]*)/?',

            'index.php?student_roll=$matches[1]',

            'top'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Register Query Variable
    |--------------------------------------------------------------------------
    */

    public function query_vars($vars)
    {

        $vars[] = 'student_roll';

        return $vars;
    }

    /*
    |--------------------------------------------------------------------------
    | Load Custom Template
    |--------------------------------------------------------------------------
    */

    public function custom_template()
    {

        $roll = get_query_var('student_roll');

        if ($roll) {

            include plugin_dir_path(__FILE__) .
                'templates/student-single.php';

            exit;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Initialize Plugin
|--------------------------------------------------------------------------
*/

new Student_Result_Pro();