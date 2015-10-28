$(document).ready(function () {

    // Run the init method on document ready:
    chat.init();

});

var chat = {

    // data holds variables for use in the class:

    data: {
        lastID: 0,
        noActivity: 0
    },

    // Init binds event listeners and sets up timers:

    init: function () {

        // Logging the user out:

        $('a.logoutButton').live('click', function () {

            $.chatPOST('logout');

            window.location.href = 'index.html';

            return false;
        });

        // Checking whether the user is already logged (browser refresh)

        $.chatGET('checkLogged', function (r) {
            if (r.logged && r.loggedAs.is_admin != 0) {
                chat.login(r.loggedAs.name, r.loggedAs.gravatar, r.loggedAs.is_admin);
            } else {
                window.location.href = 'index.html';
            }
        });

    },

    // The login method hides displays the
    // user's login data and shows the submit form

    login: function (name, gravatar, isAdmin) {

        chat.data.name = name;
        chat.data.gravatar = gravatar;
        chat.data.isAdmin = isAdmin;
        $('#chatTopBar').html(chat.render('loginTopBar', chat.data));


        $('#registerForm').fadeOut();
        $('#loginForm').fadeOut(function () {
            $('#submitForm').fadeIn();
            $('#chatText').focus();
        });

    },

    // The render method generates the HTML markup
    // that is needed by the other methods:

    render: function (template, params) {

        var arr = [];
        switch (template) {
            case 'loginTopBar':
                arr = [
                    '<span><img src="', params.gravatar, '" width="23" height="23" />',
                    '<span class="name">', params.name, '</span>'];
                if (params.isAdmin) {
                    arr.push('<a href="index.html" class="adminButton rounded">Chat</a>');
                }
                arr.push('<a href="" class="logoutButton rounded">Logout</a></span>');
                break;

            case 'chatLine':
                arr = [
                    '<div class="chat chat-', params.id, ' rounded"><span class="gravatar"><img src="', params.gravatar,
                    '" width="23" height="23" onload="this.style.visibility=\'visible\'" />', '</span><span class="author">', params.author,
                    ':</span><span class="text">', params.text, '</span><span class="time">', params.time, '</span></div>'];
                break;

            case 'user':
                arr = [
                    '<div class="user" title="', params.name, '"><img src="',
                    params.gravatar, '" width="30" height="30" onload="this.style.visibility=\'visible\'" /></div>'
                ];
                break;
        }

        // A single array join is faster than
        // multiple concatenations

        return arr.join('');

    },
    // Requesting a list with all the users.

    getUsers: function (callback) {
        $.chatGET('getUsers', function (r) {

            var users = [];

            for (var i = 0; i < r.users.length; i++) {
                if (r.users[i]) {
                    users.push(chat.render('user', r.users[i]));
                }
            }

            var message = '';

            if (r.total < 1) {
                message = 'No one is online';
            }
            else {
                message = r.total + ' ' + (r.total == 1 ? 'person' : 'people') + ' online';
            }

            users.push('<p class="count">' + message + '</p>');

            $('#chatUsers').html(users.join(''));

            setTimeout(callback, 1000);
        });
    },

    // This method displays an error message on the top of the page:

    displayError: function (msg) {
        var elem = $('<div>', {
            id: 'chatErrorMessage',
            html: msg
        });

        elem.click(function () {
            $(this).fadeOut(function () {
                $(this).remove();
            });
        });

        setTimeout(function () {
            elem.click();
        }, 10000);

        elem.hide().appendTo('body').slideDown();
    },

    displaySuccess: function (msg) {
        var elem = $('<div>', {
            id: 'chatSuccessMessage',
            html: msg
        });

        elem.click(function () {
            $(this).fadeOut(function () {
                $(this).remove();
            });
        });

        setTimeout(function () {
            elem.click();
        }, 10000);

        elem.hide().appendTo('body').slideDown();
    }
};

// Custom GET & POST wrappers:

$.chatPOST = function (action, data, callback) {
    $.post('php/ajax.php?action=' + action, data, callback, 'json');
}

$.chatGET = function (action, data, callback) {
    $.get('php/ajax.php?action=' + action, data, callback, 'json');
}

// A custom jQuery method for placeholder text:

$.fn.defaultText = function (value) {

    var element = this.eq(0);
    element.data('defaultText', value);

    element.focus(function () {
        if (element.val() == value) {
            element.val('').removeClass('defaultText');
        }
    }).blur(function () {
        if (element.val() == '' || element.val() == value) {
            element.addClass('defaultText').val(value);
        }
    });

    return element.blur();
}