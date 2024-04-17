const apiUrl = 'http://localhost/murica_api/';
const requestInvoker = new RequestInvoker(apiUrl);

$(() => {
    init(requestInvoker, 'index');
});
