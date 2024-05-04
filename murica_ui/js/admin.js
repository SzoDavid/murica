const requestInvoker = new RequestInvoker(apiUrl);
let tokenObj;

//region Subjects
function subjects(contentElement) {
    contentElement.empty();

    localStorage.setItem('admin', 'subjects');

    $('#navbar .active').removeClass('active');
    $('#navbar-subjects').addClass('active');

    let newSubjectButton = new Button('New subject').build();
    contentElement.append(newSubjectButton);

    requestInvoker.executePost('subject/all', { token: tokenObj.token }).then((response) => {
        bindClickListener(newSubjectButton, () => { newSubject(contentElement, response._links.createSubject.href); });

        console.log(response);
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

function newSubject(contentElement, saveUrl) {
    contentElement.empty();

    contentElement.append($('<h1>').text('New subject'));

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "subject-details-id").text("Id:")),
            $("<td>").append($("<input>").attr({id: 'subject-details-id', type: 'text', maxlength: 6, required: true}))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "subject-details-name").text("Name:")),
            $("<td>").append($("<input>").attr({ id: "subject-details-name", type: "text", required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "subject-details-approval").text("Approval needed:")),
            $("<td>").append($("<input>").attr({ id: "subject-details-approval", type: "checkbox", required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "subject-details-credit").text("Credit:")),
            $("<td>").append($("<input>").attr({ id: "subject-details-credit", type: "number", min: 0, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "subject-details-type").text("Type:")),
            $("<td>").append($("<input>").attr({ id: "subject-details-type", type: "text", required: true }))
        )
    );

    contentElement.append(table);
    contentElement.append($('<div>').prop('id', 'new-subject-error').addClass('hidden error'));
    contentElement.append(new Button('Save', () => { saveNewSubject(contentElement, saveUrl) }).build());
    contentElement.append(new Button('Cancel', () => { subjects(contentElement); }).build());
}

function saveNewSubject(contentElement, saveUrl) {
    $('#new-subject-error').addClass('hidden');

    requestInvoker.executePost(saveUrl, {
        token: tokenObj.token,
        id: $('#subject-details-id').val(),
        name: $('#subject-details-name').val(),
        approval: $('#subject-details-approval').is(":checked"),
        credit: $('#subject-details-credit').val(),
        type: $('#subject-details-type').val()
    }).then((response) => {
        console.log(response);
        if (response._success) subjects(contentElement);
        else $('#new-subject-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function subjectDetails(record, contentElement) {
    let container = $('<div>');

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").text("Id:"),
            $("<td>").text(record.id)
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "subject-details-name").text("Name:")),
            $("<td>").append($("<input>").attr({ id: "subject-details-name", name: "name", type: "text", value: record.name, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "subject-details-approval").text("Approval needed:")),
            $("<td>").append($("<input>").attr({ id: "subject-details-approval", type: "checkbox", checked: record.approval !== '', required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "subject-details-credit").text("Credit:")),
            $("<td>").append($("<input>").attr({ id: "subject-details-credit", type: "number", min: 0, value: record.credit, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "subject-details-type").text("Type:")),
            $("<td>").append($("<input>").attr({ id: "subject-details-type", type: "text", value: record.type, required: true }))
        )
    );

    container.append(table);

    container.append($('<div>').prop('id', 'edit-subject-error').addClass('hidden error'));
    container.append(new Button('Save', () => { updateSubject(record, contentElement) }).build());
    container.append(new Button('Remove', () => { removeSubject(record, contentElement) }).build());

    buildCourses(contentElement, container, record);

    return container;
}

function updateSubject(record, contentElement) {
    $('#edit-subject-error').addClass('hidden');

    requestInvoker.executePost(record._links.update.href, {
        token: tokenObj.token,
        id: record.id,
        name: $('#subject-details-name').val(),
        approval: $('#subject-details-approval').is(":checked"),
        credit: $('#subject-details-credit').val(),
        type: $('#subject-details-type').val()
    }).then((response) => {
        if (response._success) subjects(contentElement);
        else $('#edit-subject-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function removeSubject(record, contentElement) {
    $('#edit-subject-error').addClass('hidden');

    requestInvoker.executePost(record._links.delete.href, { token: tokenObj.token, id: record.id }).then((response) => {
        if (response._success) subjects(contentElement);
        else $('#edit-subject-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}
//endregion

//region Courses
function buildCourses(contentElement, container, record) {
    container.append($('<h2>').text('Courses'));

    let newCourseTable = $("<table>").addClass("editTable");
    newCourseTable.append(
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "new-course-id").text("Id:")),
            $("<td>").append($("<input>").attr({id: 'new-course-id', type: 'text', maxlength: 6, required: true}))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "new-course-capacity").text("Capacity:")),
            $("<td>").append($("<input>").attr({ id: "new-course-capacity", type: "number", min: 1, max: 999, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "new-course-schedule").text("Schedule:")),
            $("<td>").append($("<input>").attr({ id: "new-course-schedule", type: "text", maxlength: 13, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "new-course-term").text("Term:")),
            $("<td>").append($("<input>").attr({ id: "new-course-term", type: "text", maxlength: 9, required: true }))
        )
    );

    const roomSelector = $('<select>').attr('id', 'new-course-room');
    newCourseTable.append($('<tr>').append(
        $("<th>").append($("<label>").attr("for", "new-course-room").text("Room:")),
        roomSelector
    ));
    container.append(newCourseTable);

    requestInvoker.executePost('room/all', { token: tokenObj.token }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
        }

        $.each(response._embedded.rooms, (index, room) => {
            roomSelector.append($('<option>').prop('value', room.id).text(room.id + ' (' + room.capacity + ')'));
        });
    });

    container.append($('<div>').prop('id', 'new-course-error').addClass('hidden error'));

    const newCourseButton = new Button('Add course', ).build()
    container.append(newCourseButton);

    requestInvoker.executePost(record._links.courses.href, { token: tokenObj.token }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
            return;
        }

        bindClickListener(newCourseButton, () => { saveNewCourse(record, contentElement, response._links.add.href) })

        const tableColumns = {
            id: 'Id',
            capacity: 'Capacity',
            schedule: 'Schedule',
            term: 'Term',
        };

        const subjectsTable= new DropDownTable(tableColumns, response._embedded.courses,
            (courseRecord) => { return courseDetails(courseRecord, contentElement) }).build();
        container.append(subjectsTable);
    });
}

function saveNewCourse(record, contentElement, saveUrl) {
    $('#new-course-error').addClass('hidden');

    console.log(record);

    requestInvoker.executePost(saveUrl, {
        token: tokenObj.token,
        subjectId: record.id,
        id: $('#new-course-id').val(),
        capacity: $('#new-course-capacity').val(),
        schedule: $('#new-course-schedule').val(),
        term: $('#new-course-term').val(),
        roomId: $('#new-course-room :selected').val()
    }).then((response) => {
        console.log(response);
        if (response._success) subjects(contentElement);
        else $('#new-course-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function courseDetails(course, contentElement) {
    let container = $('<div>');

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").text("Id:"),
            $("<td>").text(course.subject.id + '-' + course.id)
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "edit-course-capacity").text("Capacity:")),
            $("<td>").append($("<input>")
                .attr({id: "new-course-capacity", type: "number", min: 1, max: 999, value: course.capacity, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "edit-course-schedule").text("Schedule:")),
            $("<td>").append($("<input>")
                .attr({ id: "new-course-schedule", type: "text", maxlength: 13, value: course.schedule, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "edit-course-term").text("Term:")),
            $("<td>").append($("<input>")
                .attr({ id: "new-course-term", type: "text", maxlength: 9, value: course.term, required: true }))
        )
    );
    const roomSelector = $('<select>').attr('id', 'edit-course-room');
    table.append($('<tr>').append(
        $("<th>").append($("<label>").attr("for", "edit-course-room").text("Room:")),
        roomSelector
    ));
    container.append(table);

    requestInvoker.executePost('room/all', { token: tokenObj.token }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
        }

        $.each(response._embedded.rooms, (index, room) => {
            roomSelector.append($('<option>').prop({value: room.id, selected: room.id === course.room.id}).text(room.id + ' (' + room.capacity + ')'));
        });
    });

    container.append($('<div>').prop('id', 'edit-course-error').addClass('hidden error'));
    container.append(new Button('Save', () => { updateCourse(course, contentElement) }).build());
    container.append(new Button('Remove', () => { removeCourse(course, contentElement) }).build());

    buildTeachers(contentElement, container, course);

    return container;
}

function updateCourse(course, contentElement) {
    $('#edit-course-error').addClass('hidden');

    requestInvoker.executePost(course._links.update.href, {
        token: tokenObj.token,
        subjectId: course.subject.id,
        id: course.id,
        capacity: $('#edit-course-capacity').val(),
        schedule: $('#edit-course-schedule').val(),
        term: $('#edit-course-term').val(),
        roomId: $('#edit-course-room :selected').val()
    }).then((response) => {
        if (response._success) subjects(contentElement);
        else $('#edit-course-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function removeCourse(course, contentElement) {
    $('#edit-course-error').addClass('hidden');

    requestInvoker.executePost(course._links.delete.href, { token: tokenObj.token, id: course.id, subjectId: course.subject.id }).then((response) => {
        if (response._success) subjects(contentElement);
        else $('#edit-course-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

//endregion

//region Teachers
function buildTeachers(contentElement, container, record) {
    container.append($('<h2>').text('Teachers'));

    let addTeacherTable = $("<table>").addClass("editTable");

    const teacherSelector = $('<select>').attr('id', 'assign-teacher-select');
    addTeacherTable.append($('<tr>').append(
        $("<th>").append($("<label>").attr("for", "assign-teacher-select").text("Teacher:")),
        teacherSelector
    ));
    container.append(addTeacherTable);

    requestInvoker.executePost('user/all', { token: tokenObj.token }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
        }

        $.each(response._embedded.users, (index, user) => {
            teacherSelector.append($('<option>').prop('value', user.id).text(user.name + ' (' + user.id + ')'));
        });
    });

    container.append($('<div>').prop('id', 'assign-teacher-error').addClass('hidden error'));

    const assignTeacherButton = new Button('Assign teacher', ).build()
    container.append(assignTeacherButton);

    requestInvoker.executePost(record._links.teachers.href, {
        token: tokenObj.token,
        subjectId: record.subject.id,
        courseId: record.id
    }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
        }

        bindClickListener(assignTeacherButton, () => { assignTeacher(record, contentElement, response._links.assignTeacher.href) })

        const tableColumns = {
            id: 'Id',
            name: 'Name',
        };

        const teachersTable= new DropDownTable(tableColumns, response._embedded.teachers,
            (teacherRecord) => { return buildTeacherDetails(contentElement, teacherRecord, record) }).build();
        container.append(teachersTable);
    });
}

function assignTeacher(record, contentElement, saveUrl) {
    $('#assign-teacher-error').addClass('hidden');

    requestInvoker.executePost(saveUrl, {
        token: tokenObj.token,
        id: record.id,
        subjectId: record.subject.id,
        teacherId: $('#assign-teacher-select :selected').val()
    }).then((response) => {
        if (response._success) subjects(contentElement);
        else $('#assign-teacher-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function buildTeacherDetails(contentElement, record, course) {
    let container = $('<div>');

    container.append($('<div>').prop('id', 'remove-teacher-error').addClass('hidden error'));
    container.append(new Button('Remove', () => { removeTeacher(contentElement, record, course) }).build());

    return container;
}

function removeTeacher(contentElement, record, course) {
    $('#remove-teacher-error').addClass('hidden');

    console.log(record);

    requestInvoker.executePost(record._links.removeTeacher.href, { token: tokenObj.token, id: course.id, subjectId: course.subject.id, teacherId: record.id }).then((response) => {
        if (response._success) subjects(contentElement);
        else $('#remove-teacher-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

//endregion

//region Programmes

function programmes(contentElement) {
    contentElement.empty();

    localStorage.setItem('admin', 'programmes');

    $('#navbar .active').removeClass('active');
    $('#navbar-programmes').addClass('active');

    let newProgrammeButton = new Button('New programme').build();
    contentElement.append(newProgrammeButton);

    requestInvoker.executePost('programme/all', { token: tokenObj.token }).then((response) => {
        bindClickListener(newProgrammeButton, () => { newProgramme(contentElement, response._links.createProgramme.href); });

        console.log(response);
        const tableColumns = {
            name: 'Name',
            type: 'Type',
            noTerms: 'Number of terms',
        };

        const programmeTable= new DropDownTable(tableColumns, response._embedded.programmes,
            (record) => { return programmeDetails(record, contentElement)}).build();
        contentElement.append(programmeTable);
    });
}

function newProgramme(contentElement, saveUrl) {
    contentElement.empty();

    contentElement.append($('<h1>').text('New programme'));

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "programme-details-name").text("Name:")),
            $("<td>").append($("<input>").attr({id: 'programme-details-name', type: 'text', maxlength: 50, required: true}))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "programme-details-type").text("Type:")),
            $("<td>").append($("<input>").attr({ id: "programme-details-type", type: "text", maxlength: 20, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "programme-details-noTerms").text("Number of terms:")),
            $("<td>").append($("<input>").attr({ id: "programme-details-noTerms", type: "number", min: 1, required: true }))
        )
    );

    contentElement.append(table);
    contentElement.append($('<div>').prop('id', 'new-programme-error').addClass('hidden error'));
    contentElement.append(new Button('Save', () => { saveNewProgramme(contentElement, saveUrl) }).build());
    contentElement.append(new Button('Cancel', () => { programmes(contentElement); }).build());
}

function saveNewProgramme(contentElement, saveUrl) {
    $('#new-programme-error').addClass('hidden');

    requestInvoker.executePost(saveUrl, {
        token: tokenObj.token,
        name: $('#programme-details-name').val(),
        type: $('#programme-details-type').val(),
        noTerms: $('#programme-details-noTerms').val(),
    }).then((response) => {
        console.log(response);
        if (response._success) programmes(contentElement);
        else $('#new-programme-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function programmeDetails(record, contentElement) {
    let container = $('<div>');

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").text("Name:"),
            $("<td>").text(record.name)
        ),
        $("<tr>").append(
            $("<th>").text("Type:"),
            $("<td>").text(record.type)
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "programme-details-noTerms").text("Number of terms:")),
            $("<td>").append($("<input>").attr({ id: "programme-details-noTerms", type: "number", min: 1, value: record.noTerms, required: true }))
        )
    );
    container.append(table);

    container.append($('<div>').prop('id', 'edit-programme-error').addClass('hidden error'));
    container.append(new Button('Save', () => { updateProgramme(record, contentElement) }).build());
    container.append(new Button('Remove', () => { removeProgramme(record, contentElement) }).build());

    return container;
}

function updateProgramme(record, contentElement) {
    $('#edit-programme-error').addClass('hidden');

    requestInvoker.executePost(record._links.update.href, {
        token: tokenObj.token,
        name: record.name,
        type: record.type,
        noTerms: $('#programme-details-noTerms').val()
    }).then((response) => {
        if (response._success) programmes(contentElement);
        else $('#edit-programme-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function removeProgramme(record, contentElement) {
    $('#edit-programme-error').addClass('hidden');

    requestInvoker.executePost(record._links.delete.href, { token: tokenObj.token, name: record.name, type: record.type }).then((response) => {
        if (response._success) programmes(contentElement);
        else $('#edit-programme-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}
//endregion

//region Users
function users(contentElement) {
    contentElement.empty();

    localStorage.setItem('admin', 'users');

    $('#navbar .active').removeClass('active');
    $('#navbar-users').addClass('active');

    let newUserButton = new Button('New user').build();
    contentElement.append(newUserButton);

    requestInvoker.executePost('user/all', { token: tokenObj.token }).then((response) => {
        bindClickListener(newUserButton, () => { newUser(contentElement, response._links.createUser.href); });

        console.log(response);
        const tableColumns = {
            id: 'Code',
            name: 'Name',
            email: 'E-mail address',
            birth_date: 'Birth date'
        };

        const usersTable= new DropDownTable(tableColumns, response._embedded.users, (record) => { return userDetails(record, contentElement)} ).build();
        contentElement.append(usersTable);
    });
}

function newUser(contentElement, saveUrl) {
    contentElement.empty();

    contentElement.append($('<h1>').text('New user'));

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "user-details-code").text("Code:")),
            $("<td>").append($("<input>").attr({id: 'user-details-code', type: 'text', maxlength: 6, required: true}))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "user-details-name").text("Name:")),
            $("<td>").append($("<input>").attr({ id: "user-details-name", type: "text", required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "user-details-email").text("E-mail address:")),
            $("<td>").append($("<input>").attr({ id: "user-details-email", type: "email", required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "user-details-birth").text("Birth date:")),
            $("<td>").append($("<input>").attr({ id: "user-details-birth", type: "date", required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "user-details-password").text("Password:")),
            $("<td>").append($("<input>").attr({ id: "user-details-password", type: "password", required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "user-details-password2").text("Password again:")),
            $("<td>").append($("<input>").attr({ id: "user-details-password2", type: "password", required: true }))
        )
    );

    contentElement.append(table);
    contentElement.append($('<div>').prop('id', 'new-user-error').addClass('hidden error'));
    contentElement.append(new Button('Save', () => { saveNewUser(contentElement, saveUrl) }).build());
    contentElement.append(new Button('Cancel', () => { users(contentElement); }).build());
}

function saveNewUser(contentElement, saveUrl) {
    $('#new-user-error').addClass('hidden');

    if ($('#user-details-password').val() !== $('#user-details-password2').val()) {
        $('#new-user-error').html('The passwords do not match!').removeClass('hidden');
        return;
    }

    requestInvoker.executePost(saveUrl, {
        token: tokenObj.token,
        id: $('#user-details-code').val(),
        name: $('#user-details-name').val(),
        email: $('#user-details-email').val(),
        birth_date: $('#user-details-birth').val(),
        password: $('#user-details-password').val()
    }).then((response) => {
        console.log(response);
        if (response._success) users(contentElement);
        else $('#new-user-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function userDetails(record, contentElement) {
    let container = $('<div>');

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").text("Code:"),
            $("<td>").text(record.id)
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "user-details-name").text("Name:")),
            $("<td>").append($("<input>").attr({ id: "user-details-name", name: "name", type: "text", value: record.name, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "user-details-email").text("E-mail address:")),
            $("<td>").append($("<input>").attr({ id: "user-details-email", name: "email", type: "email", value: record.email, required: true }))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "user-details-birth").text("Birth date:")),
            $("<td>").append($("<input>").attr({ id: "user-details-birth", name: "birth_date", type: "date", value: record.birth_date, required: true }))
        )
    );
    container.append(table);

    container.append($('<div>').prop('id', 'edit-user-error').addClass('hidden error'));
    container.append(new Button('Save', () => { updateUser(record, contentElement) }).build());
    container.append(new Button('Remove', () => { removeUser(record, contentElement) }).build());

    buildRoles(contentElement, container, record);

    return container;
}

function updateUser(record, contentElement) {
    $('#edit-user-error').addClass('hidden');

    requestInvoker.executePost(record._links.update.href, {
        token: tokenObj.token,
        id: record.id,
        name: $('#user-details-name').val(),
        email: $('#user-details-email').val(),
        birth_date: $('#user-details-birth').val()
    }).then((response) => {
        if (response._success) users(contentElement);
        else $('#edit-user-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function removeUser(record, contentElement) {
    $('#edit-user-error').addClass('hidden');

    requestInvoker.executePost(record._links.delete.href, { token: tokenObj.token, id: record.id }).then((response) => {
        if (response._success) users(contentElement);
        else $('#edit-user-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}
//endregion

//region Roles
function buildRoles(contentElement, container, user) {
    container.append($('<div>').prop('id', 'edit-roles-error').addClass('hidden error'));

    requestInvoker.executePost(user._links.roles.href, { token: tokenObj.token }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
        }

        console.log(response);

        container.append(new Button(response.isAdmin ? 'Remove admin role' : 'Make admin',
            () => {setAdminRole(user, !response.isAdmin, contentElement, response._links)}).build());

        container.append($('<h2>').text('Programmes'));
        //bindClickListener(newCourseButton, () => { saveNewCourse(record, contentElement, response._links.add.href) })

        let assignProgrammeTable = $("<table>").addClass("editTable");
        assignProgrammeTable.append(
            $("<tr>").append(
                $("<th>").append($("<label>").attr("for", "assign-programme-start-term").text("Start tem:")),
                $("<td>").append($("<input>").attr({id: 'assign-programme-start-term', type: 'text', maxlength: 9, required: true}))
            )
        );

        const programmeSelector = $('<select>').attr('id', 'assign-programme-selector');
        assignProgrammeTable.append($('<tr>').append(
            $("<th>").append($("<label>").attr("for", "assign-programme-selector").text("Programme:")),
            programmeSelector
        ));
        container.append(assignProgrammeTable);

        requestInvoker.executePost('programme/all', { token: tokenObj.token }).then((programmeResponse) => {
            if (!programmeResponse._success) {
                console.error(programmeResponse.error);
                alert('Something unexpected happened. Please try again later!');
            }

            $.each(programmeResponse._embedded.programmes, (index, programme) => {
                programmeSelector.append($('<option>')
                    .prop('value', JSON.stringify(programme))
                    .text(programme.name + '/' + programme.type));
            });
        });

        container.append($('<div>').prop('id', 'assign-programme-error').addClass('hidden error'));

        const assignProgrammeButton = new Button('Assign to programme',
            () => { assignToProgramme(contentElement, user, response._links) }).build()
        container.append(assignProgrammeButton);

        const tableColumns = {
            name: 'Name',
            type: 'Type',
            startTerm: 'Started in term',
        };

        const programmeTable= new DropDownTable(tableColumns, response.student,
            (record) => { return assignedProgrammeDetails(contentElement, record, response._links) }).build();
        container.append(programmeTable);
    });
}

function setAdminRole(user, value, contentElement, links) {
    $('#edit-user-error').addClass('hidden');

    requestInvoker.executePost(value ? links.setAdmin.href : links.unsetAdmin.href, { token: tokenObj.token }).then((response) => {
        if (response._success) users(contentElement);
        else $('#edit-user-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function assignToProgramme(contentElement, user, links) {
    $('#assign-programme-error').addClass('hidden');

    const programme = JSON.parse($('#assign-programme-selector :selected').val());

    requestInvoker.executePost(links.setStudent.href, {
        token: tokenObj.token,
        userId: user.id,
        programmeName: programme.name,
        programmeType: programme.type,
        startTerm: $('#assign-programme-start-term').val()
    }).then((response) => {
        if (response._success) users(contentElement);
        else $('#assign-programme-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function assignedProgrammeDetails(contentElement, record, links) {
    let container = $('<div>');

    container.append($('<div>').prop('id', 'edit-assigned-programme-error').addClass('hidden error'));
    container.append(new Button('Remove', () => { removeAssignedProgramme(record, contentElement, links) }).build());

    return container;
}

function removeAssignedProgramme(record, contentElement, links) {
    $('#edit-assigned-programme-error').addClass('hidden');

    requestInvoker.executePost(links.unsetStudent.href, {
        token: tokenObj.token,
        userId: record.user.id,
        programmeName: record.programme.name,
        programmeType: record.programme.type
    }).then((response) => {
        if (response._success) users(contentElement);
        else $('#edit-assigned-programme-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}
//endregion

//region Rooms
function rooms(contentElement) {
    contentElement.empty();

    localStorage.setItem('admin', 'rooms');

    $('#navbar .active').removeClass('active');
    $('#navbar-rooms').addClass('active');

    contentElement.append($('<h2>').text('Statistics'));

    let table = $("<table>").addClass("editTable");
    let cell_ki = $("<td>").attr('id', 'most-math');
    let cell_kki = $("<td>").attr('id', 'most-inf');

    table.append(
        $("<tr>").append(
            $("<th>").text("Room with most math subjects:"),
            cell_ki
        ),
        $("<tr>").append(
            $("<th>").text("Room with most info subjects:"),
            cell_kki
        )
    );

    requestInvoker.executePost('room/stats', {
        token: tokenObj.token
    }).then((response) => {
        $('#most-math').text(response.mostMath);
        $('#most-inf').text(response.mostInf);
    });

    contentElement.append(table);

    let newRoomButton = new Button('New room').build();
    contentElement.append(newRoomButton);

    requestInvoker.executePost('room/all', { token: tokenObj.token }).then((response) => {
        if (!response._success) {
            console.error(response.error);
            alert('Something unexpected happened. Please try again later!');
            return;
        }

        bindClickListener(newRoomButton, () => { newRoom(contentElement, response._links.createRoom.href); });

        const tableColumns = {
            id: 'Id',
            capacity: 'Capacity'
        };

        const roomTable= new DropDownTable(tableColumns, response._embedded.rooms, (record) => { return roomDetails(record, contentElement) }).build();
        contentElement.append(roomTable);
    });
}

function newRoom(contentElement, saveUrl) {
    contentElement.empty();

    contentElement.append($('<h1>').text('New room'));

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "room-details-id").text("Id:")),
            $("<td>").append($("<input>").attr({id: 'room-details-id', type: 'text', maxlength: 6, required: true}))
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "room-details-capacity").text("Capacity:")),
            $("<td>").append($("<input>").attr({ id: "room-details-capacity", type: "number", min: 1, required: true }))
        )
    );

    contentElement.append(table);
    contentElement.append($('<div>').prop('id', 'new-room-error').addClass('hidden error'));
    contentElement.append(new Button('Save', () => { saveNewRoom(contentElement, saveUrl) }).build());
    contentElement.append(new Button('Cancel', () => { rooms(contentElement); }).build());
}

function saveNewRoom(contentElement, saveUrl) {
    $('#new-room-error').addClass('hidden');

    requestInvoker.executePost(saveUrl, {
        token: tokenObj.token,
        id: $('#room-details-id').val(),
        capacity: $('#room-details-capacity').val(),
    }).then((response) => {
        console.log(response);
        if (response._success) rooms(contentElement);
        else $('#new-room-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function roomDetails(record, contentElement) {
    let container = $('<div>');

    let table = $("<table>").addClass("editTable");
    table.append(
        $("<tr>").append(
            $("<th>").text("Id:"),
            $("<td>").text(record.id)
        ),
        $("<tr>").append(
            $("<th>").append($("<label>").attr("for", "room-details-capacity").text("Capacity:")),
            $("<td>").append($("<input>").attr({ id: "room-details-capacity", type: "number", min: 1, value: record.capacity, required: true }))
        )
    );
    container.append(table);

    container.append($('<div>').prop('id', 'edit-room-error').addClass('hidden error'));
    container.append(new Button('Save', () => { updateRoom(record, contentElement) }).build());
    container.append(new Button('Remove', () => { removeRoom(record, contentElement) }).build());

    return container;
}

function updateRoom(record, contentElement) {
    $('#edit-room-error').addClass('hidden');

    requestInvoker.executePost(record._links.update.href, {
        token: tokenObj.token,
        id: record.id,
        capacity: $('#room-details-capacity').val(),
    }).then((response) => {
        if (response._success) rooms(contentElement);
        else $('#edit-room-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}

function removeRoom(record, contentElement) {
    $('#edit-room-error').addClass('hidden');

    requestInvoker.executePost(record._links.delete.href, { token: tokenObj.token, id: record.id }).then((response) => {
        if (response._success) rooms(contentElement);
        else $('#edit-room-error').html(string2html(response.error.details)).removeClass('hidden');
    });
}
//endregion

//region Self

function self(contentElement) {
    new SelfPage(contentElement, tokenObj._links.user.href, 'admin').build();
}
//endregion

$(() => {
    tokenObj = init(requestInvoker, 'admin');
    const contentElement = $('#content');

    const site = localStorage.getItem('admin');
    switch (site) {
        case 'subjects': subjects(contentElement); break;
        case 'programmes': programmes(contentElement); break;
        case 'users': users(contentElement); break;
        case 'rooms': rooms(contentElement); break;
        case 'self': self(contentElement); break;
    }

    bindClickListener($('#navbar-subjects'), () => { subjects(contentElement); });
    bindClickListener($('#navbar-programmes'), () => { programmes(contentElement); });
    bindClickListener($('#navbar-users'), () => { users(contentElement); });
    bindClickListener($('#navbar-rooms'), () => { rooms(contentElement); });
    bindClickListener($('#navbar-username'), () => { self(contentElement); });
});
