$(() => {
    $('#login_form').on('submit', () => {
        let data = $('#login_form :input')

        requestInvoker.executeQuery('auth/login', data).then((result) => {
            localStorage.setItem('token', JSON.stringify(result))
            window.location.href = 'index.html';
        })

        return false
    })
})