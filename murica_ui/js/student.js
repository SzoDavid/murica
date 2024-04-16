const apiUrl = 'https://localhost/murica_api/';
const requestInvoker = new RequestInvoker(apiUrl);
let tokenObj;

function init() {
    tokenObj = JSON.parse(localStorage.getItem('token'));

    if (!tokenObj || new Date(tokenObj.expires_at) < new Date()) {
        window.location.href = 'login.php';
    }

    $('#navbar-username').text(tokenObj.user.name);

    bindClickListener($('#navbar-logo'), () => {
        window.location.href = 'index.php';
    });

    bindClickListener($('#navbar-logout'), () => {
        requestInvoker.executePost(tokenObj._links.logout.href, { token: tokenObj.token}).then(() => {
            localStorage.removeItem('token');
            window.location.href = 'login.php';
        });
    });
}

$(() => {
    init();
    const contentElement = $('#content');
});