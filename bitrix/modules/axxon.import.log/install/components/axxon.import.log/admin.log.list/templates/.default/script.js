$(document).ready(function () {
    'use strict';

    const logList = $(".log-list tbody");
    const tableFields = [
        'timestamp',
        'field',
        'old_value',
        'new_value'
    ];

    // Событие нажатия на кнопку стрелки пагинации
    $(document).on('click', '.js-subnav-page-prev', function () {
        $('.js-load_page.adm-nav-page-active').prev().trigger('click');
    })
    $(document).on('click', '.js-subnav-page-next', function () {
        $('.js-load_page.adm-nav-page-active').next().trigger('click');
    })

    // Событие нажатия на кнопку страницы пагинации
    $(document).on('click', '.js-load_page', function () {
        $('.log-navigation .adm-nav-page-active').removeClass('adm-nav-page-active');
        $(this).addClass('adm-nav-page-active');

        BX.ajax.runComponentAction('axxon.import.log:admin.log.list',
            'list', {
                mode: 'class',
                signedParameters: BX.axxon.params,
                data: {
                    'page': Number($(this).data('page'))
                },
            })
            .then(function (response) {
                let tbody = createTable(response.data.rows);
                logList.html(tbody);
            })
            .catch(function (response) {
            });
    });

    /**
     * Генерирует разетку строки таблицы с данными
     * @param row
     * @returns {string}
     */
    function createTableRow(row) {
        let result = '<tr>';
        tableFields.forEach((field) => {
            result += `<td>${row[field]}</td>`;
        })

        return result + '<tr>';
    }

    /**
     * Генерирует разетку таблицы с данными
     * @param data
     * @returns {string}
     */
    function createTable(data) {
        let tbody = '';
        data.forEach((row) => {
            tbody += createTableRow(row);
        });

        return tbody;
    }
})