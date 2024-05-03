const requestInvoker = new RequestInvoker(apiUrl);

$(() => {
    let tokenObj = JSON.parse(localStorage.getItem('token'));

    if (tokenObj && new Date(tokenObj.expires_at) > new Date()) {
        window.location.href = 'login.php';
    }

    $('#login_form').on('submit', () => {
        let data = $('#login_form :input');

        requestInvoker.executePost('auth/login', data).then((result) => {
            if (!result._success) {
                $('#error').text(result.error.details);
                console.error(result);

                return;
            }

            localStorage.setItem('token', JSON.stringify(result));

            requestInvoker.executePost(result._links.roles.href, { token: result.token }).then((response) => {
                if (!response._success) alert('Something unexpected happened. Please try again later!');

                if (response.isAdmin) {
                    window.location.href = 'admin.php';
                    return;
                }

                if (response.isTeacher) {
                    window.location.href = 'teacher.php';
                    return;
                }

                $.each(response.student, (index, value) => {
                    localStorage.setItem('studentVals', JSON.stringify(value));
                    window.location.href = 'student.php';

                    return false;
                });

                window.location.href = 'index.php';
            });


        });

        return false;
    });
});