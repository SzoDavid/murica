const apiUrl = 'http://localhost/murica_api/';
const requestInvoker = new RequestInvoker(apiUrl);
let tokenObj;

//region Taken courses

function takenCourses(contentElement) {
    contentElement.empty();

    localStorage.setItem('student', 'taken-courses');

    $('#navbar .active').removeClass('active');
    $('#navbar-taken-courses').addClass('active');
    $('#navbar-courses').addClass('active');

    contentElement.append($('<h1>').text('Taken courses'));

    // TODO: add student data to request arguments
    requestInvoker.executePost('course/taken', { token: tokenObj.token }).then((response) => {
        console.log(response);
        const tableColumns = {
            id: 'Id',
            name: 'Name',
            term: 'Term',
            schedule: 'Schedule',
            credit: 'Credit',
        };

        const subjectsTable= new DropDownTable(tableColumns, response._embedded.takenCourses, (record) => { return takenCoursesDetails(record, contentElement)}).build();
        contentElement.append(subjectsTable);
    });
}

function takenCourseDetails(record, contentElement) {

}

//endregion

function courseRegistration(contentElement) {
    contentElement.empty();

    localStorage.setItem('student', 'course-registration');

    $('#navbar .active').removeClass('active');
    $('#navbar-course-registration').addClass('active');
    $('#navbar-courses').addClass('active');
}

function takenExams(contentElement) {
    contentElement.empty();

    localStorage.setItem('student', 'taken-exams');

    $('#navbar .active').removeClass('active');
    $('#navbar-taken-exam').addClass('active');
    $('#navbar-exams').addClass('active');
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