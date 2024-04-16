const apiUrl = 'https://localhost/murica_api/';
const requestInvoker = new RequestInvoker(apiUrl);
let tokenObj;

$(() => {
    tokenObj = init(requestInvoker, 'teacher');
    const contentElement = $('#content');
});