'use strict';

$(document).ready(() => {
    var handle = $("#custom-handle"), handleWidth = handle.width();
    $("#watermarkOpacitySlider").slider({
        min: 35, max: 99, value: 80,
        slide: (e, ui) => {
            $("#sliderInput").val(ui.value);
            $("#sliderInputSpan").text(ui.value);
        }
    });

    createCombobox($("#sidedWatermarkPositionSelectMenu"), [
        { key: 'bottomRightCorner', value: 'Jobb alsó sarok' },
        { key: 'bottomLeftCorner', value: 'Bal alsó sarok' },
        { key: 'upperRightCorner', value: 'Jobb felső sarok' },
        { key: 'upperLeftCorner', value: 'Bal felső sarok' }]);
    $("#updateWatermarkButton").button();

    requestInvoker
        .executeQuery('/Documents/TargetOfDocumentUsages', {})
        .then((response) => {
            let tartgetOfUsageCollection = [];

            $.each(response.responseObject, (key, value) => {
                tartgetOfUsageCollection.push({ key: key, value: value })
            });

            createCombobox($("#targetOfDocumentUsageSelectMenu"), tartgetOfUsageCollection);
        });
});

function openDocumentInNewTab(documentId, documentTitle) {
    feedbackChannel.showInformation("Információ!", `Dokumentum (${documentTitle}) megnyitása új oldalon.`)
    let watermarkSettingsJSON = localStorage.getItem('watermarkSettings');
    let watermarkSettings;
    if (!watermarkSettingsJSON) {
        feedbackChannel.showWarning("Figyelem!", `Nem volt még megnyitott pdf így az alapértelmezett vízjel beállítások lesznek használva!`)
        watermarkSettings = {
            watermarkOpacity: 60,
            sideWatermarkPosition: 'bottomRightCorner',
            centeredWatermarkHorizontalOffset: 0,
            centeredWatermarkVerticalOffset: 0,
            targetOfDocumentUsageId: null,
            fontSize: 25
        };
    }
    else {
        watermarkSettings = JSON.parse(watermarkSettingsJSON);
    }

    const args = {
        documentId: documentId,
        watermarkOpacity: watermarkSettings.watermarkOpacity,
        sideWatermarkPosition: watermarkSettings.sideWatermarkPosition,
        centeredWatermarkHorizontalOffset: watermarkSettings.centeredWatermarkHorizontalOffset,
        centeredWatermarkVerticalOffset: watermarkSettings.centeredWatermarkVerticalOffset,
        targetOfDocumentUsageId: watermarkSettings.targetOfDocumentUsageId,
        documentRotations: "[0]",
        fontSize: watermarkSettings.fontSize,
        openingType: "openInNewTab"
    }

    requestInvoker
        .executeQuery('/Documents/DocumentPreview', args)
        .then(async (response) => {
            const base64PdfString = response.responseObject.documentData;
            const byteCharacters = atob(base64PdfString);
            const byteNumbers = new Array(byteCharacters.length);
            for (let i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            const byteArray = new Uint8Array(byteNumbers);
            let pdfBlob = new Blob([byteArray], { type: "application/pdf" });
            let pdfUrl = URL.createObjectURL(pdfBlob);
            const viewerUrl = `${baseUrl}PdfViewer?url=${encodeURIComponent(pdfUrl)}&title=${documentTitle}`;
            window.open(viewerUrl, "_blank");
            feedbackChannel.showSuccess("Siker!", "A dokumentum sikeresen megnyílt!")
        });
}

const feedbackChannel = {
    showSuccess: (title, message) => {
        b5toast.show('success', title, message);
    },
    showInformation: (title, message) => {
        b5toast.show('info', title, message);
    },
    showWarning: (title, message) => {
        b5toast.show('warning', title, message);
    },
    showError: (title, message) => {
        b5toast.show('error', title, message);
    }
};

const requestInvoker = {
    executeQuery: async (url, args) => {
        return requestInvoker.sendRequest(url, 'GET', args);
    },
    executeCommand: async (url, args) => {
        return requestInvoker.sendRequest(url, 'POST', args);
    },
    executeUpdate: async (url, args) => {
        return requestInvoker.sendRequest(url, 'PUT', args);
    },
    executeDelete: async (url, args) => {
        return requestInvoker.sendRequest(url, 'DELETE', args);
    },

    sendPlainRequest: async (url, requestType, args) => { //TODO: investigate and refactor
        let callback = () => { };
        let result = {
            then: (responseHandler) => {
                callback = responseHandler;
            }
        };
        
        $.ajax({
            url: baseUrl.slice(0, -1) + url,
            type: requestType,
            data: args,
            success: (response) => {
                callback(response);
            },
            error: (xhr, status, error) => {
                feedbackChannel.showError('Ismeretlen hiba', 'Ismeretlen hiba történt. Kérjük jelezze kapcsolattartóink felé.');
            },
        });

        return result;
    },

    sendRequest: (url, requestType, args) => {
        let callback = () => { };
        let result = {
            then: (responseHandler) => {
                callback = responseHandler;
            }
        };
        
        $.ajax({
            url: baseUrl.slice(0, -1) + url,
            type: requestType,
            data: args,
            success: (response) => {
                if (response && response.feedbackMessages && response.feedbackMessages.length > 0) {
                    let displayedFeedback = response.feedbackMessages[0];
                    for (const feedbackMessage of response.feedbackMessages) {
                        if (feedbackMessage.severity !== 3) {
                            displayedFeedback = feedbackMessage;
                            break;
                        }
                    }

                    if (!displayedFeedback) {
                        feedbackChannel.showError('Ismeretlen hiba', 'Ismeretlen hiba történt. Kérjük jelezze kapcsolattartóink felé.');
                    } else if (displayedFeedback.severity < 1) {
                        feedbackChannel.showError('Hiba', displayedFeedback.message);
                    } else if (displayedFeedback.severity === 2) {
                        feedbackChannel.showWarning('Figyelem', displayedFeedback.message);
                    } else if (displayedFeedback.severity === 3) {
                        feedbackChannel.showInformation('Információ', displayedFeedback.message);
                    }
                }

                if (response && response.isOkay) {
                    callback(response);
                }
            },
            error: (xhr, status, error) => {
                feedbackChannel.showError('Ismeretlen hiba', 'Ismeretlen hiba történt. Kérjük jelezze kapcsolattartóink felé.');
            },
        });

        return result;
    }
};

const b5toast = {
    show: function (color, title, message) {
        title = title ? title : "";
        const html =
            `<div class="notify-container">
                <div class="rectangle ${color}">
                    <div class="notification-text">
                    <span><b>${title}:</b></span>
                    <span>&nbsp;&nbsp;${message}</span>
                    </div>
                </div>
            </div>`;
        const toastElement = b5toast.htmlToElement(html);
        document.getElementById("toast-container").appendChild(toastElement);
        setTimeout(() => toastElement.remove(), b5toast.delayInMilliseconds);
    },
    delayInMilliseconds: 5000,
    htmlToElement: (html) => {
        const template = document.createElement("template");
        html = html.trim();
        template.innerHTML = html;
        return template.content.firstChild;
    }
};

const documentTableBuilder  = {
    createTable: (title, columns, records, actions, page, sortState, elementsPerPage, actionButtonCount = 0, categoryId = null) => {
        elementsPerPage = Math.max(+elementsPerPage, 1);
        let documentTableElement = $(document.createElement('div'));
        documentTableElement.addClass('table-wrapper');
        let titleElement = documentTableBuilder.addTitleToTable(title);
        documentTableElement.append(titleElement);
        let documentTableContainerElement = $(document.createElement('div'));
        documentTableContainerElement.addClass('table-container');

        let attributeWrapperElement = $(document.createElement('table'));

        actions.onHeaderClick = (key) => {
            let newSortState = {
                sortBy: key,
                ascending: ((sortState.sortBy === key) ? !sortState.ascending : true)
            }
            actions.refresh(
                documentTableBuilder.createTable(
                    title,
                    columns,
                    documentTableBuilder.sortElements(records, newSortState),
                    actions,
                    1,
                    newSortState,
                    elementsPerPage,
                    actionButtonCount,
                    categoryId),
                newSortState,
                actionButtonCount);
        };
        let subtitles = documentTableBuilder.generateRow(columns, actions, null, true, sortState, false, categoryId);
        attributeWrapperElement.append(subtitles);

        if (records.length === 0) {
            attributeWrapperElement.addClass('empty');

            let emptyContentRow = $(document.createElement('tr'));

            let emptyContentElement = $(document.createElement('td'));
            emptyContentElement.addClass('empty');

            emptyContentElement.text('Ez a kategória üres.');  //TODO: consider renaming this

            emptyContentRow.append(emptyContentElement);
            attributeWrapperElement.append(emptyContentRow);

            emptyContentElement.attr('colspan', columns.size);
        } else {
            $.each(records.slice((page - 1) * elementsPerPage, (page - 1) * elementsPerPage + elementsPerPage), (index, record) => {
                let rowElement = documentTableBuilder.generateRow(record, actions, columns, false, null, actionButtonCount);
                attributeWrapperElement.append(rowElement);
            });
        }

        documentTableContainerElement.append(attributeWrapperElement);
        documentTableElement.append(documentTableContainerElement);

        if (records.length > elementsPerPage) {
            let paginationControlsContainer = $(document.createElement('div'));

            if (page !== 1) {
                let firstPageButton = buttonBuilder.createButton('◀◀');
                firstPageButton.onclick = () => {
                    actions.refresh(documentTableBuilder.createTable(title, columns, records, actions, 1, sortState, elementsPerPage), sortState);
                };
                paginationControlsContainer.append(firstPageButton);

                let previousPageButton = buttonBuilder.createButton('◀');
                previousPageButton.onclick = () => {
                    actions.refresh(documentTableBuilder.createTable(title, columns, records, actions, page - 1, sortState, elementsPerPage), sortState);
                };
                paginationControlsContainer.append(previousPageButton);
            }

            let pageDisplay = document.createTextNode(`${page}/${Math.ceil(records.length / elementsPerPage)}`);
            paginationControlsContainer.append(pageDisplay);

            if (page !== Math.ceil(records.length / elementsPerPage)) {
                let nextPageButton = buttonBuilder.createButton('▶');
                nextPageButton.onclick = () => {
                    actions.refresh(documentTableBuilder.createTable(title, columns, records, actions, page + 1, sortState, elementsPerPage), sortState);
                };
                paginationControlsContainer.append(nextPageButton);

                let lastPageButton = buttonBuilder.createButton('▶▶');
                lastPageButton.onclick = () => {
                    actions.refresh(documentTableBuilder.createTable(title, columns, records, actions, Math.ceil(records.length / elementsPerPage), sortState, elementsPerPage), sortState);
                };
                paginationControlsContainer.append(lastPageButton);
            }

            documentTableElement.append(paginationControlsContainer);
        }

        return documentTableElement;
    },

    createOnePageTable: (title, columns, records, actions, sortState) => {
        let documentTableElement = $(document.createElement('div'));
        documentTableElement.addClass('table-wrapper');
        let titleElement = documentTableBuilder.addTitleToTable(title);
        documentTableElement.append(titleElement);
        let documentTableContainerElement = $(document.createElement('div'));
        documentTableContainerElement.addClass('table-container');

        let attributeWrapperElement = $(document.createElement('table'));

        actions.onHeaderClick = (key) => {
            let newSortState = {
                sortBy: key,
                ascending: ((sortState.sortBy === key) ? !sortState.ascending : true)
            }
            actions.refresh(
                documentTableBuilder.createOnePageTable(
                    title,
                    columns,
                    documentTableBuilder.sortElements(records, newSortState),
                    actions,
                    1,
                    newSortState),
                newSortState);
        };
        let subtitles = documentTableBuilder.generateRow(columns, actions, null, true, sortState);
        attributeWrapperElement.append(subtitles);

        if (records.length === 0) {
            attributeWrapperElement.addClass('empty');

            let emptyContentRow = $(document.createElement('tr'));

            let emptyContentElement = $(document.createElement('td'));
            emptyContentElement.addClass('empty');

            emptyContentElement.text('Ez a kategória üres.');  //TODO: consider renaming this

            emptyContentRow.append(emptyContentElement);
            attributeWrapperElement.append(emptyContentRow);

            emptyContentElement.attr('colspan', columns.size);
        } else {
            $.each(records, (index, record) => {
                let rowElement = documentTableBuilder.generateRow(record, actions, columns, false, null);
                attributeWrapperElement.append(rowElement);
            });
        }

        documentTableContainerElement.append(attributeWrapperElement);
        documentTableElement.append(documentTableContainerElement);

        return documentTableElement;
    },

    createTableWithMovableProperties: (title, columns, actions, sortState, orderingRule = null) => {
        let documentTableElement = $(document.createElement('div'));
        documentTableElement.addClass('table-wrapper')
        let titleElement = documentTableBuilder.addTitleToTable(title);
        documentTableElement.append(titleElement);

        let attributeWrapperElement = $(document.createElement('div'));
        attributeWrapperElement.addClass('attribute-wrapper');

        let isEmpty = true;
        columns.every((column) => {
            if (column.content.size !== 0) {
                isEmpty = false;
                return false;
            }
            return true;
        });

        if (isEmpty) {
            let attributesElement = $(document.createElement('div'));
            attributesElement.addClass('attribute-wrapper');

            columns.forEach((column) => {
                let columnElement = documentTableBuilder.generateColumn(column.content, column.title, actions, orderingRule);
                attributesElement.append(columnElement);
            });

            attributeWrapperElement.append(attributesElement);
            attributeWrapperElement.addClass('empty');

            let emptyContentElement = $(document.createElement('p'));

            emptyContentElement.addClass('empty');
            emptyContentElement.addClass('attributes');
            emptyContentElement.text('Nem találhatóak a kért attribútumok.');

            attributeWrapperElement.append(emptyContentElement);
        } else {
            let max = columns.length - 1;

            columns.forEach((column, index) => {
                let type = {
                    pos: null,
                    col: index
                };

                switch (index) {
                    case 0:
                        type.pos = 'left';
                        break;
                    case max:
                        type.pos = 'right';
                        break;
                    default:
                        type.pos = 'middle';
                        break;
                }

                actions.moveElement = (iFrom, iTo, iIndex, orderingRule) => documentTableBuilder.moveElement(columns, iFrom, iTo, iIndex, actions, sortState, orderingRule);
                actions.moveSort = (columnIndex, iIndex, iIndexMove) => documentTableBuilder.moveSort(columns, columnIndex, iIndex, iIndexMove, actions, sortState);
                actions.updateSort = (iSortState) => actions.refreshTable(columns, iSortState);

                let columnElement = documentTableBuilder.generateColumn(column.content, column.title, type, actions, sortState, orderingRule);
                attributeWrapperElement.append(columnElement);
            });
        }

        documentTableElement.append(attributeWrapperElement);

        return documentTableElement;
    },

    addTitleToTable: (documentName) => {
        let documentTitleElement = $(document.createElement('p'));
        documentTitleElement.addClass('category-title');

        if (documentName) {
            documentTitleElement.text(documentName);
        } else {
            documentTitleElement.text('Érvénytelen táblanév');
        }

        return documentTitleElement;
    },
    generateRow: (record, actions, columns, isSubtitle, sortState, actionButtonCount = 0, categoryId = null) => {
        let attributeValueElementList = new Array();
        let rowElement = $(document.createElement('tr'));

        if (record.size === 0) {
            return rowElement;
        }

        let actionElement = null;
        let index = 0;
        record.forEach((recordValue, recordKey) => {
            if (recordKey === 'id') {
                return;
            }

            if (!isSubtitle && !columns.get(recordKey)) {
                return;
            }

            if (!isSubtitle && columns.get(recordKey)) {
                let attributeValueElement = $(document.createElement('td'));
                if (actionButtonCount > 0 && recordKey === "actions") {
                    attributeValueElement.css({ width: actionButtonCount * 10 + '%' });
                }
                if (recordKey === 'nameWithExtension') {
                    let link = $(document.createElement('a'));
                    link.text(record.get('nameWithExtension'))

                    link.click((e) => {
                        e.stopPropagation();
                        pdfPopUp.initPopUp(record.get('id'), record.get('nameWithExtension'));
                    });
                    attributeValueElement.append(link)
                    attributeValueElement.css({ cursor: 'pointer' });
                } else {
                    attributeValueElement.append(recordValue ?? '-');
                }
                rowElement.append(attributeValueElement);
                return;
            }

            let subtitleAttributeValueElement = $(document.createElement('th'));
            rowElement.append(subtitleAttributeValueElement);

            subtitleAttributeValueElement.css({
                'white-space': 'nowrap'
            });
            subtitleAttributeValueElement.data('recordValue', recordValue);
            subtitleAttributeValueElement.append(`${(recordKey === sortState.sortBy) ? (sortState.ascending ? '⯅ ' : '⯆ ') : ''}${recordValue ?? '-'}`);

            if (recordKey === 'actions') {
                actionElement = subtitleAttributeValueElement;
                return;
            }

            if (index < record.size - 2) {
                index++;
                const middleObject = createTransparentObject(recordValue);
                subtitleAttributeValueElement.append(middleObject);
                middleObject.mousedown((e) => {
                    let active = true;
                    let initialWidth = subtitleAttributeValueElement.width();
                    let nextColumnWidth = subtitleAttributeValueElement.next().width();
                    let startX = e.pageX;

                    $(document).mousemove((e) => {
                        if (!active) {
                            return;
                        }

                        let newWidth = initialWidth + (e.pageX - startX);
                        let newNextWidth = nextColumnWidth - (e.pageX - startX);

                        let saveFirst = subtitleAttributeValueElement.width();
                        let saveSecond = subtitleAttributeValueElement.next().width();
                        subtitleAttributeValueElement.width(newWidth);
                        subtitleAttributeValueElement.next().width(newNextWidth);
                        if (
                            Math.abs(newWidth - subtitleAttributeValueElement.width()) > 1 ||
                            Math.abs(newNextWidth - subtitleAttributeValueElement.next().width()) > 1
                        ) {
                            subtitleAttributeValueElement.width(saveFirst);
                            subtitleAttributeValueElement.next().width(saveSecond);
                        }
                    });
                    $(document).mouseup(() => {
                        if (!active) {
                            return;
                        }

                        active = false;
                        if (categoryId) {
                            var columnSizes = new Map();
                            attributeValueElementList.forEach((value, index) => {
                                if (index < attributeValueElementList.length - 1) {
                                    columnSizes.set(value.data('recordValue'), value.width().toFixed(2));
                                }
                            });
                            updateColumnSize(columnSizes, categoryId);
                        }
                        middleObject.off('mousemove');
                    });
                    subtitleAttributeValueElement.off('click');
                });
            }
            if (index < record.size - 1) {
                attributeValueElementList.push(subtitleAttributeValueElement);
            }
            subtitleAttributeValueElement.on('click', () => actions.onHeaderClick(recordKey));
        });

        if (!isSubtitle && actions && 'onRowClick' in actions) {
            rowElement.addClass('list-item');
            rowElement.on('click', () => {
                actions.onRowClick(record, rowElement, columns.size);
            });
        }
        if (categoryId != null) {
            editColumnWidth(categoryId, attributeValueElementList);
            if (actionElement !== null) {
                let withMultiplier = actionButtonCount > 0 ? actionButtonCount : 1;
                actionElement.css({ width: withMultiplier * 10 + '%' });
            }
        }

        return rowElement;
    },

    generateColumn: (column, header, type, actions, sortState, orderingRule = null) => {
        let columnsElement = $(document.createElement('div'));
        columnsElement.addClass('attributes');
        columnsElement.addClass('category-table-column');

        let titleBarSubtitleElement = $(document.createElement('p'));
        titleBarSubtitleElement.addClass('subtitle');
        titleBarSubtitleElement.text(`${sortState[type.col] ? '⯅ ' : '⯆ '}${header}`);
        titleBarSubtitleElement.on('click', () => {
            sortState[type.col] = !sortState[type.col];
            actions.updateSort(sortState);
        });

        columnsElement.append(titleBarSubtitleElement);

        if (column.length === 0) {
            return columnsElement;
        }

        $.each(column, (index, record) => {
            let attributeValueElementContainer = $(document.createElement('div'));
            attributeValueElementContainer.addClass('element');

            let attributeValueElement = $(document.createElement('p'));
            attributeValueElement.append(record.get('name') ?? '-');

            attributeValueElementContainer.append(attributeValueElement);

            type.row = index;

            attributeValueElementContainer.hover(
                () => documentTableBuilder.onMovableElementMouseEnter(attributeValueElement, type, index, actions, orderingRule),
                () => documentTableBuilder.onMovableElementMouseLeave(attributeValueElementContainer)
            );

            columnsElement.append(attributeValueElementContainer);
        });

        return columnsElement;
    },

    generateDropDownView: (width) => {
        let detailsRow = $(document.createElement('tr'));
        let detailsContainer = $(document.createElement('td'));
        detailsContainer.attr('colspan', width);

        detailsRow.append(detailsContainer);

        return detailsContainer;
    },

    closeDropDownView: (openedRow, requireApproval) => {
        if (requireApproval && !confirm('Esetleges változtatások mentés nélkül elveszhetnek, folytatja?')) {
            openedRow.updated = false;
            return false;
        }

        openedRow.obj.remove();
        openedRow = null;

        return true;
    },

    onMovableElementMouseEnter: (textElement, type, index, actions, orderingRule) => {
        if (type.pos === 'middle' || type.pos === 'right') {
            let moveLeftButton = buttonBuilder.createButton('◀');
            moveLeftButton.onclick = () => actions.moveElement(type.col, type.col - 1, index, orderingRule);
            textElement.before(moveLeftButton);
        }
        if ((type.pos === 'middle' || type.pos === 'left') && orderingRule != null) {
            let moveDownButton = buttonBuilder.createButton('▼');
            moveDownButton.onclick = () => actions.moveSort(type.col, index, index + 1);
            textElement.after(moveDownButton);
        }
        if ((type.pos === 'middle' || type.pos === 'left') && orderingRule != null) {
            let moveUpButton = buttonBuilder.createButton('▲');
            moveUpButton.onclick = () => actions.moveSort(type.col, index, index - 1);
            textElement.after(moveUpButton);
        }
        if (type.pos === 'middle' || type.pos === 'left') {
            let moveRightButton = buttonBuilder.createButton('▶');
            moveRightButton.onclick = () => actions.moveElement(type.col, type.col + 1, index, orderingRule);
            textElement.after(moveRightButton);
        }
    },

    onMovableElementMouseLeave: (containerElement) => {
        containerElement.find('button').remove();
    },

    moveElement: (columns, from, to, index, actions, sortState, orderingRule) => {
        let name = columns[from].content[index];
        columns[from].content.splice(index, 1);

        if (orderingRule == null) {
            columns[to].content.push(name);
        }
        else {
            let sortColumnContent = [];
            for (let i = 0; i < columns[to].content.length; i++) {
                if (i < orderingRule[name.get('id')]) {
                    sortColumnContent[i] = columns[to].content[i];
                }
                else {
                    sortColumnContent[i + 1] = columns[to].content[i];
                }
            }
            sortColumnContent[orderingRule[name.get('id')]] = name;
            columns[to].content = Object.values(sortColumnContent);
        }

        actions.refreshTable(columns, sortState);
    },

    moveSort: (columns, columnsIndex, index, iIndexMove, actions, sortState) => {
        iIndexMove = iIndexMove >= columns[columnsIndex].content.length ? 0 : iIndexMove;
        iIndexMove = iIndexMove < 0 ? columns[columnsIndex].content.length - 1 : iIndexMove;

        let container = columns[columnsIndex].content[iIndexMove];

        columns[columnsIndex].content.splice(iIndexMove, 1, columns[columnsIndex].content[index]);
        columns[columnsIndex].content.splice(index, 1, container);

        actions.refreshTable(columns, sortState);
    },

    sortElements: (records, sortState) => {
        if (records.length === 0) {
            return records;
        }

        records.sort((a, b) => {
            let valA = a.get(sortState.sortBy);
            let valB = b.get(sortState.sortBy);

            if (valA === null || valB === null) {
                let result = 0;

                if (valA === null) {
                    result++;
                }
                if (valB === null) {
                    result--;
                }

                return result;
            }

            return valA.localeCompare(valB);
        });

        if (!sortState.ascending) {
            records.reverse();
        }

        return records;
    }
};

const multiSelectBuilder = {
    /**
     * @param {Map<string, Object>} options map of options where value stores display text 
     * as 'text' and if its checked as 'selected'
     * @param {any} parent jQuery element which should toggle the dropdown
     * @param {any} onChangeEvent event that will be called when status is changed
     * @returns
     */
    createMultiSelect: (options, parent, onChangeEvent) => {
        let multiSelectElement = $(document.createElement('div'))
            .addClass('multiselect')
            .addClass('hidden');

        options.forEach((optionValue, optionKey) => {
            if (optionValue.title) {
                multiSelectElement.append(multiSelectBuilder.createTitle(optionValue.text));
                return;
            }

            multiSelectElement.append(multiSelectBuilder.createOption(
                optionKey, optionValue.text, optionValue.selected, onChangeEvent))
        });

        clickListener(parent, () => {
            multiSelectBuilder.toggleCheckboxArea(multiSelectElement);
        });
        clickListener(document, (_, event) => {
            multiSelectBuilder.toggleCheckboxArea(multiSelectElement, event);
        }, false);

        return {
            object: multiSelectElement,
            getSelected: () => { return multiSelectBuilder.getCheckedCheckboxes(multiSelectElement); }
        };
    },

    createTitle: (text) => {
        let optionText = $(document.createElement('span'))
            .text(text);

        return optionText;
    },

    createOption: (key, text, selected, onClickEvent) => {
        let optionLabel = $(document.createElement('label'));
        let optionCheckbox = $('<input type="checkbox">')
            .attr('id', key)
            .val(key)
            .prop('checked', selected)
            .change(() => { onClickEvent(optionCheckbox, key); });
        optionLabel.append(optionCheckbox).attr('for', key);

        let optionText = $(document.createElement('span'))
            .text(text);
        optionLabel.append(optionText);

        return optionLabel;
    },

    toggleCheckboxArea: (multiSelectObject, event = null) => {
        if (event == null) {
            multiSelectObject.toggleClass('hidden');
            return;
        }

        if (!$.contains(multiSelectObject[0], event.target)) {
            multiSelectObject.addClass('hidden');
        }
    },

    getCheckedCheckboxes: (multiSelectObject) => {
        var selected = [];

        $(multiSelectObject).find('label input:checked').each(function () {
            selected.push($(this).val());
        });

        return selected;
    }
};

const editDialogBuilder = {
    /**
     * Returns a dialog for editing values
     * @param {any} values a map describing the rows that should be edited:
     * when type is 'checkbox' or 'select':
     * ['recordKey', { type: type, title: recordTitle }]
     * when type is 'select':
     * ['recordKey', { type: type, options: options, title: recordTitle }]
     * where options is a map where the keys are the category ids and the values are the category names
     * @param {any} record the record which should be updated
     * @param {any} update the function which handles the update
     */
    createDialog: (values, record, update) => {
        let form = editDialogBuilder.generateForm(record, values);

        return $(document.createElement('div')).dialog({
            modal: true,
            title: 'Szerkesztés',
            autoOpen: false,
            dialogClass: 'edit-dialog',
            height: 300,
            width: 500,
            resizable: false,
            open: function () {
                $(this).append(form.obj);
            },
            buttons: {
                'Mentés': function () {
                    let results = new Map();

                    for (let [key, field] of form.fields) {
                        if (values.get(key).type === 'checkbox') {
                            results.set(key, field.is(':checked'));
                        } else if (values.get(key).type === 'select') {
                            results.set(key, field.val());
                        } else {
                            results.set(key, field.val());
                        }
                    }

                    update(results, record);
                    $(this).dialog('close');
                },
                'Mégse': function () {
                    $(this).dialog('close');
                }
            }
        });
    },

    createEmptyDialog: (id, title, update, content, resizable = false, startingWidth = 500, startingHeight = 300, startingPosition = null) => {
        let dialogBody = document.createElement('div');
        dialogBody.id = id;
        let dialog = $(dialogBody).dialog({
            modal: true,
            title: title,
            autoOpen: false,
            dialogClass: 'edit-dialog',
            width: startingWidth,
            height: startingHeight,
            resizable: resizable,
            open: function () {
                $(this).append(content);
            },
            buttons: {
                'Mentés': function () {
                    update();
                    $(this).dialog('close');
                },
                'Mégse': function () {
                    $(this).dialog('close');
                }
            }
        });
        if (startingPosition !== null) {
            dialog.dialog("option", "position", startingPosition);
        }
        return dialog;
    },

    generateForm: (record, values) => {
        let fields = new Map();

        let form = $(document.createElement('form'));

        for (let [key, value] of record) {
            let data = values.get(key)

            if (data) {
                let containerDiv = $(document.createElement('div'));
                containerDiv.addClass('edit-dialog-input')
                containerDiv.append(editDialogBuilder.generateLabel(data, key))

                if (data.type === 'checkbox') {
                    containerDiv.addClass('row');
                }
                else {
                    containerDiv.addClass('col');
                }

                fields.set(key, editDialogBuilder.generateInputs(data, key, value));
                containerDiv.append(fields.get(key));
                form.append(containerDiv)
            }
        }

        return {
            fields: fields,
            obj: form
        }
    },

    generateLabel: (data, key) => {
        let label = $(document.createElement('label'));
        label.attr('for', key);
        label.html(data.title);

        return label;
    },

    generateInputs: (data, key, value) => {
        if (data.type === 'select') {
            let input = $(document.createElement('select'));
            input.attr('name', key);

            data.options.forEach((name, id) => {
                let option = $(document.createElement('option'));
                option.attr('value', id);
                option.html(name);

                if (value === name) {
                    option.attr('selected', 'selected');
                }

                input.append(option);
            });

            return input;
        }

        let input = $(document.createElement('input'));
        input.attr('type', data.type);
        input.attr('name', key);

        if (data.type === 'checkbox') {
            input.prop('checked', value === 'Igen');
        } else {
            input.attr('value', value);
        }

        return input;
    }
};

const buttonBuilder = {
    createButton: (text) => {
        let button = document.createElement('BUTTON');
        button.className = "generated_button";

        let buttonText = document.createTextNode(text);
        button.appendChild(buttonText);

        return button;
    },
    createAnchorButton: (text) => {
        let anchorElement = document.createElement('a');

        anchorElement.className = "generated_button";
        anchorElement.classList.add('.button')
        anchorElement.href = "javascript:void(0);";
        anchorElement.text = text;

        return anchorElement;
    }
};

const storageHandler = {
    setCookie: (key, value, expireDays) => {
        const date = new Date();
        date.setTime(date.getTime() + (expireDays * 24 * 60 * 60 * 1000));

        document.cookie = `${key}=${value}; expires=${date.toUTCString()}; Secure; path=/`;
    },

    getCookie: (key) => {
        let name = key + '=';

        let decodedCookie = decodeURIComponent(document.cookie);
        let cookies = decodedCookie.split(';');

        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i];

            while (cookie.charAt(0) === ' ') {
                cookie = cookie.substring(1);
            }

            if (cookie.indexOf(name) === 0) {
                return cookie.substring(name.length, cookie.length);
            }
        }

        return '';
    },

    applyWatermarkSettings: () => {
        let watermarkSettingsJSON = localStorage.getItem('watermarkSettings');

        if (!watermarkSettingsJSON) {
            return;
        }

        let watermarkSettings = JSON.parse(watermarkSettingsJSON);

        if (watermarkSettings.watermarkOpacity) {
            $("#sliderInput").val(watermarkSettings.watermarkOpacity);
        }
        if (watermarkSettings.sideWatermarkPosition) {
            $("#sidedWatermarkPositionSelectMenu").val(watermarkSettings.sideWatermarkPosition);
        }
        if (watermarkSettings.centeredWatermarkHorizontalOffset) {
            $("#centeredWatermarkHorizontalOffset").val(watermarkSettings.centeredWatermarkHorizontalOffset);
        }
        if (watermarkSettings.centeredWatermarkVerticalOffset) {
            $("#centeredWatermarkVerticalOffset").val(watermarkSettings.centeredWatermarkVerticalOffset);
        }
        if (watermarkSettings.targetOfDocumentUsageId) {
            $("#targetOfDocumentUsageSelectMenu").val(watermarkSettings.targetOfDocumentUsageId);
        }
        if (watermarkSettings.fontSize) {
            let fontSize = Math.max(Math.min(+watermarkSettings.fontSize, +$("#fontSize").attr("max")), +$("#fontSize").attr("min"));
            $("#fontSize").val(fontSize);
        }
    },

    saveWatermarkSettings: () => {
        let centeredWatermarkHorizontalOffsetValue = +$("#centeredWatermarkHorizontalOffset").val();
        centeredWatermarkHorizontalOffsetValue = Math.max(Math.min(centeredWatermarkHorizontalOffsetValue, 100), -100);

        let centeredWatermarkVerticalOffsetValue = +$("#centeredWatermarkVerticalOffset").val();
        centeredWatermarkVerticalOffsetValue = Math.max(Math.min(centeredWatermarkVerticalOffsetValue, 100), -100);

        let watermarkFontSizeValue = +$("#fontSize").val();
        watermarkFontSizeValue = Math.max(Math.min(watermarkFontSizeValue, +$("#fontSize").attr("max")), +$("#fontSize").attr("min"));


        $("#centeredWatermarkHorizontalOffset").val(centeredWatermarkHorizontalOffsetValue);
        $("#centeredWatermarkVerticalOffset").val(centeredWatermarkVerticalOffsetValue);
        $("#fontSize").val(watermarkFontSizeValue);

        let watermarkSettings = {
            watermarkOpacity: $("#sliderInput").val(),
            sideWatermarkPosition: $("#sidedWatermarkPositionSelectMenu").val(),
            centeredWatermarkHorizontalOffset: centeredWatermarkHorizontalOffsetValue,
            centeredWatermarkVerticalOffset: centeredWatermarkVerticalOffsetValue,
            targetOfDocumentUsageId: $("#targetOfDocumentUsageSelectMenu").val(),
            fontSize: watermarkFontSizeValue
        }

        localStorage.setItem('watermarkSettings', JSON.stringify(watermarkSettings));
    }
};

const pdfViewer = {
    initialState: {},
    controls: {},
    canvas: null,
    renderTimeout: null,
    renderTask: null,
    maxZoomValue: 9.99,
    minZoomValue: 0.01,
    zoomIncrementValue: 0.25,

    /**
     * NOTE: canvas needs to be loaded with `document.querySelector()`
     */
    loadViewer: (dataDTO, title, id, page, btnWatermarkEditor, btnPrevPg, btnNextPg, btnZoomIn,
        btnZoomOut, btnFitPg, btnFitWidth, btnPrint, btnDownload, btnOpenInNewTab,
        btnRotateLeft, btnRotateRight, btnIsRotateAll,
        inCurrentPg, inZoom, labelNumPg, canvas, afterLoadEvent) => {

        pdfViewer.initialState = {
            pdfDoc: null,
            currentPage: 1,
            pageCount: 0,
            pdfScale: 1
        };

        pdfViewer.controls = {
            btnWatermarkEditor: $(btnWatermarkEditor),
            btnPrevPg: $(btnPrevPg),
            btnNextPg: $(btnNextPg),
            btnZoomIn: $(btnZoomIn),
            btnZoomOut: $(btnZoomOut),
            btnFitPg: $(btnFitPg),
            btnFitWidth: $(btnFitWidth),
            btnPrint: $(btnPrint),
            btnDownload: $(btnDownload),
            btnOpenInNewTab: $(btnOpenInNewTab),
            btnRotateLeft: $(btnRotateLeft),
            btnRotateRight: $(btnRotateRight),
            btnIsRotateAll: $(btnIsRotateAll),
            inCurrentPg: $(inCurrentPg),
            inZoom: $(inZoom),
            labelNumPg: $(labelNumPg)
        };

        let dataURI = dataDTO.documentData;
        if (dataDTO.isWatermarked) {
            pdfViewer.controls.btnWatermarkEditor.show();

            pdfViewer.controls.btnRotateLeft.prop("disabled", false);
            pdfViewer.controls.btnRotateRight.prop("disabled", false);
            pdfViewer.controls.btnIsRotateAll.prop("disabled", false);
        }
        else {
            if (document.querySelector(".editor-menu").style.maxHeight) {
                pdfViewer.controls.btnWatermarkEditor.click();
            }
            pdfViewer.controls.btnWatermarkEditor.hide();

            pdfViewer.controls.btnRotateLeft.prop("disabled", true);
            pdfViewer.controls.btnRotateRight.prop("disabled", true);
            pdfViewer.controls.btnIsRotateAll.prop("disabled", true);
        }

        pdfViewer.canvas = canvas;

        pdfViewer.bindControls(dataURI, id, title);

        pdfjsLib
            .getDocument(pdfViewer.convertDataURIToBinary(dataURI))
            .promise.then((data) => {
                pdfViewer.initialState.pdfDoc = data;
                pdfViewer.initialState.currentPage = Math.min(
                    Math.max(page, 1),
                    pdfViewer.initialState.pdfDoc.numPages,
                );

                pdfViewer.controls.labelNumPg.text(pdfViewer.initialState.pdfDoc.numPages);

                pdfViewer.loadPage(true);

                afterLoadEvent(data);
            });
    },

    bindControls: (dataURI, id, title) => {
        clickListener(pdfViewer.controls.btnPrevPg, pdfViewer.showPreviousPage);
        clickListener(pdfViewer.controls.btnNextPg, pdfViewer.showNextPage);
        clickListener(pdfViewer.controls.btnZoomIn, pdfViewer.zoomIn);
        clickListener(pdfViewer.controls.btnZoomOut, pdfViewer.zoomOut);
        clickListener(pdfViewer.controls.btnFitPg, pdfViewer.fitPage);
        clickListener(pdfViewer.controls.btnFitWidth, pdfViewer.fitWidth);
        clickListener(pdfViewer.controls.btnPrint, () => { pdfViewer.print(dataURI, id, title) });
        clickListener(pdfViewer.controls.btnDownload, () => { pdfViewer.download(dataURI, id, title) });
        clickListener(pdfViewer.controls.btnOpenInNewTab, () => { openDocumentInNewTab(id, title) });

        enterKeypressListener(pdfViewer.controls.inCurrentPg, () => {
            pdfViewer.showPage(Number(pdfViewer.controls.inCurrentPg.val()));
        });

        enterKeypressListener(pdfViewer.controls.inZoom, () => {
            pdfViewer.initialState.pdfScale = Math.max(pdfViewer.minZoomValue, Math.min(pdfViewer.maxZoomValue, Number(pdfViewer.controls.inZoom.val()) / 100));
            pdfViewer.loadPage();
        });
    },

    loadPage: (isLoadInitialConfiguration = false) => {
        pdfViewer.initialState.pdfDoc
            .getPage(pdfViewer.initialState.currentPage)
            .then((page) => {
                clearTimeout(pdfViewer.renderTimeout);

                if (pdfViewer.renderTask) {
                    pdfViewer.renderTask.cancel();
                }

                pdfViewer.renderTimeout = setTimeout(() => { pdfViewer.renderPage(page, isLoadInitialConfiguration) }, 100);
            });
    },

    renderPage: (page, isLoadInitialConfiguration) => {
        if (isLoadInitialConfiguration) {
            pdfViewer.setPdfScaleToFitPage(page);
        }

        const ctx = pdfViewer.canvas.getContext('2d');
        const viewport = page.getViewport({ scale: pdfViewer.initialState.pdfScale });
        const outputScale = window.devicePixelRatio || 1;

        pdfViewer.canvas.width = Math.floor(viewport.width * outputScale);
        pdfViewer.canvas.height = Math.floor(viewport.height * outputScale);
        pdfViewer.canvas.style.width = Math.floor(viewport.width) + "px";
        pdfViewer.canvas.style.height = Math.floor(viewport.height) + "px";

        const transform = outputScale !== 1
            ? [outputScale, 0, 0, outputScale, 0, 0]
            : null;

        const renderCtx = {
            canvasContext: ctx,
            transform: transform,
            viewport: viewport,
        };

        pdfViewer.renderTask = page.render(renderCtx);
        pdfViewer.renderTask.promise.catch((error) => {
        });

        pdfViewer.controls.inCurrentPg.val(pdfViewer.initialState.currentPage);
        pdfViewer.controls.inZoom.val(Math.floor(pdfViewer.initialState.pdfScale * 100));
    },

    setPdfScaleToFitPage: (page) => {
        const initialViewport = page.getViewport({ scale: 1 });
        const scaleX = getInnerWidth(pdfViewer.canvas.parentElement) / initialViewport.width;
        const scaleY = getInnerHeight(pdfViewer.canvas.parentElement) / initialViewport.height;

        pdfViewer.initialState.pdfScale = Math.min(scaleX, scaleY);
    },

    showPreviousPage: () => {
        if (pdfViewer.initialState.pdfDoc === null || pdfViewer.initialState.currentPage <= 1)
            return;
        pdfViewer.initialState.currentPage--;
        pdfViewer.loadPage();
    },

    showNextPage: () => {
        if (pdfViewer.initialState.pdfDoc === null || pdfViewer.initialState.currentPage >= pdfViewer.initialState.pdfDoc.numPages)
            return;

        pdfViewer.initialState.currentPage++;
        pdfViewer.loadPage();
    },

    showPage: (num) => {
        pdfViewer.initialState.currentPage = Math.min(
            Math.max(num, 1),
            pdfViewer.initialState.pdfDoc.numPages,
        );

        pdfViewer.loadPage();
    },

    zoomIn: () => {
        pdfViewer.initialState.pdfScale = Math.min(pdfViewer.maxZoomValue, pdfViewer.initialState.pdfScale + pdfViewer.zoomIncrementValue);

        pdfViewer.loadPage();
    },

    zoomOut: () => {
        pdfViewer.initialState.pdfScale = Math.max(pdfViewer.minZoomValue, pdfViewer.initialState.pdfScale - pdfViewer.zoomIncrementValue);

        pdfViewer.loadPage();
    },

    fitPage: () => {
        pdfViewer.initialState.pdfDoc
            .getPage(pdfViewer.initialState.currentPage)
            .then((page) => {
                pdfViewer.setPdfScaleToFitPage(page);

                pdfViewer.loadPage();
            });
    },

    fitWidth: () => {
        pdfViewer.initialState.pdfDoc
            .getPage(pdfViewer.initialState.currentPage)
            .then((page) => {
                const viewport = page.getViewport({ scale: 1 });
                pdfViewer.initialState.pdfScale = getInnerWidth(pdfViewer.canvas.parentElement) / viewport.width;

                pdfViewer.loadPage();
            });
    },

    print: (dataURI, id, title) => {
        printJS({
            printable: dataURI,
            type: 'pdf',
            base64: true,
            documentTitle: title
        });
        logDocumentEvent('print', id);
    },

    download: (dataURI, id, title) => {
        const downloadLink = document.createElement("a");
        downloadLink.href = 'data:application/pdf;base64,' + dataURI;
        downloadLink.download = title;
        downloadLink.click();
        downloadLink.remove();
        logDocumentEvent('download', id);
    },

    convertDataURIToBinary: (dataURI) => {
        const raw = window.atob(dataURI);
        const rawLength = raw.length;
        const array = new Uint8Array(new ArrayBuffer(rawLength));

        for (let i = 0; i < rawLength; i++) {
            array[i] = raw.charCodeAt(i);
        }
        return array;
    }
};

const pdfPopUp = {
    state: {
        rotateAll: true
    },

    // TODO: load elements as parameters
    initPopUp: (documentId, documentTitle) => {
        storageHandler.applyWatermarkSettings();

        pdfPopUp.state.documentId = documentId;
        pdfPopUp.state.documentTitle = documentTitle;
        pdfPopUp.state.rotations = [0];

        $("#PdfIsRotateAll").html("Összes lap forgatása");

        pdfPopUp.bindControls();

        pdfPopUp.showPopUp(1, true);
    },

    bindControls: () => {
        clickListener($("#updateWatermarkButton"), () => {
            storageHandler.saveWatermarkSettings();
            pdfPopUp.showPopUp(pdfViewer.initialState.currentPage)
        });

        clickListener($("#PdfRotateCCW"), () => {
            if (pdfPopUp.state.rotateAll) {
                for (let i = 0; i < pdfPopUp.state.rotations.length; i++) {
                    pdfPopUp.rotatePageCCW(i);
                }
            } else {
                pdfPopUp.rotatePageCCW(pdfViewer.initialState.currentPage - 1);
            }

            pdfPopUp.showPopUp(pdfViewer.initialState.currentPage);
        });

        clickListener($("#PdfRotateCW"), () => {
            if (pdfPopUp.state.rotateAll) {
                for (let i = 0; i < pdfPopUp.state.rotations.length; i++) {
                    pdfPopUp.rotatePageCW(i);
                }
            } else {
                pdfPopUp.rotatePageCW(pdfViewer.initialState.currentPage - 1);
            }

            pdfPopUp.showPopUp(pdfViewer.initialState.currentPage);
        });

        clickListener($("#PdfIsRotateAll"), (button) => {
            if (pdfPopUp.state.rotateAll) {
                $(button).html("Jelenlegi lap forgatása");

                pdfPopUp.state.rotateAll = false;
            } else {
                $(button).html("Összes lap forgatása");

                pdfPopUp.state.rotateAll = true;
            }
        });
    },

    showPopUp: (page = 1, init = false) => {
        requestInvoker
            .executeQuery('/Documents/DocumentPreview', pdfPopUp.getArgs())
            .then((response) => {
                pdfViewer.loadViewer(response.responseObject, pdfPopUp.state.documentTitle, pdfPopUp.state.documentId, page,
                    $('#PdfWatermarkEditor'), $("#PdfPreviousPage"), $("#PdfNextPage"), $("#PdfZoomIn"), $("#PdfZoomOut"), $("#PdfFitPage"), $("#PdfFitWidth"),
                    $("#PdfPrint"), $("#PdfDownload"), $("#OpenInNewTab"), $("#PdfRotateCCW"), $("#PdfRotateCW"), $("#PdfIsRotateAll"),
                    $("#PdfPageNum"), $("#PdfZoomScale"), $("#PdfPageCount"), document.querySelector('#pdfPreview'),
                    (init) ? pdfPopUp.createRotationArray : () => { }
                );

                if (init) {
                    $("#pdfViewer").modal({
                        escapeClose: true,
                        clickClose: false,
                        showClose: true,
                        fadeDuration: 100,
                        closeClass: 'icon-remove',
                        modalClass: 'pdf-modal',
                        closeText: 'X'
                    });
                }
            });
    },

    getArgs: () => {
        return {
            documentId: pdfPopUp.state.documentId,
            watermarkOpacity: $("#sliderInput").val(),
            sideWatermarkPosition: $("#sidedWatermarkPositionSelectMenu").val(),
            centeredWatermarkHorizontalOffset: $("#centeredWatermarkHorizontalOffset").val(),
            centeredWatermarkVerticalOffset: $("#centeredWatermarkVerticalOffset").val(),
            targetOfDocumentUsageId: $("#targetOfDocumentUsageSelectMenu").val(),
            documentRotations: JSON.stringify(pdfPopUp.state.rotations),
            fontSize: $("#fontSize").val()
        };
    },

    /**
     * @param {any} page number of page (counting starts from 0)
     */
    rotatePageCCW: (page) => {
        pdfPopUp.state.rotations[page] = (((pdfPopUp.state.rotations[page] - 1) % 4) + 4) % 4; // calculate modulo, since -1%4=-1 instead of 3
    },

    /**
     * @param {any} page number of page (counting starts from 0)
     */
    rotatePageCW: (page) => {
        pdfPopUp.state.rotations[page] = (pdfPopUp.state.rotations[page] + 1) % 4;
    },

    createRotationArray: (pdfDoc) => {
        pdfPopUp.state.rotations = [];

        for (let i = 0; i < pdfDoc.numPages; i++) {
            pdfPopUp.state.rotations.push(0);
        }
    }
};

let createCombobox = (selectElement, values) => {
    $.each(values, (index, value) => {
        let optionElement = $(document.createElement('option'));
        optionElement.attr('value', value.key);
        optionElement.text(value.value);
        if (index === 0) {
            optionElement.attr('selected', true);
        }

        selectElement.append(optionElement);
    });
}

let logDocumentEvent = (event, id) => {
    storageHandler.applyWatermarkSettings();

    let args = {
        eventName: event,
        documentId: id,
        watermarkOpacity: $("#sliderInput").val(),
        sideWatermarkPosition: $("#sidedWatermarkPositionSelectMenu").val(),
        centeredWatermarkHorizontalOffset: $("#centeredWatermarkHorizontalOffset").val(),
        centeredWatermarkVerticalOffset: $("#centeredWatermarkVerticalOffset").val(),
        targetOfDocumentUsageId: $("#targetOfDocumentUsageSelectMenu").val(),
        fontSize: $("#fontSize").val()
    };


    requestInvoker
        .executeCommand('/Documents/LogDocumentEvent', args);
}

let enterKeypressListener = (observer, event) => {
    $(observer).unbind();
    $(observer).keypress((e) => {
        if (e.which === 13) {
            e.preventDefault();
            event();
            return false;
        }
    });
}

let clickListener = (observer, event, unbindOtherListeners = true) => {
    if (unbindOtherListeners) {
        $(observer).unbind();
    }
    $(observer).click((e) => {
        e.stopPropagation();
        event(observer, e);
    });
}

let getInnerHeight = (element) => {
    const computed = getComputedStyle(element),
        padding = parseInt(computed.paddingTop) + parseInt(computed.paddingBottom);

    return element.clientHeight - padding;
}

let getInnerWidth = (element) => {
    const computed = getComputedStyle(element),
        padding = parseInt(computed.paddingLeft) + parseInt(computed.paddingRight);

    return element.clientWidth - padding;
}

let convertStringToPixelLength = (recordValue) => {
    const text = recordValue;
    const div = document.createElement('div');
    div.textContent = text;
    div.style.display = 'inline-block';
    div.style.visibility = 'hidden';
    document.body.appendChild(div);
    const widthInPixels = div.offsetWidth;
    document.body.removeChild(div);

    return widthInPixels;
}

const createTransparentObject = (recordValue) => {
    const middleObject = $('<span>');
    let widthInPixels = convertStringToPixelLength(recordValue);
    middleObject.css({
        width: '20px',
        height: '15px',
        display: 'inline-block',
        backgroundColor: 'transparent',
        position: 'relative',
        cursor: 'col-resize',
        left: `calc(50% - ${widthInPixels / 2}px)`,
    });
    return middleObject;
}

let updateColumnSize = (columnSizes, categoryId) => {
    if (columnSizes.size == 0) {
        return;
    }

    let args = {
        columnSizeNames: Array.from(columnSizes.keys()),
        columnSizeSizes: Array.from(columnSizes.values()),
        categoryId: categoryId
    }
    requestInvoker.executeUpdate('/Preferences/ColumnSize',args);

}
async function editColumnWidth(categoryId, attributeValueElementList) {
    if (attributeValueElementList.length === 0) {
        return;
    }

    let result = await requestInvoker.executeQuery('/Preferences/ColumnsSize', { categoryId });

    if (result.responseObject.length === 0) {
        return;
    };

    const sizeAttributes = result.responseObject
        .flatMap(obj => obj.preferences.Size)
        .filter(value => 
            value.preferenceName === 'Size' &&
            value.preferenceValue !== null &&
            value.preferenceValue !== 0
        );

    sizeAttributes.forEach(value => {
        const matchingElements = attributeValueElementList.filter(element =>
            element.data('recordValue') === value.attributeName
        );

        matchingElements.forEach(element => {
            element.width(value.preferenceValue);
        });
    });
}
