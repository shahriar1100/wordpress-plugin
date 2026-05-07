<?php

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

/*
|--------------------------------------------------------------------------
| Database Tables
|--------------------------------------------------------------------------
*/

$students_table = $wpdb->prefix . 'srp_students';

$courses_table = $wpdb->prefix . 'srp_courses';

/*
|--------------------------------------------------------------------------
| Get Roll From URL
|--------------------------------------------------------------------------
*/

$roll = sanitize_text_field(
    get_query_var('student_roll')
);

/*
|--------------------------------------------------------------------------
| Empty Roll
|--------------------------------------------------------------------------
*/

if (!$roll) {
    return;
}

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

    get_header();

    ?>

    <div class="srp-container">

        <div class="srp-not-found">

            No student verification record found.

        </div>

    </div>

    <?php

    get_footer();

    return;
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

get_header();

?>

<div class="srp-container srp-single-page">

    <!-- Hero Section -->

    <div class="srp-hero-section">

        <div class="srp-hero-badge">
            VERIFIED STUDENT PROFILE
        </div>

        <h1>
            STUDENT RESULT <br>
            & CERTIFICATE
        </h1>

        <p>
            Official student verification profile of
            <?php echo esc_html($student->student_name); ?>.
        </p>

    </div>

    <!-- Main Card -->

    <div class="srp-card">

        <!-- Header -->

        <div class="srp-header">

            <h1>
                Student Information
            </h1>

            <p>
                Roll Number:
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

            <!-- Student Verification URL -->


            <!-- Courses -->

            <?php

            if ($courses) :

                foreach ($courses as $course) :
            ?>

                <div class="srp-course-card">

                    <!-- Course -->

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

            <?php

                endforeach;

            else :
            ?>

                <div class="srp-not-found">

                    No Course Found

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php get_footer(); ?>