const requestInvoker = new RequestInvoker(apiUrl);
let tokenObj;

$(() => {
    tokenObj = init(requestInvoker, 'teacher');
    const contentElement = $('#content');
});