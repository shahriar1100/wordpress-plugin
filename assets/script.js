jQuery(document).ready(function ($) {

    /*
    |--------------------------------------------------------------------------
    | Search Student
    |--------------------------------------------------------------------------
    */

    $('#srp-search-btn').on('click', function () {

        let roll = $('#srp-roll').val().trim();

        /*
        |--------------------------------------------------------------------------
        | Empty Roll
        |--------------------------------------------------------------------------
        */

        if (roll === '') {

            $('#srp-result').html(`
                <div class="srp-not-found">
                    Please enter a roll number.
                </div>
            `);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Loading State
        |--------------------------------------------------------------------------
        */

        $('#srp-result').html(`
            <div class="srp-search-card">
                <h2 style="text-align:center;">
                    Searching...
                </h2>
            </div>
        `);

        /*
        |--------------------------------------------------------------------------
        | AJAX Request
        |--------------------------------------------------------------------------
        */

        $.ajax({

            url: srp_ajax_obj.ajax_url,

            type: 'POST',

            data: {

                action: 'srp_search_student',

                roll: roll
            },

            success: function (response) {

                /*
                |--------------------------------------------------------------------------
                | Student Found
                |--------------------------------------------------------------------------
                */

                if (response.success) {

                    let student = response.data.student;

                    let courses = response.data.courses;

                    let coursesHTML = '';

                    /*
                    |--------------------------------------------------------------------------
                    | Course Loop
                    |--------------------------------------------------------------------------
                    */

                    courses.forEach(function (course) {

                        coursesHTML += `

                            <div class="srp-course-card">

                                <!-- Course -->

                                <div class="srp-course-top">

                                    <h3>
                                        COURSE
                                    </h3>

                                    <p>
                                        ${course.course_name}
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
                                            ${course.mark_obtained}
                                        </p>

                                    </div>

                                    <!-- Grade -->

                                    <div class="srp-mark-box blue">

                                        <h4>
                                            Grade
                                        </h4>

                                        <p>
                                            ${course.grade}
                                        </p>

                                    </div>

                                </div>

                                <!-- Certificate -->

                                <a
                                    href="${course.certificate_url}"
                                    target="_blank"
                                    class="srp-btn"
                                >
                                    Download Certificate
                                </a>

                            </div>

                        `;
                    });

                    /*
                    |--------------------------------------------------------------------------
                    | Final Result
                    |--------------------------------------------------------------------------
                    */

                    $('#srp-result').html(`

                        <div class="srp-card">

                            <!-- Header -->

                            <div class="srp-header">

                                <h1>
                                    Student Information
                                </h1>

                                <p>
                                    Roll Number:
                                    ${student.roll}
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
                                        ${student.student_name}
                                    </p>

                                </div>

                                <!-- Father Name -->

                                <div class="srp-box">

                                    <h2>
                                        Father's Name
                                    </h2>

                                    <p>
                                        ${student.father_name}
                                    </p>

                                </div>

                                <!-- Courses -->

                                ${coursesHTML}

                            </div>

                        </div>

                    `);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Student Not Found
                    |--------------------------------------------------------------------------
                    */

                    $('#srp-result').html(`
                        <div class="srp-not-found">
                            Student not found.
                        </div>
                    `);
                }
            },

            /*
            |--------------------------------------------------------------------------
            | AJAX Error
            |--------------------------------------------------------------------------
            */

            error: function () {

                $('#srp-result').html(`
                    <div class="srp-not-found">
                        Something went wrong.
                    </div>
                `);
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Enter Key Search
    |--------------------------------------------------------------------------
    */

    $('#srp-roll').on('keypress', function (e) {

        if (e.which === 13) {

            $('#srp-search-btn').click();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Course Item Active Class
    |--------------------------------------------------------------------------
    */

    $('.srp-course-item input').on('change', function () {

        $('.srp-course-item').removeClass('active');

        $(this)
            .closest('.srp-course-item')
            .addClass('active');
    });

});

/*
|--------------------------------------------------------------------------
| Copy Student URL
|--------------------------------------------------------------------------
*/

function copyStudentURL(button) {

    let url = button.getAttribute('data-url');

    /*
    |--------------------------------------------------------------------------
    | Copy Clipboard
    |--------------------------------------------------------------------------
    */

    navigator.clipboard.writeText(url);

    /*
    |--------------------------------------------------------------------------
    | Button Feedback
    |--------------------------------------------------------------------------
    */

    let originalText = button.innerText;

    button.innerText = 'Copied!';

    button.disabled = true;

    setTimeout(function () {

        button.innerText = originalText;

        button.disabled = false;

    }, 2000);
}