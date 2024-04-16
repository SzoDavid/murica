const apiUrl = 'https://localhost/murica_api/';
const requestInvoker = new RequestInvoker(apiUrl);

$(() => {
    if (!localStorage.getItem('token'))
        window.location.href = 'login.php';

    let tokenObj = JSON.parse(localStorage.getItem('token'));

    $('#username').text(tokenObj.user.name);

    bindClickListener($('#logoutButton'), () => {
        requestInvoker.executePost(tokenObj._links.logout.href, { token: tokenObj.token}).then(() => {
            localStorage.removeItem('token');
            window.location.href = 'login.php';
        });
    });
});
