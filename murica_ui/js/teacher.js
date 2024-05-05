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
            noStudents: 'Students',
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

    let newExamButton = new Button('New exam').build();
    contentElement.append(newExamButton);

    requestInvoker.executePost('exam/teachExams', { token: tokenObj.token }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
            return;
        }

        bindClickListener(newExamButton, () => { newExam(contentElement, response._links.new.href); });

        const tableColumns = {
            id: 'Id',
            subjectName: 'Subject',
            startTime: 'Start',
            endTime: 'End',
            roomId: 'Room',
            capacity: 'Capacity',
            noStudents: 'Students'
        };

        const examsTable= new DropDownTable(tableColumns, response._embedded.exams, (record) => {return examDetails(record, contentElement)}).build();
        contentElement.append(examsTable);
    });
}

function newExam(contentElement, saveUrl) {
    contentElement.empty();

    contentElement.append($('<h1>').text('New exam'));

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "exam-details-id").text("Id:")),
            $("<td>").append($("<input>").attr({id: 'exam-details-id', type: 'text', maxlength: 6, required: true}))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "exam-details-start-time").text("Start time:")),
            $("<td>").append($("<input>").attr({ id: "exam-details-start-time", type: "datetime-local", required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "exam-details-end-time").text("End time:")),
            $("<td>").append($("<input>").attr({ id: "exam-details-end-time", type: "time", required: true }))
        ),
        $('<tr>').append(
            $("<th>").append($("<label>").attr("for", "exam-details-room").text("Room:")),
            $("<td>").append($('<select>').attr('id', 'exam-details-room'))
        ),
        $('<tr>').append(
            $("<th>").append($("<label>").attr("for", "exam-details-subject").text("Subject:")),
            $("<td>").append($('<select>').attr('id', 'exam-details-subject'))
        ),
        $('<tr>').append(
            $("<th>").append($("<label>").attr("for", "exam-details-teacher").text("Teacher:")),
            $("<td>").append($('<select>').attr('id', 'exam-details-teacher'))
        )
    );

    contentElement.append(table);

    requestInvoker.executePost('room/all', { token: tokenObj.token }).then((responseRoom) => {
        if (!responseRoom._success) {
            console.error(responseRoom.error);
            alert('Something unexpected happened. Please try again later!');
        }

        let roomSelector = $('#exam-details-room');

        $.each(responseRoom._embedded.rooms, (index, room) => {
            roomSelector.append($('<option>').prop('value', room.id).text(room.id + ' (' + room.capacity + ')'));
        });

        requestInvoker.executePost('subject/byTeacher', { token: tokenObj.token }).then((responseSubject) => {
            if (!responseSubject._success) {
                console.error(responseSubject.error);
                alert('Something unexpected happened. Please try again later!');
                return;
            }

            let subjectSelector = $('#exam-details-subject');

            $.each(responseSubject._embedded.subjects, (index, subject) => {
                subjectSelector.append($('<option>').prop('value', subject.id).text(subject.name + ' [' + subject.id + ']'));
            });

            bindClickListener(subjectSelector, () => {
                requestInvoker.executePost(responseSubject._links.teachers.href, { token: tokenObj.token, subjectId: $('#exam-details-subject :selected').val() }).then((responseTeacher) => {
                    if (!responseTeacher._success) {
                        console.error(responseTeacher.error);
                        alert('Something unexpected happened. Please try again later!');
                    }

                    let teacherSelector = $('#exam-details-teacher');

                    teacherSelector.empty();
                    $.each(responseTeacher._embedded.teachers, (index, teacher) => {
                        teacherSelector.append($('<option>').prop('value', teacher.id).text(teacher.name + ' (' + teacher.id + ')'));
                    });
                });
            });
        });
    });

    contentElement.append($('<div>').prop('id', 'new-exam-error').addClass('hidden error'));
    contentElement.append(new Button('Save', () => { saveNewExam(contentElement, saveUrl) }).build());
    contentElement.append(new Button('Cancel', () => { exams(contentElement); }).build());
}

function saveNewExam(contentElement, saveUrl) {
    $('#new-exam-error').addClass('hidden');

    let startTime = $('#exam-details-start-time').val().split('T');
    let endTime = $('#exam-details-end-time').val()

    requestInvoker.executePost(saveUrl, {
        token: tokenObj.token,
        id: $('#exam-details-id').val(),
        startTime: `${startTime[0]} ${startTime[1]}`,
        endTime: `${startTime[0]} ${endTime}`,
        subjectId: $('#exam-details-subject :selected').val(),
        roomId: $('#exam-details-room :selected').val(),
        teacherId: $('#exam-details-teacher :selected').val(),
    }).then((response) => {
        if (response._success) exams(contentElement);
        else $('#new-exam-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function examDetails(record, contentElement) {
    let container = $('<div>');

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").text("Id:"),
            $("<td>").text(record.id)
        ),
        $('<tr>').append(
            $("<th>").text("Subject:"),
            $('<td>').text(record.subjectName)
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "exam-details-start-time").text("Start time:")),
            $("<td>").append($("<input>").attr({ id: "exam-details-start-time", type: "datetime-local", value: record.startTime, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "exam-details-end-time").text("End time:")),
            $("<td>").append($("<input>").attr({ id: "exam-details-end-time", type: "time", value: record.endTime.split(' ')[1], required: true }))
        )
    );

    const roomSelector = $('<select>').attr('id', 'exam-details-room');
    table.append($('<tr>').append(
        $("<th>").append($("<label>").attr("for", "exam-details-room").text("Room:")),
        $("<td>").append(roomSelector)
    ));

    const teacherSelector = $('<select>').attr('id', 'exam-details-teacher');
    table.append($('<tr>').append(
        $("<th>").append($("<label>").attr("for", "exam-details-teacher").text("Teacher:")),
        $("<td>").append(teacherSelector)
    ));
    container.append(table);

    requestInvoker.executePost('room/all', { token: tokenObj.token }).then((responseRoom) => {
        if (!responseRoom._success) {
            console.error(responseRoom.error);
            alert('Something unexpected happened. Please try again later!');
        }

        $.each(responseRoom._embedded.rooms, (index, room) => {
            roomSelector.append($('<option>').prop({value: room.id, selected: room.id === record.room.id}).text(room.id + ' (' + room.capacity + ')'));
        });

        requestInvoker.executePost('user/teachersBySubject', { token: tokenObj.token, subjectId: record.subject.id }).then((responseTeacher) => {
            if (!responseTeacher._success) {
                console.error(responseTeacher.error);
                alert('Something unexpected happened. Please try again later!');
            }

            teacherSelector.empty();
            $.each(responseTeacher._embedded.teachers, (index, teacher) => {
                teacherSelector.append($('<option>').prop({value: teacher.id, selected: teacher.id === record.teacher.id}).text(teacher.name + ' (' + teacher.id + ')'));
            });
        });
    });

    container.append($('<div>').prop('id', 'edit-exam-error').addClass('hidden error'));
    container.append(new Button('Save', () => { updateExam(record, contentElement) }).build());
    container.append(new Button('Remove', () => { removeExam(record, contentElement) }).build());

    requestInvoker.executePost('user/byExam', { token: tokenObj.token, examId: record.id, subjectId: record.subject.id }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
            return;
        }

        const tableColumns = {
            userId: 'Id',
            userName: 'Name',
            userProgramme: 'Programme',
        };

        const studentsTable = new Table(tableColumns, response._embedded.students).build();
        container.append(studentsTable);
    });

    return container;
}

function updateExam(record, contentElement) {
    $('#edit-exam-error').addClass('hidden');

    let startTime = $('#exam-details-start-time').val().split('T');
    let endTime = $('#exam-details-end-time').val()

    requestInvoker.executePost(record._links.update.href, {
        token: tokenObj.token,
        id: record.id,
        startTime: `${startTime[0]} ${startTime[1]}`,
        endTime: `${startTime[0]} ${endTime}`,
        subjectId: record.subject.id,
        roomId: $('#exam-details-room :selected').val(),
        teacherId: $('#exam-details-teacher :selected').val(),
    }).then((response) => {
        if (response._success) exams(contentElement);
        else $('#edit-exam-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function removeExam(record, contentElement) {
    $('#edit-exam-error').addClass('hidden');

    requestInvoker.executePost(record._links.delete.href, { token: tokenObj.token, id: record.id, subjectId: record.subject.id }).then((response) => {
        if (response._success) exams(contentElement);
        else $('#edit-exam-error').html(string2html(response.error.details)).removeClass('hidden');
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