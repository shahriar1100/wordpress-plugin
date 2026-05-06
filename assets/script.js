jQuery(document).ready(function ($) {

    $('#srp-search-btn').on('click', function () {

        let roll = $('#srp-roll').val();

        if (roll === '') {

            $('#srp-result').html(`
                <div class="srp-not-found">
                    Please Enter Roll Number
                </div>
            `);

            return;
        }

        $.ajax({
            url: srp_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'srp_search_student',
                roll: roll
            },

            beforeSend: function () {

                $('#srp-result').html(`
                    <div class="srp-not-found">
                        Searching...
                    </div>
                `);
            },

            success: function (response) {

                if (response.success) {

                    let student = response.data.student;
                    let courses = response.data.courses;

                    let courseHTML = '';

                    courses.forEach(function (course, index) {

                        courseHTML += `

                            <div class="srp-course-card">

                                <div class="srp-course-top">
                                    <h3>Course ${index + 1}</h3>
                                    <p>${course.course_name}</p>
                                </div>

                                <div class="srp-mark-grid">

                                    <div class="srp-mark-box green">
                                        <h4>Mark</h4>
                                        <p>${course.mark_obtained}</p>
                                    </div>

                                    <div class="srp-mark-box blue">
                                        <h4>Grade</h4>
                                        <p>${course.grade}</p>
                                    </div>

                                </div>

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

                    $('#srp-result').html(`

                        <div class="srp-card">

                            <div class="srp-header">
                                <h1>Student Information</h1>
                                <p>Roll Number: ${student.roll}</p>
                            </div>

                            <div class="srp-body">

                                <div class="srp-box">
                                    <h2>Student Name</h2>
                                    <p>${student.student_name}</p>
                                </div>

                                <div class="srp-box">
                                    <h2>Father's Name</h2>
                                    <p>${student.father_name}</p>
                                </div>

                                ${courseHTML}

                            </div>

                        </div>
                    `);

                } else {

                    $('#srp-result').html(`
                        <div class="srp-not-found">
                            Student Not Found
                        </div>
                    `);
                }
            },

            error: function () {

                $('#srp-result').html(`
                    <div class="srp-not-found">
                        Something went wrong
                    </div>
                `);
            }
        });
    });
});