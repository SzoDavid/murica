const apiUrl = 'https://localhost/murica_api/';
const requestInvoker = new RequestInvoker(apiUrl);

$(() => {
    if (!localStorage.getItem('token'))
        window.location.href = 'login.html';

    let tokenObj = JSON.parse(localStorage.getItem('token'));

    $('#username').text(tokenObj.user.name);

    bindClickListener($('#logoutButton'), () => {
        requestInvoker.executePost(tokenObj._links.logout.href, { token: tokenObj.token}).then(() => {
            localStorage.removeItem('token');
            window.location.href = 'login.html';
        });
    });

    const contentElement = $('#content');
    requestInvoker.executePost('user/all', { token: tokenObj.token }).then((response) => {
        console.log(response);
        const tableColumns = {
            id: 'Kód',
            name: 'Név',
            email: 'E-mail cím',
            birth_date: 'Születési dátum'
        };

        const usersTable= new Table(tableColumns, response._embedded.users).build();
        contentElement.append(usersTable);
    });
});
