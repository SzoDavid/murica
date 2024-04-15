const apiUrl = 'https://localhost/murica_api/';
const requestInvoker = new RequestInvoker(apiUrl);
let tokenObj;

function init() {
    tokenObj = JSON.parse(localStorage.getItem('token'));

    if (!tokenObj || new Date(tokenObj.expires_at) < new Date()) {
        window.location.href = 'login.php';
    }

    $('#navbar-username').text(tokenObj.user.name);

    bindClickListener($('#navbar-logo'), () => {
        window.location.href = 'index.php';
    });

    bindClickListener($('#navbar-logout'), () => {
        requestInvoker.executePost(tokenObj._links.logout.href, { token: tokenObj.token}).then(() => {
            localStorage.removeItem('token');
            window.location.href = 'login.php';
        });
    });
}

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

    //TODO: validate values
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

    // TODO: add table for the courses

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

        const programmeTable= new DropDownTable(tableColumns, response._embedded.programmes, (record) => { return programmeDetails(record, contentElement)}).build();
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
        )
    );

    contentElement.append(table);
    contentElement.append($('<div>').prop('id', 'new-user-error').addClass('hidden error'));
    contentElement.append(new Button('Save', () => { saveNewUser(contentElement, saveUrl) }).build());
    contentElement.append(new Button('Cancel', () => { users(contentElement); }).build());
}

function saveNewUser(contentElement, saveUrl) {
    $('#new-user-error').addClass('hidden');

    //TODO: validate values
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

    return container;
}

function updateUser(record, contentElement) {
    $('#edit-user-error').addClass('hidden');

    let args = {
        token: tokenObj.token,
        id: record.id
    }

    const nameField = $('#user-details-name');
    const emailField = $('#user-details-email');
    const birthDateField = $('#user-details-birth');

    if (nameField.val()) args['name'] = nameField.val();
    if (emailField.val()) args['email'] = emailField.val();
    if (birthDateField.val()) args['birth_date'] = birthDateField.val();

    requestInvoker.executePost(record._links.update.href, args).then((response) => {
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

//region Rooms

function rooms(contentElement) {
    contentElement.empty();

    localStorage.setItem('admin', 'rooms');

    $('#navbar .active').removeClass('active');
    $('#navbar-rooms').addClass('active');

    let newRoomButton = new Button('New room').build();
    contentElement.append(newRoomButton);

    requestInvoker.executePost('room/all', { token: tokenObj.token }).then((response) => {
        bindClickListener(newRoomButton, () => { newRoom(contentElement, response._links.createRoom.href); });

        console.log(response);
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
    //TODO: request
}

function roomDetails(record, contentElement) {
    
}

//endregion

$(() => {
    init();
    const contentElement = $('#content');

    const site = localStorage.getItem('admin');
    switch (site) {
        case 'subjects': subjects(contentElement); break;
        case 'programmes': programmes(contentElement); break;
        case 'users': users(contentElement); break;
        case 'rooms': rooms(contentElement); break;
    }

    bindClickListener($('#navbar-subjects'), () => { subjects(contentElement); });
    bindClickListener($('#navbar-programmes'), () => { programmes(contentElement); });
    bindClickListener($('#navbar-users'), () => { users(contentElement); });
    bindClickListener($('#navbar-rooms'), () => { rooms(contentElement); });
});
