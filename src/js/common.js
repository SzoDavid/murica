const bindClickListener = (observer, event, unbindOtherListeners = true) => {
    if (unbindOtherListeners) {
        $(observer).off()
    }
    $(observer).on('click', (e) => {
        e.stopPropagation()
        event(observer, e)
    })
}

const tableBuilder = {
    /**
     * Creates a table
     * @param {Object} headers The headers where the keys define the keys used in the records array and the values are what should appear
     * @param {Array.<Object>} records An array of objects containing the records
     * @returns {JQuery<HTMLElement>}
     */
    createTable: (headers, records) => {
        const tableContainerElement = $('<table></table>')

        tableContainerElement.append(tableBuilder.createHeaders(headers))

        $.each(records, (index, record) => {
            tableContainerElement.append(tableBuilder.createRow(headers, record))
        })

        return tableContainerElement
    },

    /**
     * Creates a table with dropdown rows
     * @param {Object} headers The headers where the keys define the keys used in the records array and the values are what should appear
     * @param {Array.<Object>} records An array of objects containing the records
     * @param {Function} dropDownEvent The event that should return a html element which will populate the dropdown menu
     * @returns {JQuery<HTMLElement>}
     */
    createDropDownTable: (headers, records, dropDownEvent) => {
        const tableContainerElement = $('<table></table>')

        tableContainerElement.append(tableBuilder.createHeaders(headers))

        $.each(records, (index, record) => {
            tableContainerElement.append(tableBuilder.createDropDownRow(headers, record, dropDownEvent))
        })

        return tableContainerElement
    },

    /**
     * Creates header row for table
     * @param {Object} headers
     * @returns {JQuery<HTMLElement>}
     */
    createHeaders: (headers) => {
        const headerRowElement = $('<tr></tr>')

        $.each(headers, (key, value) => {
            const headerElement = $(`<th></th>`).text(value)

            headerRowElement.append(headerElement)
        })

        return headerRowElement
    },

    /**
     * Creates row for table
     * @param {Object} headers
     * @param {Object} record
     * @returns {JQuery<HTMLElement>}
     */
    createRow: (headers, record) => {
        const rowElement = $('<tr></tr>')

        $.each(headers, (key) => {
            const tableValueElement = $('<td></td>').text(record[key])

            rowElement.append(tableValueElement)
        })

        return rowElement
    },

    /**
     * Creates drop down row for table
     * @param {Object} headers
     * @param {Object} record
     * @param {Function} dropDownEvent
     * @returns {JQuery<HTMLElement>}
     */
    createDropDownRow: (headers, record, dropDownEvent) => {
        const rowElement = tableBuilder.createRow(headers, record).addClass('dropDownRow')

        bindClickListener(rowElement, (obj, event) => {
            if (rowElement.hasClass(`open`)) {
                rowElement.removeClass('open')
                rowElement.next().remove()
                return
            }

            const openSiblings = rowElement.siblings('.open')
            openSiblings.next().remove()
            openSiblings.removeClass('open')
            rowElement.addClass('open')

            rowElement.after($('<tr></tr>').append($('<td></td>')
                .addClass('dropDownContainer')
                .attr('colspan', rowElement.children().length)
                .append(dropDownEvent())))
        })

        return rowElement
    }
}
