const enableConsoleLog = false;
// function log(d, o="INFO"){var r;r={INFO:"#82AAFF",WARN:"#FFCB6B",ERROR:"#FF5370",SUCCESS:"#C3E88D",DEBUG:"#d382ff",UNKNOWN:"#abb2bf",background:"#434C5E"},log("%c [NAME OF YOUR APP] "+o+" %c "+d+" ","background: "+r[o]+"; color: "+r.background+"; padding: 1px; border-radius: 3px 0 0 3px;","background: "+r.background+"; color: "+r[o]+"; padding: 1px; border-radius: 0 3px 3px 0;")}
function log(message) {
    if (enableConsoleLog) {
        console.log(message);
    }
}


if (typeof jQuery == 'undefined') {
    // if not, load it
    log('jQuery not found. Loading jQuery...');
    var jq = document.createElement('script');
    jq.type = 'text/javascript';
    // Path to jquery.js file, eg. Google hosted version
    jq.src = '//ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js';
    document.getElementsByTagName('head')[0].appendChild(jq);
} else {
    log('jQuery found.');
}

// check if data-bs-theme is set in the <html> tag
if (document.getElementsByTagName('html')[0].getAttribute('data-bs-theme')) {
    log('data-bs-theme is set in the <html> tag: ' + document.getElementsByTagName('html')[0].getAttribute('data-bs-theme'));
    // if it is, set the theme to the value of data-bs-theme
    // loop through all the document.getElementsByTagName('iconcaptcha-holder')[0].getAttribute('data-bs-theme'); elements and set data-theme= to the value of data-bs-theme, (if it is not already set correctly)
    var IC_bsTheme = document.getElementsByTagName('html')[0].getAttribute('data-bs-theme');
    IC_setTheme(IC_bsTheme);
} else {
    log('data-bs-theme is auto. Not changing the theme.');
}

function IC_setTheme(theme) {
    log('Setting theme to ' + theme);
    // loop through all the <div class="iconcaptcha-holder"> elements and set data-theme= to the value of theme, (if it is not already set correctly)
    document.getElementById('iconCaptcha').setAttribute('data-theme', theme);
}

// if bs theme changes, change the theme of the iconcaptcha-holder divs
document.getElementsByTagName('html')[0].addEventListener('DOMAttrModified', function(e) {
    if (e.attrName === 'data-bs-theme') {
        log('data-bs-theme changed to ' + e.newValue);
        IC_setTheme(e.newValue);
    }
});

var IC_flush = true;
// var IC_LangVersion = [get from class iconcaptcha-holder data-langVersion];
var IC_LangVersion = document.getElementById('iconCaptcha').getAttribute('data-langVersion');
log('IC_LangVersion: ' + IC_LangVersion);
if(IC_flush) {
    // flush the translation file cache
    if (localStorage.getItem('iconCaptchaTranslation')) {
        log('Found translation file in localStorage - checking version...');
        // check if version is not the same
        if (JSON.parse(localStorage.getItem('iconCaptchaTranslation')).version != IC_LangVersion) {
            // clear the lang file cache in localStorage
            localStorage.removeItem('iconCaptchaTranslation');
            // check if the lang file cache was cleared
            if (!localStorage.getItem('iconCaptchaTranslation')) {
                // reload the pagek
                log('Successfully cleared the lang file cache in localStorage');
                // location.reload();
            }
        } else {
            log('Version is the same (' + JSON.parse(localStorage.getItem('iconCaptchaTranslation')).version + ')');
        }
    } else {
        log('No lang file cache found in localStorage');
    }
}





document.addEventListener('DOMContentLoaded', function() {
    // get browser preferred language
    var userLang = navigator.language || navigator.userLanguage;
    var userLangShort = userLang.substring(0, 2);
    log(userLangShort + ' - ' + userLang);


    // if window.IC_settings is not set, set it to the default settings
    if (typeof window.IC_settings == 'undefined') {
        log('window.IC_settings not found. Setting default settings...');
        window.IC_settings = {
            general: {
                endpoint: './assets/inc/captcha-request.php',
                fontFamily: 'inherit',
            },
            security: {
                interactionDelay: 1000,
                hoverProtection: true,
                displayInitialMessage: true,
                initializationDelay: 500,
                incorrectSelectionResetDelay: 3000,
                loadingAnimationDuration: 1000,
            }
        };
    }

    



    var translation;
    // check if the translation file is already saved in localStorage
    if (localStorage.getItem('iconCaptchaTranslation')) {
        // initialize IconCaptcha with the translation file from localStorage
        log('Found translation file in localStorage: ' + localStorage.getItem('iconCaptchaTranslation'));
         translation = JSON.parse(localStorage.getItem('iconCaptchaTranslation'));

         
    log('translation: ' + translation);

    // initialize IconCaptcha
    IC_settings.locale = translation;
        $('.iconcaptcha-widget').iconCaptcha(IC_settings);
    } else {
        log('Translation file not found in localStorage. Getting url: ' + 'https://api.jm26.net/icon-captcha/v1/assets/translations/' + userLangShort + '.json');
    
    // get the translation file from the server
    $.getJSON('./assets/iconcaptcha/translations/get.php?lang=' + userLangShort).done(function (data) {
        // initialize IconCaptcha with the translation file
        log('Found translation file for ' + userLangShort + '.json');
        translation = data;

// save the translation file to localStorage
        localStorage.setItem('iconCaptchaTranslation', JSON.stringify(data));


        
    log('translation: ' + translation);

    // initialize IconCaptcha
    IC_settings.locale = translation;
        $('.iconcaptcha-widget').iconCaptcha(IC_settings);
    }).fail(function () {
        // if the translation file is not found, initialize IconCaptcha with the default translation file
        log('Translation file for ' + userLangShort + '.json not found: ');
        var translation = {
            initialization: {
                verify: 'Verify that you are human.',
                loading: 'Loading challenge...',
            },
            header: 'Select the image displayed the <u>least</u> amount of times',
            correct: 'Verification complete.',
            incorrect: {
                title: 'Uh oh.',
                subtitle: "You've selected the wrong image."
            },
            timeout: {
                title: 'Please wait.',
                subtitle: 'You made too many incorrect selections.'
            }
        };
    });
    }

});