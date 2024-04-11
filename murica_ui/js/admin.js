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

function subjects(contentElement) {
    contentElement.empty();

    localStorage.setItem('admin', 'subjects');

    $('#navbar .active').removeClass('active');
    $('#navbar-subjects').addClass('active');
}

function programmes(contentElement) {
    contentElement.empty();

    localStorage.setItem('admin', 'programmes');

    $('#navbar .active').removeClass('active');
    $('#navbar-programmes').addClass('active');
}

//region Users

function users(contentElement) {
    contentElement.empty();

    localStorage.setItem('admin', 'users');

    $('#navbar .active').removeClass('active');
    $('#navbar-users').addClass('active');


    let newUserButton = new Button('New user').build()
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

        const usersTable= new DropDownTable(tableColumns, response._embedded.users, userDetails).build();
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

    contentElement.append(table)
    contentElement.append(new Button('Save', () => { saveNewUser(contentElement, saveUrl) }).build());
    contentElement.append(new Button('Cancel', () => { users(contentElement); }).build());
}

function saveNewUser(contentElement, saveUrl) {
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
    });
}

function userDetails(record) {
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

    container.append(new Button('Save', () => { console.log('save'); }).build());
    container.append(new Button('Remove', () => { console.log('remove'); }).build());

    return container;
}

//endregion

function rooms(contentElement) {
    contentElement.empty();

    localStorage.setItem('admin', 'rooms');

    $('#navbar .active').removeClass('active');
    $('#navbar-rooms').addClass('active');
}

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
