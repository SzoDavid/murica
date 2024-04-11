const bindClickListener = (observer, event, unbindOtherListeners = true) => {
    if (unbindOtherListeners) {
        $(observer).off();
    }
    $(observer).on('click', (e) => {
        e.stopPropagation();
        event(observer, e);
    });
}

class Button {
    /**
     * @param {string} text
     * @param {Function} event
     */
    constructor(text, event) {
        this.text = text;
        this.event = event;
    }

    /**
     * @returns {JQuery<HTMLElement>}
     */
    build() {
        const button = $('<button></button>').text(this.text);
        bindClickListener(button, this.event);
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
            },
        });

        return result;
    }
}


