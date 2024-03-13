let openedRow = null;
let mainTableSortState = {
    sortBy: 'nameWithExtension',
    ascending: true
};
let detailsTableSortState = {
    sortBy: 'metadataName',
    ascending: true
};
let filterSelector = null;

$(document).ready(() => {
    $('#button-addon2').on('click', (e) => {
        e.stopPropagation();

        refreshPage();
    });

    enterKeypressListener($('#search-txt'), refreshPage);

    initFilters();
    populatePage();
});

function initFilters() {
    requestInvoker
        .executeQuery('/Categories', { searchText: '*' })
        .then((response) => {
            let documentsFilterSettingsJSON = localStorage.getItem('documentsFilterSettings');
            let documentsFilterSettings = null;

            if (documentsFilterSettingsJSON) {
                documentsFilterSettings = JSON.parse(documentsFilterSettingsJSON);
            }

            let filters = new Map();
            filters.set('TITLE-0', { text: 'Keresési célok:', title: true });
            filters.set('title', { text: 'Dokumentum név', selected: documentsFilterSettings ? documentsFilterSettings.includes('title') : true });
            filters.set('freetext', { text: 'Dokumentum tartalom', selected: documentsFilterSettings ? documentsFilterSettings.includes('freetext') : false });
            filters.set('property', { text: 'Dokumentum tulajdonság', selected: documentsFilterSettings ? documentsFilterSettings.includes('property') : false });
            filters.set('TITLE-1', { text: 'Kategóriák:', title: true });

            $.each(response.responseObject, (index, category) => {
                filters.set(category.id, { text: category.categoryName, selected: documentsFilterSettings ? documentsFilterSettings.includes(category.id) : true });
            });

            filterSelector = multiSelectBuilder.createMultiSelect(filters, $('#filter-icon'), (lastUpdatedCheckbox, key) => { validateSelection(lastUpdatedCheckbox, key); cacheFilterSettings(); });
            $('#filter-container').append(filterSelector.object);
        });
}

function validateSelection(lastUpdatedCheckbox, key) {
    let selected = filterSelector.getSelected()
    if (!selected.includes('title') && !selected.includes('freetext') && !selected.includes('property')) {
        lastUpdatedCheckbox.prop('checked', selected);
        b5toast.show('info', 'Információ', 'Legalább egy keresési célt ki kell jelölni.');
        return;
    }

    if (key == 'freetext' && lastUpdatedCheckbox.is(":checked")) {
        b5toast.show('info', 'Információ', 'Tartalomra való keresésnél csak egész szavas keresés lehetséges. Helyettesitő karakterek (*, ?) nem kerülnek figyelembe.');
    }
}

function cacheFilterSettings() {
    if (!filterSelector) {
        return;
    }
    localStorage.setItem('documentsFilterSettings', JSON.stringify(filterSelector.getSelected()));
}

function refreshPage() {
    let content = $('#documentTableContainer');
    content.remove();
    populatePage();
}

function populatePage() {
    requestInvoker
        .executeQuery('/Documents/Admin', { searchText: $('#search-txt').val() ?? '*', filters: filterSelector ? JSON.stringify(filterSelector.getSelected()) : '' })
        .then((response) => {
            let contentContainerElement = $('#contentContainer');
            let documents = response.responseObject;

            let tableName = 'Dokumentumok';

            let columns = new Map();
            columns.set('nameWithExtension', 'Név');
            columns.set('category', 'Kategória');
            columns.set('isObsolete', 'Elavult');
            columns.set('actions', 'Műveletek');

            let records = [];

            let tableContainer = $(document.createElement('div'));
            tableContainer.attr('id', 'documentTableContainer');

            $.each(documents, (index, document) => {
                let record = new Map();
                record.set('id', document.id);
                record.set('nameWithExtension', document.nameWithExtension);
                record.set('category', document.category.categoryName);
                record.set('categoryId', document.category.id)
                record.set('isObsolete', document.isObsolete ? "Igen" : "Nem")
                record.set('attributes', document.attributes);

                let editButton = buttonBuilder.createButton('Szerkesztés');
                editButton.onclick = (e) => {
                    e.stopPropagation();
                    editDocumentCategory(record, columns);
                };

                let openInNewTabButton = buttonBuilder.createButton('Megnyitás új lapon');
                openInNewTabButton.onclick = (e) => {
                    e.stopPropagation();
                    openDocumentInNewTab(document.id, document.nameWithExtension);
                };

                record.set('actions', [editButton, openInNewTabButton]);

                records.push(record);
            });

            let table;

            let actions = {
                refresh: (newTable, newMainTableSortState) => {
                    mainTableSortState = newMainTableSortState;
                    table.remove();
                    table = newTable;
                    tableContainer.append(table);
                },
                onRowClick: onRowClick
            };

            table = documentTableBuilder.createTable(tableName, columns, documentTableBuilder.sortElements(records, mainTableSortState), actions, 1, mainTableSortState, 25, 2);
            tableContainer.append(table);
            contentContainerElement.append(tableContainer);
        });
}

function onRowClick(record, row, numParentColumns) {
    requestInvoker
        .executeQuery('/Documents/Metadata', {documentId: record.get('id')})
        .then((response) => {
            let columns = new Map();
            columns.set('metadataName', 'Metaadat neve');
            columns.set('metadataValue', 'Értéke');
            columns.set('actions', 'Műveletek');

            let attributes = [];

            $.each(response.responseObject, (key, value) => {
                let attribute = new Map();
                attribute.set('metadataName', key);
                attribute.set('metadataValue', value);
                attribute.set('documentId', record.get('id'));

                let button = buttonBuilder.createButton('Szerkesztés');
                button.onclick = (e) => {
                    e.stopPropagation();
                    editAttribute(attribute, columns, () => onRowClick(record, row, numParentColumns));
                };
                attribute.set('actions', button);

                attributes.push(attribute);
            });

            openDetails(record, row, numParentColumns, {
                attributes: attributes,
                columns: columns
            });
        });
}

function openDetails(record, row, numParentColumns, details) {
    if (openedRow) {
        documentTableBuilder.closeDropDownView(openedRow, false);
    }

    let detailsContainer = documentTableBuilder.generateDropDownView(numParentColumns);
    detailsContainer.addClass('opened');

    let tableContainer = $(document.createElement('div'));
    let table;

    let actions = {
        refresh: (newTable, newDetailsTableSortState) => {
            detailsTableSortState = newDetailsTableSortState;
            table.remove();
            table = newTable;
            tableContainer.append(table);
            table.addClass('opened');
        }
    }

    table = documentTableBuilder.createTable(record.get('nameWithExtension'), details.columns, documentTableBuilder.sortElements(details.attributes, detailsTableSortState), actions, 1, detailsTableSortState, 25);
    table.addClass('opened');

    tableContainer.append(table)
    detailsContainer.append(tableContainer);

    let closeButton = buttonBuilder.createButton('Bezárás');
    closeButton.onclick = () => documentTableBuilder.closeDropDownView(openedRow, false);
    detailsContainer.append(closeButton);

    row.after(detailsContainer.parent());

    openedRow = {
        obj: detailsContainer.parent(),
        id: record.get('id')
    };
}

function editAttribute(attribute, columns, refresh) {
    let values = new Map();
    values.set('metadataName', {type: 'text', title: columns.get('metadataName')});
    values.set('metadataValue', {type: 'text', title: columns.get('metadataValue')});

    editDialogBuilder.createDialog(values, attribute, (iResults, iAttribute) => updateAttribute(iResults, iAttribute, refresh)).dialog('open');
}

function editDocumentCategory(document, columns) {
    requestInvoker
        .executeQuery('/Categories', {searchText: '*'})
        .then((response) => {
            let categories = response.responseObject;
            let options = new Map();

            $.each(categories, (index, category) => {
                options.set(category.id, category.categoryName);
            });

            let values = new Map();
            values.set('category', {type: 'select', options: options, title: columns.get('category')});
            values.set('isObsolete', {type: 'checkbox', title: "Elavult", categories})

            editDialogBuilder.createDialog(values, document, (iResult, iDocument) => updateDocumentCategory(iResult, iDocument, () => {
                let content = $('#documentTableContainer');
                content.remove();
                populatePage();
            })).dialog('open');
        });
}

function updateAttribute(results, attribute, refresh) {
    requestInvoker
        .executeUpdate('/Documents/Attribute',
            {
                documentId: attribute.get('documentId'),
                newAttributeName: results.get('metadataName'),  //TODO: change these to attribute
                attributeValue: results.get('metadataValue'),
                oldAttributeName: attribute.get('metadataName')
            })
        .then((response) => {
            refresh();
        });
}

function updateDocumentCategory(result, document, refresh) {
    requestInvoker
        .executeUpdate('/Documents/DocumentCategoryRelation',
            {
                documentId: document.get('id'),
                newCategoryId: result.get('category')
            })
        .then((response) => {
            refresh();
        });

    requestInvoker
        .executeUpdate('/Administration/UpdateDocumentIsObsolete',
            {
                documentId: document.get('id'),
                isObsolete: result.get('isObsolete')
            })
        .then((response) => {
        });
}
