<?php

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$students_table = $wpdb->prefix . 'srp_students';
$courses_table = $wpdb->prefix . 'srp_courses';

$edit_data = null;

/*
|--------------------------------------------------------------------------
| Delete Student
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $delete_roll = sanitize_text_field($_GET['delete']);

    $student = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$students_table} WHERE roll = %s",
            $delete_roll
        )
    );

    if ($student) {

        // Delete all courses
        $wpdb->delete(
            $courses_table,
            array(
                'student_id' => $student->id
            )
        );

        // Delete student
        $wpdb->delete(
            $students_table,
            array(
                'id' => $student->id
            )
        );

        echo '
            <div class="updated">
                <p>Student Deleted Successfully!</p>
            </div>
        ';
    }
}

/*
|--------------------------------------------------------------------------
| Edit Student
|--------------------------------------------------------------------------
*/

if (isset($_GET['edit'])) {

    $edit_roll = sanitize_text_field($_GET['edit']);

    $edit_data = $wpdb->get_row(
        $wpdb->prepare(
            "
            SELECT 
                s.*,
                c.course_name,
                c.mark_obtained,
                c.grade,
                c.certificate_url

            FROM {$students_table} s

            LEFT JOIN {$courses_table} c
            ON s.id = c.student_id

            WHERE s.roll = %s

            ORDER BY c.id DESC

            LIMIT 1
            ",
            $edit_roll
        )
    );
}

/*
|--------------------------------------------------------------------------
| Save Student
|--------------------------------------------------------------------------
*/

if (isset($_POST['srp_save_student'])) {

    $roll = sanitize_text_field($_POST['roll']);

    $student_name = sanitize_text_field($_POST['student_name']);

    $father_name = sanitize_text_field($_POST['father_name']);

    $course_name = sanitize_text_field($_POST['course_name']);

    $mark_obtained = sanitize_text_field($_POST['mark_obtained']);

    $grade = sanitize_text_field($_POST['grade']);

    $certificate_url = esc_url($_POST['certificate_url']);

    $existing_student = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $students_table WHERE roll = %s",
            $roll
        )
    );

    /*
    |--------------------------------------------------------------------------
    | Existing Student
    |--------------------------------------------------------------------------
    */

    if ($existing_student) {

        $student_id = $existing_student->id;

        // Update student info
        $wpdb->update(
            $students_table,
            array(
                'student_name' => $student_name,
                'father_name' => $father_name
            ),
            array(
                'id' => $student_id
            )
        );

        // Add new course
        $wpdb->insert(
            $courses_table,
            array(
                'student_id' => $student_id,
                'course_name' => $course_name,
                'mark_obtained' => $mark_obtained,
                'grade' => $grade,
                'certificate_url' => $certificate_url
            )
        );

    } else {

        /*
        |--------------------------------------------------------------------------
        | New Student
        |--------------------------------------------------------------------------
        */

        $wpdb->insert(
            $students_table,
            array(
                'roll' => $roll,
                'student_name' => $student_name,
                'father_name' => $father_name
            )
        );

        $student_id = $wpdb->insert_id;

        // Insert first course
        $wpdb->insert(
            $courses_table,
            array(
                'student_id' => $student_id,
                'course_name' => $course_name,
                'mark_obtained' => $mark_obtained,
                'grade' => $grade,
                'certificate_url' => $certificate_url
            )
        );
    }

    echo '
        <div class="updated">
            <p>Student Saved Successfully!</p>
        </div>
    ';
}

?>

<div class="wrap">

    <h1>Student Result System</h1>

    <p>
        Use this shortcode:
        <strong>[student_result_search]</strong>
    </p>

    <hr>

    <form method="POST">

        <table class="form-table">

            <!-- Roll -->

            <tr>

                <th>Student Roll</th>

                <td>

                    <input
                        type="text"
                        name="roll"
                        required
                        class="regular-text"
                        value="<?php echo $edit_data ? esc_attr($edit_data->roll) : ''; ?>"
                    >

                </td>

            </tr>

            <!-- Student Name -->

            <tr>

                <th>Student Name</th>

                <td>

                    <input
                        type="text"
                        name="student_name"
                        required
                        class="regular-text"
                        value="<?php echo $edit_data ? esc_attr($edit_data->student_name) : ''; ?>"
                    >

                </td>

            </tr>

            <!-- Father Name -->

            <tr>

                <th>Father Name</th>

                <td>

                    <input
                        type="text"
                        name="father_name"
                        required
                        class="regular-text"
                        value="<?php echo $edit_data ? esc_attr($edit_data->father_name) : ''; ?>"
                    >

                </td>

            </tr>

            <!-- Course Name -->

            <tr>

                <th>Course Name</th>

                <td>

                    <input
                        type="text"
                        name="course_name"
                        required
                        class="regular-text"
                        value="<?php echo $edit_data ? esc_attr($edit_data->course_name) : ''; ?>"
                    >

                </td>

            </tr>

            <!-- Mark -->

            <tr>

                <th>Mark</th>

                <td>

                    <input
                        type="text"
                        name="mark_obtained"
                        required
                        class="regular-text"
                        value="<?php echo $edit_data ? esc_attr($edit_data->mark_obtained) : ''; ?>"
                    >

                </td>

            </tr>

            <!-- Grade -->

            <tr>

                <th>Grade</th>

                <td>

                    <input
                        type="text"
                        name="grade"
                        required
                        class="regular-text"
                        value="<?php echo $edit_data ? esc_attr($edit_data->grade) : ''; ?>"
                    >

                </td>

            </tr>

            <!-- Certificate URL -->

            <tr>

                <th>Certificate URL</th>

                <td>

                    <input
                        type="url"
                        name="certificate_url"
                        required
                        class="regular-text"
                        value="<?php echo $edit_data ? esc_attr($edit_data->certificate_url) : ''; ?>"
                    >

                </td>

            </tr>

        </table>

        <?php submit_button('Save Student & Course', 'primary', 'srp_save_student'); ?>

    </form>

    <hr>

    <h2>Saved Students</h2>

    <table class="widefat striped">

        <thead>

            <tr>

                <th>Roll</th>

                <th>Name</th>

                <th>Father Name</th>

                <th>Courses</th>

                <th>Marks</th>

                <th>Grades</th>

                <th>Edit</th>

                <th>Delete</th>

            </tr>

        </thead>

        <tbody>

            <?php

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            $per_page = 10;

            $current_page = isset($_GET['paged'])
                ? max(1, intval($_GET['paged']))
                : 1;

            $offset = ($current_page - 1) * $per_page;

            /*
            |--------------------------------------------------------------------------
            | Total Students
            |--------------------------------------------------------------------------
            */

            $total_students = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$students_table}"
            );

            $total_pages = ceil($total_students / $per_page);

            /*
            |--------------------------------------------------------------------------
            | Get Students
            |--------------------------------------------------------------------------
            */

            $students = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT * FROM {$students_table}
                    ORDER BY id DESC
                    LIMIT %d OFFSET %d
                    ",
                    $per_page,
                    $offset
                )
            );

            if ($students) :

                foreach ($students as $student) :

                    $courses = $wpdb->get_results(
                        $wpdb->prepare(
                            "
                            SELECT * FROM {$courses_table}
                            WHERE student_id = %d
                            ",
                            $student->id
                        )
                    );
            ?>

                <tr>

                    <!-- Roll -->

                    <td>
                        <?php echo esc_html($student->roll); ?>
                    </td>

                    <!-- Name -->

                    <td>
                        <?php echo esc_html($student->student_name); ?>
                    </td>

                    <!-- Father -->

                    <td>
                        <?php echo esc_html($student->father_name); ?>
                    </td>

                    <!-- Courses -->

                    <td>

                        <?php

                        if ($courses) :

                            foreach ($courses as $course) :
                        ?>

                            <div style="margin-bottom:10px;">

                                <strong>
                                    <?php echo esc_html($course->course_name); ?>
                                </strong>

                            </div>

                        <?php
                            endforeach;

                        endif;
                        ?>

                    </td>

                    <!-- Marks -->

                    <td>

                        <?php

                        if ($courses) :

                            foreach ($courses as $course) :
                        ?>

                            <div style="margin-bottom:10px;">

                                <?php echo esc_html($course->mark_obtained); ?>

                            </div>

                        <?php
                            endforeach;

                        endif;
                        ?>

                    </td>

                    <!-- Grades -->

                    <td>

                        <?php

                        if ($courses) :

                            foreach ($courses as $course) :
                        ?>

                            <div style="margin-bottom:10px;">

                                <?php echo esc_html($course->grade); ?>

                            </div>

                        <?php
                            endforeach;

                        endif;
                        ?>

                    </td>

                    <!-- Edit -->

                    <td>

                        <a
                            href="?page=student-result-pro&edit=<?php echo $student->roll; ?>"
                            class="button button-primary"
                        >
                            Edit
                        </a>

                    </td>

                    <!-- Delete -->

                    <td>

                        <a
                            href="?page=student-result-pro&delete=<?php echo $student->roll; ?>"
                            class="button button-secondary"
                            onclick="return confirm('Are you sure?')"
                        >
                            Delete
                        </a>

                    </td>

                </tr>

            <?php

                endforeach;

            else :
            ?>

                <tr>

                    <td colspan="8">
                        No Student Found
                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

    <!-- Pagination -->

    <div style="margin-top:20px;">

        <?php

        echo paginate_links(array(

            'base' => add_query_arg('paged', '%#%'),

            'format' => '',

            'prev_text' => '« Prev',

            'next_text' => 'Next »',

            'total' => $total_pages,

            'current' => $current_page

        ));

        ?>

    </div>

</div>