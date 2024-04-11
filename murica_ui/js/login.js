const apiUrl = 'https://localhost/murica_api/';
const requestInvoker = new RequestInvoker(apiUrl);

$(() => {
    if (localStorage.getItem('token'))
        window.location.href = 'index.php';

    $('#login_form').on('submit', () => {
        let data = $('#login_form :input');

        requestInvoker.executePost('auth/login', data).then((result) => {
            if (result._success) {
                localStorage.setItem('token', JSON.stringify(result));
                window.location.href = 'index.php';
            }
            else {
                $('#error').text(result.error.details);
                console.error(result);
            }
        });

        return false;
    });
});