<?php

nocache_headers();

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

/*
|--------------------------------------------------------------------------
| Roll
|--------------------------------------------------------------------------
*/

$roll = get_query_var(
    'student_roll'
);

/*
|--------------------------------------------------------------------------
| Tables
|--------------------------------------------------------------------------
*/

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
        SELECT *
        FROM {$students_table}
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

    wp_die('Student not found.');
}

/*
|--------------------------------------------------------------------------
| Get Courses
|--------------------------------------------------------------------------
*/

$courses = $wpdb->get_results(
    $wpdb->prepare(
        "
        SELECT *
        FROM {$courses_table}
        WHERE student_id = %d
        ORDER BY id DESC
        ",
        $student->id
    )
);

?>

<!DOCTYPE html>

<html <?php language_attributes(); ?>>

<head>

    <meta charset="<?php bloginfo('charset'); ?>">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?php echo esc_html($student->student_name); ?>
    </title>

    <?php wp_head(); ?>

</head>

<body>

<div class="srp-container">

    <!-- HERO -->

    <div class="srp-hero-section">

        <div class="srp-hero-badge">
            VERIFIED STUDENT PROFILE
        </div>

        <h1>
            STUDENT RESULT
            <br>
            & CERTIFICATE
        </h1>

        <p>
            Official student verification profile of
            <?php echo esc_html($student->student_name); ?>.
        </p>

    </div>

    <!-- RESULT -->

    <div class="srp-card">

        <!-- HEADER -->

        <div class="srp-header">

            <h1>
                Student Information
            </h1>

            <p>
                Roll Number:
                <?php echo esc_html($student->roll); ?>
            </p>

        </div>

        <!-- BODY -->

        <div class="srp-body">

            <!-- INFO GRID -->

            <div
                style="
                    display:grid;
                    grid-template-columns:repeat(2,1fr);
                    gap:25px;
                    margin-bottom:10px;
                "
            >

                <!-- Student -->

                <div class="srp-box">

                    <h2>
                        Student Name
                    </h2>

                    <p>
                        <?php echo esc_html($student->student_name); ?>
                    </p>

                </div>

                <!-- Father -->

                <div class="srp-box">

                    <h2>
                        Father's Name
                    </h2>

                    <p>
                        <?php echo esc_html($student->father_name); ?>
                    </p>

                </div>

            </div>

            <!-- COURSE TITLE -->

            <div
                style="
                    margin-top:40px;
                    margin-bottom:10px;
                "
            >

                <h2
                    style="
                        font-size:34px;
                        font-weight:900;
                        color:#020617;
                    "
                >
                    Completed Courses
                </h2>

            </div>

            <!-- COURSE GRID -->

            <div
                style="
                    display:grid;
                    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
                    gap:25px;
                "
            >

                <?php if ($courses) : ?>

                    <?php foreach ($courses as $course) : ?>

                        <div class="srp-course-card">

                            <!-- Course Header -->

                            <div class="srp-course-top">

                                <h3>
                                    COURSE NAME
                                </h3>

                                <p>
                                    <?php echo esc_html($course->course_name); ?>
                                </p>

                            </div>

                            <!-- Mark & Grade -->

                            <div class="srp-mark-grid">

                                <!-- Mark -->

                                <div class="srp-mark-box green">

                                    <h4>
                                        Mark
                                    </h4>

                                    <p>
                                        <?php echo esc_html($course->mark_obtained); ?>
                                    </p>

                                </div>

                                <!-- Grade -->

                                <div class="srp-mark-box blue">

                                    <h4>
                                        Grade
                                    </h4>

                                    <p>
                                        <?php echo esc_html($course->grade); ?>
                                    </p>

                                </div>

                            </div>

                            <!-- Certificate -->

                            <a
                                href="<?php echo esc_url($course->certificate_url); ?>"
                                target="_blank"
                                class="srp-btn"
                                style="
                                    width:100%;
                                "
                            >
                                Download Certificate
                            </a>

                        </div>

                    <?php endforeach; ?>

                <?php else : ?>

                    <div class="srp-not-found">

                        No Course Found

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<?php wp_footer(); ?>

</body>

</html>