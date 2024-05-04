const requestInvoker = new RequestInvoker(apiUrl);
let tokenObj;

//region Taken courses

function takenCourses(contentElement) {
    contentElement.empty();

    localStorage.setItem('student', 'taken-courses');

    $('#navbar .active').removeClass('active');
    $('#navbar-taken-courses').addClass('active');
    $('#navbar-courses').addClass('active');

    contentElement.append($('<h2>').text('Statistics'));

    let table = $("<table>").addClass("editTable");
    let cell_ki = $("<td>").attr('id', 'calc-ki');
    let cell_kki = $("<td>").attr('id', 'calc-kki');

    table.append(
        $("<tr>").append(
            $("<th>").text("Credit index:"),
            cell_ki
        ),
        $("<tr>").append(
            $("<th>").text("Korigalt Credit index:"),
            cell_kki
        )
    );

    let studentObj = JSON.parse(localStorage.getItem('studentVals'));

    requestInvoker.executePost('course/averages', {
        token: tokenObj.token,
        programmeName: studentObj.programme.name,
        programmeType: studentObj.programme.type
    }).then((response) => {
        $('#calc-ki').text(response.ki);
        $('#calc-kki').text(response.kki);
    });

    contentElement.append(table);

    contentElement.append($('<h1>').text('Taken courses'));

    requestInvoker.executePost('course/taken', {
        token: tokenObj.token,
        programmeName: studentObj.programme.name,
        programmeType: studentObj.programme.type
    }).then((response) => {
        const tableColumns = {
            id: 'Id',
            name: 'Name',
            term: 'Term',
            schedule: 'Schedule',
            grade: 'Grade',
        };

        const coursesTable= new DropDownTable(tableColumns, response._embedded.takenCourses,
            (record) => { return takenCourseDetails(record, contentElement)}).build();
        contentElement.append(coursesTable);
    });
}

function takenCourseDetails(record, contentElement) {
    let container = $('<div>');

    container.append($('<div>').prop('id', 'drop-course-error').addClass('hidden error'));
    container.append(new Button('Drop course', () => { removeTakenCourse(record, contentElement) }).build());

    return container;
}

function removeTakenCourse(record, contentElement) {
    $('#drop-course-error').addClass('hidden');

    let studentObj = JSON.parse(localStorage.getItem('studentVals'));

    requestInvoker.executePost(record._links.unregister.href, {
        token: tokenObj.token,
        id: record.courseId,
        subjectId: record.subjectId,
        programmeName: studentObj.programme.name,
        programmeType: studentObj.programme.type
    }).then((response) => {
        if (response._success) subjects(contentElement);
        else $('#drop-course-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

//endregion

function courseRegistration(contentElement) {
    contentElement.empty();

    localStorage.setItem('student', 'course-registration');

    $('#navbar .active').removeClass('active');
    $('#navbar-course-registration').addClass('active');
    $('#navbar-courses').addClass('active');

    contentElement.append($('<h1>').text('Register courses'));

    requestInvoker.executePost('subject/all', { token: tokenObj.token }).then((response) => {
        const tableColumns = {
            id: 'Id',
            name: 'Name',
            approval: 'Approval needed',
            credit: 'Credit',
            type: 'Type'
        };

        const subjectsTable= new DropDownTable(tableColumns, response._embedded.subjects, (record) => { return subjectDetails(record, contentElement)}).build();
        contentElement.append(subjectsTable);
    });
}

function subjectDetails(record, contentElement) {
    let container = $('<div>');

    requestInvoker.executePost(record._links.courses.href, { token: tokenObj.token }).then((response) => {
        const tableColumns = {
            id: 'Id',
            capacity: 'Capacity',
            schedule: 'Schedule',
            term: 'Term',
        };

        const coursesTable= new DropDownTable(tableColumns, response._embedded.courses,
            (courseRecord) => { return courseDetails(courseRecord, contentElement) }).build();
        container.append(coursesTable);
    });

    return container;
}

function courseDetails(record, contentElement) {
    let container = $('<div>');

    container.append($('<div>').prop('id', 'register-course-error').addClass('hidden error'));
    container.append(new Button('Register', () => { addTakenCourse(record, contentElement) }).build());

    return container;
}

function addTakenCourse(record, contentElement) {
    $('#register-course-error').addClass('hidden');

    let studentObj = JSON.parse(localStorage.getItem('studentVals'));

    requestInvoker.executePost(record._links.register.href, {
        token: tokenObj.token,
        id: record.id,
        subjectId: record.subject.id,
        programmeName: studentObj.programme.name,
        programmeType: studentObj.programme.type
    }).then((response) => {
        if (response._success) subjects(contentElement);
        else $('#drop-course-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function takenExams(contentElement) {
    contentElement.empty();

    localStorage.setItem('student', 'taken-exams');

    $('#navbar .active').removeClass('active');
    $('#navbar-taken-exam').addClass('active');
    $('#navbar-exams').addClass('active');

    contentElement.append($('<h1>').text('Taken exams'));

    let studentObj = JSON.parse(localStorage.getItem('studentVals'));

    // TODO: finish it, the whole thing bruv
    requestInvoker.executePost('exam/takenExams', {
        token: tokenObj.token,
        programmeName: studentObj.programme.name,
        programmeType: studentObj.programme.type
    }).then((response) => {
        const tableColumns = {
            id: 'Id',
            name: 'Name',
            term: 'Term',
            schedule: 'Schedule',
            credit: 'Credit',
        };

        const coursesTable= new DropDownTable(tableColumns, response._embedded.takenCourses,
            (record) => { return takenCourseDetails(record, contentElement)}).build();
        contentElement.append(coursesTable);
    });
}

function examRegistration(contentElement) {
    contentElement.empty();

    localStorage.setItem('student', 'exam-registration');

    $('#navbar .active').removeClass('active');
    $('#navbar-exam-registration').addClass('active');
    $('#navbar-exams').addClass('active');

}

function self(contentElement) {
    new SelfPage(contentElement, tokenObj._links.user.href, 'student').build();
}

$(() => {
    tokenObj = init(requestInvoker, 'student');
    const contentElement = $('#content');

    const site = localStorage.getItem('student');
    switch (site) {
        case 'taken-courses': takenCourses(contentElement); break;
        case 'course-registration': courseRegistration(contentElement); break;
        case 'taken-exams': takenExams(contentElement); break;
        case 'exam-registration': examRegistration(contentElement); break;
        case 'self': self(contentElement); break;
    }

    bindClickListener($('#navbar-courses'), () => { takenCourses(contentElement); });
    bindClickListener($('#navbar-taken-courses'), () => { takenCourses(contentElement); });
    bindClickListener($('#navbar-course-registration'), () => { courseRegistration(contentElement); });
    bindClickListener($('#navbar-exams'), () => { takenExams(contentElement); });
    bindClickListener($('#navbar-taken-exam'), () => { takenExams(contentElement); });
    bindClickListener($('#navbar-exam-registration'), () => { examRegistration(contentElement); });
    bindClickListener($('#navbar-username'), () => { self(contentElement); });
});