const requestInvoker = new RequestInvoker(apiUrl);
let tokenObj;

function self(contentElement) {
    new SelfPage(contentElement, tokenObj._links.user.href, 'index').build();
}

$(() => {
    tokenObj = init(requestInvoker, 'index');
    const contentElement = $('#content');
    bindClickListener($('#navbar-username'), () => { self(contentElement); });
});
