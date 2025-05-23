const serverUrl = 'https://magic.mk';
let sendViaSMS = false, useCaptcha = false, authTypeLink = false;

const safelySetValidationMessage = message => {
    const validationMessage = document.getElementById('validation-message');
    if (validationMessage) {
        validationMessage.textContent = message;
    }
};

const safelyUpdateCardHTML = (element, content) => {
    element.innerHTML = '';
    const parser = new DOMParser();
    const doc = parser.parseFromString(content, 'text/html');
    Array.from(doc.body.children).forEach(child => element.appendChild(child));
};

const sanitizeInput = input => {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#x27;',
        "/": '&#x2F;',
    };
    return input.replace(/[&<>"'/]/ig, match => map[match]);
};

const validateEmail = email => {
    const emailRegex = /^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/i;

    if (!emailRegex.test(email)) return false;
    const [localPart, domain] = email.split('@');
    return !(localPart.length > 64 || domain.length > 255);
};

const validatePhoneNumber = phoneNumber => {
    if (!/^\+[1-9]\d{1,14}$/.test(phoneNumber)) return false;
    const digits = phoneNumber.slice(1);
    return !(digits.length < 7 || digits.length > 15) && !/(\d)\1{5,}/.test(digits);
};

const handleVerificationLinkLogin = (data, input, card) => {
    const safeEmail = sanitizeInput(input.value);
    safelyUpdateCardHTML(card, `<p id="sent-title">A <b>verification link</b> has been sent to: ${safeEmail}</p>`);

    const reverbSettings = {
        appId: data.appId,
        key: data.key,
        host: data.host,
        port: data.port,
        scheme: data.scheme
    };

    const wsUrl = `${reverbSettings.scheme === 'https' ? 'wss' : 'ws'}://${reverbSettings.host}:${reverbSettings.port}/app/${reverbSettings.key}?protocol=7&client=js&version=8.4.0-rc2&flash=false`;
    const socket = new WebSocket(wsUrl);

    socket.onopen = () => {
        console.log("[open] Connection established");
        socket.send(JSON.stringify({
            event: 'pusher:subscribe',
            data: {
                auth: '',
                channel: `authenticated.${data.request_id}`
            }
        }));
    };

    socket.onmessage = event => {
        const response = JSON.parse(event.data);
        if (response.event === 'App\\Events\\UserAuthenticatedEvent') {
            const authData = JSON.parse(response.data);
            if (authData?.token) {
                card.querySelector('#sent-title').textContent = "Login Successful!";
                socket.close();
                redirectToUrl(authData);
                dispatchLoginSuccessEvent(authData);
            }
        }
    };

    socket.onclose = event => {
        console.log(event.wasClean
            ? `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
            : '[close] Connection died');
    };

    socket.onerror = error => console.log(`[error] ${error.message}`);
};

const handleCodeLogin = (data, input, card) => {
    const safeEmail = sanitizeInput(input.value);
    safelyUpdateCardHTML(card, `
        <p id="sent-title">A <b>verification code</b> has been sent to: ${safeEmail}</p>
        <input id="magic-input" placeholder="Enter code" required>
        <button id="magic-submit">Enter Code</button>
        <p id="validation-message"></p>
    `);

    const codeInput = document.getElementById('magic-input');
    const codeSubmit = document.getElementById('magic-submit');

    codeSubmit.addEventListener('click', async (event) => {
        event.preventDefault();
        try {
            const response = await fetch(`${serverUrl}/api/code_validate/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    code: codeInput.value,
                    request_id: data.request_id
                })
            });

            const validationData = await response.json();
            card.querySelector('#sent-title').textContent = validationData.consumed
                ? "Login Successful!"
                : "Login failed, try again!";

            if (validationData.consumed) {
                redirectToUrl(validationData);
                dispatchLoginSuccessEvent(validationData);
            }
        } catch (error) {
            console.error('Error validating code:', error);
            card.querySelector('#sent-title').textContent = "An error occurred. Please try again.";
        }
    });
};

const handleSubmit = async (validate, input, submit, card, event) => {
    event.preventDefault();
    submit.disabled = true;

    if (!validate(input.value)) {
        const invalidInput = sanitizeInput(input.value);
        safelySetValidationMessage(`Invalid input: ${invalidInput}`);
        submit.disabled = false;
        return;
    }

    const data = (!authTypeLink && sendViaSMS) ? {phone: input.value} : {email: input.value};

    if (useCaptcha) {
        const recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            alert('Please complete the reCAPTCHA');
            submit.disabled = false;
            return;
        }
        Object.assign(data, {'g-recaptcha-response': recaptchaResponse});
    }

    if (window.magicmk) Object.assign(data, window.magicmk);

    try {
        const response = await fetch(`${serverUrl}/api/login/`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) throw new Error('Login request failed');

        const responseData = await response.json();
        if (!responseData?.request_id) throw new Error('Invalid response data');

        document.getElementById('RecaptchaField')?.remove();

        authTypeLink
            ? handleVerificationLinkLogin(responseData, input, card)
            : handleCodeLogin(responseData, input, card);
    } catch (error) {
        console.error('Error during login:', error);
        safelyUpdateCardHTML(card, '<div>Something went wrong. <a href=".">Try again.</a></div>');
    } finally {
        submit.disabled = false;
    }
};

const magicAuth = () => {
    let placeholder = "your@email.com"
    let submitText = "Login via mail"
    let autocomplete = "email"

    if (!authTypeLink && sendViaSMS) {
        placeholder = "+46700000000"
        submitText = "Log in via SMS"
        autocomplete = "tel"
    }

    const card = document.getElementById('magic-form');
    if (!card) {
        console.log("Could not find the div with id 'magic-form'");
        return;
    }

    let input = document.getElementById('magic-input');
    let submit = document.getElementById('magic-submit');

    if (!input || !submit) {
        safelyUpdateCardHTML(card, `
            <input id="magic-input" placeholder="${placeholder}" required>
            <button id="magic-submit">${submitText}</button>
            <p id="validation-message"></p>
        `);
        input = document.getElementById('magic-input');
        submit = document.getElementById('magic-submit');
    }

    input.placeholder = input.placeholder || placeholder;
    input.autocomplete = input.autocomplete || autocomplete;
    submit.innerHTML = submit.innerHTML || submitText;

    const validator = input => (!authTypeLink && sendViaSMS) ? validatePhoneNumber(input) : validateEmail(input);
    submit.addEventListener('click', handleSubmit.bind(null, validator, input, submit, card));
};

const redirectToUrl = data => {
    const baseUrl = data.redirect_url || window.magicmk.redirect_url || window.location.href;
    const url = new URL(baseUrl);

    url.searchParams.append('type', 'magic');
    url.searchParams.append('token', data.token);
    url.searchParams.append('project', window.magicmk.project_slug);
    url.searchParams.append('request_id', data.request_id);

    data.redirect_url || window.magicmk.redirect_url
        ? window.location.replace(url.toString())
        : window.location.href = url.toString();
};

const dispatchLoginSuccessEvent = data => {
    dispatchEvent(new CustomEvent('magicauth-success', {
        detail: {
            token: data.token,
            project: window.magicmk.project_slug,
            request_id: data.request_id
        }
    }));
};

async function magic_script() {
    try {
        const response = await fetch(`${serverUrl}/api/project_info/${window.magicmk.project_slug}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
        });

        if (!response.ok) return null;

        const data = await response.json();
        sendViaSMS = Boolean(data.send_via_sms);
        authTypeLink = (Boolean)(data.auth_type_link)

        if (data.use_captcha) {
            useCaptcha = true;
            window.onloadCallback = () => {
                grecaptcha.render('RecaptchaField', {
                    'sitekey': '6Ley-VcqAAAAAIxTx0EV3bKSajPug2D2emuea6gQ'
                });
                magicAuth();
            };

            const recaptchaScript = document.createElement('script');
            recaptchaScript.src = 'https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit';
            recaptchaScript.async = true;
            recaptchaScript.defer = true;
            document.head.appendChild(recaptchaScript);
        } else {
            magicAuth();
        }
    } catch (error) {
        console.error('Error initializing Magic Auth:', error);
    }
}

export default magic_script;
