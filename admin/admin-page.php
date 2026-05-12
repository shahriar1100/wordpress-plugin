<?php

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

/*
|--------------------------------------------------------------------------
| Tables
|--------------------------------------------------------------------------
*/

$students_table = $wpdb->prefix . 'srp_students';

$courses_table = $wpdb->prefix . 'srp_courses';

/*
|--------------------------------------------------------------------------
| Available Courses
|--------------------------------------------------------------------------
*/

$available_courses = array(

    'Professional Electrical House Wiring',

    'Professional Electrical Industrial Wiring',

    'Electrical VFD and Motor Controlling',

    'Refrigeration and Air Conditioning-(RAC)',

    'CCTV Camera Installation and Maintenance'
);

/*
|--------------------------------------------------------------------------
| Edit Student
|--------------------------------------------------------------------------
*/

$edit_student = null;

if (isset($_GET['edit_student'])) {

    $student_id = intval($_GET['edit_student']);

    $edit_student = $wpdb->get_row(
        $wpdb->prepare(
            "
            SELECT *
            FROM {$students_table}
            WHERE id = %d
            ",
            $student_id
        )
    );
}

/*
|--------------------------------------------------------------------------
| Edit Course
|--------------------------------------------------------------------------
*/

$edit_course = null;

if (isset($_GET['edit_course'])) {

    $course_id = intval($_GET['edit_course']);

    $edit_course = $wpdb->get_row(
        $wpdb->prepare(
            "
            SELECT *
            FROM {$courses_table}
            WHERE id = %d
            ",
            $course_id
        )
    );
}

/*
|--------------------------------------------------------------------------
| Save Student
|--------------------------------------------------------------------------
*/

if (isset($_POST['srp_save_student'])) {

    $student_id = intval($_POST['student_id']);

    $roll = sanitize_text_field(
        $_POST['roll']
    );

    $student_name = sanitize_text_field(
        $_POST['student_name']
    );

    $father_name = sanitize_text_field(
        $_POST['father_name']
    );

    /*
    |--------------------------------------------------------------------------
    | Update Student
    |--------------------------------------------------------------------------
    */

    if ($student_id > 0) {

        $wpdb->update(

            $students_table,

            array(

                'roll' => $roll,

                'student_name' => $student_name,

                'father_name' => $father_name

            ),

            array(

                'id' => $student_id

            )
        );

    } else {

        /*
        |--------------------------------------------------------------------------
        | Duplicate Roll Check
        |--------------------------------------------------------------------------
        */

        $existing_student = $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT *
                FROM {$students_table}
                WHERE roll = %s
                ",
                $roll
            )
        );

        if (!$existing_student) {

            $wpdb->insert(

                $students_table,

                array(

                    'roll' => $roll,

                    'student_name' => $student_name,

                    'father_name' => $father_name
                )
            );
        }
    }

    echo '
        <div class="updated">
            <p>Student saved successfully.</p>
        </div>
    ';
}

/*
|--------------------------------------------------------------------------
| Save Course
|--------------------------------------------------------------------------
*/

if (isset($_POST['srp_save_course'])) {

    $course_id = intval($_POST['course_id']);

    $student_id = intval($_POST['course_student_id']);

    $course_name = sanitize_text_field(
        $_POST['course_name']
    );

    $mark_obtained = sanitize_text_field(
        $_POST['mark_obtained']
    );

    $grade = sanitize_text_field(
        $_POST['grade']
    );

    $certificate_url = esc_url(
        $_POST['certificate_url']
    );

    /*
    |--------------------------------------------------------------------------
    | Update Course
    |--------------------------------------------------------------------------
    */

    if ($course_id > 0) {

        $wpdb->update(

            $courses_table,

            array(

                'course_name' => $course_name,

                'mark_obtained' => $mark_obtained,

                'grade' => $grade,

                'certificate_url' => $certificate_url

            ),

            array(

                'id' => $course_id

            )
        );

    } else {

        /*
        |--------------------------------------------------------------------------
        | Duplicate Prevention
        |--------------------------------------------------------------------------
        */

        $existing_course = $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT *
                FROM {$courses_table}

                WHERE student_id = %d
                AND course_name = %s
                ",
                $student_id,
                $course_name
            )
        );

        if (!$existing_course) {

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
    }

    echo '
        <div class="updated">
            <p>Course saved successfully.</p>
        </div>
    ';
}

/*
|--------------------------------------------------------------------------
| Delete Course
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete_course'])) {

    $course_id = intval($_GET['delete_course']);

    $wpdb->delete(

        $courses_table,

        array(
            'id' => $course_id
        )
    );

    echo '
        <div class="updated">
            <p>Course deleted successfully.</p>
        </div>
    ';
}

/*
|--------------------------------------------------------------------------
| Get Students
|--------------------------------------------------------------------------
*/

$students = $wpdb->get_results(
    "
    SELECT *
    FROM {$students_table}
    ORDER BY id DESC
    "
);

?>

<div class="wrap">

    <h1
        style="
            font-size:34px;
            font-weight:800;
            margin-bottom:10px;
        "
    >
        Student Result System
    </h1>

    <p
        style="
            color:#666;
            margin-bottom:25px;
        "
    >
        Shortcode:
        <strong>
            [student_result_search]
        </strong>
    </p>

    <!-- STUDENT FORM -->

    <div class="srp-admin-card">

        <h2>
            <?php echo $edit_student ? 'Edit Student' : 'Add Student'; ?>
        </h2>

        <form method="POST">

            <input
                type="hidden"
                name="student_id"
                value="<?php echo $edit_student ? esc_attr($edit_student->id) : ''; ?>"
            >

            <table class="form-table">

                <!-- Roll -->

                <tr>

                    <th>
                        Student Roll
                    </th>

                    <td>

                        <input
                            type="text"
                            name="roll"
                            required
                            class="regular-text"
                            value="<?php echo $edit_student ? esc_attr($edit_student->roll) : ''; ?>"
                        >

                    </td>

                </tr>

                <!-- Name -->

                <tr>

                    <th>
                        Student Name
                    </th>

                    <td>

                        <input
                            type="text"
                            name="student_name"
                            required
                            class="regular-text"
                            value="<?php echo $edit_student ? esc_attr($edit_student->student_name) : ''; ?>"
                        >

                    </td>

                </tr>

                <!-- Father -->

                <tr>

                    <th>
                        Father's Name
                    </th>

                    <td>

                        <input
                            type="text"
                            name="father_name"
                            required
                            class="regular-text"
                            value="<?php echo $edit_student ? esc_attr($edit_student->father_name) : ''; ?>"
                        >

                    </td>

                </tr>

            </table>

            <?php

            submit_button(
                $edit_student
                    ? 'Update Student'
                    : 'Save Student',
                'primary',
                'srp_save_student'
            );

            ?>

        </form>

    </div>

    <!-- ADD COURSE -->

    <div class="srp-admin-card">

        <h2>
            <?php echo $edit_course ? 'Edit Course' : 'Add Course'; ?>
        </h2>

        <form method="POST">

            <input
                type="hidden"
                name="course_id"
                value="<?php echo $edit_course ? esc_attr($edit_course->id) : ''; ?>"
            >

            <table class="form-table">

                <!-- Student -->

                <tr>

                    <th>
                        Select Student
                    </th>

                    <td>

                        <select
                            name="course_student_id"
                            required
                        >

                            <option value="">
                                Select Student
                            </option>

                            <?php foreach ($students as $student) : ?>

                                <option
                                    value="<?php echo esc_attr($student->id); ?>"
                                    <?php selected(
                                        $edit_course ? $edit_course->student_id : '',
                                        $student->id
                                    ); ?>
                                >
                                    <?php echo esc_html($student->roll . ' - ' . $student->student_name); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </td>

                </tr>

                <!-- Course -->

                <tr>

                    <th>
                        Course Name
                    </th>

                    <td>

                        <div class="srp-course-grid">

                            <?php foreach ($available_courses as $course) : ?>

                                <label class="srp-course-item">

                                    <input
                                        type="radio"
                                        name="course_name"
                                        required
                                        value="<?php echo esc_attr($course); ?>"
                                        <?php checked(
                                            $edit_course ? $edit_course->course_name : '',
                                            $course
                                        ); ?>
                                    >

                                    <span>
                                        <?php echo esc_html($course); ?>
                                    </span>

                                </label>

                            <?php endforeach; ?>

                        </div>

                    </td>

                </tr>

                <!-- Mark -->

                <tr>

                    <th>
                        Mark
                    </th>

                    <td>

                        <input
                            type="text"
                            name="mark_obtained"
                            required
                            class="regular-text"
                            value="<?php echo $edit_course ? esc_attr($edit_course->mark_obtained) : ''; ?>"
                        >

                    </td>

                </tr>

                <!-- Grade -->

                <tr>

                    <th>
                        Grade
                    </th>

                    <td>

                        <input
                            type="text"
                            name="grade"
                            required
                            class="regular-text"
                            value="<?php echo $edit_course ? esc_attr($edit_course->grade) : ''; ?>"
                        >

                    </td>

                </tr>

                <!-- Certificate -->

                <tr>

                    <th>
                        Certificate URL
                    </th>

                    <td>

                        <input
                            type="url"
                            name="certificate_url"
                            required
                            class="regular-text"
                            value="<?php echo $edit_course ? esc_attr($edit_course->certificate_url) : ''; ?>"
                        >

                    </td>

                </tr>

            </table>

            <?php

            submit_button(
                $edit_course
                    ? 'Update Course'
                    : 'Add Course',
                'primary',
                'srp_save_course'
            );

            ?>

        </form>

    </div>

    <!-- COURSE TABLE -->

    <div class="srp-admin-card">

        <h2>
            Student Courses
        </h2>

        <table class="widefat striped">

            <thead>

                <tr>

                    <th>Roll</th>

                    <th>Student</th>

                    <th>Father</th>

                    <th>Course</th>

                    <th>Mark</th>

                    <th>Grade</th>

                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

                <?php

                $results = $wpdb->get_results(
                    "
                    SELECT

                        s.id as student_id,
                        s.roll,
                        s.student_name,
                        s.father_name,

                        c.id as course_id,
                        c.course_name,
                        c.mark_obtained,
                        c.grade,
                        c.certificate_url

                    FROM {$students_table} s

                    LEFT JOIN {$courses_table} c
                    ON s.id = c.student_id

                    ORDER BY c.id DESC
                    "
                );

                if ($results) :

                    foreach ($results as $row) :

                        $student_url = home_url(
                            '/srp-verification/' . $row->roll
                        );

                ?>

                        <tr>

                            <td>
                                <?php echo esc_html($row->roll); ?>
                            </td>

                            <td>
                                <?php echo esc_html($row->student_name); ?>
                            </td>

                            <td>
                                <?php echo esc_html($row->father_name); ?>
                            </td>

                            <td>
                                <?php echo esc_html($row->course_name); ?>
                            </td>

                            <td>
                                <?php echo esc_html($row->mark_obtained); ?>
                            </td>

                            <td>
                                <?php echo esc_html($row->grade); ?>
                            </td>

                            <td>

                                <!-- Edit Student -->

                                <a
                                    href="?page=student-result-pro&edit_student=<?php echo $row->student_id; ?>"
                                    class="button"
                                >
                                    Edit Student
                                </a>

                                <!-- Edit Course -->

                                <a
                                    href="?page=student-result-pro&edit_course=<?php echo $row->course_id; ?>"
                                    class="button button-primary"
                                >
                                    Edit Course
                                </a>

                                <!-- Delete -->

                                <a
                                    href="?page=student-result-pro&delete_course=<?php echo $row->course_id; ?>"
                                    class="button button-secondary"
                                    onclick="return confirm('Are you sure?')"
                                >
                                    Delete
                                </a>

                                <!-- View -->

                                <a
                                    href="<?php echo esc_url($student_url); ?>"
                                    target="_blank"
                                    class="button"
                                >
                                    View
                                </a>

                                <!-- Copy URL -->

                                <button
                                    type="button"
                                    class="button"
                                    onclick="copyStudentURL(this)"
                                    data-url="<?php echo esc_url($student_url); ?>"
                                >
                                    Copy URL
                                </button>

                            </td>

                        </tr>

                <?php

                    endforeach;

                else :

                ?>

                    <tr>

                        <td colspan="7">

                            No Data Found

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>