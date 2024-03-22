// on load function:
$(() => {
    if (!localStorage.getItem('token'))
        window.location.href = 'login.html';

    let tokenObj = JSON.parse(localStorage.getItem('token'))

    bindClickListener($('#logoutButton'), () => {
        requestInvoker.executeQuery(tokenObj._links.logout.href, { token: tokenObj.token}).then((response) => {
            localStorage.removeItem('token');
            window.location.href = 'login.html';
        })
    })

    const contentElement = $('#content')
    requestInvoker.executeQuery('users', { token: tokenObj.token }).then((response) => {
        console.log(response)
        const tableColumns = {
            id: 'Kód',
            name: 'Név',
            email: 'E-mail cím',
            birth_date: 'Születési dátum'
        }
        const usersTable = tableBuilder.createTable(tableColumns, response._embedded.users)
        contentElement.append(usersTable)
    })
})
