const init = (requestInvoker, context) => {
    let tokenObj = JSON.parse(localStorage.getItem('token'));

    if (!tokenObj || new Date(tokenObj.expires_at) < new Date()) {
        window.location.href = 'login.php';
    }

    $('#navbar-username').text(tokenObj.user.name);

    requestInvoker.executePost(tokenObj._links.roles.href, { token: tokenObj.token }).then((response) => {
        if (!response._success) alert('Something unexpected happened. Please try again later!');

        if (response.isAdmin) {
            $('#navbar-role-select').append($('<option>')
                .attr({id: 'navbar-role-admin', selected: context === 'admin'})
                .text('Administrator'));

            bindClickListener($('#navbar-role-admin'), () => {
                window.location.href = 'admin.php';
            });
        }

        if (response.isTeacher) {
            $('#navbar-role-select').append($('<option>')
                .attr({id: 'navbar-role-teacher', selected: context === 'teacher'})
                .text('Teacher'));

            bindClickListener($('#navbar-role-teacher'), () => {
                window.location.href = 'teacher.php';
            });
        }

        $.each(response.student, (index, value) => {
            let studentObj = JSON.parse(localStorage.getItem('studentVals'))
            const selected = context === 'student' && studentObj
                && studentObj.programme.name === value.programme.name
                && studentObj.programme.type === value.programme.type;

            const option = $('<option>')
                .attr({ id: 'navbar-role-student-' + index, selected: selected})
                .text(value.programme.name + '/' + value.programme.type + '/' + value.startTerm);

            $('#navbar-role-select').append(option);

            bindClickListener(option, () => {
                localStorage.setItem('studentVals', JSON.stringify(value));
                window.location.href = 'student.php';
            })
        });
    });

    bindClickListener($('#navbar-logo'), () => {
        window.location.href = 'index.php';
    });

    bindClickListener($('#navbar-logout'), () => {
        requestInvoker.executePost(tokenObj._links.logout.href, { token: tokenObj.token}).then(() => {
            localStorage.removeItem('token');
            window.location.href = 'login.php';
        });
    });

    return tokenObj;
}

const bindClickListener = (observer, event, unbindOtherListeners = true) => {
    if (unbindOtherListeners) {
        $(observer).off();
    }
    $(observer).on('click', (e) => {
        e.stopPropagation();
        event(observer, e);
    });
}

const string2html = (string) => {
    return string.replace(/&/g, '&amp;')
        .replace(/>/g, '&gt;')
        .replace(/</g, '&lt;')
        .replace(/\\n/g, '<br>');
}

class Button {
    /**
     * @param {string} text
     * @param {Function} event
     */
    constructor(text, event=null) {
        this.text = text;
        this.event = event;
    }

    /**
     * @returns {JQuery<HTMLElement>}
     */
    build() {
        const button = $('<button></button>').text(this.text);
        if (this.event) {
            bindClickListener(button, this.event);
        }
        return button;
    }
}

class Table {
    /**
     * @param {Object} headers The headers where the keys define the keys used in the records array and the values are what should appear
     * @param {Array.<Object>} records An array of objects containing the records
     */
    constructor(headers, records) {
        this.headers = headers;
        this.records = records;
    }

    /**
     * @returns {JQuery<HTMLElement>}
     */
    build() {
        const tableContainerElement = $('<table></table>');

        tableContainerElement.append(this.createHeaders());

        $.each(this.records, (index, record) => {
            tableContainerElement.append(this.createRow(record));
        });

        return tableContainerElement;
    }

    /**
     * @returns {JQuery<HTMLElement>}
     */
    createHeaders() {
        const headerRowElement = $('<tr></tr>');

        $.each(this.headers, (key, value) => {
            const headerElement = $(`<th></th>`).text(value);

            headerRowElement.append(headerElement);
        });

        return headerRowElement;
    }

    /**
     * @param {Object} record
     * @returns {JQuery<HTMLElement>}
     */
    createRow(record) {
        const rowElement = $('<tr></tr>');

        $.each(this.headers, (key) => {
            const tableValueElement = $('<td></td>').text(record[key]);

            rowElement.append(tableValueElement);
        })

        return rowElement;
    }
}

class DropDownTable extends Table {
    /**
     * @param {Object} headers The headers where the keys define the keys used in the records array and the values are what should appear
     * @param {Array.<Object>} records An array of objects containing the records
     * @param {Function} dropDownEvent The event that should return a html element which will populate the dropdown menu
     */
    constructor(headers, records, dropDownEvent) {
        super(headers, records);
        this.dropDownEvent = dropDownEvent;
    }

    /**
     * @returns {JQuery<HTMLElement>}
     */
    build() {
        const tableContainerElement = $('<table></table>');

        tableContainerElement.append(this.createHeaders());

        $.each(this.records, (index, record) => {
            tableContainerElement.append(this.createRow(record));
        });

        return tableContainerElement;
    }

    /**
     * @param {Object} record
     * @returns {JQuery<HTMLElement>}
     */
    createRow(record) {
        const rowElement = super.createRow(record).addClass('dropDownRow');

        bindClickListener(rowElement, (obj, event) => {
            if (rowElement.hasClass(`open`)) {
                rowElement.removeClass('open');
                rowElement.next().remove();
                return;
            }

            const openSiblings = rowElement.siblings('.open');
            openSiblings.next().remove();
            openSiblings.removeClass('open');
            rowElement.addClass('open');

            rowElement.after($('<tr></tr>').append($('<td></td>')
                .addClass('dropDownContainer')
                .attr('colspan', rowElement.children().length)
                .append(this.dropDownEvent(record))));
        });

        return rowElement;
    }
}

class RequestInvoker {
    constructor(apiUrl) {
        this.apiUrl = apiUrl;
    }

    async executePost(url, args) {
        return this.sendRequest(url, 'POST', args);
    }

    sendRequest(url, requestType, args) {
        let callback = () => {};
        let result = {
            then: (responseHandler) => {
                callback = responseHandler;
            },
        };

        if (!url.startsWith('http')) {
            url = this.apiUrl + url;
        }

        $.ajax({
            url: url,
            type: requestType,
            data: args,
            success: (response) => {
                callback(response);
            },
            error: (xhr, status, error) => {
                console.error(error);
                alert('Something unexpected happened. Please try again later!')
            },
        });

        return result;
    }
}

class SelfPage {
    /**
     * @param {JQuery<HTMLElement>} contentElement
     * @param {string} fetchUserUrl
     * @param {string} context
     */
    constructor(contentElement, fetchUserUrl, context) {
        this.contentElement = contentElement;
        this.fetchUserUrl = fetchUserUrl;
        this.context = context;
    }

    build() {
        this.contentElement.empty();

        localStorage.setItem(this.context, 'self');
        $('#navbar .active').removeClass('active');
        $('#navbar-username').addClass('active');

        this.contentElement.append($('<h1>').text('My data'));

        let table = $("<table>").addClass("editTable");
        table.append(
            $("<tr>").append(
                $("<th>").text("Code:"),
                $("<td>").prop('id', 'self-details-id')
            ),
            $("<tr>").append(
                $("<th>").append($("<label>").attr("for", "self-details-name").text("Name:")),
                $("<td>").append($("<input>").attr({ id: "self-details-name", name: "name", type: "text", required: true }))
            ),
            $("<tr>").append(
                $("<th>").append($("<label>").attr("for", "self-details-email").text("E-mail address:")),
                $("<td>").append($("<input>").attr({ id: "self-details-email", name: "email", type: "email", required: true }))
            ),
            $("<tr>").append(
                $("<th>").append($("<label>").attr("for", "self-details-birth").text("Birth date:")),
                $("<td>").append($("<input>").attr({ id: "self-details-birth", name: "birth_date", type: "date", required: true }))
            ),
            $("<tr>").append(
                $("<th>").append($("<label>").attr("for", "self-details-password").text("Password:")),
                $("<td>").append($("<input>").attr({ id: "self-details-password", type: "password", required: true }))
            ),
            $("<tr>").append(
                $("<th>").append($("<label>").attr("for", "self-details-password2").text("Password again:")),
                $("<td>").append($("<input>").attr({ id: "self-details-password2", type: "password", required: true }))
            )
        );

        const updateButton = new Button('Save').build()

        this.contentElement.append(table);
        this.contentElement.append($('<div>').prop('id', 'edit-self-error').addClass('hidden error'));
        this.contentElement.append(updateButton);

        requestInvoker.executePost(this.fetchUserUrl, { token: tokenObj.token }).then((response) => {
            $('#self-details-id').text(response.id);
            $('#self-details-name').val(response.name);
            $('#self-details-email').val(response.email);
            $('#self-details-birth').val(response.birth_date);

            bindClickListener(updateButton, () => { this.updateSelf(response) });
        });

        requestInvoker.executePost('message/all', { token: tokenObj.token }).then((response) => {
            console.log(response);
            const tableColumns = {
                subject: 'subject',
                dateTime: 'Date Time'
            };

            this.contentElement.append($('<h2>').text('Messages'));
            const messagesTable= new DropDownTable(tableColumns, response._embedded.message, (record) => { return this.messagesDetails(record, this.contentElement)}).build();
            this.contentElement.append(messagesTable);
        });

        //messages(this.contentElement);


    }

    updateSelf(record) {
        const errorContainer = $('#edit-self-error').addClass('hidden');

        let args = {
            token: tokenObj.token,
            id: record.id,
            name: $('#self-details-name').val(),
            email: $('#self-details-email').val(),
            birth_date: $('#self-details-birth').val()
        };

        const pw = $('#self-details-password').val();
        const pw2 = $('#self-details-password2').val();

        if (pw || pw2) {
            if (pw !== pw2) {
                errorContainer.html('The passwords do not match!').removeClass('hidden');
                return;
            } else {
                args['password'] = pw;
            }
        }

        requestInvoker.executePost(record._links.update.href, args).then((response) => {
            if (response._success) this.build();
            else errorContainer.html(string2html(response.error.details)).removeClass('hidden');
        });
    }

    messagesDetails(record, contentElement) {
        let container = $('<div>');

        let table = $("<table>").addClass("editTable");
        table.append(
            $("<tr>").append(
                $("<td>").text(record.content)
            )
        );

        container.append(table);

        container.append($('<div>').prop('id', 'edit-subject-error').addClass('hidden error'));

        return container;
    }
}
