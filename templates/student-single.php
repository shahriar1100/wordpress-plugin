<?php

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$roll = get_query_var('student_roll');

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

<div class="srp-single-page">

    <div class="srp-card">

        <!-- Header -->

        <div class="srp-header">

            <h1>
                Student Verification
            </h1>

            <p>
                Roll:
                <?php echo esc_html($student->roll); ?>
            </p>

        </div>

        <!-- Body -->

        <div class="srp-body">

            <!-- Student Name -->

            <div class="srp-box">

                <h2>
                    Student Name
                </h2>

                <p>
                    <?php echo esc_html($student->student_name); ?>
                </p>

            </div>

            <!-- Father Name -->

            <div class="srp-box">

                <h2>
                    Father's Name
                </h2>

                <p>
                    <?php echo esc_html($student->father_name); ?>
                </p>

            </div>

            <!-- Courses -->

            <?php if ($courses) : ?>

                <?php foreach ($courses as $course) : ?>

                    <div class="srp-course-card">

                        <!-- Course Name -->

                        <div class="srp-course-top">

                            <h3>
                                COURSE
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
                        >
                            Download Certificate
                        </a>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php wp_footer(); ?>

</body>

</html>