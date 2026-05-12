<?php
/*
Plugin Name: Student Result Pro
Description: Dynamic student result management system with verification URL & master protection.
Version: 16.1
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
    | Master Password
    |--------------------------------------------------------------------------
    */

    private $master_password = 'SHAHRIAR_SRS_PRO_2026';

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {

        register_activation_hook(
            __FILE__,
            array($this, 'activate_plugin')
        );

        add_action(
            'admin_menu',
            array($this, 'admin_menu')
        );

        add_action(
            'wp_enqueue_scripts',
            array($this, 'enqueue_assets')
        );

        add_action(
            'admin_enqueue_scripts',
            array($this, 'enqueue_assets')
        );

        add_shortcode(
            'student_result_search',
            array($this, 'search_shortcode')
        );

        add_action(
            'wp_ajax_srp_search_student',
            array($this, 'search_student')
        );

        add_action(
            'wp_ajax_nopriv_srp_search_student',
            array($this, 'search_student')
        );

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

        flush_rewrite_rules();
    }

    /*
    |--------------------------------------------------------------------------
    | Create Tables
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
    | Enqueue Assets
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

        /*
        |--------------------------------------------------------------------------
        | Check Verification
        |--------------------------------------------------------------------------
        */

        $verified = get_option(
            'srp_plugin_verified'
        );

        /*
        |--------------------------------------------------------------------------
        | Get Password From Random Input
        |--------------------------------------------------------------------------
        */

        $password = '';

        foreach ($_POST as $key => $value) {

            if (
                strpos(
                    $key,
                    'srp_secure_access_'
                ) !== false
            ) {

                $password = sanitize_text_field(
                    $value
                );

                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Password
        |--------------------------------------------------------------------------
        */

        if (!empty($password)) {

            if (
                $password === $this->master_password
            ) {

                update_option(
                    'srp_plugin_verified',
                    true
                );

                $verified = true;

            } else {

                echo '
                    <div class="notice notice-error">
                        <p>Invalid Master Password.</p>
                    </div>
                ';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Locked Screen
        |--------------------------------------------------------------------------
        */

        if (!$verified) {

            ?>

            <div class="wrap">

                <div
                    style="
                        max-width:520px;
                        margin:100px auto;
                        background:#fff;
                        padding:45px;
                        border-radius:24px;
                        box-shadow:0 15px 50px rgba(0,0,0,0.08);
                    "
                >

                    <h1
                        style="
                            margin-bottom:18px;
                            font-size:34px;
                            font-weight:900;
                        "
                    >
                        Plugin Verification
                    </h1>

                    <p
                        style="
                            color:#666;
                            line-height:1.8;
                            margin-bottom:30px;
                        "
                    >
                        Enter master password to unlock
                        Student Result Pro.
                    </p>

                    <form method="POST" autocomplete="off">

                        <!-- Fake Hidden Inputs -->

                        <input
                            type="text"
                            style="display:none"
                            autocomplete="username"
                        >

                        <input
                            type="password"
                            style="display:none"
                            autocomplete="new-password"
                        >

                        <!-- Fake Username -->

                        <input
                            type="text"
                            name="fake-user"
                            style="display:none"
                        >

                        <!-- Real Password Field -->

                        <input
                            type="password"
                            name="srp_secure_access_<?php echo rand(1000,9999); ?>"
                            data-lpignore="true"
                            data-form-type="other"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="off"
                            spellcheck="false"
                            readonly
                            onfocus="this.removeAttribute('readonly');"
                            placeholder="Enter Master Password"
                            style="
                                width:100%;
                                height:58px;
                                padding:0 20px;
                                border-radius:14px;
                                border:1px solid #ddd;
                                margin-bottom:20px;
                                font-size:15px;
                            "
                        >

                        <button
                            type="submit"
                            class="button button-primary"
                            style="
                                width:100%;
                                height:54px;
                                font-size:16px;
                                font-weight:700;
                            "
                        >
                            Unlock Plugin
                        </button>

                    </form>

                </div>

            </div>

            <?php

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Load Dashboard
        |--------------------------------------------------------------------------
        */

        require_once plugin_dir_path(__FILE__) .
            'admin/admin-page.php';
    }

    /*
    |--------------------------------------------------------------------------
    | Shortcode
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
    | AJAX Search
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
        | Student URL
        |--------------------------------------------------------------------------
        */

        $student_url = home_url(
            '/srp-verification/' . $student->roll
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
    | Rewrite Rule
    |--------------------------------------------------------------------------
    */

   public function custom_rewrite_rule()
{

    add_rewrite_rule(

        '^srp-verification/([^/]*)/?$',

        'index.php?student_roll=$matches[1]',

        'top'
    );
}
    /*
    |--------------------------------------------------------------------------
    | Query Vars
    |--------------------------------------------------------------------------
    */

    public function query_vars($vars)
    {

        $vars[] = 'student_roll';

        return $vars;
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Template
    |--------------------------------------------------------------------------
    */

    public function custom_template()
    {

        $roll = get_query_var(
            'student_roll'
        );

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