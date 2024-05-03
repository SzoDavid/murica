const requestInvoker = new RequestInvoker(apiUrl);
let tokenObj;

//region Courses
function courses(contentElement) {
    contentElement.empty();

    localStorage.setItem('teacher', 'courses');

    $('#navbar .active').removeClass('active');
    $('#navbar-courses').addClass('active');

    requestInvoker.executePost('course/byTeacher', { token: tokenObj.token }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
            return;
        }

        const tableColumns = {
            id: 'Id',
            name: 'Name',
            capacity: 'Capacity',
            schedule: 'Schedule',
            term: 'Term',
            room: 'Room'
        };

        const coursesTable= new DropDownTable(tableColumns, response._embedded.courses, (record) => {return courseDetails(record, contentElement)}).build();
        contentElement.append(coursesTable);
    });
}

function courseDetails(course, contentElement) {
    let container = $('<div>');

    requestInvoker.executePost(course._links.students.href, { token: tokenObj.token, courseId: course.course.id, subjectId: course.course.subject.id }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
            return;
        }

        const tableColumns = {
            userId: 'Id',
            userName: 'Name',
            userProgramme: 'Programme',
            grade: 'Grade'
        };

        if (course.course.subject.approval) tableColumns.approvedVisual = 'Approved';

        const studentsTable = new DropDownTable(tableColumns, response._embedded.students, (record) => {return courseStudentDetails(record, contentElement)}).build();
        container.append(studentsTable);
    })

    return container;
}

function courseStudentDetails(student, contentElement) {
    let container = $('<div>');

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "student-details-approved").text("Approved:")),
            $("<td>").append($("<input>").attr({ id: "student-details-approved", type: "checkbox", checked: student.approved, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", 'student-details-grade').text("Grade:")),
            $("<td>").append($('<select>').attr('id', 'student-details-grade').append(
                $('<option>').prop({value: null}).text('-'),
                $('<option>').prop({value: 1, selected: student.grade == 1}).text('1'),
                $('<option>').prop({value: 2, selected: student.grade == 2}).text('2'),
                $('<option>').prop({value: 3, selected: student.grade == 3}).text('3'),
                $('<option>').prop({value: 4, selected: student.grade == 4}).text('4'),
                $('<option>').prop({value: 5, selected: student.grade == 5}).text('5'),
            ))
        ),
    );

    container.append(table);

    container.append($('<div>').prop('id', 'student-details-error').addClass('hidden error'));

    container.append(new Button('Save', () => { saveCourseStudent(student, contentElement) }).build());

    return container;
}

function saveCourseStudent(course, contentElement) {
    $('#student-details-error').addClass('hidden');

    let args = {
        token: tokenObj.token,
        subjectId: course.course.subject.id,
        courseId: course.course.id,
        studentId: course.student.user.id,
        programmeName: course.student.programme.name,
        programmeType: course.student.programme.type,
        approved: $('#student-details-approved').is(":checked"),
    }

    let grade = $('#student-details-grade :selected').val();
    if (grade !== 'null') args.grade = grade;

    requestInvoker.executePost(course._links.updateResults.href, args).then((response) => {
        if (response._success) courses(contentElement);
        else $('#student-details-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}
//endregion

//region Exams
function exams(contentElement) {
    contentElement.empty();

    localStorage.setItem('teacher', 'exams');

    $('#navbar .active').removeClass('active');
    $('#navbar-exams').addClass('active');

    requestInvoker.executePost('exam/teachExams', { token: tokenObj.token }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
            return;
        }

        const tableColumns = {
            id: 'Id',
            subjectName: 'Subject',
            startTerm: 'Start',
            endTerm: 'End',
            roomId: 'Room',
        };

        const coursesTable= new Table(tableColumns, response._embedded.courses).build();
        contentElement.append(coursesTable);
    });
}
//endregion

function self(contentElement) {
    new SelfPage(contentElement, tokenObj._links.user.href, 'teacher').build();
}

$(() => {
    tokenObj = init(requestInvoker, 'teacher');
    const contentElement = $('#content');

    const site = localStorage.getItem('teacher');
    switch (site) {
        case 'courses': courses(contentElement); break;
        case 'exams': exams(contentElement); break;
        case 'self': self(contentElement); break;
    }

    bindClickListener($('#navbar-courses'), () => { courses(contentElement); });
    bindClickListener($('#navbar-exams'), () => { exams(contentElement); });
    bindClickListener($('#navbar-username'), () => { self(contentElement); });
});