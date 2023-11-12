const config = {
    messagingSenderId: "1098109170346"
};

firebase.initializeApp(config);

const messaging = firebase.messaging();

messaging.usePublicVapidKey('BNKvxb5U8lDBehgh6xkVapt-jjN1nmLjrJpSXLipGfd_NgGMTDAZ0L1wNOdoJUkBo1XjVaRKKjmyZoVaRflf5sM');
const tokenDivId = 'token_div';
const permissionDivId = 'permission_div';

messaging.onTokenRefresh(() => {
    messaging.getToken().then((refreshedToken) => {
        console.log('Token refreshed.');
        // Indicate that the new Instance ID token has not yet been sent to the
        // app server.
        setTokenSentToServer(false);
        // Send Instance ID token to app server.
        sendTokenToServer(refreshedToken);
        // [START_EXCLUDE]
        // Display new Instance ID token and clear UI of all previous messages.
        resetUI();
        // [END_EXCLUDE]
    }).catch((err) => {
        console.log('Unable to retrieve refreshed token ', err);
        showToken('Unable to retrieve refreshed token ', err);
    });
});

messaging.onMessage((payload) => {
    console.log('Message received. ', payload);
    appendMessage(payload);
    // [END_EXCLUDE]
});

function resetUI() {
    //clearMessages();
    //showToken('loading...');
    messaging.getToken().then((currentToken) => {
        if (currentToken) {
            sendTokenToServer(currentToken);
            //updateUIForPushEnabled(currentToken);
        } else {
            // Show permission request.
            console.log('No Instance ID token available. Request permission to generate one.');
            // Show permission UI.
            //updateUIForPushPermissionRequired();
            setTokenSentToServer(false);
        }
    }).catch((err) => {
        console.log('An error occurred while retrieving token. ', err);
        //showToken('Error retrieving Instance ID token. ', err);
        setTokenSentToServer(false);
    });
    // [END get_token]
}

function showToken(currentToken) {
    // Show token in console and UI.
    const tokenElement = document.querySelector('#token');
    tokenElement.textContent = currentToken;
}

function sendTokenToServer(currentToken) {
    if (!isTokenSentToServer()) {
        console.log('Sending token to server...');
        // TODO(developer): Send the current token to your server.
        addtoken(currentToken);
        setTokenSentToServer(true);
    } else {
        console.log('Token already sent to server so won\'t send it again ' +
            'unless it changes');
    }
}

function isTokenSentToServer() {
    return window.localStorage.getItem('sentToServer') === '1';
}
function setTokenSentToServer(sent) {
    window.localStorage.setItem('sentToServer', sent ? '1' : '0');
}
function showHideDiv(divId, show) {
    const div = document.querySelector('#' + divId);
    if (show) {
        div.style = 'display: visible';
    } else {
        div.style = 'display: none';
    }
}
function requestPermission() {
    mensagemPermissao();
    console.log('Requesting permission...');
}

function deleteToken() {
    // Delete Instance ID token.
    // [START delete_token]
    messaging.getToken().then((currentToken) => {
        messaging.deleteToken(currentToken).then(() => {
            console.log('Token deleted.');
            setTokenSentToServer(false);
            // [START_EXCLUDE]
            // Once token is deleted update UI.
            resetUI();
            // [END_EXCLUDE]
        }).catch((err) => {
            console.log('Unable to delete token. ', err);
        });
        // [END delete_token]
    }).catch((err) => {
        console.log('Error retrieving Instance ID token. ', err);
        showToken('Error retrieving Instance ID token. ', err);
    });
}
// Add a message to the messages element.
function appendMessage(payload) {
    /*const messagesElement = document.querySelector('#messages');
    const dataHeaderELement = document.createElement('h5');
    const dataElement = document.createElement('pre');
    dataElement.style = 'overflow-x:hidden;';
    dataHeaderELement.textContent = 'Received message:';
    dataElement.textContent = JSON.stringify(payload, null, 2);
    messagesElement.appendChild(dataHeaderELement);
    messagesElement.appendChild(dataElement);
    console.log("chegou");*/

    const data = JSON.parse(payload.data.notification);
    const notification = new Notification(data.title, data);
    notification.icon = data.icon;
    notification.onclick = function(event) {
        event.preventDefault();
        window.open(data.click_action, "_blank");
        this.close();
    }
}
// Clear the messages element of all children.
function clearMessages() {
    const messagesElement = document.querySelector('#messages');
    while (messagesElement.hasChildNodes()) {
        messagesElement.removeChild(messagesElement.lastChild);
    }
}
function updateUIForPushEnabled(currentToken) {
    showHideDiv(tokenDivId, true);
    showHideDiv(permissionDivId, false);
    showToken(currentToken);
}
function updateUIForPushPermissionRequired() {
    showHideDiv(tokenDivId, false);
    showHideDiv(permissionDivId, true);
}

resetUI();

function mensagemPermissao() {
    if (Notification.permission === "default") {
        swal.fire({
            title: 'Permissão',
            text: 'Você permite acesso a notificações?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3F51B5',
            confirmButtonText: 'Permitir',
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false,
        })
            .then((result) => {
                if (result.value) {
                    // [START request_permission]
                    Notification.requestPermission().then((permission) => {
                        console.log(permission);
                        if (permission === 'granted') {
                            console.log('Notification permission granted.');
                            // [START_EXCLUDE]
                            // In many cases once an app has been granted notification permission,
                            // it should update its UI reflecting this.
                            resetUI();
                            // [END_EXCLUDE]
                        } else {
                            console.log('Unable to get permission to notify.');
                        }
                    });
                    // [END request_permission]
                } else {
                    //GUARDAR NO BANCO A OPÇÃO DE NÃO RECEBIMENTO DE NOTIFICAÇÕES
                }
            });
    }
}

function addtoken(token) {


        $.ajax({
            type: "POST",
            url: "/menu/addpush",
            data: "token=" + token,
            dataType: "json"
        }).done(function (retorno) {
            if (retorno.error == '0') {
                console.log("token registrado");
            } else {
                console.log("falha ao registrar token");
            }
        }).fail(function (xhr, status, error) {
            console.log("Erro ao registrar dispositivo")
        });
}

