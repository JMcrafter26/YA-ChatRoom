function logOut() {
    $('#logoutBtn').prop('disabled', true);
    $('#logoutBtn').html('Logging out <i class="fas fa-circle-notch fa-spin"></i>');
    $.ajax({
        url: 'api.php',
        type: 'GET',
        data: {
            action: 'logout',
            token: window.config.ctoken
        },
        success: function(data) {
            if (data.status == 'success') {
                // reload the page
                window.location.reload();
            } else {
                $('#error').html(data.message);
            }
        },
        error: function() {
            $('#error').html('An error occurred');
        }
    });

    $('#logoutBtn').prop('disabled', false);
    $('#logoutBtn').html('Logout <i class="fas fa-sign-out-alt"></i>');
}

function loadData() {
    var focus = true;
    // if checkbox is not checked, set focus to true
    if (!$('#sendBusyStatusSwitch').is(':checked')) {
        focus = true;
    } else {
        // if checkbox is checked, check if tab is focused
        if (!document.hasFocus()) {
            focus = false;
        } else {
            focus = true;
        }
    }
    // if focus is false, set yourStatus color to busy
    if (!focus) {
        $('#yourStatus').removeClass('bg-success');
        $('#yourStatus').addClass('bg-warning');
    } else {
        $('#yourStatus').removeClass('bg-warning');
        $('#yourStatus').addClass('bg-success');
    }
    if (window.lastFocus == undefined || window.lastFocus == null) {
        window.lastFocus = Math.floor(Date.now() / 1000);
    }
    if (document.hasFocus()) {
        window.lastFocus = Math.floor(Date.now() / 1000);
    }
    console.log('Last focus: ' + window.lastFocus + ' (seconds ago: ' + (Math.floor(Date.now() / 1000) - window.lastFocus) + ')');

    if (window.lastMsgHash == undefined || window.lastMsgHash == null) {
        window.lastMsgHash = 0;
    }

    $.ajax({
        url: 'api.php',
        type: 'GET',
        data: {
            action: 'getChat',
            token: window.config.token,
            focus: focus,
            lastHash: window.lastMsgHash
        },
        success: function(data) {
            if (data.status == 'success') {
                $('#users').html('');

                /* example response
                    {
                        "status": "success",
                        "message": "Users and chats fetched successfully",
                        "data": {
                            "users": [
                            {
                                "id": 3,
                                "name": "operagx",
                                "countryCode": "DE",
                                "activity": 1706649005,
                                "isFocus": 0
                            },
                            {
                                "id": 4,
                                "name": "chrome",
                                "countryCode": "DE",
                                "activity": 1706649000,
                                "isFocus": 0
                            }
                            ],
                            "chats": [
                            {
                                "id": 1,
                                "name": "operagx",
                                "message": "lol",
                                "time": 1706649003
                            }
                            ]
                        }
                    }
                */


                // remove yourself from the list
                // data.data.users = data.data.users.filter(function(user) {
                //     // sanitize the name using DOMPurify
                //     user.name = DOMPurify.sanitize(user.name);
                //     return user.name != window.config.name;
                // });

                var newUsers = [];
                var leftUsers = [];

                if(window.usersList == undefined || window.usersList == null) {
                    window.usersList = data.data.users;
                }

                // check if a user is no longer in the list (user is in window.users but not in data.data.users)
                $.each(window.usersList, function(index, user) {
                    if (!data.data.users.some(e => e.name === user.name)) {
                        leftUsers.push(user);
                        console.log('left user: ' + user.name);
                    }
                });

                $.each(data.data.users, function(index, user) {
                    // if user is you, skip it
                    if (user.name == window.config.name) {
                        window.yourColor = user.color;
                        return;
                    }

                    // check if there is a new user (user is not in window.users)
                    if (!window.usersList.some(e => e.name === user.name)) {
                        newUsers.push(user);
                        console.log('new user: ' + user.name);
                    }


                    var status = user.status;
                    // if (user.isFocus) {
                    //     if (user.activity < (Math.floor(Date.now() / 1000) - 90)) {
                    //         status = 'offline';
                    //     } else {
                    //         status = 'online';
                    //     }
                    // } else {
                    //     // if user is longer than 90 seconds inactive, he is offline, else he is busy
                    //     if (user.activity < (Math.floor(Date.now() / 1000) - 90)) {
                    //         status = 'offline';
                    //     } else {
                    //         status = 'busy';
                    //     }
                    // }

                    $('#users').append('<li class="list-group-item d-flex justify-content-between align-items-center"><span class="status ' + status + '"></span><span class="fw-bold">' + user.name + 
                    '<span class="badge" style="background-color: #'+ user.color + '; padding: 3px; margin-left: 2px;" title="#' + user.color + '" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="#' + user.color + '" data-bs-custom-class="tooltip-color-' + user.color + '" >#<span class="d-lg-none">' + user.color + '</span></span>' +
                    '</span><span class="fi flag-icon fi-' + user.countryCode.toLowerCase() + '"></span></li>');

                    // create new style element for the tooltip, if it does not exist
                    if ($('.tooltip-color-' + user.color).length == 0) {
                        var style = document.createElement('style');
                        style.type = 'text/css';
                        style.innerHTML = '.tooltip-color-' + user.color + ' .tooltip-inner { background-color: #' + user.color + '; color: #fff; }';
                        document.getElementsByTagName('head')[0].appendChild(style);
                    }
                });
                $('#userCount').html(data.data.users.length);
                $('#userCountMobile').html(data.data.users.length);
                window.usersList = data.data.users;

                // enable tooltips
                // var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                // var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                //     return new bootstrap.Tooltip(tooltipTriggerEl);
                // });




                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
                const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))



                // update messages
                // $('#messages').html('');

                var firstTime = false;
                // if the loader is still there, remove it
                if ($('#messages').find('.spinner-border').length > 0) {
                    $('#messages').html('');
                    firstTime = true;
                }

                // get the scroll position
                var scrollPos = $('#messages').scrollTop();

                // check if user is at (or near) the bottom
                if (scrollPos >= ($('#messages')[0].scrollHeight - $('#messages').height()) - 100) {
                    // if so, scroll to bottom after loading the messages
                    var scrollToBottom = true;
                } else {
                    var scrollToBottom = false;
                }

                // favicon count (unread messages)
                var unread = 0;

                $.each(data.data.chats, function(index, message) {
                                            // if data.spam is 1, and the user has hideSpamMessages enabled, add invisible class
                        var spamClass = '';
                        var spamClassData = false;
                        if (message.spam == true && getFromStorage('hideSpamMessages') == true) {
                            spamClass = 'd-none spam-message';
                            spamClassData = true;
                        } else if (message.spam == true) {
                            spamClass = 'spam-message';
                            spamClassData = true;
                        } else {
                            spamClass = '';
                            spamClassData = false;
                        }



                    // if focus is false, and the user didn't read the message, add to unread
                    if (!focus && message.time > window.lastFocus && spamClassData == false) {
                        unread++;
                    }
                    // check if message is already in the list, if so, skip it
                    if ($('li[data-hash="' + message.hash + '"]').length > 0) {
                        return;
                    }

                    if(message.spam == 1) {
                        document.getElementById('spamCount').innerText = parseInt(document.getElementById('spamCount').innerText) + 1;
                        }

                    // sanitize the data using DOMPurify
                    message.name = DOMPurify.sanitize(message.name);
                    message.message = DOMPurify.sanitize(message.message);




                    // get message.color from the user list
                    var color = '000000';
                    console.log(window.usersList);
                    $.each(window.usersList, function(index, user) {
                        console.log('user: ' + user.name);
                        console.log('message: ' + message.name);
                        console.log('color: ' + user.color);
                        if (user.name == message.name) {
                            console.log('color: ' + user.color);
                            color = user.color;
                        }
                    });
                    message.color = color;

                    // if message is from yourself, add (You) badge to the name
                    if (message.name == window.config.name) {
                        message.name = message.name + ' <span class="badge" style="background-color: #' + message.color + ';">You</span>';
                    }

                    const date = new Date(message.time * 1000);
                    const hours = date.getHours();
                    const minutes = "0" + date.getMinutes();
                    const seconds = "0" + date.getSeconds();
                    const formattedTime = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);

                    // replace urls with clickable links
                      var urlRegex = /(https?:\/\/[^\s]+)/g;
                        message.message = message.message.replace(urlRegex, function(url) {
                            return '<a data-href="' + url + '" class="msg-url" href="javascript:void(0)">' + url + '</a>';
                        });

                        // if message.message is //frog.gif//, replace it with an image
                        if (message.message == '//frog.gif//') {
                            message.message = '<img src="assets/emojis/frog.gif" alt="frog" style="height: 2rem; user-select: none; pointer-events: none; draggable: false;">';
                        }

                        // if message contains //frog.gif//, remove it
                        if (message.message.includes('//frog.gif//')) {
                            message.message = message.message.replace('//frog.gif//', '');
                            // if message.message is empty, remove the message
                            // if (message.message == '') {
                            //     return;
                            // }
                        }



                        // console.log('spam: ' + message.message);
                        

                    var colorBadge = `<span class="badge" style="background-color: #${message.color};">#${message.color}</span>`;

                    // if user is you, 
                    if (message.name.includes('You')) {
                        colorBadge = '';
                    }

                    const base = `<li class="list-group-item ${spamClass}" data-hash="${message.hash}" data-spam="${spamClassData}">
                        <div class="row">
                            <div class="col-12 d-flex justify-content-between align-items-center">
                                <div class="fw-bold">${message.name} ${colorBadge}</div>
                                <div class="text-muted">${formattedTime}</div>
                                
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <p class="mb-0">${message.message}</p>
                                </div>

                        </div>
                    </li>`;
                    $('#messages').append(base);

                    window.lastMsgHash = message.hash;
                    console.log('lastMsgHash: ' + window.lastMsgHash);
                });

                if(firstTime) {
                    // append the info message
                    const infoMsg = `<li class="list-group-item" data-hash="0">

                    <div class="row">
                        <div class="col-12 user-select-none">
                            <p class="mb-0 fw-bold">Welcome to the chat!</p>
                            <p class="mb-0">Please be respectful to others and follow the rules.</p>
                            <p class="mb-0">The messages are only stored for 1 minute, if you refresh the page, the old messages will be gone.</p>
                            <p class="mb-0 text-warning"><i class="fas fa-exclamation-triangle"></i> Please do not share any personal information, as this is a public chat <i class="fas fa-exclamation-triangle"></i></p>
                            <p class="mb-0">Have fun! <i class="far fa-smile"></i></p>
                        </div>
                    </div>
                </li>`;
                    $('#messages').append(infoMsg);
                }

                if(getFromStorage('joinMessages') != true) {
                    newUsers = [];
                    leftUsers = [];
                }

                // if new users are in the list, show a message for each new user
                $.each(newUsers, function(index, user) {
                    var userJoinMessage = [
                        "Welcome to the chat, " + user.name + "!",
                        "Hello, " + user.name + "!",
                        "User " + user.name + " has joined the chat!",
                        "New user " + user.name + " has joined the chat!",
                        "Welcome, " + user.name + "!",
                        "User " + user.name + " has entered the chat!",
                        "A wild " + user.name + " has appeared!",
                        "User " + user.name + " has arrived!",
                        "User " + user.name + " has joined!"
                    ];
                    userJoinMessage = userJoinMessage[Math.floor(Math.random() * userJoinMessage.length)];
                    userJoinMessage = userJoinMessage.replace(user.name, '<span class="fw-bold text-info">' + user.name + '</span>');
                    
                    // if user is someone26, show a special message
                    if (user.name == 'someone26') {
                        // add custom css to the page
                        var style = document.createElement('style');
                        style.type = 'text/css';
                        style.innerHTML = `
                        .textcontainer{padding:20px 0;text-align:center}.particletext{text-align:center;font-size:24px;position:relative;&.bubbles{>.particle{opacity:0;position:absolute;background-color:rgba(33,150,243,.5);animation:bubbles 3s ease-in infinite;border-radius:100%}}&.confetti{>.particle{opacity:0;position:absolute;animation:confetti 3s ease-in infinite;&.c1{background-color:rgba(76,175,80,.5)}&.c2{background-color:rgba(156,39,176,.5)}}}}@keyframes confetti{0%{opacity:0;transform:translateY(0%) rotate(0deg)}10%{opacity:1}35%{transform:translateY(-800%) rotate(270deg)}80%{opacity:1}100%{opacity:0;transform:translateY(2000%) rotate(1440deg)}}
                        `;
                        document.getElementsByTagName('head')[0].appendChild(style);

                        userJoinMessage = '<div class="textcontainer"><span class="particletext confetti">Someone26</span></div>' + ' has joined the chat!';

                    }
                    // make username bold
                    // append the info message
                    const infoMsg = `<li class="list-group-item" data-hash="0">
                    <div class="row">
                        <div class="col-12 user-select-none">
                            <p class="mb-0 fw-bold">${userJoinMessage}</p>
                        </div>
                    </div>
                </li>`;
                    $('#messages').append(infoMsg);
                    if (user.name == 'someone26') {
                        confetti();
                    }

                });

                // if left users are in the list, show a message for each left user
                $.each(leftUsers, function(index, user) {
                    var userLeftMessage = [
                        user.name + " has left the chat!",
                        user.name + " has left!",
                        "User " + user.name + " has left the chat!",
                        "We lost " + user.name + "!",
                        user.name + " disappeared!",
                        user.name + " seems to be gone!",
                        user.name + " said goodbye!",
                        user.name + " has left us!"
                    ];
                    userLeftMessage = userLeftMessage[Math.floor(Math.random() * userLeftMessage.length)];
                    // make username bold
                    userLeftMessage = userLeftMessage.replace(user.name, '<span class="fw-bold text-danger">' + user.name + '</span>');
                    // append the info message
                    const infoMsg = `<li class="list-group-item" data-hash="0">
                    <div class="row">
                        <div class="col-12 user-select-none">
                            <p class="mb-0 fw-bold">${userLeftMessage}</p>
                        </div>
                    </div>
                </li>`;
                    $('#messages').append(infoMsg);
                });
                            


                // get the new scroll position
                var newScrollPos = $('#messages')[0].scrollHeight - $('#messages').height();

                // if the user was at the bottom, scroll to bottom
                if (scrollToBottom) {
                    $('#messages').scrollTop(newScrollPos);
                } else {
                    // if the user was not at the bottom, scroll to the same position
                    $('#messages').scrollTop(scrollPos);
                }
                $('#captchaModal').modal('hide');

                // if focus is false, set the favicon count
                console.log('unread: ' + unread);
                if (!focus) {
                    if (unread > 0) {
                        setFaviconCount(unread);
                    } else {
                        setFaviconCount('');
                    }
                } else {
                    setFaviconCount('');
                }


            } else {
                // if errCode is not set, show the message
                if (data.errCode == undefined) {
                    $('#error').html(data.message);
                } else {
                    if (data.errCode == 'invalid-token') {
                        logOut();
                    } else if (data.errCode == 'token-expired') {
                        logOut();
                    } else if (data.errCode == 'verify-captcha') {
                        $('#error').html(data.message);
                        verifyCaptcha();
                    } else {
                        $('#error').html(data.message);
                    }
                }
                
            }
        },
        error: function() {
            $('#error').html('An error occurred');
        }
    });
}


// if clicked on element with class url-msg,
$(document).on('click', function(e) {
    if (e.target.classList.contains('msg-url')) {
        // open the link
        linkOpen(e.target);
    }
});

// if clicked on element with class useTheme,
$(document).on('click', function(e) {
    if (e.target.classList.contains('useTheme')) {
        // set the theme
        setBootswatchTheme(e.target.dataset.theme);
    }
});

function verifyCaptcha() {
    // if modal is already open, return
    if ($('#captchaModal').hasClass('show')) {
        // console.log('token: ' + window.config.token);
        return;
    }
    // create a modal with an iframe to the captcha page
    $('#iconCaptchaIframe').attr('src', './assets/iconcaptcha/iframe.php?token=' + window.config.ctoken + '&r=' + Math.floor(Math.random() * 1000));
    $('#captchaModal').modal('show');
}


function saveSettings() {
    // save settings to localstorage chat-settings array
    var settings = {
        darkMode: $('#darkModeSwitch').is(':checked'),
        faviconCount: $('#faviconCountSwitch').is(':checked'),
        sendBusyStatus: $('#sendBusyStatusSwitch').is(':checked'),
        hideSpamMessages: $('#hideSpamSwitch').is(':checked'),
        hideUrlWarning: !$('#showUrlWarningSwitch').is(':checked'),
        joinMessages: $('#joinMessagesSwitch').is(':checked'),
    };
    // merge the settings with the existing settings, update the existing settings
    var existingSettings = JSON.parse(localStorage.getItem('chat-settings'));
    settings = {...existingSettings, ...settings};

    localStorage.setItem('chat-settings', JSON.stringify(settings));
}

function saveToStorage(key, value) {
    var settings = JSON.parse(localStorage.getItem('chat-settings'));
    settings[key] = value;
    localStorage.setItem('chat-settings', JSON.stringify(settings));
}

function loadSettings() {
    // load settings from localstorage chat-settings array
    var settings = JSON.parse(localStorage.getItem('chat-settings'));
    if (settings) {
        // load the theme
        if (settings.bsTheme != undefined) {
            setBootswatchTheme(settings.bsTheme);
        }
        $('#darkModeSwitch').prop('checked', settings.darkMode);
        $('#faviconCountSwitch').prop('checked', settings.faviconCount);
        $('#sendBusyStatusSwitch').prop('checked', settings.sendBusyStatus);
        $('#hideSpamSwitch').prop('checked', settings.hideSpamMessages);
        $('#showUrlWarningSwitch').prop('checked', !settings.hideUrlWarning);
        $('#joinMessagesSwitch').prop('checked', settings.joinMessages);
    }
}

function getFromStorage(key) {
    var settings = JSON.parse(localStorage.getItem('chat-settings'));
    // if settings is not set, run saveSettings to create it
    if (settings == null) {
        saveSettings();
        settings = JSON.parse(localStorage.getItem('chat-settings'));
    }
    return settings[key];
}

function sendMessage() {
    var message = $('#messageArea').val();
    if (message == '' || message.length > 1000 || message.length < 1) {
        $('#error').html('Message must be between 1 and 1000 characters');
        return;
    }

    $('#sendBtn').prop('disabled', true);
    $('#sendBtn').html('<i class="fas fa-circle-notch fa-spin"></i>');

    // sanitize the message using DOMPurify
    var cleanMessage = DOMPurify.sanitize(message);
    if (cleanMessage != message) {
        console.log('Message was sanitized');
    }
    message = cleanMessage;

    // send message to api
    $.ajax({
        url: 'api.php',
        type: 'POST',
        data: {
            message: message,
            action: 'send',
            token: window.config.token
        },
        success: function(data) {
            if (data.status == 'success') {
                if(data.data.spam != undefined && data.data.spam == true) {
                    $('#error').html('Your message was marked as spam (' + data.data.spam_category + ') and may not be visible to others');
                } else {
                    $('#error').html('');
                }
                $('#messageArea').val('');
                $('#sendBtn').prop('disabled', false);
                $('#sendBtn').html('<i class="fas fa-paper-plane"></i>');
            } else {
                if (data.errCode != undefined) {
                    if (data.errCode == 'spamming') {
                        // disable the send button and show a countdown
                        $('#sendBtn').prop('disabled', true);
                        // count down from data.data.until, e.g. 1706897270
                        var countDownDate = new Date(data.data.until * 1000).getTime();
                        var now = new Date().getTime();
                        var distance = countDownDate - now;
                        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        $('#error').html(data.message + ' Try again in ' + seconds + ' seconds');
                        var x = setInterval(function() {
                            var now = new Date().getTime();
                            var distance = countDownDate - now;
                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                            $('#error').html(data.message + ' Try again in ' + seconds + ' seconds');
                            if (distance < 0) {
                                clearInterval(x);
                                $('#error').html('');
                                $('#sendBtn').prop('disabled', false);
                                $('#sendBtn').html('<i class="fas fa-paper-plane"></i>');
                            }
                        }, 1000);
                        
                } else {
                    $('#error').html(data.message);
                    $('#sendBtn').prop('disabled', false);
                    $('#sendBtn').html('<i class="fas fa-paper-plane"></i>');
                }
            } else {
                $('#error').html(data.message);
                $('#sendBtn').prop('disabled', false);
                $('#sendBtn').html('<i class="fas fa-paper-plane"></i>');
            }
        }
    },

        error: function() {
            $('#error').html('An error occurred, this is most likely <strong>not</strong> your fault');
            $('#sendBtn').prop('disabled', false);
            $('#sendBtn').html('<i class="fas fa-paper-plane"></i>');
        }
    });

    loadData();
}

function linkOpen(e) {
    if (getFromStorage('hideUrlWarning') == true) {
        window.open(e.getAttribute('data-href'), '_blank');
    } else {

    document.getElementById('urlInput').value = e.getAttribute('data-href');
    document.getElementById('urlFavicon').src = 'https://www.google.com/s2/favicons?domain=' + e.getAttribute('data-href').split('/')[2] + '&sz=128';
    $('#urlWarningModal').modal('show');

}
}

function updateActivity(loop = false) {
    loadData();
    if (document.hasFocus()) {
        if (loop) {
            setTimeout(function() {
                updateActivity(true);
            }, window.config.fetchInterval);
        }
    } else {
        if (loop) {
            setTimeout(function() {
                updateActivity(true);
            }, window.config.backgroundFetchInterval);
        }
    }
}

function setBootswatchTheme(theme) {
    // set the theme using the bootswatch link
    document.getElementById('bootswatchLink').href = theme;
    
    // save the theme to localstorage
    saveToStorage('bsTheme', theme);

    // add a colored border to the selected theme, by getting the element with the theme url as dataset.theme
    var themeList = document.getElementById('themeList');
    for (var i = 0; i < themeList.children.length; i++) {
        var child = themeList.children[i];
        if (child.dataset.theme == theme) {
            child.classList.add('border-primary');
            // set the use theme button to disabled
            child.querySelector('button').disabled = true;
            child.querySelector('button').innerHTML = 'In use';
        } else {
            child.classList.remove('border-primary');
            // set the use theme button to enabled
            child.querySelector('button').disabled = false;
            child.querySelector('button').innerHTML = 'Use theme';
        }
    }

    setVh();

}

function loadBootswatchThemeList() {
    // load the bootswatch theme list
console.log('loading themes');

    const theme = loadTheme();

    const themes = {
                0: {
                    name: 'Default',
                    url: 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css',
                    slogan: 'The default theme'
                },
                1: {
                    name: 'Vapor',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/vapor/bootstrap.min.css',
                    slogan: 'The cool secret theme'
                },
                2: {
                    name: 'Flatly',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/flatly/bootstrap.min.css',
                    slogan: 'Flat and modern'
                },
                3: {
                    name: 'Journal',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/journal/bootstrap.min.css',
                    slogan: 'Crisp and clean'
                },
                4: {
                    name: 'Litera',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/litera/bootstrap.min.css',
                    slogan: 'Only typography'
                },
                5: {
                    name: 'Lumen',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/lumen/bootstrap.min.css',
                    slogan: 'Light and shadow'
                },
                6: {
                    name: 'Materia',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/materia/bootstrap.min.css',
                    slogan: 'Material design'
                },
                7: {
                    name: 'Minty',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/minty/bootstrap.min.css',
                    slogan: 'A fresh feel'
                },
                8: {
                    name: 'Pulse',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/pulse/bootstrap.min.css',
                    slogan: 'Compact and vibrant'
                },
                9: {
                    name: 'Quartz',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/quartz/bootstrap.min.css',
                    slogan: 'Subtle and solid'
                },
                10: {
                    name: 'Sketchy',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/sketchy/bootstrap.min.css',
                    slogan: 'A hand-drawn look'
                },
                11: {
                    name: 'Slate',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/slate/bootstrap.min.css',
                    slogan: 'Shades of gunmetal'
                },
                12: {
                    name: 'Solar',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/solar/bootstrap.min.css',
                    slogan: 'A spin on Solarized'
                },
                13: {
                    name: 'Yeti',
                    url: 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.2/yeti/bootstrap.min.css',
                    slogan: 'A friendly foundation'
                }
        };

    window.themes = themes;
        var themeList = document.getElementById('themeList');
    themeList.innerHTML = '';

    for (const [key, value] of Object.entries(themes)) {
        // skip the default theme
        if (key == 0 || key == 1) {
            continue;
        }
        var thumbnail = './assets/theme-thumbnail/' + theme + '/' + value.name.toLowerCase() + '.png';
        var li = document.createElement('div');
        li.className = 'card m-1';
        li.dataset.theme = value.url;
        li.style = 'width: 15rem;';
        li.innerHTML = `<img class="card-img-top lazyload thumbnail" alt="${value.name}" src="${thumbnail}" style="height: 10rem; object-fit: cover;" onclick="window.open('${thumbnail}', '_blank')" loading="lazy">
                        <div class="card-body">
                            <h5 class="card-title">${value.name}</h5>
                            <p class="card-text">${value.slogan}</p>
                            <button type="button" class="btn btn-primary useTheme" data-theme="${value.url}">Use theme</button>
                        </div>`;
        themeList.appendChild(li);
    }

}

function centerThumbnail(element) {
    // if image needs to be more to the right append the class thumbnail-right
    console.log(element.getBoundingClientRect().left);
    console.log(window.innerWidth);
    console.log(element.getBoundingClientRect().right);
    
    // get the middle of the screen
    var middle = window.innerWidth / 2;
    // get the middle of the image
    var imageMiddle = element.getBoundingClientRect().left + (element.getBoundingClientRect().width / 2);
    // get the difference
    var difference = middle - imageMiddle;
    console.log('difference: ' + difference + ' | middle: ' + middle + ' | imageMiddle: ' + imageMiddle);

    // add class thumbnail-hover
    element.classList.add('thumbnail-hover');

    // if the difference is less than the 50px threshold, do nothing
    if (Math.abs(difference) < 50) {
        return;
    }

    // if the difference is less than 0, add the class thumbnail-right
    if (difference < 0) {
        element.classList.add('thumbnail-left');
        console.log('right');
    }

    // if the difference is more than 0, add the class thumbnail-left
    if (difference > 0) {
        element.classList.add('thumbnail-right');
        console.log('left');
    }
}

function runcommands(message) {
    if ($('#messageArea').val() == 'showMeTheSecrets') {
        // clear the message area
        $('#messageArea').val('');
        // check if #messages already has the secret emoji message (data-hash="secret")
        if ($('li[data-hash="secret"]').length > 0) {
            return true;
        }
        // append the secret emoji info text to the chat
        const scrtMsg = `<li class="list-group-item" data-hash="secret">
                    <div class="row">
                        <div class="col-12 user-select-none">
                            <p class="mb-0 fw-bold"><i class="fa-solid fa-user-secret"></i> Psst!</p>
                            <p class="mb-0">This is a list of all the secret emojis and other commands:</p>
                            <ul>
                                <li><strong>showMeTheSecrets</strong> - Show this list</li>
                                <li><strong>!clear</strong> - Clear all messages (only works for you)</li>
                                <li><strong>!theme</strong> - Change the theme (e.g. !theme slate, there is also a secret theme!)</li>
                                <li><strong>!reload</strong> - Reload the chat</li>
                                <li><strong>!logout</strong> - Log out</li>
                                <li><strong>!frog</strong> - <img src="assets/emojis/frog.gif" alt="frog" style="height: 2rem; user-select: none; pointer-events: none; draggable: false;"></li>
                            </ul>
                            <p class="mb-0">But don't tell anyone! <i class="far fa-smile-wink"></i></p>
                        </div>
                    </div>
                </li>`;
        $('#messages').append(scrtMsg);
        return true;
    } else if ($('#messageArea').val() == '!clear') {
        // clear the message area
        $('#messageArea').val('');
        $('#messages').html('');

        return true;
    } else if ($('#messageArea').val().startsWith('!theme')) {
        // clear the message area
        // if the message is !theme, open the theme modal
        if ($('#messageArea').val() == '!theme') {
            $('#messageArea').val('');
            $('#themeModal').modal('show');
            return true;
        }
        // if the message starts with !theme, change the theme to a specific theme
        var theme = $('#messageArea').val().split(' ')[1];
        // check if the theme is in the themes list as a key value(e.g. name: themeName, url: themeUrl)

        var themeFound = false;
        $.each(window.themes, function(key, value) {
            if (value.name.toLowerCase() == theme.toLowerCase()) {
                themeFound = true;
                setBootswatchTheme(value.url);
            }
        });
        if (themeFound) {
            // clear the message area
            if ($('#messageArea').val() == '!theme vapor') {
                const scrtMsg = `<li class="list-group-item" data-hash="secret-vapor">
                    <div class="row">
                        <div class="col-12 user-select-none">
                            <p class="mb-0 fw-bold">Secret theme unlocked!</p>
                            <p class="mb-0">You have unlocked the secret theme <strong>Vapor</strong>! Enjoy!</p>
                            <p class="mb-0">This is a secret theme, only a few people know about it. You are one of them now! <i class="far fa-smile-wink"></i></p>
                            <p class="mb-0 text-muted">This message will self-destruct in 10 seconds</p>
                        </div>
                    </div>
                </li>`;
                $('#messages').append(scrtMsg);
                setTimeout(function() {
                    $('li[data-hash="secret-vapor"]').remove();
                }, 10000);
            }
            $('#messageArea').val('');
            $('#themeModal').modal('hide');
            return true;
        }
        // if the theme is not in the themes list, show an error
        $('#error').html('Theme not found');
        $('#messageArea').val('');
        return true;
    } else if ($('#messageArea').val() == '!reload') {
        // clear the message area
        $('#messageArea').val('');
        // clear all messages
        $('#messages').html('');
        window.lastMsgHash = 0;
        // reload the chat
        loadData();
        return true;
    } else if ($('#messageArea').val() == '!logout') {
        // clear the message area
        $('#messageArea').val('');
        // log out
        logOut();
        return true;
    }
}

function setFaviconCount(count) {
    console.log('New messages: ' + count);

    // if faviconCount is enabled, set the favicon count
    if ($('#faviconCountSwitch').is(':checked')) {
        if (window.favicon == undefined) {
            window.favicon = new Favico({
                animation: 'pop'
            });
        }
        if (count > 0) {
            document.title = '(' + count + ') ' + window.config.title;
            window.favicon.badge(count);
        } else {
            document.title = window.config.title;
            window.favicon.badge(0);
        }
    } else {
        document.title = window.config.title;
    }
}

function confetti() {
    $.each($(".particletext.confetti"), function(){
       var confetticount = ($(this).width()/50)*10;
       for(var i = 0; i <= confetticount; i++) {
          $(this).append('<span class="particle c' + $.rnd(1,2) + '" style="top:' + $.rnd(10,50) + '%; left:' + $.rnd(0,100) + '%;width:' + $.rnd(6,8) + 'px; height:' + $.rnd(3,4) + 'px;animation-delay: ' + ($.rnd(0,30)/10) + 's;"></span>');
       }
    });
 }
 
 jQuery.rnd = function(m,n) {
       m = parseInt(m);
       n = parseInt(n);
       return Math.floor( Math.random() * (n - m + 1) ) + m;
 }

$('#sendBtn').click(function() {

if(runcommands($('#messageArea').val())) {
        return;
    }
    sendMessage();
});


// if enter is pressed in the message area, send the message
$('#chatForm').submit(function(e) {
    e.preventDefault();
    if(runcommands($('#messageArea').val())) {
        return;
    }
    // if the send button is not disabled, send the message
    if (!$('#sendBtn').is(':disabled')) {
        sendMessage();
    }
});

// if a checkbox with role switch is changed, save settings
$('.form-check-input').on('change', function() {
    saveSettings();
});


// if image with class thumbnail is hovered, show the image in the center
$(document).on('mouseenter', '.thumbnail', function() {
    // user needs to hover over the image for at least 500ms
    
    var timer = setTimeout(function() {
        // if the image is still hovered, show the image in the center
        if (element.matches(':hover')) {
            centerThumbnail(element);

}
}, 500);
    var element = this;
    // if the mouse leaves the image, clear the timeout
    element.addEventListener('mouseleave', function() {
        clearTimeout(timer);
    });

});

// if image with class thumbnail is hovered, show the image in the center
$(document).on('mouseleave', '.thumbnail', function() {
    // remove the classes
    this.classList.remove('thumbnail-left');
    this.classList.remove('thumbnail-right');
    this.classList.remove('thumbnail-hover');
});

// DEBUG: Open theme modal automatically on document ready
// on document ready
// $(document).ready(function() {
//     // open the theme modal
//     $('#themeModal').modal('show');
// });

$('#openUrlBtn').click(function() {
    // if modal is open, open the url
    if ($('#urlWarningModal').hasClass('show')) {
        window.open(document.getElementById('urlInput').value, '_blank');
    }
});


$('#urlWarningModal').on('hidden.bs.modal', function() {
    // when modal is closed, clear the input
    document.getElementById('urlInput').value = '';
    document.getElementById('urlFavicon').src = '';
});


$('#neverShowAgainBtn').click(function() {
    // change button text to "Are you sure?" and wait for another click
    $('#neverShowAgainBtn').html('Are you sure?');
    $('#neverShowAgainBtn').click(function() {
    // if never show again button is clicked, set the localstorage variable
    saveToStorage('hideUrlWarning', true);
    loadSettings();
    $('#urlWarningModal').modal('hide');
    });
});


$('#darkModeSwitch').on('change', function() {
    if ($(this).is(':checked')) {
        setTheme('dark');
    } else {
        setTheme('light');
    }
    loadBootswatchThemeList();
});

$('#faviconCountSwitch').on('change', function() {
    saveSettings();
});

$('#hideSpamSwitch').on('change', function() {
    saveSettings();
    // if hideSpamSwitch is changed, from elements with data-spam="true", remove/add the message-hidden class
    if ($(this).is(':checked')) {
        // all elements with data-spam="true" should be hidden, if not already
        // loop through all elements with data-spam="true"
        $('[data-spam="true"]').each(function() {
            // if the element is not hidden, hide it
            if (!$(this).hasClass('d-none')) {
                $(this).addClass('d-none');
            }
        });
    } else {
        // all elements with data-spam="true" should be shown, if not already
        // loop through all elements with data-spam="true"
        $('[data-spam="true"]').each(function() {
            // if the element is hidden, show it
            if ($(this).hasClass('d-none')) {
                $(this).removeClass('d-none');
            }
        });
    }
});

// if logout button is clicked
$('#logoutBtn').click(function() {
    logOut();
});

$('#reloadCaptchaBtn').click(function() {
    // reload the iframe
    var src = document.getElementById('iconCaptchaIframe').src;
    // remove the content after &r= and add a new random number
    src = src.split('&r=')[0] + '&r=' + Math.floor(Math.random() * 1000);
    document.getElementById('iconCaptchaIframe').src = src;
});

// on mobile, convert the member list to a modal
// function convertMemberList() {
//     // load data
//     loadData();
// }


// // on window resize
// $(window).resize(function() {
//     convertMemberList();
// });

// on focus
$(window).focus(function() {
    updateActivity();
});

$(document).ready(function() {
    // scroll to bottom
    $('#messages').scrollTop($('#messages')[0].scrollHeight);


    // scroll to bottom when window is resized
    // $(window).resize(function() {
    //     $('#messages').scrollTop($('#messages')[0].scrollHeight);
    // });

    // set the dark mode switch
    if (loadTheme() == 'dark') {
         $('#darkModeSwitch').prop('checked', true);
    }

    // check if settigs are set, if not, run saveSettings to create them
    if (localStorage.getItem('chat-settings') == null) {
        saveSettings();
    }
    
    loadSettings();
    loadBootswatchThemeList();

    if ($('#faviconCountSwitch').is(':checked')) {
        window.favicon = new Favico({
            animation : 'popFade'
        });

        console.log('favicon enabled');

    } else {
        /* add favicons:
            <!-- <link rel="icon"  type="image/png" sizes="32x32" href="./assets/favicon/favicon-32x32.png"> -->
    <!-- <link rel="icon" type="image/png" sizes="16x16" href="./assets/favicon/favicon-16x16.png"> -->
    */
   $('head').append('<link rel="icon" type="image/png" sizes="32x32" href="./assets/favicon/favicon-32x32.png">');
    $('head').append('<link rel="icon" type="image/png" sizes="16x16" href="./assets/favicon/favicon-16x16.png">');
    }

    // convertMemberList();
    updateActivity(true);
});