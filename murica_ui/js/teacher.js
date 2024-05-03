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

    return container;
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